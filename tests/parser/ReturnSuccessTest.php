<?php

namespace Pay4App\EcoCashLite;

class ReturnSuccessTest extends \TestCase {

	/**
	 * @covers Parser::returnSuccess()
	 */
	public function test_return_value()
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
	    $this->assertEquals(json_encode(['payload'=>['success' =>true, 'error'=>null]]), $parser->returnSuccess());
	}

}