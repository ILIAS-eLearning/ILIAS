<?php
/**
 * Message for the user. Mostly they are stacked, to be shown on rendering to the user all at once.
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleMessage
{
    const TYPE_INFO = 0;
    const TYPE_SUCCESS = 1;
    const TYPE_ERROR = 2;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var int
     */
    protected $type_id = self::TYPE_SUCCESS;

    /**
     * ilMessageStack constructor.
     * @param string $message
     * @param int $type_id
     */
    public function __construct($message, $type_id = self::TYPE_SUCCESS)
    {
        $this->setMessage($message);
        $this->setTypeId($type_id);
    }


    /**
     * @return string
     */
    public function getMessageOutput()
    {
        return $this->message . "</br>";
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->type_id;
    }

    /**
     * @param $type_id
     * @throws ilSystemStyleMessageStackException
     */
    public function setTypeId($type_id)
    {
        if ($this->isValidTypeId($type_id)) {
            $this->type_id = $type_id;
        } else {
            throw new ilSystemStyleMessageStackException(ilSystemStyleMessageStackException::MESSAGE_STACK_TYPE_ID_DOES_NOT_EXIST);
        }
    }

    protected function isValidTypeId($type_id)
    {
        switch ($type_id) {
            case self::TYPE_ERROR:
            case self::TYPE_INFO:
            case self::TYPE_SUCCESS:
                return true;
            default:
                return false;
        }
    }
}
