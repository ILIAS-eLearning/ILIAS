<?php
/* Copyright (c) 2016 Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessage.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessageStack.php");

/**
 *
 * @author            Tomasz Kolonko <thomas.kolonko@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleMessageStackTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ilSystemStyleMessage
     */
    protected $ilSystemStyleMessage;

    /**
     * @var messageStringOne
     */
    protected $messageStringOne = "This is a message";

    /**
     * @var messageStringTwo
     */
    protected $messageStringTwo = "Godzilla has taken over the world.";

    /**
     * @var messageStringThree
     */
    protected $messageStringThree = "A small, cute cat destroyed Godzilla.";

    /**
     * @var ilSystemStyleMessage[]
     */
    protected $messages = array();

    /**
     * @var ilSystemStyleMessageStack
     */
    protected $ilSystemStyleMessageStack;

    public function testPrependMessage()
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->prependMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringTwo, ilSystemStyleMessage::TYPE_SUCCESS);
        $this->ilSystemStyleMessageStack->prependMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringThree, ilSystemStyleMessage::TYPE_ERROR);
        $this->ilSystemStyleMessageStack->prependMessage($this->ilSystemStyleMessage);
        $this->messages = $this->ilSystemStyleMessageStack->getMessages();

        $this->assertTrue($this->messages[0]->getMessage() === $this->messageStringThree);
        $this->assertTrue($this->messages[0]->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);

        $this->assertTrue($this->messages[1]->getMessage() === $this->messageStringTwo);
        $this->assertTrue($this->messages[1]->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);

        $this->assertTrue($this->messages[2]->getMessage() === $this->messageStringOne);
        $this->assertTrue($this->messages[2]->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
    }

    public function testAddMessage()
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringTwo, ilSystemStyleMessage::TYPE_SUCCESS);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringThree, ilSystemStyleMessage::TYPE_ERROR);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);
        $this->messages = $this->ilSystemStyleMessageStack->getMessages();

        $this->assertTrue($this->messages[2]->getMessage() === $this->messageStringThree);
        $this->assertTrue($this->messages[2]->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);

        $this->assertTrue($this->messages[1]->getMessage() === $this->messageStringTwo);
        $this->assertTrue($this->messages[1]->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);

        $this->assertTrue($this->messages[0]->getMessage() === $this->messageStringOne);
        $this->assertTrue($this->messages[0]->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
    }

    public function testJoinedMessages()
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringTwo, ilSystemStyleMessage::TYPE_SUCCESS);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage("Another SUCCESS message", ilSystemStyleMessage::TYPE_SUCCESS);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringThree, ilSystemStyleMessage::TYPE_ERROR);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage("Another ERROR message", ilSystemStyleMessage::TYPE_ERROR);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage("YET another ERROR message", ilSystemStyleMessage::TYPE_ERROR);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->assertTrue(count($this->ilSystemStyleMessageStack->getJoinedMessages()) === 3);
        $this->assertTrue($this->ilSystemStyleMessageStack->getJoinedMessages()[0] === $this->messageStringOne . "</br>");
        $this->assertTrue($this->ilSystemStyleMessageStack->getJoinedMessages()[1] === $this->messageStringTwo .
            "</br>" . "Another SUCCESS message" . "</br>");
        $this->assertTrue($this->ilSystemStyleMessageStack->getJoinedMessages()[2] === $this->messageStringThree .
            "</br>" . "Another ERROR message" . "</br>" . "YET another ERROR message" . "</br>");
    }

    public function testGetAndSetMessages()
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringTwo, ilSystemStyleMessage::TYPE_SUCCESS);
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->assertTrue($this->ilSystemStyleMessageStack->getMessages()[1]->getMessage() === $this->messageStringTwo);
        $this->ilSystemStyleMessageStack->getMessages()[1]->setMessage("Godzilla has NOT taken over the world.");
        $this->assertTrue($this->ilSystemStyleMessageStack->getMessages()[1]->getMessage() === "Godzilla has NOT taken over the world.");
    }

    public function testHasMessages()
    {
        $this->createTestEnvironment();

        $this->assertFalse($this->ilSystemStyleMessageStack->hasMessages());

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->assertTrue($this->ilSystemStyleMessageStack->hasMessages());
    }

    protected function createTestEnvironment()
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageStringOne, ilSystemStyleMessage::TYPE_INFO);
        $this->ilSystemStyleMessageStack = new ilSystemStyleMessageStack();
    }
}
