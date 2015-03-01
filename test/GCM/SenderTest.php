<?php

/**
 * User: meier
 * Date: 01.03.15
 * Time: 13:04
 */
namespace GCM;

class SenderTest extends \PHPUnit_Framework_TestCase
{

    private $Sender;

    /**
     * @setup
     */
    function setup() {
        $this->Sender = new Sender("API_KEY");
    }


    function testHandleHTTPCode200()
    {
        $this->assertTrue(Sender::handleHttpCode(200));
    }

    /**
     * @expectedException Exception
     */
    function testHandleHTTPCode400()
    {
        Sender::handleHttpCode(400);
    }

    /**
     * @expectedException Exception
     */
    function testHandleHTTPCode5XX()
    {
        Sender::handleHttpCode(555);
    }

    /**
     * @expectedException Exception
     */
    function testHandleHTTPCodeNull()
    {
        Sender::handleHttpCode(false);
    }

    /**
     * @expectedException Exception
     */
    function testBuildJSONWithoutRecipients() {
        $sender = new Sender("API_KEY");
        $sender->sendMessage("some_message");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetTimeToLiveToValueThatIsToLow() {
        $sender = new Sender("API_KEY");
        $sender->setTimeToLive(-1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetTimeToLiveToAValueThatIsToHigh() {
        $sender = new Sender("API_KEY");
        $sender->setTimeToLive(2419201);
    }


    function testSetTimeToLiveToCorrectValue() {
        $sender = new Sender("API_KEY");
        $this->assertTrue($sender->setTimeToLive(2500));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testSetNegativeTimeout() {
        $sender = new Sender("API_KEY");
        $sender->setTimeout(-1);
    }

    function testSetTimeoutToInfinity() {
        $sender = new Sender("API_KEY");
        $this->assertTrue($sender->setTimeout(0));
    }

    function testSetTimeoutNormal() {
        $sender = new Sender("API_KEY");
        $this->assertTrue($sender->setTimeout(2500));
    }
}

 