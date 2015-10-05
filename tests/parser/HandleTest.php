<?php

namespace Pay4App\EcoCashLite;

class HandleTest extends \TestCase {

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_request_not_authentic()
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

		$POST = [
			'message' 	    => 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp'=> '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertEquals($parser->returnFailure('Authentication Fail'), $parser->handle($POST));
	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_sms_not_valid()
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

        $POST = [
			'from' 		    => '+2637704',
			'message' 	    => 'Phones at discounted prices!',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp'=> '123456789',
			'device_id' 	=> 'ABCD',
		];
        //The networks also send non-transfer messages from the same numbers
        $this->assertEquals($parser->returnSuccess(), $parser->handle($POST));
	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_transfer_already_received()
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
        $TransferRepository->shouldReceive('alreadyReceived')
        					->with('ECOCASHLITE', [
            					'phonenumber'      => '772345678',
            					'amount'           => 12.34,
            					'transactioncode'  => 'SRTC150923'
            				])
            				->andReturn(true);
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
        $POST = [
			'from' 		    => '+2637704',
			'message' 	    => 'You have received $12.34 from 772345678 -Paul Smith. Approval Code: '.
							'SRTC150923. New wallet balance: $32.90',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp'=> '123456789',
			'device_id' 	=> 'ABCD',
		];
        //Already received
        $this->assertEquals($parser->returnSuccess(), $parser->handle($POST));

	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_audit_fails()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'aux1'   	=> true,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        $TransferRepository->shouldReceive('alreadyReceived')
        					->with('ECOCASHLITE', [
            					'phonenumber'      => '772345678',
            					'amount'           => 12.34,
            					'transactioncode'  => 'SRTC150923'
            				])
            				->andReturn(false);
        $TransferRepository->shouldReceive('mostRecent')
        					->with('ECOCASHLITE')
        					->andReturn((object)[
        						'amount'	=> 10,
        						'balance'	=> 10,
        					]);
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

        $POST = [
			'from' 		    => '+2637704',
			'message' 	    => 'You have received $12.34 from 772345678 -Paul Smith. Approval Code: '.
							'SRTC150923. New wallet balance: $32.90',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp'=> '123456789',
			'device_id' 	=> 'ABCD',
		];
        
        $this->assertEquals($parser->returnFailure('Audit Fail'), $parser->handle($POST));
	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_all_checks_good_but_transfer_not_expected()
	{
		//main test is that the transfer is saved
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'aux1'   	=> true,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        
        $CheckoutRepository->shouldReceive('getRecentCheckouts')
        					->with('ECOCASHLITE', 43200, [
	        					'completed' 		=> false,
	            				'phonenumber'		=> '772345678',
					            'transactioncode'	=> 'SRTC150923'
				            ])
        					->andReturn(null);
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        $TransferRepository->shouldReceive('alreadyReceived')
        					->with('ECOCASHLITE', [
            					'phonenumber'      => '772345678',
            					'amount'           => 12.34,
            					'transactioncode'  => 'SRTC150923'
            				])
            				->andReturn(false);
        $TransferRepository->shouldReceive('mostRecent')
        					->with('ECOCASHLITE')
        					->andReturn((object)[
        						'amount'	=> 10,
        						'balance'	=> 20.56,
        					]);

        $TransferRepository->shouldReceive('insert')
        					->with([
        						'gateway'			=> 'ECOCASHLITE',
						        'phonenumber'		=> '772345678',
						        'sendername'		=> 'Paul Smith',
						        'amount'			=> 12.34,
						        'transactioncode'	=> 'SRTC150923',
						        'checkout'			=> null,
						        'balance'			=> 32.90,
        					])
        					->andReturn(2);
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);
        
        $POST = [
			'from' 		    => '+2637704',
			'message'       => 'You have received $12.34 from 772345678 -Paul Smith. Approval Code: '.
							'SRTC150923. New wallet balance: $32.90',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp'=> '123456789',
			'device_id' 	=> 'ABCD',
		];
        
        $this->assertEquals($parser->returnSuccess(), $parser->handle($POST));
	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_all_checks_good_and_transfer_expected_but_amount_is_wrong()
	{
		//test if transfer gets claimed, and user/admin are emailed
	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_all_checks_good_and_transfer_expected_and_amount_is_correct()
	{
		//test if transfer gets claimed, and checkout gets completed
	}

}