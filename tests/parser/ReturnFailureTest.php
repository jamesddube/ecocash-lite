<?php

namespace Pay4App\EcoCashLite;

class ReturnFailureTest extends \TestCase {

	/**
	 * @covers Parser::returnFailure()
	 */
	public function test_where_no_error_message_specified()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
	    $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
	    $GatewayConfig->secretKey = '123456';
	    $GatewayConfig->paymentGateways = [
	        'ECOCASHLITE' => (object)[
	            'publicKey' => 'ABCD', 'secretKey' => 'XYZ', 'aux1' => FALSE,
	        ],
	    ];
	    $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
	    $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
	    $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
	    $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository); 
	    $this->assertEquals(json_encode(['payload'=>['success' =>false, 'error'=>'']]), $parser->returnFailure());
	}

	/**
	 * @covers Parser::returnFailure()
	 */
	public function test_where_error_message_specified()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
	    $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
	    $GatewayConfig->secretKey = '123456';
	    $GatewayConfig->paymentGateways = [
	        'ECOCASHLITE' => (object)[
	            'publicKey' => 'ABCD', 'secretKey' => 'XYZ', 'aux1' => FALSE,
	        ],
	    ];
	    $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
	    $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
	    $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
	    $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
	    $this->assertEquals(json_encode(['payload'=>['success'=>false, 'error'=>'test']]),
	    		$parser->returnFailure('test'));
	}

}