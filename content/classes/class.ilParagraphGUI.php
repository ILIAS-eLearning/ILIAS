<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./content/classes/class.ilParagraph.php");

/**
* Class ilParagraphGUI
*
* User Interface for Paragraph Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilParagraphGUI
{
	var $para_obj;
	var $ilias;
	var $tpl;
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function ilParagraphGUI(&$a_para_obj)
	{
		global $ilias, $tpl, $lng;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->para_obj =& $a_para_obj;
	}

	function edit($a_template_var)
	{
		$this->tpl->addBlockFile($a_template_var, "paragraph_edit", "tpl.paragraph_edit.html", true);
		$this->tpl->setVariable("PAR_TA_NAME", "par_content");
echo htmlentities($this->para_obj->getText());
		$this->tpl->setVariable("PAR_TA_CONTENT", $this->xml2nl($this->para_obj->getText()));
		//$this->tpl->setVariable("PAR_TA_CONTENT", "Hallo Echo");
		$this->tpl->parseCurrentBlock();
	}

	function processInput()
	{
		$this->para_obj->setText($this->nl2xml($_POST["par_content"]));
	}

	function nl2xml($a_text)
	{
		$a_text = ereg_replace(chr(13).chr(10),"<br />",trim($a_text));
		$a_text = ereg_replace(chr(13),"<br />", $a_text);
		$a_text = ereg_replace(chr(10),"<br />", $a_text);
		/*$blob = ereg_replace("<NR><NR>","<P>",$blob);
		$blob = ereg_replace("<NR>"," ",$blob);*/

		//$a_text = nl2br($a_text);
		return $a_text;
	}

	function xml2nl($a_text)
	{
		return str_replace("<br />", "\n", $a_text);
		//return str_replace("<br />", chr(13).chr(10), $a_text);
	}

}
?>
