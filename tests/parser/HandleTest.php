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
        $View = \Mockery::mock('Illuminate\View\View');
        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);

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
        $View = \Mockery::mock('Illuminate\View\View');
        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);

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
        $View = \Mockery::mock('Illuminate\View\View');
        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);
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
        $View = \Mockery::mock('Illuminate\View\View');
        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);

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
					            'held'              => false,
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
        $View = \Mockery::mock('Illuminate\View\View');
        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);
        
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
    public function test_where_all_checks_good_and_transfer_expected_and_amount_is_correct()
    {
        //test if transfer gets claimed, and checkout gets completed
        $GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'aux1'      => true,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutHandler->shouldReceive('complete')
                            ->with(45, [])
                            ->andReturn(null);

        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');        
        $CheckoutRepository->shouldReceive('getRecentCheckouts')
                            ->with('ECOCASHLITE', 43200, [
                                'completed'         => false,
                                'phonenumber'       => '772345678',
                                'held'              => false,
                            ])
                            ->andReturn((object)[
                                'id'        => 45,
                                'amount'    => 12.34,
                            ]);
        $CheckoutRepository->shouldReceive('update')
                            ->with(45, [
                                'transfer'          => 2,
                                'phonenumber'       => '772345678',
                                'transactioncode'   => 'SRTC150923',
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
                                'amount'    => 10,
                                'balance'   => 20.56,
                            ]);
        $TransferRepository->shouldReceive('update')
                            ->with(2, ['checkout' => 45])
                            ->andReturn(null);

        $TransferRepository->shouldReceive('insert')
                            ->with([
                                'gateway'           => 'ECOCASHLITE',
                                'phonenumber'       => '772345678',
                                'sendername'        => 'Paul Smith',
                                'amount'            => 12.34,
                                'transactioncode'   => 'SRTC150923',
                                'checkout'          => null,
                                'balance'           => 32.90,
                            ])
                            ->andReturn(2);
        $View = \Mockery::mock('Illuminate\View\View');
        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);
        
        $POST = [
            'from'          => '+2637704',
            'message'       => 'You have received $12.34 from 772345678 -Paul Smith. Approval Code: '.
                            'SRTC150923. New wallet balance: $32.90',
            'message_id'    => '2',
            'secret'        => 'XYZ',
            'sent_timestamp'=> '123456789',
            'device_id'     => 'ABCD',
        ];
        
        $this->assertEquals($parser->returnSuccess(), $parser->handle($POST));
    }

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_all_checks_good_and_transfer_expected_but_amount_is_wrong()
	{
		//test if transfer gets claimed, and user/admin are emailed
        $GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'aux1'      => true,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutHandler->shouldReceive('complete')
                            ->with(45, [])
                            ->andReturn(null);

        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');        
        $CheckoutRepository->shouldReceive('getRecentCheckouts')
                            ->with('ECOCASHLITE', 43200, [
                                'completed'         => false,
                                'phonenumber'       => '772345678',
                                'held'              => false,
                            ])
                            ->andReturn((object)[
                                'id'        => 45,
                                'amount'    => 12.34,
                            ]);
        $CheckoutRepository->shouldReceive('update')
                            ->with(45, [
                                'transfer'          => 2,
                                'phonenumber'       => '772345678',
                                'transactioncode'   => 'SRTC150923',
                            ])->andReturn(null);
        $CheckoutRepository->shouldReceive('update')
                            ->with(45, [
                                'held'      => true,
                                'status'    => 'wrongamount',
                            ])->andReturn(null);

        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        $TransferRepository->shouldReceive('alreadyReceived')
                            ->with('ECOCASHLITE', [
                                'phonenumber'      => '772345678',
                                'amount'           => 12.44,
                                'transactioncode'  => 'SRTC150923'
                            ])
                            ->andReturn(false);
        $TransferRepository->shouldReceive('mostRecent')
                            ->with('ECOCASHLITE')
                            ->andReturn((object)[
                                'amount'    => 10,
                                'balance'   => 20.56,
                            ]);
        $TransferRepository->shouldReceive('update')
                            ->with(2, ['checkout' => 45])
                            ->andReturn(null);

        $TransferRepository->shouldReceive('insert')
                            ->with([
                                'gateway'           => 'ECOCASHLITE',
                                'phonenumber'       => '772345678',
                                'sendername'        => 'Paul Smith',
                                'amount'            => 12.44,
                                'transactioncode'   => 'SRTC150923',
                                'checkout'          => null,
                                'balance'           => 33.00,
                            ])
                            ->andReturn(2);
        $View = \Mockery::mock('Illuminate\View\View');
        //Admin email
        $View->shouldReceive('make')
                ->with('ecocashlite::wrongAmountAdminEmailSubject', [
                    'checkout' => (object)['id'=>45, 'amount'=>12.34],
                    'transfer' => (object)[
                        'amount'        => '12.44',
                        'senderNumber'  => '772345678',
                        'senderName'    => 'Paul Smith',
                        'fullTransactionCode'=> 'SRTC150923',
                        'newBalance'    => '33.00',
                        'transactionCode'   => 'RTC150923',
                    ],
                ])
                ->once()
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('render')
                ->andReturn('Test Subject');
        
        //Buyer email
        $View->shouldReceive('make')
                ->with('ecocashlite::wrongAmountBuyerEmailSubject', [
                    'checkout' => (object)['id'=>45, 'amount'=>12.34],
                    'transfer' => (object)[
                        'amount'        => '12.44',
                        'senderNumber'  => '772345678',
                        'senderName'    => 'Paul Smith',
                        'fullTransactionCode'=> 'SRTC150923',
                        'newBalance'    => '33.00',
                        'transactionCode'   => 'RTC150923',
                    ],
                ])
                ->once()
                ->andReturn(\Mockery::self())
                ->getMock()
                ->shouldReceive('render')
                ->andReturn('Test Subject');

        $Mailer = \Mockery::mock('Illuminate\Mail\Mailer');
        putenv('ECOCASHLITE_ADMIN_EMAIL=admin@example.com');
        putenv('ECOCASHLITE_CUSTOMER_CONTACT_EMAIL=support@example.com');
        putenv('ECOCASHLITE_CUSTOMER_CONTACT_EMAIL_LABEL=Support');
        $Mailer->shouldReceive('send');
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository, $View, $Mailer);
        
        $POST = [
            'from'          => '+2637704',
            'message'       => 'You have received $12.44 from 772345678 -Paul Smith. Approval Code: '.
                            'SRTC150923. New wallet balance: $33.00',
            'message_id'    => '2',
            'secret'        => 'XYZ',
            'sent_timestamp'=> '123456789',
            'device_id'     => 'ABCD',
        ];
        
        $this->assertEquals($parser->returnSuccess(), $parser->handle($POST));
	}

}