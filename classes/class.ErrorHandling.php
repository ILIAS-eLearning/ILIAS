<?php
/**
* Error Handling
* uses PEAR error class
*
* @version $Id$
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
*
* @package application
* @todo when an error occured and clicking the back button to return to previous page the referer-var in session is deleted -> server error
*/
class ErrorHandling
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
	function ErrorHandling()
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
		if($_GET["message"])
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
			
			header("location: error.php?message=".$message);
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
			header("location: ".$_SESSION["referer"].$glue."message=".urlencode($a_error_obj->getMessage()));
			exit;
		}
	}
	/**
	* sends a message to the actual page
	* @access	public
    * @param string message
	*/
	function sendInfo($a_info)
	{
		if(!$_SESSION["info"])
		{
			$_SESSION["info"] = $a_info;
		}
	}
		
} // END class.ErrorHandling
?>