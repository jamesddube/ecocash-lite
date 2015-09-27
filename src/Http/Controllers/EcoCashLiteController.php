<?php

namespace Pay4App\EcoCashLite\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Pay4App\Http\Requests;
use Pay4App\Http\Controllers\Controller;
use Pay4App\EcoCashLite\API;

class EcoCashLiteController extends Controller
{
	
	public function __construct(API $api)
	{
		$this->api = $api;
	}
}
