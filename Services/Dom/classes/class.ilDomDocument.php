<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dom document wrapper. 
 *
 * @author author <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup Services/Dom
 */
class ilDomDocument
{
	private $doc;
	private $errors = array();

	/**
	 * Constructor
	 * @param	DOMDocument		PHP dom document
	 */
	function __construct()
	{
		$this->doc = new DOMDocument();
	}

	/**
	 * Call
	 */
	public function __call($a_method, $a_args)
	{
		if (in_array($a_method, array("validate", "loadXML")))
		{
			set_error_handler(array($this, "handleError"));
			$rv = call_user_func_array(array($this->doc, $a_method), $a_args);
			restore_error_handler();
			return $rv;
		}
		else
		{
			return call_user_func_array(array($this->doc, $a_method), $a_args);
		}
	}
	
	/**
	 * Get
	 */
	public function __get($a_mem)
	{
		if ($a_mem == "errors")
		{
			return $this->errors;
		}
		else
		{
			return $this->doc->$a_mem;
		}
	}

	/**
	 *  Set
	 */
	public function __set($a_mem, $a_val)
	{
		$this->_delegate->$a_mem = $a_val;
	}
	
	/**
	 * Handle error
	 */
	public function handleError($a_no, $a_string, $a_file = null, $a_line = null, $a_context = null)
	{
		$pos = strpos($a_string, "]:");
		$err = trim(substr($a_string, $pos + 2));
		$this->errors[] = $err;
	}
}
    
?>