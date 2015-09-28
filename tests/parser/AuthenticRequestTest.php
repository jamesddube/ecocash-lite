<?php

namespace Pay4App\EcoCashLite;

class AuthenticRequestTest extends \TestCase {

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_from_is_missing()
	{
		
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp' => '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_message_is_missing()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp' => '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_message_id_is_missing()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'secret' 		=> 'XYZ',
			'sent_timestamp' => '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_secret_is_missing()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'sent_timestamp' => '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_sent_timestamp_is_missing()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'device_id' 	=> 'ABCD',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_device_id_is_missing()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp' => '123456789',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_device_id_is_wrong()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp' => '123456789',
			'device_id' 	=> '1122',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_field_secret_is_wrong()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'EWYEYU',
			'sent_timestamp' => '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertFalse($parser->authenticRequest($POST));
	}

	/**
	 * @covers Parser::authenticRequest()
	 */
	public function test_where_everything_is_correct()
	{
		$GatewayConfig = \Mockery::mock('Pay4App\GatewayConfig');
        $GatewayConfig->checkoutsRoot = 'http://acme.com/checkout';
        $GatewayConfig->secretKey = '123456';
        $GatewayConfig->paymentGateways = [
            'ECOCASHLITE' => (object)[
                    'publicKey' => 'ABCD',
                    'secretKey' => 'XYZ',
                    'sandbox'   => FALSE,
            ],
        ];
        $CheckoutHandler = \Mockery::mock('Pay4App\Services\CheckoutHandler');
        $CheckoutRepository = \Mockery::mock('Pay4App\Contracts\CheckoutRepositoryInterface');
        $TransferRepository = \Mockery::mock('Pay4App\Contracts\TransferRepositoryInterface');
        
        $parser = new Parser($GatewayConfig, $CheckoutHandler, $CheckoutRepository, $TransferRepository);

		$POST = [
			'from' 		=> '+2637704',
			'message' 	=> 'Lorem ipsum dolor amet',
			'message_id' 	=> '2',
			'secret' 		=> 'XYZ',
			'sent_timestamp' => '123456789',
			'device_id' 	=> 'ABCD',
		];
		$this->assertTrue($parser->authenticRequest($POST));
	}

}