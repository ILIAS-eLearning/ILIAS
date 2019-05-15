<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a block method of a block.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/
abstract class ilBlockGUI
{
	/**
	 * @return string
	 */
	abstract public function getBlockType(): string;

	/**
	 * Returns whether block has a corresponding repository object
	 *
	 * @return bool
	 */
	abstract protected function isRepositoryObject(): bool;

	protected $data = array();
	protected $colspan = 1;
	protected $enablenuminfo = true;
	protected $bigmode = false;
	protected $footer_links = array();
	protected $block_id = 0;
	protected $header_commands = array();
	protected $allow_moving = true;
	protected $move = array("left" => false, "right" => false, "up" => false, "down" => false);
	protected $footerinfo = false;
	protected $footerinfo_icon = false;
	protected $block_commands = array();
	protected $max_count = false;
	protected $close_command = false;
	protected $image = false;
	protected $property = false;
	protected $nav_value = "";
	protected $css_row = "";

	protected $dropdown;

	/**
	 * @var ilTemplate|null block template
	 */
	protected $tpl;

	/**
	 * @var ilTemplate|null main template
	 */
	protected $main_tpl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var
	 */
	protected $obj_def;

	/**
	 * Constructor
	 *
	 * @param
	 */
	function __construct()
	{
		global $DIC;

		$this->user = $DIC->user();
		$this->ctrl = $DIC->ctrl();
		$this->access = $DIC->access();
		$this->lng = $DIC->language();
		$this->main_tpl = $DIC["tpl"];
		$this->obj_def = $DIC["objDefinition"];

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initConnection();
		$this->main_tpl->addJavaScript("./Services/Block/js/ilblockcallback.js");

		$this->setLimit($this->user->getPref("hits_per_page"));
	}


	/**
	 * Set Data.
	 *
	 * @param    array $a_data Data
	 */
	function setData($a_data)
	{
		$this->data = $a_data;
	}

	/**
	 * Get Data.
	 *
	 * @return    array    Data
	 */
	function getData()
	{
		return $this->data;
	}

	/**
	 * Set Big Mode.
	 *
	 * @param    boolean $a_bigmode Big Mode
	 */
	function setBigMode($a_bigmode)
	{
		$this->bigmode = $a_bigmode;
	}

	/**
	 * Get Big Mode.
	 *
	 * @return    boolean    Big Mode
	 */
	function getBigMode()
	{
		return $this->bigmode;
	}

	/**
	 * Set Block Id
	 *
	 * @param    int $a_block_id Block ID
	 */
	function setBlockId($a_block_id = 0)
	{
		$this->block_id = $a_block_id;
	}

	/**
	 * Get Block Id
	 *
	 * @return    int            Block Id
	 */
	function getBlockId()
	{
		return $this->block_id;
	}


	/**
	 * Set GuiObject.
	 * Only used for repository blocks, that are represented as
	 * real repository objects (have a ref id and permissions)
	 *
	 * @param    object $a_gui_object GUI object
	 */
	public function setGuiObject(&$a_gui_object)
	{
		$this->gui_object = $a_gui_object;
	}

	/**
	 * Get GuiObject.
	 *
	 * @return    object    GUI object
	 */
	public function getGuiObject()
	{
		return $this->gui_object;
	}


	/**
	 * Set Title.
	 *
	 * @param    string $a_title Title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get Title.
	 *
	 * @return    string    Title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set Image.
	 *
	 * @param    string $a_image Image
	 */
	function setImage($a_image)
	{
		$this->image = $a_image;
	}

	/**
	 * Get Image.
	 *
	 * @return    string    Image
	 */
	function getImage()
	{
		return $this->image;
	}

	/**
	 * Set Offset.
	 *
	 * @param    int $a_offset Offset
	 */
	function setOffset($a_offset)
	{
		$this->offset = $a_offset;
	}

	/**
	 * Get Offset.
	 *
	 * @return    int    Offset
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
	 * @param    int $a_limit Limit
	 */
	function setLimit($a_limit)
	{
		$this->limit = $a_limit;
	}

	/**
	 * Get Limit.
	 *
	 * @return    int    Limit
	 */
	function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Set EnableEdit.
	 *
	 * @param    boolean $a_enableedit EnableEdit
	 */
	function setEnableEdit($a_enableedit)
	{
		$this->enableedit = $a_enableedit;
	}

