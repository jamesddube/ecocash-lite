<?php

namespace Pay4App\EcoCashLite;

class IsValidSMSTest extends \TestCase {

	/**
	 * @covers Parser::isValidSMS()
	 */
	public function test_where_is_not_merchant_line_transfer_sms()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'aux1'   	=> FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
        
        $this->assertFalse($parser->isValidSMS("You have received 5.00 from 772345678 -LOREM IPSUM. ".
        	"Approval Code: MP123456.7890.123456. New wallet balance: 134.28. Hee what what"));
	}

	/**
	 * @covers Parser::isValidSMS()
	 */
	public function test_where_is_merchant_line_transfer_sms()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'aux1'   	=> FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
        
        $payment = $parser->isValidSMS("You have received $5.00 from 772345678 -LOREM IPSUM. Approval Code: ".
        	"MP123456.7890.123456. New wallet balance: $134.28");

        $this->assertEquals(5.00, $payment->amount);
		$this->assertEquals(772345678, $payment->senderNumber);
		$this->assertEquals('LOREM IPSUM', $payment->senderName);
		$this->assertEquals('MP123456.7890.123456', $payment->fullTransactionCode);
		$this->assertEquals(134.28, $payment->newBalance);
		$this->assertEquals('123456', $payment->transactionCode);

	}

}