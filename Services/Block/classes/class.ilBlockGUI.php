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
	function ilBlockGUI($a_parent_class, $a_parent_cmd = "")
	{
		global $ilUser;

		$this->setParentClass($a_parent_class);
		$this->setParentCmd($a_parent_cmd);

		$this->setLimit($ilUser->getPref("hits_per_page"));
	}

	/**
	* Set Parent class name.
	*
	* @param	string	$a_parentclass	Parent class name
	*/
	function setParentClass($a_parentclass)
	{
		$this->parentclass = $a_parentclass;
	}

	/**
	* Get Parent class name.
	*
	* @return	string	Parent class name
	*/
	function getParentClass()
	{
		return $this->parentclass;
	}

	/**
	* Set Parent command.
	*
	* @param	string	$a_parentcmd	Parent command
	*/
	function setParentCmd($a_parentcmd)
	{
		$this->parentcmd = $a_parentcmd;
	}

	/**
	* Get Parent command.
	*
	* @return	string	Parent command
	*/
	function getParentCmd()
	{
		return $this->parentcmd;
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
	* Set Image.
	*
	* @param	string	$a_image	Image
	*/
	function setImage($a_image)
	{
		$this->image = $a_image;
	}

	/**
	* Get Image.
	*
	* @return	string	Image
	*/
	function getImage()
	{
		return $this->image;
	}

	/**
	* Set Offset.
	*
	* @param	int	$a_offset	Offset
	*/
	function setOffset($a_offset)
	{
		$this->offset = $a_offset;
	}

	/**
	* Get Offset.
	*
	* @return	int	Offset
	*/
	function getOffset()
	{
		return $this->offset;
	}

	/**
	* Set Limit.
	*
	* @param	int	$a_limit	Limit
	*/
	function setLimit($a_limit)
	{
		$this->limit = $a_limit;
	}

	/**
	* Get Limit.
	*
	* @return	int	Limit
	*/
	function getLimit()
	{
		return $this->limit;
	}

	/**
	* Set Prefix.
	*
	* @param	string	$a_prefix	Prefix
	*/
	function setPrefix($a_prefix)
	{
		$this->prefix = $a_prefix;
	}

	/**
	* Get Prefix.
	*
	* @return	string	Prefix
	*/
	function getPrefix()
	{
		return $this->prefix;
	}

	/**
	* Set Row Template Name.
	*
	* @param	string	$a_rowtemplatename	Row Template Name
	*/
	function setRowTemplate($a_rowtemplatename, $a_rowtemplatedir = "")
	{
		$this->rowtemplatename = $a_rowtemplatename;
		$this->rowtemplatedir = $a_rowtemplatedir;
	}

	final public function getNavParameter()
	{
		return $this->prefix."_block_nav";
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
		$this->tpl = new ilTemplate("tpl.block.html", true, true, "Services/Block");
		
		$this->nav_value = ($_POST[$this->getNavParameter()] != "")
			? $_POST[$this->getNavParameter()]
			: $_GET[$this->getNavParameter()];
		$nav = explode(":", $this->nav_value);
		$this->setOffset($nav[2]);
		
		// data
		$this->tpl->addBlockFile("BLOCK_ROW", "block_row", $this->getRowTemplateName(),
			$this->getRowTemplateDir());
			
		$data = $this->getData();
		$this->max_count = count($data);
		$data = array_slice($data, $this->getOffset(), $this->getLimit());
		
		foreach($data as $record)
		{
			$this->tpl->setCurrentBlock("block_row");
			$this->fillRowColor();
			$this->fillRow($record);
			$this->tpl->setCurrentBlock("block_row");
			$this->tpl->parseCurrentBlock();
		}
		
		// commands
		if (count($this->getBlockCommands()) > 0)
		{
			foreach($this->getBlockCommands() as $command)
			{
				$this->tpl->setCurrentBlock("block_command");
				$this->tpl->setVariable("CMD_HREF", $command["href"]);
				$this->tpl->setVariable("CMD_TEXT", $command["text"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("block_commands");
			$this->tpl->parseCurrentBlock();
		}
		
		// image
		if ($this->getImage() != "")
		{
			$this->tpl->setCurrentBlock("block_img");
			$this->tpl->setVariable("IMG_BLOCK", $this->getImage());
			$this->tpl->parseCurrentBlock();
		}
		
		// fill footer row
		$this->fillFooter();
		
		// title
		$this->tpl->setVariable("BLOCK_TITLE",
			$this->getTitle());
		return $this->tpl->get();
	}
	
	
	function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
	
	final protected function fillRowColor($a_placeholder = "CSS_ROW")
	{
		$this->css_row = ($this->css_row != "tblrow1")
			? "tblrow1"
			: "tblrow2";
		$this->tpl->setVariable($a_placeholder, $this->css_row);
	}

	/**
	* Fill footer row
	*/
	function fillFooter()
	{
		global $lng, $ilCtrl;

		$footer = false;
				
		// table footer numinfo
		$start = $this->getOffset() + 1;				// compute num info
		$end = $this->getOffset() + $this->getLimit();
			
		if ($end > $this->max_count or $this->getLimit() == 0)
		{
			$end = $this->max_count;
		}
			
		$numinfo = "(".$start."-".$end." ".strtolower($lng->txt("of"))." ".$this->max_count.")";

		if ($this->max_count > 0)
		{
			$this->tpl->setVariable("NUMINFO", $numinfo);
		}
		$footer = true;

		// table footer linkbar
		if ($this->getLimit()  != 0
			 && $this->max_count > 0)
		{
			$linkbar = $this->getLinkbar();
			$this->tpl->setVariable("LINKBAR", $linkbar);
			$footer = true;
		}

		if ($footer)
		{
			$this->tpl->setCurrentBlock("block_footer");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Get previous/next linkbar.
	*
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @return	array	linkbar or false on error
	*/
	function getLinkbar()
	{
		global $ilCtrl, $lng;
		
		$link = $ilCtrl->getLinkTargetByClass($this->getParentClass(), $this->getParentCmd()).
			"&".$this->getNavParameter()."=".
			"::";
		
		$LinkBar = "";
		$layout_prev = $lng->txt("previous");
		$layout_next = $lng->txt("next");
		
		// if more entries then entries per page -> show link bar
		if ($this->max_count > $this->getLimit())
		{
			// previous link
			if ($this->getOffset() >= 1)
			{
				$prevoffset = $this->getOffset() - $this->getLimit();
				$LinkBar .= "<a class=\"small\" href=\"".$link.$prevoffset."\">".$layout_prev."</a>";
			}

			// calculate number of pages
			$pages = intval($this->max_count / $this->getLimit());

			// add a page if a rest remains
			if (($this->max_count % $this->getLimit()))
				$pages++;

			// show next link (if not last page)
			if (! ( ($this->getOffset() / $this->getLimit())==($pages-1) ) && ($pages!=1) )
			{
				if ($LinkBar != "")
					$LinkBar .= "<span class=\"small\" > | </span>"; 
				$newoffset = $this->getOffset() + $this->getLimit();
				$LinkBar .= "<a class=\"small\" href=\"".$link.$newoffset."\">".$layout_next."</a>";
			}
	
			return $LinkBar;
		}
		else
		{
			return false;
		}
	}

}