	/**
	 * Get EnableEdit.
	 *
	 * @return    boolean    EnableEdit
	 */
	function getEnableEdit()
	{
		return $this->enableedit;
	}

	/**
	 * Set RepositoryMode.
	 *
	 * @param    boolean $a_repositorymode RepositoryMode
	 */
	function setRepositoryMode($a_repositorymode)
	{
		$this->repositorymode = $a_repositorymode;
	}

	/**
	 * Get RepositoryMode.
	 *
	 * @return    boolean    RepositoryMode
	 */
	function getRepositoryMode()
	{
		return $this->repositorymode;
	}

	/**
	 * Set Footer Info.
	 *
	 * @param    string $a_footerinfo Footer Info
	 */
	function setFooterInfo($a_footerinfo, $a_hide_and_icon = false)
	{
		if ($a_hide_and_icon)
		{
			$this->footerinfo_icon = $a_footerinfo;
		} else
		{
			$this->footerinfo = $a_footerinfo;
		}
	}

	/**
	 * Get Footer Info.
	 *
	 * @return    string    Footer Info
	 */
	function getFooterInfo($a_hide_and_icon = false)
	{
		if ($a_hide_and_icon)
		{
			return $this->footerinfo_icon;
		} else
		{
			return $this->footerinfo;
		}
	}

	/**
	 * Set Subtitle.
	 *
	 * @param    string $a_subtitle Subtitle
	 */
	function setSubtitle($a_subtitle)
	{
		$this->subtitle = $a_subtitle;
	}

	/**
	 * Get Subtitle.
	 *
	 * @return    string    Subtitle
	 */
	function getSubtitle()
	{
		return $this->subtitle;
	}

	/**
	 * Set Ref Id (only used if isRepositoryObject() is true).
	 *
	 * @param    int $a_refid Ref Id
	 */
	function setRefId($a_refid)
	{
		$this->refid = $a_refid;
	}

	/**
	 * Get Ref Id (only used if isRepositoryObject() is true).
	 *
	 * @return    int        Ref Id
	 */
	function getRefId()
	{
		return $this->refid;
	}

	/**
	 * Set Administration Commmands.
	 *
	 * @param    boolean $a_admincommands Administration Commmands
	 */
	function setAdminCommands($a_admincommands)
	{
		$this->admincommands = $a_admincommands;
	}

	/**
	 * Get Administration Commmands.
	 *
	 * @return    boolean    Administration Commmands
	 */
	function getAdminCommands()
	{
		return $this->admincommands;
	}

	/**
	 * Set Columns Span.
	 *
	 * @param    int $a_colspan Columns Span
	 */
	function setColSpan($a_colspan)
	{
		$this->colspan = $a_colspan;
	}

	/**
	 * Get Columns Span.
	 *
	 * @return    int    Columns Span
	 */
	function getColSpan()
	{
		return $this->colspan;
	}

	/**
	 * Set Enable Item Number Info.
	 *
	 * @param    boolean $a_enablenuminfo Enable Item Number Info
	 */
	function setEnableNumInfo($a_enablenuminfo)
	{
		$this->enablenuminfo = $a_enablenuminfo;
	}

	/**
	 * Get Enable Item Number Info.
	 *
	 * @return    boolean    Enable Item Number Info
	 */
	function getEnableNumInfo()
	{
		return $this->enablenuminfo;
	}

	/**
	 * This function is supposed to be used for block type specific
	 * properties, that should be inherited through ilColumnGUI->setBlockProperties
	 *
	 * @param    string $a_properties properties array (key => value)
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
	 * @param    string $a_rowtemplatename Row Template Name
	 */
	function setRowTemplate($a_rowtemplatename, $a_rowtemplatedir = "")
	{
		$this->rowtemplatename = $a_rowtemplatename;
		$this->rowtemplatedir = $a_rowtemplatedir;
	}

	final public function getNavParameter()
	{
		return $this->getBlockType() . "_" . $this->getBlockId() . "_blnav";
	}

	final public function getConfigParameter()
	{
		return $this->getBlockType() . "_" . $this->getBlockId() . "_blconf";
	}

	final public function getMoveParameter()
	{
		return $this->getBlockType() . "_" . $this->getBlockId() . "_blmove";
	}

	/**
	 * Get Row Template Name.
	 *
	 * @return    string    Row Template Name
	 */
	function getRowTemplateName()
	{
		return $this->rowtemplatename;
	}

