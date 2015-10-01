<?php

namespace Pay4App\EcoCashLite;

class HandleTest extends \TestCase {

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_request_not_authentic()
	{

	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_sms_not_valid()
	{

	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_transfer_already_received()
	{

	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_audit_fails()
	{

	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_all_checks_good_but_transfer_not_expected()
	{
		//main test is that the transfer is saved
	}

	/**
	 * @covers Parser::handle()
	 */
	public function test_where_all_checks_good_and_transfer_expetced()
	{
		//test if transfer gets claimed, and checkout gets completed
	}

}