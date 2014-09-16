<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesForm Services/Form
 */

/**
* This class represents a form user interface
*
* @author 	Alex Killing <alex.killing@gmx.de> 
* @version 	$Id$
* @ingroup	ServicesForm
*/
class ilFormGUI
{
	protected $formaction;
	protected $multipart = false;
	protected $keepopen = false;
	protected $opentag = true;
	protected $id;
	protected $name;
	protected $prevent_double_submission = false;
	
	/**
	* Constructor
	*
	* @param
	*/
	function ilFormGUI()
	{
	}

	/**
	* Set FormAction.
	*
	* @param	string	$a_formaction	FormAction
	*/
	function setFormAction($a_formaction)
	{
		$this->formaction = $a_formaction;
	}

	/**
	* Get FormAction.
	*
	* @return	string	FormAction
	*/
	function getFormAction()
	{
		return $this->formaction;
	}

	/**
	* Set Target.
	*
	* @param	string	$a_target	Target
	*/
	function setTarget($a_target)
	{
		$this->target = $a_target;
	}

	/**
	* Get Target.
	*
	* @return	string	Target
	*/
	function getTarget()
	{
		return $this->target;
	}

	/**
	* Set Enctype Multipart/Formdata true/false.
	*
	* @param	boolean	$a_multipart	Enctype Multipart/Formdata true/false
	*/
	function setMultipart($a_multipart)
	{
		$this->multipart = $a_multipart;
	}

	/**
	* Get Enctype Multipart/Formdata true/false.
	*
	* @return	boolean	Enctype Multipart/Formdata true/false
	*/
	function getMultipart()
	{
		return $this->multipart;
	}

	/**
	* Set Id. If you use multiple forms on a screen you should set this value.
	*
	* @param	string	$a_id	Id
	*/
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	* Get Id.
	*
	* @return	string	Id
	*/
	function getId()
	{
		return $this->id;
	}
	
	/**
	* Set Name. Useful for Javascript
	*
	* @param	string	$a_name	Name
	*/
	function setName($a_name)
	{
		$this->name = $a_name;
	}

	/**
	* Get Name.
	*
	* @return	string	Name
	*/
	function getName()
	{
		return $this->name;
	}

	/**
	* Set Keep Form Tag Open.
	*
	* @param	boolean	$a_keepopen	Keep Form Tag Open
	*/
	function setKeepOpen($a_keepopen)
	{
		$this->keepopen = $a_keepopen;
	}

	/**
	* Get Keep Form Tag Open.
	*
	* @return	boolean	Keep Form Tag Open
	*/
	function getKeepOpen()
	{
		return $this->keepopen;
	}

	/**
	* Enable/Disable Open Form Tag.
	*
	* @param	boolean	$a_keepopen	enable/disable form open tag
	*/
	function setOpenTag($a_open)
	{
		$this->opentag = $a_open;
	}

	/**
	* Get Open Form Tag Enabled.
	*
	* @return	boolean	open form tag enabled
	*/
	function getOpenTag()
	{
		return $this->opentag;
	}
	
	/**
	* Set close tag
	*
	* @param	boolean		close tag true/false
	*/
	function setCloseTag($a_val)
	{
		$this->setKeepOpen(!$a_val);
	}
	
	/**
	* Get close tag
	*
	* @return	boolean		close tag true/false
	*/
	function getCloseTag()
	{
		return !$this->getKeepOpen();
	}
	
	/**
	 * Set prevent double submission
	 *
	 * @param bool $a_val prevent double submission	
	 */
	function setPreventDoubleSubmission($a_val)
	{
		$this->prevent_double_submission = $a_val;
	}
	
	/**
	 * Get prevent double submission
	 *
	 * @return bool prevent double submission
	 */
	function getPreventDoubleSubmission()
	{
		return $this->prevent_double_submission;
	}
	
	/**
	* Get HTML.
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.form.html", true, true, "Services/Form");
		
		// this line also sets multipart, so it must be before the multipart check
		$content = $this->getContent();
		if ($this->getOpenTag())
		{
			$opentpl = new ilTemplate('tpl.form_open.html', true, true, "Services/Form");
			if ($this->getTarget() != "")
			{
				$opentpl->setCurrentBlock("form_target");
				$opentpl->setVariable("FORM_TARGET", $this->getTarget());
				$opentpl->parseCurrentBlock();
			}
			if ($this->getName() != "")
			{
				$opentpl->setCurrentBlock("form_name");
				$opentpl->setVariable("FORM_NAME", $this->getName());
				$opentpl->parseCurrentBlock();
			}
			if ($this->getPreventDoubleSubmission())
			{
				$opentpl->setVariable("FORM_CLASS", "preventDoubleSubmission");
			}

			if ($this->getMultipart())
			{
				$opentpl->touchBlock("multipart");
				/*if (function_exists("apc_fetch"))
				//
				// Progress bar would need additional browser window (popup)
				// to not be stopped, when form is submitted  (we can't work
				// with an iframe or httprequest solution here)
				//
				{
					$tpl->touchBlock("onsubmit");
					
					//onsubmit="postForm('{ON_ACT}','form_{F_ID}',1); return false;"
					$tpl->setCurrentBlock("onsubmit");
					$tpl->setVariable("ON_ACT", $this->getFormAction());
					$tpl->setVariable("F_ID", $this->getId());
					$tpl->setVariable("F_ID", $this->getId());
					$tpl->parseCurrentBlock();
	
					$tpl->setCurrentBlock("hidden_progress");
					$tpl->setVariable("APC_PROGRESS_ID", uniqid());
					$tpl->setVariable("APC_FORM_ID", $this->getId());
					$tpl->parseCurrentBlock();
				}*/
			}
			$opentpl->setVariable("FORM_ACTION", $this->getFormAction());
			if ($this->getId() != "")
			{
				$opentpl->setVariable("FORM_ID", $this->getId());
			}
			$opentpl->parseCurrentBlock();
			$tpl->setVariable('FORM_OPEN_TAG', $opentpl->get());
		}
		$tpl->setVariable("FORM_CONTENT", $content);
		if (!$this->getKeepOpen())
		{
			$tpl->setVariable("FORM_CLOSE_TAG", "</form>");
		}
		return $tpl->get();
	}

	/**
	* Get Content.
	*/
	function getContent()
	{
		return "";
	}

}
?>