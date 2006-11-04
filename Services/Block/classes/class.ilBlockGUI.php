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

/**
* This class represents a block method of a block.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/
class ilBlockGUI
{
	var $ctrl;
	var $data = array();

	/**
	* Constructor
	*
	* @param
	*/
	function ilBlockGUI()
	{
	}
	
	/**
	* Set Data.
	*
	* @param	array	$a_data	Data
	*/
	function setData($a_data)
	{
		$this->data = $a_data;
	}

	/**
	* Get Data.
	*
	* @return	array	Data
	*/
	function getData()
	{
		return $this->data;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Row Template Name.
	*
	* @param	string	$a_rowtemplatename	Row Template Name
	*/
	function setRowTemplateName($a_rowtemplatename)
	{
		$this->rowtemplatename = $a_rowtemplatename;
	}

	/**
	* Get Row Template Name.
	*
	* @return	string	Row Template Name
	*/
	function getRowTemplateName()
	{
		return $this->rowtemplatename;
	}

	/**
	* Set Row Template Directory.
	*
	* @param	string	$a_rowtemplatedir	Row Template Directory
	*/
	function setRowTemplateDir($a_rowtemplatedir)
	{
		$this->rowtemplatedir = $a_rowtemplatedir;
	}

	/**
	* Get Row Template Directory.
	*
	* @return	string	Row Template Directory
	*/
	function getRowTemplateDir()
	{
		return $this->rowtemplatedir;
	}

	/**
	* Add Block Command.
	*
	* @param	string	$a_href		command link target
	* @param	string	$a_text		text
	*/
	function addBlockCommand($a_href, $a_text)
	{
		return $this->block_commands[] = 
			array("href" => $a_href,
				"text" => $a_text);
	}

	/**
	* Get Block commands.
	*
	* @return	array	block commands
	*/
	function getBlockCommands()
	{
		return $this->block_commands;
	}

	/**
	* Get HTML.
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.block.html", true, true, "Services/Block");
		
		// data
		$tpl->addBlockFile("BLOCK_ROW", "block_row", $this->getRowTemplateName(),
			$this->getRowTemplateDir());
		foreach($this->getData() as $record)
		{
			$tpl->setCurrentBlock("block_row");
			foreach ($record as $key => $value)
			{
				$tpl->setVariable("VAL_".strupper($key), $value);
			}
			$tpl->parseCurrentBlock();
		}
		
		// commands
		if (count($this->getBlockCommands()) > 0)
		{
			foreach($this->getBlockCommands() as $command)
			{
				$tpl->setCurrentBlock("block_command");
				$tpl->setVariable("CMD_HREF", $command["href"]);
				$tpl->setVariable("CMD_TEXT", $command["text"]);
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("block_commands");
			$tpl->parseCurrentBlock();
		}
		
		// title
		$tpl->setVariable("BLOCK_TITLE",
			$this->getTitle());
		return $tpl->get();
	}
}
