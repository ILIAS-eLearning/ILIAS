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

use ILIAS\UI\Component\MessageBox\MessageBox;

/**
 * Used to stack messages to be shown to the user. Mostly used in ilUtil-Classes to present via ilUtil::sendMessage()
 */
class ilSystemStyleMessageStack
{
    /**
     * @var ilSystemStyleMessage[]
     */
    protected array $messages = [];
    private ilGlobalTemplateInterface $main_tpl;
    public function __construct(ilGlobalTemplateInterface $main_tpl)
    {
        $this->main_tpl = $main_tpl;
    }

    /**
     * Add a message to be displayed before all others
     */
    public function prependMessage(ilSystemStyleMessage $message) : void
    {
        array_unshift($this->messages, $message);
    }

    /**
     * Add a message to be displayed by the stack
     */
    public function addMessage(ilSystemStyleMessage $message) : void
    {
        $this->messages[] = $message;
    }

    /**
     * Return Messages as UI Component
     * @return MessageBox[]
     */
    public function getUIComponentsMessages(\ILIAS\UI\Factory $f) : array
    {
        $messages = [];
        foreach ($this->getJoinedMessages() as $type => $joined_message) {
            switch ($type) {
                case ilSystemStyleMessage::TYPE_SUCCESS:
                    $messages[] = $f->messageBox()->success($joined_message);
                    break;
                case ilSystemStyleMessage::TYPE_INFO:
                    $messages[] = $f->messageBox()->info($joined_message);
                    break;
                case ilSystemStyleMessage::TYPE_ERROR:
                    $messages[] = $f->messageBox()->failure($joined_message);
                    break;
            }
        }
        return $messages;
    }

    /**
     * Return an array containing a string with all messages for each type
     *
     * @return string[]
     */
    public function getJoinedMessages() : array
    {
        $joined_messages = [];
        foreach ($this->getMessages() as $message) {
            if (!array_key_exists($message->getTypeId(), $joined_messages)) {
                $joined_messages[$message->getTypeId()] = '';
            }
            $joined_messages[$message->getTypeId()] .= $message->getMessageOutput();
        }
        return $joined_messages;
    }

    /**
     * @return ilSystemStyleMessage[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }

    /**
     * @param ilSystemStyleMessage[] $messages
     */
    public function setMessages(array $messages) : void
    {
        $this->messages = $messages;
    }

    /**
     * Return wheter there are any message at all stored in the stack
     */
    public function hasMessages() : bool
    {
        return count($this->getMessages()) > 0;
    }

    /**
     * Send messages via ilUtil to be displayed, still needed for messagees, that need to survive a redirect
     */
    public function sendMessages(bool $keep = true) : void
    {
        foreach ($this->getJoinedMessages() as $type => $joined_message) {
            switch ($type) {
                case ilSystemStyleMessage::TYPE_SUCCESS:
                    $this->main_tpl->setOnScreenMessage('success', $joined_message, $keep);
                    break;
                case ilSystemStyleMessage::TYPE_INFO:
                    $this->main_tpl->setOnScreenMessage('info', $joined_message, $keep);
                    break;
                case ilSystemStyleMessage::TYPE_ERROR:
                    $this->main_tpl->setOnScreenMessage('failure', $joined_message, $keep);
                    break;
            }
        }
    }
}
