<?php

namespace Pay4App\EcoCashLite;

use Pay4App\GatewayConfig;
use Pay4App\Services\CheckoutHandler;
use Pay4App\Contracts\CheckoutRepositoryInterface;

Class Parser {

	private $gatewayConfig;
	private $checkoutHandler;
	private $checkoutRepository;
	public 	$gatewayID = 'ECOCASHLITE';

	public function __construct(GatewayConfig $gatewayConfig, CheckoutHandler $checkoutHandler,
							CheckoutRepositoryInterface $checkoutRepository)
	{
		$this->gatewayConfig = $gatewayConfig;
		$this->checkoutHandler = $checkoutHandler;
		$this->checkoutRepository = $checkoutRepository;
	}

	/**
	 * Receives an SMS and processes it
	 * 
	 * @return void
	 */
	public function handle()
	{
		
	}

	/**
	 * Validates the SmsSync payload
	 *
	 * @param array $POST the SmsSync payload
	 * @return bool
	 */
	public function authenticRequest($POST)
	{
		
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
		
	}

	/**
     * Determines if an SMS message is an EcoCash merchant line payment notification
     *      
     * @param string 	$sms The SMS
     * @return bool
     */
	public static function isMerchantMessage($sms)
	{		
		$pattern = "/You have received \\$([0-9.]+) from ([0-9]+) -(.+)\. Approval Code: (.+)\. New wallet balance: \\$([0-9]+\.[0-9]+)/";		
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
		$pattern = "/You have received \\$([0-9]+\.[0-9]+) from ([0-9]+) -(.+)\. Approval Code: (.+)\. New wallet balance: \\$([0-9]+\.[0-9]+)/";		
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
     * Determines if an SMS message is an EcoCash personal line payment notification
     *      
     * @param string 	$sms The SMS
     * @return bool
     */
	public static function isPersonalMessage($sms)
	{		
		$pattern = "/EcoCash: Transfer Confirmation\. \\$\s?([0-9]+\.[0-9]+) from (.+)\.? Approval Code: (.+)\. New wallet balance: \\$\s?([0-9]+\.[0-9]+)/";		
		return preg_match($pattern, $sms) ? true : false;
	}

	/**
	 * Parses and returns transaction information from personal message
	 * 
	 * @param string $sms
	 * @return object
	 */
	public static function parsePersonalMessage($sms)
	{		
		$pattern = "/EcoCash: Transfer Confirmation\. \\$\s?([0-9]+\.[0-9]+) from (.+)\.? Approval Code: (.+)\. New wallet balance: \\$\s?([0-9]+\.[0-9]+)/";		
		preg_match($pattern, $sms, $outputArray);		
		$details = new \StdClass();
		$details->amount = $outputArray[1];
		$details->senderName = $outputArray[2];
		$details->fullTransactionCode = $outputArray[3];
		$details->newBalance = $outputArray[4];
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

	}

	/**
	 * Returns failure message to the SMS sender, so that sender may retry later
	 * 
	 * @return void
	 */
	public function returnFailure()
	{
		
	}

	

}