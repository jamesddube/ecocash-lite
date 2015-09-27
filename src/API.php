<?php

namespace Pay4App\EcoCashLite;

use Pay4App\GatewayConfig;
use Pay4App\Services\CheckoutHandler;
use Pay4App\Contracts\CheckoutRepositoryInterface;

Class API {

	private $gatewayConfig;
	private $checkoutHandler;
	private $checkoutRepository;
	public 	$errors;
	public 	$gatewayID = 'ECOCASHLITE';

	public function __construct(GatewayConfig $gatewayConfig, CheckoutHandler $checkoutHandler,
							CheckoutRepositoryInterface $checkoutRepository)
	{
		$this->gatewayConfig = $gatewayConfig;
		$this->checkoutHandler = $checkoutHandler;
		$this->checkoutRepository = $checkoutRepository;
	}

	public function processCheckout($checkoutDetails)
	{
		
	}
	
	/**
	 * Returns name of button view to show in combined checkout
	 *
	 * @return string
	 */
	public function buttonViewName()
	{
		return 'ecocashlite::payButton';
	}
}