	/**
	 * Get Row Template Directory.
	 *
	 * @return    string    Row Template Directory
	 */
	function getRowTemplateDir()
	{
		return $this->rowtemplatedir;
	}

	/**
	 * Add Block Command.
	 *
	 * @param    string $a_href command link target
	 * @param    string $a_text text
	 */
	function addBlockCommand($a_href, $a_text, $a_target = "", $a_img = "", $a_right_aligned = false,
							 $a_checked = false, $a_html = "")
	{
		return $this->block_commands[] =
			array("href" => $a_href,
				"text" => $a_text, "target" => $a_target, "img" => $a_img,
				"right" => $a_right_aligned, "checked" => $a_checked, "html" => $a_html);
	}

	/**
	 * Get Block commands.
	 *
	 * @return    array    block commands
	 */
	function getBlockCommands()
	{
		return $this->block_commands;
	}

	/**
	 * Add Header Block Command.
	 *
	 * @param    string $a_href command link target
	 * @param    string $a_text text
	 */
	function addHeaderCommand($a_href, $a_text, $a_as_close = false)
	{
		if ($a_as_close)
		{
			$this->close_command = $a_href;
		} else
		{
			$this->header_commands[] =
				array("href" => $a_href,
					"text" => $a_text);
		}
	}

	/**
	 * Get Header Block commands.
	 *
	 * @return    array    header block commands
	 */
	function getHeaderCommands()
	{
		return $this->header_commands;
	}

