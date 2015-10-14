<?php

namespace Pay4App\EcoCashLite;

use Illuminate\View\View;
use Illuminate\Mail\Mailer;
use Pay4App\GatewayConfig;
use Pay4App\Services\CheckoutHandler;
use Pay4App\Contracts\CheckoutRepositoryInterface;
use Pay4App\Contracts\TransferRepositoryInterface;

Class Parser {

	private $view;
	private $mailer;
	private $gatewayConfig;
	private $checkoutHandler;
	private $checkoutRepository;
	private $TransferRepository;
	public 	$gatewayID = 'ECOCASHLITE';
	private $auditTransfers;

	private $minutesInOneMonth = 43200;
	private $authenticationFailMessage = 'Authentication Fail';
	private $auditFailMessage = 'Audit Fail';
	private $customerContactEmail;
	private $customerContactEmailLabel;
	private $adminEmailAdress;
 
	public function __construct(GatewayConfig $gatewayConfig, CheckoutHandler $checkoutHandler,
		CheckoutRepositoryInterface $checkoutRepository, TransferRepositoryInterface $transferRepository,
		View $view, Mailer $mailer)
	{
		$this->gatewayConfig = $gatewayConfig;
		$this->checkoutHandler = $checkoutHandler;
		$this->checkoutRepository = $checkoutRepository;
		$this->transferRepository = $transferRepository;
		$this->view = $view;
		$this->mailer = $mailer;
		$this->auditTransfers = $gatewayConfig->paymentGateways[$this->gatewayID]->aux1;

		$this->customerContactEmail = getenv('ECOCASHLITE_CUSTOMER_CONTACT_EMAIL');
		$this->customerContactEmailLabel = getenv('ECOASHLITE_CUSTOMER_CONTACT_EMAIL_LABEL');
		$this->adminEmailAdress = getenv('ECOCASHLITE_ADMIN_EMAIL');
	}

	/**
	 * Receives an SMS and processes it
	 * 
	 * @param array $POST The SmsSync payload
	 * @return void
	 */
	public function handle($POST)
	{
		if(!$this->authenticRequest($POST))	return $this->returnFailure($this->authenticationFailMessage);
		if(!$details = $this->isValidSMS($POST['message'])) return $this->returnSuccess();
		if( $this->transferRepository->alreadyReceived($this->gatewayID, [
            'phonenumber'		=> $details->senderNumber,
            'amount'			=> $details->amount,
            'transactioncode'	=> $details->fullTransactionCode
        ])) return $this->returnSuccess();

		if($this->auditTransfers)
		{
			if(!$this->passesAudit($details)) return $this->returnFailure($this->auditFailMessage);
		}

		$transferId = $this->transferRepository->insert([
            'gateway'       => $this->gatewayID,
            'phonenumber'   => $details->senderNumber,
            'sendername'    => $details->senderName,
            'amount'        => $details->amount,
            'transactioncode' => $details->fullTransactionCode,
            'checkout'      => null,
            'balance'       => $details->newBalance
        ]);
		
		//Check if expected
		if (!$checkout = $this->checkoutRepository->getRecentCheckouts($this->gatewayID, $this->minutesInOneMonth,
			[
				'completed' 		=> false,
				'phonenumber'		=> $details->senderNumber,
				'held'				=> false,//Cancelled payments are held
            ])) return $this->returnSuccess();
		//Claim transfer
		$this->checkoutRepository->update($checkout->id,
			[
				'transfer'		=>	$transferId,
				'phonenumber' 	=>	$details->senderNumber,
            	'transactioncode' => $details->fullTransactionCode,
            ]);
        $this->transferRepository->update($transferId, ['checkout'=>$checkout->id]);

        //If amount correct, complete checkout
        if ((float)$details->amount == $checkout->amount)
        {
        	$this->checkoutHandler->complete($checkout->id, []);
         	return $this->returnSuccess();
        }

        //We get here, amount is wrong. Email parties
        $this->checkoutRepository->update($checkout->id, [
        	'held'=>true,
        	'status'=>'wrongamount',
        ]);
        //Email admin
        $subject = $this->view->make('ecocashlite::wrongAmountAdminEmailSubject', ['checkout'=>$checkout, 'transfer'=>$details]);
        $this->mailer->send(
				'ecocashlite::wrongAmountAdminEmail',
				[
					'checkout' => $checkout,
					'transfer' => $details
				],
				function ($m) use ($checkout, $details) {
					$m->to($this->adminEmailAdress)
						->subject($subject->render())
						->from($this->customerContactEmail, $this->customerContactEmailLabel)
						->replyTo($this->customerContactEmail, $this->customerContactEmailLabel);
				});
        //Email buyer
        $subject = $this->view->make('ecocashlite::wrongAmountBuyerEmailSubject', [
	        			'checkout'=>$checkout,
	        			'transfer'=>$details
	        		])->render();
        $this->mailer->send(
				'ecocashlite::wrongAmountBuyerEmail',
				[
					'checkout' => $checkout,
					'transfer' => $details
				],
				function ($m) use ($checkout, $details) {
					$m->to($checkout->email)
						->subject($subject->render())
						->from($this->customerContactEmail, $this->customerContactEmailLabel)
						->replyTo($this->customerContactEmail, $this->customerContactEmailLabel);
				});
        return $this->returnSuccess();
	}

	/**
	 * Validates the SmsSync payload
	 *
	 * @param array $POST the SmsSync payload
	 * @return bool
	 */
	public function authenticRequest($POST)
	{
		if (!(
			isset($POST['from']) &&
			isset($POST['message']) &&
			isset($POST['message_id']) &&
			isset($POST['secret']) &&
			isset($POST['sent_timestamp']) &&
			isset($POST['device_id'])
		)) return false;

		if($POST['device_id'] !== $this->gatewayConfig->paymentGateways[$this->gatewayID]->publicKey) return false;
		if($POST['secret'] !== $this->gatewayConfig->paymentGateways[$this->gatewayID]->secretKey) return false;
		return true;
	}

	/**
	 * Takes an SMS string and returns the relevant details (or false on failure)
	 * (Will also audit against balance where option is set)
	 * 
	 * @param string $sms The message string
	 * @return object Parsed SMS (or false on failure)
	 */
	public function isValidSMS($sms)
	{
		if (self::isMerchantMessage($sms)) return self::parseMerchantMessage($sms);
		return false;
	}

	/**
	 * Given transfer details - and with the option validated - checks if
	 * this transfer's amount and new balance fits arithmetically next to the
	 * most recent transfer before this one
	 * 
	 * @return bool
	 */
	public function passesAudit($transfer)
	{
		if(!$this->auditTransfers) return true;
		if(!$mostRecent = $this->transferRepository->mostRecent($this->gatewayID)) return true;
		return ($mostRecent->balance + $transfer->amount == $transfer->newBalance);
	}

	/**
     * Determines if an SMS message is an EcoCash merchant line payment notification
     *      
     * @param string 	$sms The SMS
     * @return bool
     */
	public static function isMerchantMessage($sms)
	{		
		$pattern = "/You have received [^0-9 ]?([0-9.]+) from ([0-9]+) -(.+)\. Approval Code: (.+)\. New wallet balance: \\$([0-9]+\.[0-9]+)/";		
		return preg_match($pattern, $sms) ? true : false;
	}

	/**
	 * Parses and returns transaction information from merchant message
	 * 
	 * @param string sms
	 * @return object
	 */
	public static function parseMerchantMessage($sms)
	{		
		$pattern = "/You have received [^0-9 ]?([0-9]+\.[0-9]+) from ([0-9]+) -(.+)\. Approval Code: (.+)\. New wallet balance: \\$([0-9]+\.[0-9]+)/";		
		preg_match($pattern, $sms, $outputArray);		
		$details = new \StdClass();
		$details->amount = $outputArray[1];
		$details->senderNumber = $outputArray[2];
		$details->senderName = $outputArray[3];
		$details->fullTransactionCode = $outputArray[4];
		$details->newBalance = $outputArray[5];
		$details->transactionCode = substr($details->fullTransactionCode,
										strrpos($details->fullTransactionCode, '.') + 1);
		return $details;
	}

	/**
	 * Returns success message to the SMS sender
	 * 
	 * @return void
	 */
	public function returnSuccess()
	{
		return json_encode(['payload'=>['success'=>true, 'error'=>null]]);
	}

	/**
	 * Returns failure message to the SMS sender, so that sender may retry later
	 * 
	 * @param string $errorMessage Reason for failure (if any)
	 * @return void
	 */
	public function returnFailure($errorMessage = '')
	{
		return json_encode(['payload'=>['success'=>false, 'error'=>$errorMessage]]);
	}

	

}