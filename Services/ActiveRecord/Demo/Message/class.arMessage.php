<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecord.php');
require_once(dirname(__FILE__) . '/../../Connector/class.arConnectorSession.php');

/**
 * Class arMessage
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class arMessage extends ActiveRecord {

	const TYPE_NEW = 1;
	const TYPE_READ = 2;
	const PRIO_LOW = 1;
	const PRIO_NORMAL = 5;
	const PRIO_HIGH = 9;


	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return 'ar_message';
	}


	/**
	 * @description how to use Acriverecord to read, write, update and delete objects
	 */
	public function modifyObjects() {
		$arMessage = new arMessage();
		$arMessage->setTitle('Hello World');
		$arMessage->setBody('Development using ActiveRecord saves a lot of time');
		$arMessage->create();
		// OR
		$arMessage = new arMessage(3);
		echo $arMessage->getBody();
		// OR
		$arMessage = new arMessage(6);
		$arMessage->setType(arMessage::TYPE_READ);
		$arMessage->update();
		// OR
		$arMessage = arMessage::find(58); // find() Uses the ObjectCache
		$arMessage->delete();
	}


	/**
	 * @return arMessage[]
	 * @description a way to get all objects is to call get() directly on your class
	 */
	public static function getAllObjects() {
		$array_of_arMessages = arMessage::get();

		// OR

		$arMessageList = new arMessageList();
		$array_of_arMessages = $arMessageList->get();

		return $array_of_arMessages;
	}


	/**
	 * @param bool $by_list
	 *
	 * @return arMessage[]
	 *
	 * @description Both ways result in this query:
	 *              SELECT ar_message.*, usr_data.email
	 *                  FROM ar_message
	 *              JOIN usr_data
	 *                  ON ar_message.receiver_id = usr_data.usr_id
	 *              WHERE ar_message.type = 1
	 *              ORDER BY  title ASC
	 *              LIMIT 0, 5
	 */
	public function getSomeObjects($by_list = true) {
		if ($by_list) {
			// One possibility is to use an List-object (extends from ActiverecordList)

			$arMessageList = new arMessageList();
			$arMessageList->innerjoin('usr_data', 'receiver_id', 'usr_id', array( 'email' ));
			$arMessageList->where(array( 'type' => arMessage::TYPE_NEW ));
			$arMessageList->orderBy('title');
			$arMessageList->limit(0, 5);

			return $arMessageList->get();
		} else {

			// Or you can access the list through your AR-Class
			return self::innerjoin('usr_data', 'receiver_id', 'usr_id', array( 'email' ))->where(array( 'type' => arMessage::TYPE_NEW ))->orderBy('title')
				->limit(0, 5)->get();
		}
	}


	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
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
	public function setBody($body) {
		$this->body = $body;
	}


	/**
	 * @return mixed
	 */
	public function getBody() {
		return $this->body;
	}


	/**
	 * @param int $priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}


	/**
	 * @return int
	 */
	public function getPriority() {
		return $this->priority;
	}


	/**
	 * @param int $receiver_id
	 */
	public function setReceiverId($receiver_id) {
		$this->receiver_id = $receiver_id;
	}


	/**
	 * @return int
	 */
	public function getReceiverId() {
		return $this->receiver_id;
	}


	/**
	 * @param int $sender_id
	 */
	public function setSenderId($sender_id) {
		$this->sender_id = $sender_id;
	}


	/**
	 * @return int
	 */
	public function getSenderId() {
		return $this->sender_id;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param int $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param int  $primary_key
	 * @param bool $dev
	 */
	public function __construct($primary_key = 0, $dev = false) {
		if ($dev) {
			$connector = new arConnectorSession();
		} else {
			$connector = NULL;
		}
		parent::__construct($primary_key, $connector);
	}
}

?>
