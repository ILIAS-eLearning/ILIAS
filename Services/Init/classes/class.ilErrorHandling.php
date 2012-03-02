<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Error Handling & global info handling
* uses PEAR error class
*
* @author	Stefan Meyer <meyer@leifos.com>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
* @extends PEAR
* @todo		when an error occured and clicking the back button to return to previous page the referer-var in session is deleted -> server error
*/
include_once 'PEAR.php';

class ilErrorHandling extends PEAR
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
		$this->PEAR();

		// init vars
		$this->DEBUG_ENV = true;
		$this->FATAL	 = 1;
		$this->WARNING	 = 2;
		$this->MESSAGE	 = 3;

		$this->error_obj = false;
	}

	function getLastError()
	{
		return $this->error_obj;
	}

	/**
	* defines what has to happen in case of error
	* @access	private
	* @param	object	Error
	*/
	function errorHandler($a_error_obj)
	{
		global $log;

		$this->error_obj =& $a_error_obj;
//echo "-".$_SESSION["referer"]."-";
		if ($_SESSION["failure"] && substr($a_error_obj->getMessage(), 0, 22) != "Cannot find this block")
		{
			$m = "Fatal Error: Called raise error two times.<br>".
				"First error: ".$_SESSION["failure"].'<br>'.
				"Last Error:". $a_error_obj->getMessage();
			//return;
			$log->logError($a_error_obj->getCode(), $m);
			unset($_SESSION["failure"]);
			die ($m);
		}

		if (substr($a_error_obj->getMessage(), 0, 22) == "Cannot find this block")
		{
			if (DEVMODE == 1)
			{
				echo "<b>DEVMODE</b><br><br>";
				echo "<b>Template Block not found.</b><br>";
				echo "You used a template block in your code that is not available.<br>";
				echo "Native Messge: <b>".$a_error_obj->getMessage()."</b><br>";
				if (is_array($a_error_obj->backtrace))
				{
					echo "Backtrace:<br>";
					foreach ($a_error_obj->backtrace as $b)
					{
						if ($b["function"] == "setCurrentBlock" &&
							basename($b["file"]) != "class.ilTemplate.php")
						{
							echo "<b>";
						}
						echo "File: ".$b["file"].", ";
						echo "Line: ".$b["line"].", ";
						echo $b["function"]."()<br>";
						if ($b["function"] == "setCurrentBlock" &&
							basename($b["file"]) != "class.ilTemplate.php")
						{
							echo "</b>";
						}
					}
				}
				exit;
			}
			return;
		}

		if (is_object($log) and $log->enabled == true)
		{
			$log->logError($a_error_obj->getCode(),$a_error_obj->getMessage());
		}

//echo $a_error_obj->getCode().":"; exit;
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

			$_SESSION["failure"] = $message;

			if (!defined("ILIAS_MODULE"))
			{
				ilUtil::redirect("error.php");
			}
			else
			{
				ilUtil::redirect("../error.php");
			}
		}

		if ($a_error_obj->getCode() == $this->MESSAGE)
		{
			$_SESSION["failure"] = $a_error_obj->getMessage();
			// save post vars to session in case of error
			$_SESSION["error_post_vars"] = $_POST;

			if (empty($_SESSION["referer"]))
			{
				$dirname = dirname($_SERVER["PHP_SELF"]);
				$ilurl = parse_url(ILIAS_HTTP_PATH);
				$subdir = substr(strstr($dirname,$ilurl["path"]),strlen($ilurl["path"]));
				$updir = "";

				if ($subdir)
				{
					$num_subdirs = substr_count($subdir,"/");

					for ($i=1;$i<=$num_subdirs;$i++)
					{
						$updir .= "../";
					}
				}
				ilUtil::redirect($updir."index.php");
			}

			// check if already GET-Parameters exists in Referer-URI
			if (substr($_SESSION["referer"],-4) == ".php")
			{
				$glue = "?";
			}
			else
			{
				$glue = "&";
			}
			ilUtil::redirect($_SESSION["referer"].$glue);
		}
	}

	function getMessage()
	{
		return $this->message;
	}
	function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	function appendMessage($a_message)
	{
		if($this->getMessage())
		{
			$this->message .= "<br /> ";
		}
		$this->message .= $a_message;
	}
	
	/**
	 * This is used in Soap calls to write PHP error in ILIAS Logfile
	 * Not used yet!!!
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _ilErrorWriter($errno, $errstr, $errfile, $errline)
	{
		global $ilLog;
		
		switch($errno)
		{
			case E_USER_ERROR:
				$ilLog->write('PHP errror: '.$errstr.'. FATAL error on line '.$errline.' in file '.$errfile);
				unset($ilLog);
				exit(1);
			
			case E_USER_WARNING:
				$ilLog->write('PHP warning: ['.$errno.'] '.$errstr.' on line '.$errline.' in file '.$errfile);
				break;
			
		}				
		return true;
	}

} // END class.ilErrorHandling
?>
