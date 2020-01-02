<?php
/* Copyright (c) 2016 Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessage.php");

/**
 *
 * @author            Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleMessageTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ilSystemStyleMessage
     */
    protected $ilSystemStyleMessage;

    /**
     * @var messageString
     */
    protected $messageString = "This is a message";

    public function testConstructor()
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
        $this->assertTrue($this->ilSystemStyleMessage->getMessage() === $this->messageString);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_SUCCESS);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);
        $this->assertTrue($this->ilSystemStyleMessage->getMessage() === $this->messageString);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_ERROR);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);
        $this->assertTrue($this->ilSystemStyleMessage->getMessage() === $this->messageString);
    }

    public function testGetAndSetMessage()
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->ilSystemStyleMessage->setMessage("This is an altered message");
        $this->assertTrue($this->ilSystemStyleMessage->getMessage() === "This is an altered message");
    }

    public function testGetAndSetTypeID()
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->ilSystemStyleMessage->setTypeId(ilSystemStyleMessage::TYPE_SUCCESS);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);

        $this->ilSystemStyleMessage->setTypeId(ilSystemStyleMessage::TYPE_ERROR);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);

        $this->ilSystemStyleMessage->setTypeId(ilSystemStyleMessage::TYPE_INFO);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
    }

    public function testGetMessageOutput()
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->assertTrue($this->ilSystemStyleMessage->getMessageOutput() === "This is a message</br>");
    }
}
