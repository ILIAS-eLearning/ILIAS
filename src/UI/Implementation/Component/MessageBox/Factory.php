<?php declare(strict_types=1);

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\MessageBox;

use ILIAS\UI\Component as C;

/**
 * Class Factory
 */
class Factory implements C\MessageBox\Factory
{
    /**
     * @inheritdoc
     */
    public function failure(string $message_text) : C\MessageBox\MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::FAILURE, $message_text);
    }

    /**
     * @inheritdoc
     */
    public function success(string $message_text) : C\MessageBox\MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::SUCCESS, $message_text);
    }

    /**
     * @inheritdoc
     */
    public function info(string $message_text) : C\MessageBox\MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::INFO, $message_text);
    }

    /**
     * @inheritdoc
     */
    public function confirmation(string $message_text) : C\MessageBox\MessageBox
    {
        return new MessageBox(C\MessageBox\MessageBox::CONFIRMATION, $message_text);
    }
}
