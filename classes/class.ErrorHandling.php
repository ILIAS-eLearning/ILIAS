<?php
/**
 * Error Handling
 * @version $Id$
 * @author Stefan Meyer <smeyer@databay.de>
 * @package application
 */
class ErrorHandling
{
	var $DEBUG_ENV;
	var $FATAL = 1;
	var $WARNING = 2;
	var $MESSAGE = 3;
/**
 * Constructor
 * @access public
 * 
 */
	function ErrorHandling()
	{
		$this->DEBUG_ENV = 1;
	}
	/**
	* defines what has to happen in case of error
	* @param object Error
	*/
	function errorHandler($a_error_obj)
	{
		if($a_error_obj->getCode() == $this->FATAL)
		{
			die ($a_error_obj->getMessage());
		}
		if($a_error_obj->getCode() == $this->WARNING)
		{
			if($this->DEBUG_ENV == 1)
			{
				$message = $a_error_obj->getMessage();
			}
			else
			{
				$message = "Under Construction";
			}
			header("location: error.php?message=$message");
			exit();
		}
		if($a_error_obj->getCode() == $this->MESSAGE)
		{
			header("location: $_SESSION[referer]"."&message=".$a_error_obj->getMessage());
			exit();
		}
	}
}
?>