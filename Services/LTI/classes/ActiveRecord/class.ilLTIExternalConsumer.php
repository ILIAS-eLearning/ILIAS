<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class ilLTIExternalConsumer
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilLTIExternalConsumer extends ActiveRecord
{
	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $id = 0;

	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    255
	 */
	protected $title = '';

	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    255
	 */
	protected $description = '';

	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    255
	 */
	protected $prefix = '';

	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    255
	 */
	protected $consumer_key = '';

	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    255
	 */
	protected $consumer_secret = '';

	/**
	 * @var string
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    255
	 */
	protected $user_language = '';

	/**
	 * @var integer
	 *
	 * @con_has_field true
	 * @con_fieldtype integer
	 */
	protected $active = 0;

	/**
	 * @var int
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     11
	 */
	protected $role = 0;

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return "lti_ext_consumer";
	}

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
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
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->consumer_key = $key;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->consumer_key;
	}

	/**
	 * @param string $secret
	 */
	public function setSecret($secret)
	{
		$this->consumer_secret = $secret;
	}

	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->consumer_secret;
	}

	/**
	 * @param string $lang  (int?)
	 */
	public function setLanguage($lang)
	{
		$this->user_language = $lang;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->user_language;
	}

	/**
	 * @param integer $value
	 */
	public function setActive($value)
	{
		$this->active = $value;
	}

	/**
	 * @return integer
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param integer $role_id
	 */
	public function setRole($role_id)
	{
		$this->role = $role_id;
	}

	/**
	 * @return integer
	 */
	public function getRole()
	{
		return $this->role;
	}

	public static function getAll() {
		return self::get();
	}


}