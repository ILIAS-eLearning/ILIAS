<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");

/**
* Confirmation screen class.
*
* @author	Helmut Schottmüller <ilias@aurealis.eu>
* @version	$Id$
*
* @ingroup ServicesUtilities
*/
class ilSimpleConfirmationGUI
{
	private $buttons = array();

	/**
	* Constructor
	*
	*/
	public function __construct()
	{
	}

	final public function setFormAction($a_form_action)
	{
		$this->form_action = $a_form_action;
	}
	
	final public function getFormAction()
	{
		return $this->form_action;
	}

	/**
	* Set Set header text.
	*
	* @param	string	$a_headertext	Set header text
	*/
	function setHeaderText($a_headertext)
	{
		$this->headertext = $a_headertext;
	}

	/**
	* Get Set header text.
	*
	* @return	string	Set header text
	*/
	function getHeaderText()
	{
		return $this->headertext;
	}

	/**
	* Set cancel button command and text
	*
	* @param	string		cancel text
	* @param	string		cancel command
	*/
	final public function addButton($a_txt, $a_cmd)
	{
		$this->buttons[] = array(
			"txt" => $a_txt, "cmd" => $a_cmd);
	}

	/**
	* Set cancel button command and text
	*
	* @param	string		cancel text
	* @param	string		cancel command
	*/
	final public function setCancel($a_txt, $a_cmd)
	{
		$this->cancel_txt = $a_txt;
		$this->cancel_cmd = $a_cmd;
	}

	/**
	* Set confirmation button command and text
	*
	* @param	string		confirmation button text
	* @param	string		confirmation button command
	*/
	final public function setConfirm($a_txt, $a_cmd)
	{
		$this->confirm_txt = $a_txt;
		$this->confirm_cmd = $a_cmd;
	}

	/**
	* Get confirmation screen HTML.
	*
	* @return	string		HTML code.
	*/
	public function getHTML()
	{
		global $lng;
		
		ilUtil::sendQuestion($this->getHeaderText());
		
		$template = new ilTemplate("tpl.confirmation.simple.html", TRUE, TRUE, "Services/Utilities");

		if (strlen($this->confirm_cmd))
		{
			$template->setCurrentBlock('cmd');
			$template->setVariable('CMD', $this->confirm_cmd);
			$template->setVariable('TXT_CMD', $this->confirm_txt);
			$template->parseCurrentBlock();
		}

		if (strlen($this->cancel_cmd))
		{
			$template->setCurrentBlock('cmd');
			$template->setVariable('CMD', $this->cancel_cmd);
			$template->setVariable('TXT_CMD', $this->cancel_txt);
			$template->parseCurrentBlock();
		}

		// add buttons
		foreach ($this->buttons as $b)
		{
			$template->setCurrentBlock('cmd');
			$template->setVariable('CMD', $b['cmd']);
			$template->setVariable('TXT_CMD', $b['txt']);
			$template->parseCurrentBlock();
		}
		
		$template->setVariable("FORMACTION", $this->getFormAction());
		return $template->get();
	}
}
?>
