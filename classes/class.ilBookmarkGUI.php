<?php
/**
* GUI class for bookmark handling
*
* @author Peter Gabriel <alex.killing@gmx.de>
* @version $Id$
*
* @package application
*/
class ilBookmarkGUI
{
	/**
	* User Id
	* @var integer
	* @access public
	*/
	var $user_Id;

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;

	/**
	* Constructor
	* @access	public
	* @param	integer		user_id (optional)
	*/
	function ilBookmarkGUI($a_user_id = 0)
	{
		global $ilias;

		// Initiate variables
		$this->ilias =& $ilias;
		$this->userId = $a_user_id;
	}

	function displayEditForm()
	{
	}

	function save()
	{
	}

?>
