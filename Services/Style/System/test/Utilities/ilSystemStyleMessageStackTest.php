<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');
include_once('./tests/UI/UITestHelper.php');

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\MessageBox\MessageBox as IMessageBox;

class ilSystemStyleMessageStackTest extends TestCase
{
    protected ilSystemStyleMessage $ilSystemStyleMessage;

    protected string $messageStringOne = 'This is a message';
    protected string $messageStringTwo = 'Godzilla has taken over the world.';
    protected string $messageStringThree = 'A small, cute cat destroyed Godzilla.';
    /**
     * @var ilSystemStyleMessage[]
     */
    protected array $messages = [];
    protected ilSystemStyleMessageStack $ilSystemStyleMessageStack;

    public function testPrependMessage() : void
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->prependMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringTwo,
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->prependMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringThree,
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->prependMessage($this->ilSystemStyleMessage);
        $this->messages = $this->ilSystemStyleMessageStack->getMessages();

        $this->assertTrue($this->messages[0]->getMessage() === $this->messageStringThree);
        $this->assertTrue($this->messages[0]->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);

        $this->assertTrue($this->messages[1]->getMessage() === $this->messageStringTwo);
        $this->assertTrue($this->messages[1]->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);

        $this->assertTrue($this->messages[2]->getMessage() === $this->messageStringOne);
        $this->assertTrue($this->messages[2]->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
    }

    public function testAddMessage() : void
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringTwo,
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringThree,
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);
        $this->messages = $this->ilSystemStyleMessageStack->getMessages();

        $this->assertTrue($this->messages[2]->getMessage() === $this->messageStringThree);
        $this->assertTrue($this->messages[2]->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);

        $this->assertTrue($this->messages[1]->getMessage() === $this->messageStringTwo);
        $this->assertTrue($this->messages[1]->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);

        $this->assertTrue($this->messages[0]->getMessage() === $this->messageStringOne);
        $this->assertTrue($this->messages[0]->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
    }

    public function testJoinedMessages() : void
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringTwo,
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            'Another SUCCESS message',
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringThree,
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            'Another ERROR message',
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            'YET another ERROR message',
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->assertTrue(count($this->ilSystemStyleMessageStack->getJoinedMessages()) === 3);
        $this->assertTrue($this->ilSystemStyleMessageStack->getJoinedMessages()[0] === $this->messageStringOne . '</br>');
        $this->assertTrue($this->ilSystemStyleMessageStack->getJoinedMessages()[1] === $this->messageStringTwo .
            '</br>' . 'Another SUCCESS message' . '</br>');
        $this->assertTrue($this->ilSystemStyleMessageStack->getJoinedMessages()[2] === $this->messageStringThree .
            '</br>' . 'Another ERROR message' . '</br>' . 'YET another ERROR message' . '</br>');
    }

    public function testGetUIComponentsMessages() : void
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringTwo,
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            'Another SUCCESS message',
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringThree,
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            'Another ERROR message',
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            'YET another ERROR message',
            ilSystemStyleMessage::TYPE_ERROR
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $ui_helper = new UITestHelper();

        $message_components = $this->ilSystemStyleMessageStack->getUIComponentsMessages($ui_helper->factory());

        $this->assertCount(3, $message_components);
        $this->assertInstanceOf(IMessageBox::class, $message_components[0]);
        $this->assertInstanceOf(IMessageBox::class, $message_components[1]);
        $this->assertInstanceOf(IMessageBox::class, $message_components[2]);

        $this->assertEquals(ILIAS\UI\Component\MessageBox\MessageBox::INFO, $message_components[0]->getType());
        $this->assertEquals(ILIAS\UI\Component\MessageBox\MessageBox::SUCCESS, $message_components[1]->getType());
        $this->assertEquals(ILIAS\UI\Component\MessageBox\MessageBox::FAILURE, $message_components[2]->getType());

        $this->assertEquals('This is a message</br>', $message_components[0]->getMessageText());
    }

    public function testGetAndSetMessages() : void
    {
        $this->createTestEnvironment();

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringTwo,
            ilSystemStyleMessage::TYPE_SUCCESS
        );
        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->assertTrue($this->ilSystemStyleMessageStack->getMessages()[1]->getMessage() === $this->messageStringTwo);
        $this->ilSystemStyleMessageStack->getMessages()[1]->setMessage('Godzilla has NOT taken over the world.');
        $this->assertTrue($this->ilSystemStyleMessageStack->getMessages()[1]->getMessage() === 'Godzilla has NOT taken over the world.');
    }

    public function testHasMessages() : void
    {
        $this->createTestEnvironment();

        $this->assertFalse($this->ilSystemStyleMessageStack->hasMessages());

        $this->ilSystemStyleMessageStack->addMessage($this->ilSystemStyleMessage);

        $this->assertTrue($this->ilSystemStyleMessageStack->hasMessages());
    }

    protected function createTestEnvironment() : void
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage(
            $this->messageStringOne,
            ilSystemStyleMessage::TYPE_INFO
        );
        $this->ilSystemStyleMessageStack = new ilSystemStyleMessageStack(
            $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock()
        );
    }
}
