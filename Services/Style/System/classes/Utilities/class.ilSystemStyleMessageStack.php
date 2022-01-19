<?php

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
    public function setMessages(array $messages)
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
}
