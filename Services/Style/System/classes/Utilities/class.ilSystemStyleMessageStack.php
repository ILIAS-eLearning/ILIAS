<?php

/**
 * Used to stack messages to be shown to the user. Mostly used in ilUtil-Classes to present via ilUtil::sendMessage()
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleMessageStack
{

    /**
     * @var ilSystemStyleMessage[]
     */
    protected $messages = array();

    /**
     * Add a message to be displayed before all others
     *
     * @param ilSystemStyleMessage $message
     */
    public function prependMessage(ilSystemStyleMessage $message)
    {
        array_unshift($this->messages, $message);
    }

    /**
     * Add a message to be displayed by the stack
     *
     * @param ilSystemStyleMessage $message
     */
    public function addMessage(ilSystemStyleMessage $message)
    {
        $this->messages[] = $message;
    }

    /**
     * Send messages via ilUtil to be displayed
     *
     * @param bool|false $keep
     */
    public function sendMessages($keep = false)
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
    public function getJoinedMessages()
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
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ilSystemStyleMessage[] $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Return wheter there are any message at all stored in the stack
     *
     * @return bool
     */
    public function hasMessages()
    {
        return count($this->getMessages()) > 0;
    }
}
