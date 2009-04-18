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
abstract class ilBlockGUI
{
	abstract static function getBlockType();		// return block type, e.g. "feed"
	abstract static function isRepositoryObject();	// returns whether block has a
													// corresponding repository object
	
	protected $data = array();
	protected $colspan = 1;
	protected $enablenuminfo = true;
	protected $detail_min = 0;
	protected $detail_max = 0;
	protected $bigmode = false;
	protected $footer_links = array();
	protected $block_id = 0;
	protected $header_commands = array();
	protected $allow_moving = true;
	protected $move = array("left" => false, "right" => false, "up" => false, "down" => false);
	protected $enabledetailrow = true;
	protected $header_links = array();

	/**
	* Constructor
	*
	* @param
	*/
	function ilBlockGUI()
	{
		global $ilUser, $tpl, $ilCtrl;

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initConnection();
		$tpl->addJavaScript("./Services/Block/js/ilblockcallback.js");

		$this->setLimit($ilUser->getPref("hits_per_page"));
	}
	
	public function addHeaderLink($a_href, $a_text, $status = true)
	{
		$this->header_links[] = 
			array('href' => $a_href, 'text' => $a_text, 'status' => (bool)$status);
	}

	public function getHeaderLinks()
	{
		return $this->header_links;
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
	* Set Big Mode.
	*
	* @param	boolean	$a_bigmode	Big Mode
	*/
	function setBigMode($a_bigmode)
	{
		$this->bigmode = $a_bigmode;
	}

	/**
	* Get Big Mode.
	*
	* @return	boolean	Big Mode
	*/
	function getBigMode()
	{
		return $this->bigmode;
	}

	/**
	* Set Block Id
	*
	* @param	int			$a_block_id		Block ID
	*/
	function setBlockId($a_block_id = 0)
	{
		$this->block_id = $a_block_id;
	}

	/**
	* Get Block Id
	*
	* @return	int			Block Id
	*/
	function getBlockId()
	{
		return $this->block_id;
	}

	/**
	* Set Available Detail Levels
	*
	* @param	int		$a_max	Max Level
	* @param	int		$a_min	Min Level (Default 0)
	*/
	function setAvailableDetailLevels($a_max, $a_min = 0)
	{
		$this->detail_min = $a_min;
		$this->detail_max = $a_max;
		$this->handleDetailLevel();
	}

	/**
	* Set Current Detail Level.
	*
	* @param	int	$a_currentdetaillevel	Current Detail Level
	*/
	function setCurrentDetailLevel($a_currentdetaillevel)
	{
		$this->currentdetaillevel = $a_currentdetaillevel;
	}
	
	/**
	* Set GuiObject.
	* Only used for repository blocks, that are represented as
	* real repository objects (have a ref id and permissions)
	*
	* @param	object	$a_gui_object	GUI object
	*/
	public function setGuiObject(&$a_gui_object)
	{
		$this->gui_object = $a_gui_object;
	}

	/**
	* Get GuiObject.
	*
	* @return	object	GUI object
	*/
	public function getGuiObject()
	{
		return $this->gui_object;
	}

	/**
	* Get Current Detail Level.
	*
	* @return	int	Current Detail Level
	*/
	function getCurrentDetailLevel()
	{
		return $this->currentdetaillevel;
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
	
	function correctOffset()
	{
		if (!($this->offset < $this->max_count))
		{
			$this->setOffset(0);
		}
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
	* Set EnableEdit.
	*
	* @param	boolean	$a_enableedit	EnableEdit
	*/
	function setEnableEdit($a_enableedit)
	{
		$this->enableedit = $a_enableedit;
	}

	/**
	* Get EnableEdit.
	*
	* @return	boolean	EnableEdit
	*/
	function getEnableEdit()
	{
		return $this->enableedit;
	}

	/**
	* Set RepositoryMode.
	*
	* @param	boolean	$a_repositorymode	RepositoryMode
	*/
	function setRepositoryMode($a_repositorymode)
	{
		$this->repositorymode = $a_repositorymode;
	}

	/**
	* Get RepositoryMode.
	*
	* @return	boolean	RepositoryMode
	*/
	function getRepositoryMode()
	{
		return $this->repositorymode;
	}

	/**
	* Set Footer Info.
	*
	* @param	string	$a_footerinfo	Footer Info
	*/
	function setFooterInfo($a_footerinfo, $a_hide_and_icon = false)
	{
		if ($a_hide_and_icon)
		{
			$this->footerinfo_icon = $a_footerinfo;
		}
		else
		{
			$this->footerinfo = $a_footerinfo;
		}
	}

	/**
	* Get Footer Info.
	*
	* @return	string	Footer Info
	*/
	function getFooterInfo($a_hide_and_icon = false)
	{
		if ($a_hide_and_icon)
		{
			return $this->footerinfo_icon;
		}
		else
		{
			return $this->footerinfo;
		}
	}

	/**
	* Set Subtitle.
	*
	* @param	string	$a_subtitle	Subtitle
	*/
	function setSubtitle($a_subtitle)
	{
		$this->subtitle = $a_subtitle;
	}

	/**
	* Get Subtitle.
	*
	* @return	string	Subtitle
	*/
	function getSubtitle()
	{
		return $this->subtitle;
	}

	/**
	* Set Ref Id (only used if isRepositoryObject() is true).
	*
	* @param	int	$a_refid	Ref Id
	*/
	function setRefId($a_refid)
	{
		$this->refid = $a_refid;
	}

	/**
	* Get Ref Id (only used if isRepositoryObject() is true).
	*
	* @return	int		Ref Id
	*/
	function getRefId()
	{
		return $this->refid;
	}

	/**
	* Set Administration Commmands.
	*
	* @param	boolean	$a_admincommands	Administration Commmands
	*/
	function setAdminCommands($a_admincommands)
	{
		$this->admincommands = $a_admincommands;
	}

	/**
	* Get Administration Commmands.
	*
	* @return	boolean	Administration Commmands
	*/
	function getAdminCommands()
	{
		return $this->admincommands;
	}

	/**
	* Set Columns Span.
	*
	* @param	int	$a_colspan	Columns Span
	*/
	function setColSpan($a_colspan)
	{
		$this->colspan = $a_colspan;
	}

	/**
	* Get Columns Span.
	*
	* @return	int	Columns Span
	*/
	function getColSpan()
	{
		return $this->colspan;
	}
	
	/**
	* Set EnableDetailRow.
	*
	* @param	boolean	$a_enabledetailrow	EnableDetailRow
	*/
	function setEnableDetailRow($a_enabledetailrow)
	{
		$this->enabledetailrow = $a_enabledetailrow;
	}

	/**
	* Get EnableDetailRow.
	*
	* @return	boolean	EnableDetailRow
	*/
	function getEnableDetailRow()
	{
		return $this->enabledetailrow;
	}


	/**
	* Set Enable Item Number Info.
	*
	* @param	boolean	$a_enablenuminfo	Enable Item Number Info
	*/
	function setEnableNumInfo($a_enablenuminfo)
	{
		$this->enablenuminfo = $a_enablenuminfo;
	}

	/**
	* Get Enable Item Number Info.
	*
	* @return	boolean	Enable Item Number Info
	*/
	function getEnableNumInfo()
	{
		return $this->enablenuminfo;
	}

	/**
	* This function is supposed to be used for block type specific
	* properties, that should be inherited through ilColumnGUI->setBlockProperties
	*
	* @param	string	$a_properties		properties array (key => value)
	*/
	function setProperties($a_properties)
	{
		$this->property = $a_properties;
	}
	
	function getProperty($a_property)
	{
		return $this->property[$a_property];
	}

	function setProperty($a_property, $a_value)
	{
		$this->property[$a_property] = $a_value;
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
		return $this->getBlockType()."_".$this->getBlockId()."_blnav";
	}

	final public function getDetailParameter()
	{
		return $this->getBlockType()."_".$this->getBlockId()."_bldet";
	}

	final public function getConfigParameter()
	{
		return $this->getBlockType()."_".$this->getBlockId()."_blconf";
	}

	final public function getMoveParameter()
	{
		return $this->getBlockType()."_".$this->getBlockId()."_blmove";
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
	function addBlockCommand($a_href, $a_text, $a_target = "", $a_img = "", $a_right_aligned = false)
	{
		return $this->block_commands[] = 
			array("href" => $a_href,
				"text" => $a_text, "target" => $a_target, "img" => $a_img,
				"right" => $a_right_aligned);
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
	* Add Header Block Command.
	*
	* @param	string	$a_href		command link target
	* @param	string	$a_text		text
	*/
	function addHeaderCommand($a_href, $a_text, $a_as_close = false)
	{
		if ($a_as_close)
		{
			$this->close_command = $a_href;
		}
		else
		{
			$this->header_commands[] = 
				array("href" => $a_href,
					"text" => $a_text);
		}
	}

	/**
	* Get Header Block commands.
	*
	* @return	array	header block commands
	*/
	function getHeaderCommands()
	{
		return $this->header_commands;
	}
	
	/**
	* Add a footer text/link
	*/
	function addFooterLink($a_text, $a_href = "", $a_onclick = "", $a_block_id = "",
		$a_top = false, $a_omit_separator = false)
	{
		$this->footer_links[] = array(
			"text" => $a_text,
			"href" => $a_href,
			"onclick" => $a_onclick,
			"block_id" => $a_block_id,
			"top" => $a_top,
			"omit_separator" => $a_omit_separator);
	}

	/**
	* Get footer links.
	*/
	function getFooterLinks()
	{
		return $this->footer_links;
	}
	
	/**
	* Clear footer links.
	*/
	function clearFooterLinks()
	{
		$this->footer_links = array();
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		return IL_SCREEN_SIDE;
	}
	
	/**
	* Handle read/write current detail level.
	*/
	function handleDetailLevel()
	{
		global $ilUser;

		// set/get detail level
		if ($this->detail_max > $this->detail_min)
		{
			include_once("Services/Block/classes/class.ilBlockSetting.php");
			if (isset($_GET[$this->getDetailParameter()]))
			{
				ilBlockSetting::_writeDetailLevel($this->getBlockType(), $_GET[$this->getDetailParameter()],
					$ilUser->getId(), $this->block_id);
				$this->setCurrentDetailLevel($_GET[$this->getDetailParameter()]);
			}
			else
			{
				$this->setCurrentDetailLevel(ilBlockSetting::_lookupDetailLevel($this->getBlockType(),
					$ilUser->getId(), $this->block_id));
			}
		}
	}
	
	/**
	* Handle config status.
	*/
/*
	function handleConfigStatus()
	{
		$this->config_mode = false;

		if ($_GET[$this->getConfigParameter()] == "toggle")
		{
			if ($_SESSION[$this->getConfigParameter()] == "on")
			{
				$_SESSION[$this->getConfigParameter()] = "off";
			}
			else
			{
				$_SESSION[$this->getConfigParameter()] = "on";
			}
		}
		if ($_SESSION[$this->getConfigParameter()] == "on")
		{
			$this->config_mode = true;
		}
	}
*/
	
	/**
	* Set Config Mode (move blocks).
	*
	* @param	boolean	$a_configmode	Config Mode (move blocks)
	*/
	function setConfigMode($a_configmode)
	{
		$this->config_mode = $a_configmode;
	}

	/**
	* Get Config Mode (move blocks).
	*
	* @return	boolean	Config Mode (move blocks)
	*/
	function getConfigMode()
	{
		return $this->config_mode;
	}

	/**
	* Get HTML.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilAccess;
		
		if ($this->isRepositoryObject())
		{
			if (!$ilAccess->checkAccess("visible", "", $this->getRefId()))
			{
				return "";
			}
		}
		
		$this->tpl = new ilTemplate("tpl.block.html", true, true, "Services/Block");
		
//		$this->handleConfigStatus();
		
		$this->fillDataSection();
		
		if ($this->getRepositoryMode() && $this->isRepositoryObject()
			&& $this->getAdminCommands())
		{
			$this->tpl->setCurrentBlock("block_check");
			$this->tpl->setVariable("BL_REF_ID", $this->getRefId());
			$this->tpl->parseCurrentBlock();
			
			if ($ilAccess->checkAccess("delete", "", $this->getRefId()))
			{
				$this->addBlockCommand(
					"repository.php?ref_id=".$_GET["ref_id"]."&cmd=delete".
					"&item_ref_id=".$this->getRefId(),
					$lng->txt("delete"));
			}
		}

		// footer info
		if ($this->getFooterInfo() != "")
		{
			$this->tpl->setCurrentBlock("footer_information");
			$this->tpl->setVariable("FOOTER_INFO", $this->getFooterInfo());
			$this->tpl->setVariable("FICOLSPAN", $this->getColSpan());
			$this->tpl->parseCurrentBlock();
		}
		
		// commands
		if (count($this->getBlockCommands()) > 0)
		{

			foreach($this->getBlockCommands() as $command)
			{
				if ($command["target"] != "")
				{
					$this->tpl->setCurrentBlock("bc_target");
					$this->tpl->setVariable("CMD_TARGET", $command["target"]);
					$this->tpl->parseCurrentBlock();
				}
					
				if ($command["img"] != "")
				{
					$this->tpl->setCurrentBlock("bc_image");
					$this->tpl->setVariable("SRC_BC", $command["img"]);
					$this->tpl->setVariable("ALT_BC", $command["text"]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("block_command");
				}
				else
				{
					$this->tpl->setCurrentBlock("block_command");
					$this->tpl->setVariable("CMD_TEXT", $command["text"]);
					$this->tpl->setVariable("BC_CLASS", 'class="il_ContainerItemCommand"');
				}

				$this->tpl->setVariable("CMD_HREF", $command["href"]);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("block_commands");
			$this->tpl->setVariable("CCOLSPAN", $this->getColSpan());
			$this->tpl->parseCurrentBlock();
		}
		
		// image
		if ($this->getImage() != "")
		{
			$this->tpl->setCurrentBlock("block_img");
			$this->tpl->setVariable("IMG_BLOCK", $this->getImage());
			$this->tpl->setVariable("IMID",
				"block_".$this->getBlockType()."_".$this->block_id);
			$this->tpl->setVariable("IMG_ALT",
				str_replace(array("'",'"'), "", strip_tags($lng->txt("icon")." ".$this->getTitle())));
			$this->tpl->parseCurrentBlock();
		}
		
		// fill previous next
		$this->fillPreviousNext();

		// fill footer
		$this->fillFooter();
		
		// fill row for setting details
		$this->fillDetailRow();

		// fill row for setting details
		if ($this->allow_moving)
		{
			$this->fillMoveRow();
		}

		// header commands
		if (count($this->getHeaderCommands()) > 0 ||
			($this->detail_max > $this->detail_min && $this->detail_min == 0) ||
			$this->close_command != "" || $this->allow_moving)
		{

			foreach($this->getHeaderCommands() as $command)
			{
				$this->tpl->setCurrentBlock("header_command");
				$this->tpl->setVariable("HREF_HCOMM", $command["href"]);
				$this->tpl->setVariable("TXT_HCOMM", $command["text"]);
				$this->tpl->parseCurrentBlock();
			}

			// close button
			if (($this->detail_max > $this->detail_min && $this->detail_min == 0 &&
				!$this->getRepositoryMode())
				||
				$this->close_command != "")
			{
				$this->tpl->setCurrentBlock("header_close");
				$this->tpl->setVariable("ALT_CLOSE", $lng->txt("close"));
				if ($this->getBigMode())
				{
					$this->tpl->setVariable("IMG_CLOSE", ilUtil::getImagePath("icon_close2.gif"));
				}
				else
				{
					$this->tpl->setVariable("IMG_CLOSE", ilUtil::getImagePath("icon_close2_s.gif"));
				}
				if ($this->close_command != "")
				{
					$this->tpl->setVariable("HREF_CLOSE",
						$this->close_command);
				}
				else
				{
					$ilCtrl->setParameterByClass("ilcolumngui",
						$this->getDetailParameter(), "0");
					$this->tpl->setVariable("HREF_CLOSE",
							$ilCtrl->getLinkTargetByClass("ilcolumngui",
							""));
					$ilCtrl->setParameterByClass("ilcolumngui",
						$this->getDetailParameter(), "");
				}
				$this->tpl->parseCurrentBlock();
			}
			
			// move button
	/*		
			if ($this->allow_moving)
			{
				$ilCtrl->setParameterByClass("ilcolumngui",
					$this->getConfigParameter(), "toggle");

					// ajax link
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "block_".$this->getBlockType()."_".$this->block_id);
				$this->tpl->setCurrentBlock("oncclick");
				$this->tpl->setVariable("OC_BLOCK_ID",
					"block_".$this->getBlockType()."_".$this->block_id);
				$this->tpl->setVariable("OC_HREF",
					$ilCtrl->getLinkTargetByClass("ilcolumngui",
					"updateBlock", "", true));
				$this->tpl->parseCurrentBlock();
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "");

				// normal link
				$this->tpl->setCurrentBlock("header_config");
				$this->tpl->setVariable("IMG_CONFIG", ilUtil::getImagePath("icon_move_s.gif"));
				$this->tpl->setVariable("ALT_CONFIG", $lng->txt("move"));
				$this->tpl->setVariable("HREF_CONFIG",
					$ilCtrl->getLinkTargetByClass("ilcolumngui", ""));
				$this->tpl->parseCurrentBlock();
				$ilCtrl->setParameterByClass("ilcolumngui",
					$this->getConfigParameter(), "");
			}
*/

			$this->tpl->setCurrentBlock("header_commands");
			$this->tpl->parseCurrentBlock();
		}
		
		// header links
		if(count($this->getHeaderLinks()))
		{
			$counter = 0;
			foreach($this->getHeaderLinks() as $command)
			{
				if($counter > 0)
				{
					$this->tpl->setCurrentBlock('head_delim');
					$this->tpl->touchBlock('head_delim');
					$this->tpl->parseCurrentBlock();
				}
				if($command['status'] == true)
				{
					$this->tpl->setCurrentBlock('head_link');
					$this->tpl->setVariable('HHREF', $command['href']);
					$this->tpl->setVariable('HLINK', $command['text']);	
					$this->tpl->parseCurrentBlock();
				}
				else
				{
					$this->tpl->setCurrentBlock('head_text');
					$this->tpl->setVariable('HTEXT', $command['text']);	
					$this->tpl->parseCurrentBlock();
				}
				
				$this->tpl->setCurrentBlock('head_item');
				$this->tpl->parseCurrentBlock();
				
				++$counter;			
			}
			
			$this->tpl->setCurrentBlock('header_links');
			$this->tpl->parseCurrentBlock();
		}
		
		// title
		$this->tpl->setVariable("BTID",
			"block_".$this->getBlockType()."_".$this->block_id);
		$this->tpl->setVariable("BLOCK_TITLE",
			$this->getTitle());
		$this->tpl->setVariable("TXT_BLOCK",
			$lng->txt("block"));
		$this->tpl->setVariable("COLSPAN", $this->getColSpan());
		if ($this->getBigMode())
		{
			$this->tpl->touchBlock("hclassb");
		}
		else
		{
			$this->tpl->touchBlock("hclass");
		}

		if ($ilCtrl->isAsynch())
		{
			// return without div wrapper
			echo $this->tpl->getAsynch();
		}
		else
		{
			// return incl. wrapping div with id
			return '<div id="'."block_".$this->getBlockType()."_".$this->block_id.'">'.
				$this->tpl->get().'</div>';
		}
	}
	
	/**
	* Call this from overwritten fillDataSection(), if standard row based data is not used.
	*/
	function setDataSection($a_content)
	{
		$this->tpl->setCurrentBlock("data_section");
		$this->tpl->setVariable("DATA", $a_content);
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* Standard implementation for row based data.
	* Overwrite this and call setContent for other data.
	*/
	function fillDataSection()
	{
		$this->nav_value = ($_POST[$this->getNavParameter()] != "")
			? $_POST[$this->getNavParameter()]
			: $_GET[$this->getNavParameter()];
		$this->nav_value = ($this->nav_value == "")
			? $_SESSION[$this->getNavParameter()]
			: $this->nav_value;
			
		$_SESSION[$this->getNavParameter()] = $this->nav_value;
			
		$nav = explode(":", $this->nav_value);
		$this->setOffset($nav[2]);
		
		// data
		$this->tpl->addBlockFile("BLOCK_ROW", "block_row", $this->getRowTemplateName(),
			$this->getRowTemplateDir());
			
		$data = $this->getData();
		$this->max_count = count($data);
		$this->correctOffset();
		$data = array_slice($data, $this->getOffset(), $this->getLimit());
		
		foreach($data as $record)
		{
			$this->tpl->setCurrentBlock("block_row");
			$this->fillRowColor();
			$this->fillRow($record);
			$this->tpl->setCurrentBlock("block_row");
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function fillRow($a_set)
	{
		foreach ($a_set as $key => $value)
		{
			$this->tpl->setVariable("VAL_".strtoupper($key), $value);
		}
	}
	
	function fillFooter()
	{
	}
	
	final protected function fillRowColor($a_placeholder = "CSS_ROW")
	{
		$this->css_row = ($this->css_row != "tblrow1")
			? "tblrow1"
			: "tblrow2";
		$this->tpl->setVariable($a_placeholder, $this->css_row);
	}

	/**
	* Fill previous/next row
	*/
	function fillPreviousNext()
	{
		global $lng, $ilCtrl;

		$pn = false;
				
		// table pn numinfo
		$numinfo = "";
		if ($this->getEnableNumInfo() && $this->max_count > 0)
		{
			$start = $this->getOffset() + 1;				// compute num info
			$end = $this->getOffset() + $this->getLimit();
				
			if ($end > $this->max_count or $this->getLimit() == 0)
			{
				$end = $this->max_count;
			}
				
			$numinfo = "(".$start."-".$end." ".strtolower($lng->txt("of"))." ".$this->max_count.")";
		}

		$this->setPreviousNextLinks();
		$this->fillFooterLinks(true, $numinfo);

	}

	/**
	* Get previous/next linkbar.
	*
	* @author Sascha Hofmann <shofmann@databay.de>
	*
	* @return	array	linkbar or false on error
	*/
	function setPreviousNextLinks()
	{
		global $ilCtrl, $lng;
		
		// if more entries then entries per page -> show link bar
		if ($this->max_count > $this->getLimit() && ($this->getLimit() != 0))
		{
			// previous link
			if ($this->getOffset() >= 1)
			{
				$prevoffset = $this->getOffset() - $this->getLimit();
				
				$ilCtrl->setParameterByClass("ilcolumngui",
					$this->getNavParameter(), "::".$prevoffset);
				
				// ajax link
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "block_".$this->getBlockType()."_".$this->block_id);
				$block_id = "block_".$this->getBlockType()."_".$this->block_id;
				$onclick = $ilCtrl->getLinkTargetByClass("ilcolumngui",
					"updateBlock", "", true);
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "");
					
				// normal link
				$href = $ilCtrl->getLinkTargetByClass("ilcolumngui", "");
				$text = $lng->txt("previous");
				
				$this->addFooterLink($text, $href, $onclick, $block_id, true);
			}

			// calculate number of pages
			$pages = intval($this->max_count / $this->getLimit());

			// add a page if a rest remains
			if (($this->max_count % $this->getLimit()))
				$pages++;

			// show next link (if not last page)
			if (! ( ($this->getOffset() / $this->getLimit())==($pages-1) ) && ($pages!=1) )
			{
				$newoffset = $this->getOffset() + $this->getLimit();

				$ilCtrl->setParameterByClass("ilcolumngui",
					$this->getNavParameter(), "::".$newoffset);

				// ajax link
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "block_".$this->getBlockType()."_".$this->block_id);
				//$this->tpl->setCurrentBlock("pnonclick");
				$block_id = "block_".$this->getBlockType()."_".$this->block_id;
				$onclick = $ilCtrl->getLinkTargetByClass("ilcolumngui",
					"updateBlock", "", true);
//echo "-".$onclick."-";
				//$this->tpl->parseCurrentBlock();
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "");

				// normal link
				$href = $ilCtrl->getLinkTargetByClass("ilcolumngui", "");
				$text = $lng->txt("next");

				$this->addFooterLink($text, $href, $onclick, $block_id, true);
			}
			$ilCtrl->setParameterByClass("ilcolumngui",
				$this->getNavParameter(), "");
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* Fill footer links
	*
	* @return	array	linkbar or false on error
	*/
	function fillFooterLinks($a_top = false, $a_numinfo = "")
	{
		global $ilCtrl, $lng;

		$first = true;
		$flinks = $this->getFooterLinks();
		
		$prefix = ($a_top) ? "top" : "foot";

		$omit_separator = false;
		foreach($flinks as $flink)
		{
			if ($flink["top"] != $a_top)
			{
				continue;
			}
			
			if (!$first && !$omit_separator)
			{
				$this->tpl->touchBlock($prefix."_delim");
				$this->tpl->touchBlock($prefix."_item");
			}

			// ajax link
			if ($flink["onclick"] != "")
			{
				$this->tpl->setCurrentBlock($prefix."_onclick");
				$this->tpl->setVariable("OC_BLOCK_ID",
					$flink["block_id"]);
				$this->tpl->setVariable("OC_HREF",
					$flink["onclick"]);
				$this->tpl->parseCurrentBlock();
			}
			
			// normal link
			if ($flink["href"] != "")
			{
				// normal link
				$this->tpl->setCurrentBlock($prefix."_link");
				$this->tpl->setVariable("FHREF",
					$flink["href"]);
				$this->tpl->setVariable("FLINK", $flink["text"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock($prefix."_item");
			}
			else
			{
				$this->tpl->setCurrentBlock($prefix."_text");
				$this->tpl->setVariable("FTEXT", $flink["text"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock($prefix."_item");
			}
			$first = false;
			$omit_separator = $flink["omit_separator"];
		}

		if ($a_numinfo != "")
		{
			$this->tpl->setVariable("NUMINFO", $a_numinfo);
			$first = false;
		}

		if (!$first)
		{
			$this->tpl->setVariable("PCOLSPAN", $this->getColSpan());
			$this->tpl->setCurrentBlock($prefix."_row");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Fill Detail Setting Row.
	*/
	function fillDetailRow()
	{
		global $ilCtrl, $lng;

		if ($this->enabledetailrow == false)
		{
			return;
		}
		
		$start = ($this->detail_min < 1)
			? $start = 1
			: $this->detail_min;
		
		$end = ($this->detail_max < $this->detail_min)
			? $this->detail_min
			: $this->detail_max;
		
		$settings = array();
		for ($i = $start; $i <= $end; $i++)
		{
			$settings[] = $i;
		}
		
		if ($end > $start)
		{
			foreach ($settings as $i)
			{
				if (($i > $start && $i > 1))
				{
					//$this->tpl->touchBlock("det_delim");
					//$this->tpl->touchBlock("det_item");
				}
				if ($i != $this->getCurrentDetailLevel())
				{
					$ilCtrl->setParameterByClass("ilcolumngui",
						$this->getDetailParameter(), $i);

					// ajax link
					if ($i > 0)
					{
						$ilCtrl->setParameterByClass("ilcolumngui",
							"block_id", "block_".$this->getBlockType()."_".$this->block_id);
						$this->tpl->setCurrentBlock("onclick");
						$this->tpl->setVariable("OC_BLOCK_ID",
							"block_".$this->getBlockType()."_".$this->block_id);
						$this->tpl->setVariable("OC_HREF",
							$ilCtrl->getLinkTargetByClass("ilcolumngui",
							"updateBlock", "", true));
						$this->tpl->parseCurrentBlock();
						$ilCtrl->setParameterByClass("ilcolumngui",
							"block_id", "");
					}
					
					// normal link
					$this->tpl->setCurrentBlock("det_link");
					//$this->tpl->setVariable("DLINK", $i);
					$this->tpl->setVariable("SRC_LINK",
						ilUtil::getImagePath("details".$i.".gif"));
					$this->tpl->setVariable("ALT_LINK",
						$lng->txt("details_".$i));
					$this->tpl->setVariable("DHREF",
						$ilCtrl->getLinkTargetByClass("ilcolumngui",
						""));
					$this->tpl->parseCurrentBlock();
					$this->tpl->touchBlock("det_item");
				}
				else
				{
					$this->tpl->setCurrentBlock("det_text");
					//$this->tpl->setVariable("DTEXT", $i);
					$this->tpl->setVariable("ALT_NO_LINK",
						$lng->txt("details_".$i));
					$this->tpl->setVariable("SRC_NO_LINK",
						ilUtil::getImagePath("details".$i."off.gif"));
					$this->tpl->parseCurrentBlock();
					$this->tpl->touchBlock("det_item");
				}
			}
			
			// info + icon in detail row
			if ($this->getFooterInfo(true) != "")
			{
				$this->tpl->setCurrentBlock("det_info");
				$this->tpl->setVariable("INFO_TEXT", $this->getFooterInfo(true));
				$this->tpl->setVariable("ALT_DET_INFO", $lng->txt("info_short"));
				$this->tpl->setVariable("DI_BLOCK_ID", $this->getBlockType()."_".$this->getBlockId());
				$this->tpl->setVariable("IMG_DET_INFO", ilUtil::getImagePath("icon_info_s.gif"));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("detail_setting");
			$this->tpl->setVariable("TXT_DETAILS", $lng->txt("details"));
			$this->tpl->setVariable("DCOLSPAN", $this->getColSpan());
			$this->tpl->parseCurrentBlock();
	
			$ilCtrl->setParameterByClass("ilcolumngui",
				$this->getDetailParameter(), "");
		}
	}
	
	/**
	* Fill row for Moving
	*/
	function fillMoveRow()
	{
		global $ilCtrl, $lng;
		
		if ($this->config_mode)
		{
			if ($this->getAllowMove("left"))
			{
				$this->fillMoveLink("left", "icon_left_s.gif", $lng->txt("move_left"));
			}
			if ($this->getAllowMove("up"))
			{
				$this->fillMoveLink("up", "icon_up_s.gif", $lng->txt("move_up"));
			}
			if ($this->getAllowMove("down"))
			{
				$this->fillMoveLink("down", "icon_down_s.gif", $lng->txt("move_down"));
			}
			if ($this->getAllowMove("right"))
			{
				$this->fillMoveLink("right", "icon_right_s.gif", $lng->txt("move_right"));
			}
			$ilCtrl->setParameter($this, $this->getMoveParameter(), "");
			
			$this->tpl->setCurrentBlock("move");
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function getAllowMove($a_direction)
	{
		return $this->move[$a_direction];
	}

	function setAllowMove($a_direction, $a_allow = true)
	{
		$this->move[$a_direction] = $a_allow;
//var_dump($this->move);
	}
	
	function fillMoveLink($a_value, $a_img, $a_txt)
	{
		global $ilCtrl, $lng;

		$ilCtrl->setParameterByClass("ilcolumngui", "block_id", 
			$this->getBlockType()."_".$this->getBlockId());
		$ilCtrl->setParameterByClass("ilcolumngui", "move_dir", 
			$a_value);
		$this->tpl->setCurrentBlock("move_link");
		$this->tpl->setVariable("IMG_MOVE", ilUtil::getImagePath($a_img));
		$this->tpl->setVariable("ALT_MOVE", $a_txt);
		$this->tpl->setVariable("HREF_MOVE",
			$ilCtrl->getLinkTargetByClass("ilcolumngui",
			"moveBlock"));
		$this->tpl->parseCurrentBlock();
	}
}
