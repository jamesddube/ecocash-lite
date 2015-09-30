<?php

namespace Pay4App\EcoCashLite;

class MessageParsingTest extends \TestCase {

    /**
     * @covers Parser::isMerchantMessage()
     */
    public function testIsMerchantMessage()
    {
        
        $this->assertEquals(true, Parser::isMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28"
        ));

        $this->assertEquals(true, Parser::isMerchantMessage(
            "You have received *5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28."
        ));

        $this->assertEquals(true, Parser::isMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28. Hee what what"
        ));

        $this->assertEquals(true, Parser::isMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM MIDDLENAME IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28. Hee what what"
        ));        

        $this->assertEquals(true, Parser::isMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM TWO MIDDLE NAMES IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28. Hee what what"
        ));

        $this->assertEquals(false, Parser::isMerchantMessage(
            "You have received 5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: 134.28. Hee what what"
        ));

        $this->assertEquals(true, Parser::isMerchantMessage(
            "You have received $5.00 from 772345678 - LOREM IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28. Hee what what"
        ));        

    }

    /**
     * @covers Parser::parseMerchantMessage()
     */
    public function testParseMerchantMessage()
    {

        $parts = Parser::parseMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.123456. ".
            "New wallet balance: $134.28. Hee what what");

        $this->assertEquals(5.00, $parts->amount);
        $this->assertEquals(772345678, $parts->senderNumber);
        $this->assertEquals('LOREM IPSUM', $parts->senderName);
        $this->assertEquals('MP123456.7890.123456', $parts->fullTransactionCode);
        $this->assertEquals('123456', $parts->transactionCode);
        $this->assertEquals(134.28, $parts->newBalance);

        $parts2 = Parser::parseMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.EP3456. ".
            "New wallet balance: $134.28. Hee what what");
        $this->assertEquals('EP3456', $parts2->transactionCode);

        $parts3 = Parser::parseMerchantMessage(
            "You have received $5.00 from 772345678 -LOREM IPSUM. Approval Code: MP123456.7890.ABCDEF. ".
            "New wallet balance: $134.28. Hee what what");
        $this->assertEquals('ABCDEF', $parts3->transactionCode);

    }

}