	/**
	 * Add a footer text/link
	 */
	function addFooterLink($a_text, $a_href = "", $a_onclick = "", $a_block_id = "",
						   $a_top = false, $a_omit_separator = false, $a_checked = false)
	{
		$this->footer_links[] = array(
			"text" => $a_text,
			"href" => $a_href,
			"onclick" => $a_onclick,
			"block_id" => $a_block_id,
			"top" => $a_top,
			"omit_separator" => $a_omit_separator,
			"checked" => $a_checked);
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
	 * Get HTML.
	 */
	function getHTML()
	{
		if ($this->new_rendering)
		{
			return $this->getHTMLNew();
		}

		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilAccess = $this->access;
		$ilUser = $this->user;
		$objDefinition = $this->obj_def;

		if ($this->isRepositoryObject())
		{
			if (!$ilAccess->checkAccess("read", "", $this->getRefId()))
			{
				return "";
			}
		}

		$this->tpl = new ilTemplate("tpl.block.html", true, true, "Services/Block");

//		$this->handleConfigStatus();

		$this->fillDataSection();

		if ($this->getRepositoryMode() && $this->isRepositoryObject())
		{
			// #10993
			if ($this->getAdminCommands())
			{
				$this->tpl->setCurrentBlock("block_check");
				$this->tpl->setVariable("BL_REF_ID", $this->getRefId());
				$this->tpl->parseCurrentBlock();
			}

			if ($ilAccess->checkAccess("delete", "", $this->getRefId()))
			{
				$this->addBlockCommand(
					"ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $_GET["ref_id"] . "&cmd=delete" .
					"&item_ref_id=" . $this->getRefId(),
					$lng->txt("delete"));

				// see ilObjectListGUI::insertCutCommand();
				$this->addBlockCommand(
					"ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $_GET["ref_id"] . "&cmd=cut" .
					"&item_ref_id=" . $this->getRefId(),
					$lng->txt("move"));
			}

			// #14595 - see ilObjectListGUI::insertCopyCommand()
			if ($ilAccess->checkAccess("copy", "", $this->getRefId()))
			{
				$parent_type = ilObject::_lookupType($_GET["ref_id"], true);
				$parent_gui = "ilObj" . $objDefinition->getClassName($parent_type) . "GUI";

				$ilCtrl->setParameterByClass("ilobjectcopygui", "source_id", $this->getRefId());
				$copy_cmd = $ilCtrl->getLinkTargetByClass(
					array("ilrepositorygui", $parent_gui, "ilobjectcopygui"),
					"initTargetSelection");

				// see ilObjectListGUI::insertCopyCommand();
				$this->addBlockCommand(
					$copy_cmd,
					$lng->txt("copy"));
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

		$this->dropdown = array();

		// commands
		if (count($this->getBlockCommands()) > 0)
		{
			$has_block_command = false;

			foreach ($this->getBlockCommands() as $command)
			{
				if (!$command["img"] && !$command["html"])
				{
					$this->dropdown[] = $command;
					continue;
				}

				$has_block_command = true;

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
				} else if ($command["html"] != "")
				{
					if ($command['href'] !== '')
					{
						$this->tpl->setCurrentBlock("bc_html");
						$this->tpl->setVariable("HTML", $command["html"]);
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("block_command");
					} else
					{
						$this->tpl->setCurrentBlock('bc_html_nohref');
						$this->tpl->setVariable('HTML_NOHREF', $command['html']);
						$this->tpl->parseCurrentBlock();
						$this->tpl->setCurrentBlock("block_command");
					}
				} else
				{
					$this->tpl->setCurrentBlock("block_command");
					$this->tpl->setVariable("CMD_TEXT", $command["text"]);
					$this->tpl->setVariable("BC_CLASS", 'class="il_ContainerItemCommand"');
				}

				if ($command['href'] !== '')
				{
					$this->tpl->setVariable("CMD_HREF", $command["href"]);
				}
				$this->tpl->parseCurrentBlock();
			}

			if ($has_block_command)
			{
				$this->tpl->setCurrentBlock("block_commands");
				$this->tpl->setVariable("CCOLSPAN", $this->getColSpan());
				$this->tpl->parseCurrentBlock();
			}
		}

		// fill previous next
		$this->fillPreviousNext();

		// fill footer
		$this->fillFooter();


		// for screen readers we first output the title and the commands
		// (e.g. close icon afterwards), otherwise we first output the
		// header commands, since we want to have the close icon top right
		// and not floated after the title
		if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization"))
		{
			$this->fillHeaderTitleBlock();
			$this->fillHeaderCommands();
		} else
		{
			$this->fillHeaderCommands();
			$this->fillHeaderTitleBlock();
		}

		$this->tpl->setVariable("COLSPAN", $this->getColSpan());
		if ($this->getBigMode())
		{
			$this->tpl->touchBlock("hclassb");
		} else
		{
			$this->tpl->touchBlock("hclass");
		}

		if ($ilCtrl->isAsynch())
		{
			// return without div wrapper
			echo $this->tpl->get();
			//echo $this->tpl->getAsynch();
		} else
		{
			// return incl. wrapping div with id
			return '<div id="' . "block_" . $this->getBlockType() . "_" . $this->block_id . '">' .
				$this->tpl->get() . '</div>';
		}
	}

	/**
	 * Fill header commands block
	 */
	function fillHeaderCommands()
	{
		$lng = $this->lng;
		$ilCtrl = $this->ctrl;


		// header commands
		if (count($this->getHeaderCommands()) > 0)
		{

			foreach ($this->getHeaderCommands() as $command)
			{
				$this->tpl->setCurrentBlock("header_command");
				$this->tpl->setVariable("HREF_HCOMM", $command["href"]);
				$this->tpl->setVariable("TXT_HCOMM", $command["text"]);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("header_commands");
			$this->tpl->parseCurrentBlock();
		}

		// adv selection gui
		include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
		$dropdown = new ilAdvancedSelectionListGUI();
		$dropdown->setUseImages(true);
		$dropdown->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK_BUTTON);
		$dropdown->setHeaderIcon(ilAdvancedSelectionListGUI::ICON_CONFIG);
		$dropdown->setId("block_dd_" . $this->getBlockType() . "_" . $this->block_id);
		foreach ($this->dropdown as $item)
		{
			if ($item["href"] || $item["onclick"])
			{
				if ($item["checked"])
				{
					$item["image"] = ilUtil::getImagePath("icon_checked.svg");
				}
				$dropdown->addItem($item["text"], "", $item["href"], $item["image"],
					$item["text"], "", "", false, $item["onclick"]);
			}
		}
		$dropdown = $dropdown->getHTML();
		$this->tpl->setCurrentBlock("header_dropdown");
		$this->tpl->setVariable("ADV_DROPDOWN", $dropdown);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("hitem");
		$this->tpl->parseCurrentBlock();
	}


	/**
	 * Fill header title block (title and
	 */
	function fillHeaderTitleBlock()
	{
		$lng = $this->lng;

		// image
		if ($this->getImage() != "")
		{
			$this->tpl->setCurrentBlock("block_img");
			$this->tpl->setVariable("IMG_BLOCK", $this->getImage());
			$this->tpl->setVariable("IMID",
				"block_" . $this->getBlockType() . "_" . $this->block_id);
			$this->tpl->setVariable("IMG_ALT",
				str_replace(array("'", '"'), "", strip_tags($lng->txt("icon") . " " . $this->getTitle())));
			$this->tpl->parseCurrentBlock();
		}

		// header title
		$this->tpl->setCurrentBlock("header_title");
		$this->tpl->setVariable("BTID",
			"block_" . $this->getBlockType() . "_" . $this->block_id);
		$this->tpl->setVariable("BLOCK_TITLE",
			$this->getTitle());
		$this->tpl->setVariable("TXT_BLOCK",
			$lng->txt("block"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("hitem");
		$this->tpl->parseCurrentBlock();
	}


	/**
	 * Call this from overwritten fillDataSection(), if standard row based data is not used.
	 */
	function setDataSection($a_content)
	{
		$this->tpl->setCurrentBlock("data_section");
		$this->tpl->setVariable("DATA", $a_content);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setVariable("BLOCK_ROW", "");
	}

	/**
	 * Standard implementation for row based data.
	 * Overwrite this and call setContent for other data.
	 */
	function fillDataSection()
	{
		$this->nav_value = (isset($_POST[$this->getNavParameter()]) && $_POST[$this->getNavParameter()] != "")
			? $_POST[$this->getNavParameter()]
			: (isset($_GET[$this->getNavParameter()]) ? $_GET[$this->getNavParameter()] : $this->nav_value);
		$this->nav_value = ($this->nav_value == "" && isset($_SESSION[$this->getNavParameter()]))
			? $_SESSION[$this->getNavParameter()]
			: $this->nav_value;

		$_SESSION[$this->getNavParameter()] = $this->nav_value;

		$nav = explode(":", $this->nav_value);
		if (isset($nav[2]))
		{
			$this->setOffset($nav[2]);
		} else
		{
			$this->setOffset(0);
		}

		// data
		$this->tpl->addBlockFile("BLOCK_ROW", "block_row", $this->getRowTemplateName(),
			$this->getRowTemplateDir());

		$data = $this->getData();
		$this->max_count = count($data);
		$this->correctOffset();
		$data = array_slice($data, $this->getOffset(), $this->getLimit());

		$this->preloadData($data);

		foreach ($data as $record)
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
			$this->tpl->setVariable("VAL_" . strtoupper($key), $value);
		}
	}

	function fillFooter()
	{
	}

	final protected function fillRowColor($a_placeholder = "CSS_ROW")
	{
		$this->css_row = ($this->css_row != "ilBlockRow1")
			? "ilBlockRow1"
			: "ilBlockRow2";
		$this->tpl->setVariable($a_placeholder, $this->css_row);
	}

	/**
	 * Fill previous/next row
	 */
	function fillPreviousNext()
	{
		$lng = $this->lng;

		// table pn numinfo
		$numinfo = "";
		if ($this->getEnableNumInfo() && $this->max_count > 0)
		{
			$start = $this->getOffset() + 1;                // compute num info
			$end = $this->getOffset() + $this->getLimit();

			if ($end > $this->max_count or $this->getLimit() == 0)
			{
				$end = $this->max_count;
			}

			$numinfo = "(" . $start . "-" . $end . " " . strtolower($lng->txt("of")) . " " . $this->max_count . ")";
		}

		$this->setPreviousNextLinks();
		$this->fillFooterLinks(true, $numinfo);

	}

	/**
	 * Get previous/next linkbar.
	 *
	 * @author Sascha Hofmann <shofmann@databay.de>
	 *
	 * @return    array    linkbar or false on error
	 */
	function setPreviousNextLinks()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;

		// if more entries then entries per page -> show link bar
		if ($this->max_count > $this->getLimit() && ($this->getLimit() != 0))
		{
			// previous link
			if ($this->getOffset() >= 1)
			{
				$prevoffset = $this->getOffset() - $this->getLimit();

				$ilCtrl->setParameterByClass("ilcolumngui",
					$this->getNavParameter(), "::" . $prevoffset);

				// ajax link
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "block_" . $this->getBlockType() . "_" . $this->block_id);
				$block_id = "block_" . $this->getBlockType() . "_" . $this->block_id;
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
			if (!(($this->getOffset() / $this->getLimit()) == ($pages - 1)) && ($pages != 1))
			{
				$newoffset = $this->getOffset() + $this->getLimit();

				$ilCtrl->setParameterByClass("ilcolumngui",
					$this->getNavParameter(), "::" . $newoffset);

				// ajax link
				$ilCtrl->setParameterByClass("ilcolumngui",
					"block_id", "block_" . $this->getBlockType() . "_" . $this->block_id);
				//$this->tpl->setCurrentBlock("pnonclick");
				$block_id = "block_" . $this->getBlockType() . "_" . $this->block_id;
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
		} else
		{
			return false;
		}
	}

	/**
	 * Fill footer links
	 *
	 * @return    array    linkbar or false on error
	 */
	function fillFooterLinks($a_top = false, $a_numinfo = "")
	{
		$first = true;
		$flinks = $this->getFooterLinks();

		$prefix = ($a_top) ? "top" : "foot";

		$has_link = false;

		$omit_separator = false;
		foreach ($flinks as $flink)
		{
			if ($flink["top"] != $a_top)
			{
				continue;
			}

			if (!$a_top)
			{
				if ($flink["onclick"])
				{
					$flink["onclick"] = "ilBlockJSHandler('" . $flink["block_id"] .
						"','" . $flink["onclick"] . "')";
				}
				$this->dropdown[] = $flink;
				continue;
			}

			$has_link = true;

			if (!$first && !$omit_separator)
			{
				$this->tpl->touchBlock($prefix . "_delim");
				$this->tpl->touchBlock($prefix . "_item");
			}

			// ajax link
			if ($flink["onclick"] != "")
			{
				$this->tpl->setCurrentBlock($prefix . "_onclick");
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
				$this->tpl->setCurrentBlock($prefix . "_link");
				$this->tpl->setVariable("FHREF",
					$flink["href"]);
				$this->tpl->setVariable("FLINK", $flink["text"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock($prefix . "_item");
			} else
			{
				$this->tpl->setCurrentBlock($prefix . "_text");
				$this->tpl->setVariable("FTEXT", $flink["text"]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->touchBlock($prefix . "_item");
			}

			$first = false;
			$omit_separator = $flink["omit_separator"];
		}

		if ($a_numinfo != "" && $has_link)
		{
			$this->tpl->setVariable("NUMINFO", $a_numinfo);
			$first = false;
		}

		/*
		if (!$first)
		{
			$this->tpl->setVariable("PCOLSPAN", $this->getColSpan());
			$this->tpl->setCurrentBlock($prefix."_row");
			$this->tpl->parseCurrentBlock();
		}		 
		*/
	}


	/**
	 * Can be overwritten in subclasses. Only the visible part of the complete data was passed so a preload of the visible data is possible.
	 * @param array $data
	 */
	protected function preloadData(array $data)
	{
	}

	/**
	 * Use this for final get before sending asynchronous output (ajax)
	 * per echo to output.
	 */
	public function getAsynch()
	{
		header("Content-type: text/html; charset=UTF-8");
		return $this->tpl->get();
	}

	//
	// New rendering
	//

	// temporary flag
	protected $new_rendering = false;

	/**
	 * Get HTML.
	 */
	function getHTMLNew()
	{
		global $DIC;
		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		$actions = [];

		foreach ($this->getHeaderCommands() as $command)
		{
			$actions[] = $factory->button()->shy($command["text"], $command["href"]);
		}
		foreach ($this->getFooterLinks() as $command)
		{
			$href = ($command["onclick"] != "")
				? ""
				: $command["href"];
			$button = $factory->button()->shy($command["text"], $href);
			if ($command["onclick"])
			{
				$button = $button->withOnLoadCode(function($id) use ($command) {
					return
						"$(\"#$id\").click(function() { ilBlockJSHandler('" . $command["block_id"] .
						"','" . $command["onclick"] . "');});";
				});
			}
			$actions[] = $button;
		}
		foreach ($this->getBlockCommands() as $command)
		{
			$actions[] = $factory->button()->shy($command["text"], $command["href"]);
		}


		$actions = $factory->dropdown()->standard($actions);

		$legacy = $factory->legacy("Legacy content");

		$panel = $factory->panel()->secondary()->legacy(
			$this->getTitle(),
			$legacy)->withActions($actions);

		$html = $renderer->render($panel);

		$this->new_rendering = false;
		$html.= $this->getHTML();

		return $html;
	}
}
