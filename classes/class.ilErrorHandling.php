<?php
/**
* Error Handling & global info handling
* uses PEAR error class
*
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
* @package	application
* @todo		when an error occured and clicking the back button to return to previous page the referer-var in session is deleted -> server error
*/
class ilErrorHandling
{
	/**
	* Toggle debugging on/off
	* @var		boolean
	* @access	private
	*/
	var $DEBUG_ENV;

	/**
	* Error level 1: exit application immedietly
	* @var		integer
	* @access	public
	*/
	var $FATAL;

	/**
	* Error level 2: show warning page
	* @var		integer
	* @access	public
	*/
	var $WARNING;

	/**
	* Error level 3: show message in recent page
	* @var		integer
	* @access	public
	*/
	var $MESSAGE;

	/**
	* Constructor
	* @access	public
	*/
	function ilErrorHandling()
	{
		// init vars
		$this->DEBUG_ENV = true;
		$this->FATAL	 = 1;
		$this->WARNING	 = 2;
		$this->MESSAGE	 = 3;
	}
	
	/**
	* defines what has to happen in case of error
	* @access	private
	* @param	object	Error
	*/
	function errorHandler($a_error_obj)
	{
		if($_SESSION["message"])
		{
			return;
		}

		if ($a_error_obj->getCode() == $this->FATAL)
		{
			die (stripslashes($a_error_obj->getMessage()));
		}
		
		if ($a_error_obj->getCode() == $this->WARNING)
		{
			if ($this->DEBUG_ENV)
			{
				$message = $a_error_obj->getMessage();
			}
			else
			{
				$message = "Under Construction";
			}

			$_SESSION["message"] = $message;

			header("location: error.php");
			exit;
		}

		if ($a_error_obj->getCode() == $this->MESSAGE)
		{	
			// check if already GET-Parameters exists in Referer-URI
			if (substr($_SESSION["referer"],-4) == ".php")
			{
				$glue = "?";
			}
			else
			{
				$glue = "&";
			}

			$_SESSION["message"] = $a_error_obj->getMessage();

			header("location: ".$_SESSION["referer"].$glue);
			exit;
		}
	}
} // END class.ilErrorHandling
?>
