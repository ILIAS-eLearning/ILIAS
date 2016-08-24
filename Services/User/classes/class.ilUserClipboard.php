<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User clipboard
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilUserClipboard
{
	const SESSION_KEYWORD = 'usr_clipboard';
	
	private static $instance = null;
	
	private $user_id = 0;
	private $clipboard = array();
	
	
	/**
	 * singleton constructor
	 */
	protected static function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	
	/**
	 * Get singelton instance
	 * @param int $a_usr_id
	 * @return ilUserClipboard
	 */
	public static function getInstance($a_usr_id)
	{
		if(!self::$instance)
		{
			self::$instance = new self($a_usr_id);
		}
		return self::$instance;
	}
	
	/**
	 * Check if clipboard has content
	 * @return bool
	 */
	public function hasContent()
	{
		return count($this->clipboard);
	}
	
	/**
	 * Get clipboard content
	 * @return array
	 */
	public function get()
	{
		return (array) $this->clipboard;
	}
	
	/**
	 * Add entries to clipboard
	 */
	public function add($a_usr_ids)
	{
		$this->clipboard = array_unique(array_merge($this->clipboard), (array) $a_usr_ids);
	}
	
	/**
	 * Replace clipboard content
	 * @param array $a_usr_ids
	 */
	public function replace(array $a_usr_ids)
	{
		$this->clipboard = $a_usr_ids;
	}
	
	public function clear()
	{
		$this->clipboard = array();
	}
	
	/**
	 * Save clipboard content in session
	 */
	public function save()
	{
		ilSession::set(self::SESSION_KEYWORD, (array) $this->clipboard);
	}
	
	/**
	 * Read from session
	 */
	protected function read()
	{
		$this->clipboard = (array) ilSession::get(self::SESSION_KEYWORD);
		$this->clipboard = array(6);
	}
}
?>