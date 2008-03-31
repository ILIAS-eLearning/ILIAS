<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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
	* Get HTML.
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.form.html", true, true, "Services/Form");
		$tpl->setVariable("FORM_CONTENT", $this->getContent());
		$tpl->setVariable("FORM_ACTION", $this->getFormAction());
		if ($this->getMultipart())
		{
			$tpl->touchBlock("multipart");
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

		if ($this->getId() != "")
		{
			$tpl->setCurrentBlock("form_id");
			$tpl->setVariable("FORM_ID", $this->getId());
			$tpl->parseCurrentBlock();
		}
		
		if ($this->getTarget() != "")
		{
			$tpl->setCurrentBlock("form_target");
			$tpl->setVariable("FORM_TARGET", $this->getTarget());
			$tpl->parseCurrentBlock();
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
