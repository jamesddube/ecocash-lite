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
	 * Takes an SMS string and returns the relevant details (or false on failure)
	 * 
	 * @param string $sms The message string
	 * @return object Parsed SMS (or false on failure)
	 */
	public function isValidSMS($sms)
	{
		
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

	/**
	 * Receives an SMS and processes it
	 * 
	 * @return void
	 */
	public function handle()
	{
		
	}

}