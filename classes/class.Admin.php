<?php
/**
 * Class Admin
 * Objectmanagement functions
 * 
 * @author Stefan Meyer <smeyer@databay.de>
 * @author SAscha Hofmann <shofmann@databay.de> 
 * @version $Id$
 * 
 * @package ilias-core
 */
class Admin 
{
	/**
	* ilias object
	* @var	object	ilias
	* @access	private
	*/
	var $ilias;

	/**
	* language object
	* @var	object	language
	* @access	private
	*/
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function Admin()
	{
		global $ilias, $lng;

		$this->ilias = &$ilias;
		$this->lng   = &$lng;
	}


	//
	// all methods moved to ObjectOut/GUI class
	//


} // END class.Admin
