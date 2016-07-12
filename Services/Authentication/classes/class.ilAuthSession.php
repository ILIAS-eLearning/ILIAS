<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthSession
{
	private static $instance = null;
	
	private $id = '';
	
	/**
	 * Consctructor
	 */
	private function __construct()
	{
		;
	}
	
	/**
	 * Get instance
	 * @return ilAuthSession
	 */
	public static function getInstance()
	{
		if(static::$instance)
		{
			return new static::$instance;
		}
		return static::$instance = new self();
	}
	
	public function setId($a_id)
	{
		$this->id = $a_id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
}
?>