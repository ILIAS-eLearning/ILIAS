<?php

declare(strict_types=1);

require_once('libs/composer/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class ilSystemStyleMessageTest extends TestCase
{
    protected ilSystemStyleMessage $ilSystemStyleMessage;
    protected string $messageString = 'This is a message';

    public function testConstructor(): void
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

    public function testGetAndSetMessage(): void
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->ilSystemStyleMessage->setMessage('This is an altered message');
        $this->assertTrue($this->ilSystemStyleMessage->getMessage() === 'This is an altered message');
    }

    public function testGetAndSetTypeID(): void
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->ilSystemStyleMessage->setTypeId(ilSystemStyleMessage::TYPE_SUCCESS);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_SUCCESS);

        $this->ilSystemStyleMessage->setTypeId(ilSystemStyleMessage::TYPE_ERROR);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_ERROR);

        $this->ilSystemStyleMessage->setTypeId(ilSystemStyleMessage::TYPE_INFO);
        $this->assertTrue($this->ilSystemStyleMessage->getTypeId() === ilSystemStyleMessage::TYPE_INFO);
    }

    public function testGetMessageOutput(): void
    {
        $this->ilSystemStyleMessage = new ilSystemStyleMessage($this->messageString, ilSystemStyleMessage::TYPE_INFO);
        $this->assertTrue($this->ilSystemStyleMessage->getMessageOutput() === 'This is a message</br>');
    }
}
