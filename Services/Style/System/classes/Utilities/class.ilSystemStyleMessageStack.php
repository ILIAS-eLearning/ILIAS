<?php declare(strict_types=1);


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
     * Send messages via ilUtil to be displayed
     */
    public function sendMessages(bool $keep = false)
    {
        foreach ($this->getJoinedMessages() as $type => $joined_message) {
            switch ($type) {
                case ilSystemStyleMessage::TYPE_SUCCESS:
                    ilUtil::sendSuccess($joined_message, $keep);
                    break;
                case ilSystemStyleMessage::TYPE_INFO:
                    ilUtil::sendInfo($joined_message, $keep);
                    break;
                case ilSystemStyleMessage::TYPE_ERROR:
                    ilUtil::sendFailure($joined_message, $keep);
                    break;
            }
        }
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
                $joined_messages[$message->getTypeId()] = "";
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
