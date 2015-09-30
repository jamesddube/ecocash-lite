<?php

namespace Pay4App\EcoCashLite;

class PassesAuditTest extends \TestCase {

	/**
	 * @covers Parser::passesAudit()
	 */
	public function test_where_audit_option_is_false()
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
	    
	    $this->assertTrue($parser->passesAudit(
	    	(object)[
	    		'amount' 		=> 2.00,
				'senderNumber' 	=> 772345678,
				'senderName' 	=> 'John Doe',
				'fullTransactionCode' => 'LETR083.123.C123',
				'newBalance' 	=> 11.00,
				'transactionCode' => 'C123',
	    	]	
	    ));
	}

	/**
	 * @covers Parser::passesAudit()
	 */
	public function test_where_audit_option_is_true_and_this_is_first_ever_transfer()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
	    $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
	    $GatewayConfig->secretKey = '123456';
	    $GatewayConfig->paymentGateways = [
	        'ECOCASHLITE' => (object)[
	                'publicKey' => 'ABCD',
	                'secretKey' => 'XYZ',
	                'aux1'   	=> TRUE,
	        ],
	    ];
	    $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
	    $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
	    $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');        
	    $TransferRepository->shouldReceive('mostRecent')->once()->andReturn(null);
	    $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
	    
	    $this->assertTrue($parser->passesAudit(
	    	(object)[
	    		'amount' 		=> 2.00,
				'senderNumber' 	=> 772345678,
				'senderName' 	=> 'John Doe',
				'fullTransactionCode' => 'LETR083.123.C123',
				'newBalance' 	=> 11.00,
				'transactionCode' => 'C123',
	    	]	
	    ));
	}

	/**
	 * @covers Parser::passesAudit()
	 */
	public function test_where_audit_option_is_true_and_balance_audit_fails()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
	    $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
	    $GatewayConfig->secretKey = '123456';
	    $GatewayConfig->paymentGateways = [
	        'ECOCASHLITE' => (object)[
	                'publicKey' => 'ABCD',
	                'secretKey' => 'XYZ',
	                'aux1'   	=> TRUE,
	        ],
	    ];
	    $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
	    $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
	    $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');        
	    $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
	    
	    $TransferRepository->shouldReceive('mostRecent')
	    					->once()
	    					->with('ECOCASHLITE')
	    					->andReturn((object)[
	    							'phonenumber' 		=> '770112233',
	    							'sendername' 		=> 'Lorem Ipsum',
	    							'transactioncode' 	=> 'SWTC150924',
	    							'amount'        	=> 5.00,
	    							'balance'       	=> 22.00,
	    							'checkout'      	=> null,
	    						]);

	    $this->assertFalse($parser->passesAudit(
	    	(object)[
	    		'amount' 		=> 2.00,
				'senderNumber' 	=> 772345678,
				'senderName' 	=> 'John Doe',
				'fullTransactionCode' => 'LETR083.123.C123',
				'newBalance' 	=> 11.00,
				'transactionCode' => 'C123',
	    	]	
	    ));
	}

	/**
	 * @covers Parser::passesAudit()
	 */
	public function test_where_audit_option_is_true_and_balance_audit_passes()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
	    $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
	    $GatewayConfig->secretKey = '123456';
	    $GatewayConfig->paymentGateways = [
	        'ECOCASHLITE' => (object)[
	                'publicKey' => 'ABCD',
	                'secretKey' => 'XYZ',
	                'aux1'   	=> TRUE,
	        ],
	    ];
	    $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
	    $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
	    $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');        
	    $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
	    
	    $TransferRepository->shouldReceive('mostRecent')
	    					->once()
	    					->with('ECOCASHLITE')
	    					->andReturn((object)[
	    							'phonenumber' 		=> '770112233',
	    							'sendername' 		=> 'Lorem Ipsum',
	    							'transactioncode' 	=> 'SWTC150924',
	    							'amount'        	=> 5.00,
	    							'balance'       	=> 22.00,
	    							'checkout'      	=> null,
	    						]);

	    $this->assertTrue($parser->passesAudit(
	    	(object)[
	    		'amount' 		=> 2.00,
				'senderNumber' 	=> 772345678,
				'senderName' 	=> 'John Doe',
				'fullTransactionCode' => 'LETR083.123.C123',
				'newBalance' 	=> 24.00,
				'transactionCode' => 'C123',
	    	]	
	    ));
	}

}