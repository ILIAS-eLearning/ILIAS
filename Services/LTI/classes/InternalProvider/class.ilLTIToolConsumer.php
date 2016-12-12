<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

use IMSGlobal\LTI\ToolProvider;

/**
 * LTI provider for LTI launch 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilLTIToolConsumer extends ToolProvider\ToolConsumer
{

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * @var string
	 */
	protected $user_language = '';

	/**
	 * @var int
	 */
	protected $role = 0;


	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->getRecordId();
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
		// $this->consumerName = $title;
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
	 * @param string $secret
	 */
	public function setSecret($secret)
	{
		$this->secret = $secret;
	}

	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->secret;
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
		$this->enabled = $value;
	}

	/**
	 * @return integer
	 */
	public function getActive()
	{
		return $this->enabled;
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

// local_role_always_member, default_skin


	
/**
 * Load the tool consumer from the database by its record ID.
 *
 * @param int             $id                The consumer key record ID
 * @param DataConnector   $dataConnector    Database connection object
 *
 * @return object ToolConsumer       The tool consumer object
 */
    public static function fromRecordId($id, $dataConnector)
    {

        $toolConsumer = new ilLTIToolConsumer(null, $dataConnector);

        $toolConsumer->initialize();
        $toolConsumer->setRecordId($id);
        if (!$dataConnector->loadToolConsumerILIAS($toolConsumer)) {
            $toolConsumer->initialize();
        }
        return $toolConsumer;

    }

/**
 * Save the tool consumer to the database with ILIAS extension.
 *
 * @return boolean True if the object was successfully saved
 */
    public function saveLTI($dataConnector)
    {
        $ok = $dataConnector->saveToolConsumerILIAS($this);
        return $ok;
    }

//AB HIER WEG	
	// /**
	 // * @param integer $id
	 // */
	// public function setId($id)
	// {
		// $this->id = $id;
	// }	

	 // public function __construct($key = null, $dataConnector = null, $autoEnable = false)
    // {

        // $this->initialize();
        // // if (empty($dataConnector)) {
            // // $dataConnector = DataConnector::getDataConnector();
            // // $dataConnector = DataConnector::getDataConnector(,"ilias");
        // // }
        // $this->dataConnector = $dataConnector;
		// // require_once 'Services/LTI/classes/class.ilLTIDataConnector.php';
		// // $this->dataConnector = new ilLTIDataConnector();
        // if (!empty($key)) {
            // $this->load($key, $autoEnable);
        // } else {
            // // $this->secret = DataConnector::getRandomString(32);
        // }

    // }

	
	// public static function getAllConsumers() {
		// return self::get();
	// }


}
