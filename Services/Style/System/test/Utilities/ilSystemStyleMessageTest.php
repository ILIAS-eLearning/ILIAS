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
