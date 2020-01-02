<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');
require_once(dirname(__FILE__) . '/../../Connector/class.arConnectorSession.php');

/**
 * Class arMessage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arMessage extends ActiveRecord
{
    const TYPE_NEW = 1;
    const TYPE_READ = 2;
    const PRIO_LOW = 1;
    const PRIO_NORMAL = 5;
    const PRIO_HIGH = 9;


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return 'ar_message';
    }


    /**
     * @var int
     *
     * @con_is_primary   true
     * @con_has_field    true
     * @con_fieldtype    integer
     * @con_length       8
     * @con_sequence     true
     */
    protected $id;
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype text
     * @con_length    256
     */
    protected $title = '';
    /**
     * @var string
     *
     * @con_has_field true
     * @con_fieldtype clob
     * @con_length    4000
     */
    protected $body = '';
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $sender_id = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_is_notnull true
     * @con_length     1
     */
    protected $receiver_id = 0;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $priority = self::PRIO_NORMAL;
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     * @con_is_notnull true
     */
    protected $type = self::TYPE_NEW;


    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }


    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }


    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }


    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }


    /**
     * @param int $receiver_id
     */
    public function setReceiverId($receiver_id)
    {
        $this->receiver_id = $receiver_id;
    }


    /**
     * @return int
     */
    public function getReceiverId()
    {
        return $this->receiver_id;
    }


    /**
     * @param int $sender_id
     */
    public function setSenderId($sender_id)
    {
        $this->sender_id = $sender_id;
    }


    /**
     * @return int
     */
    public function getSenderId()
    {
        return $this->sender_id;
    }


    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param int $primary_key
     * @param bool $dev
     */
    //	public function __construct($primary_key = 0, $dev = false) {
    //		parent::__construct($primary_key, new arConnectorSession());
    //	}
}
