<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define ("IL_COL_LEFT", "left");
define ("IL_COL_RIGHT", "right");
define ("IL_COL_CENTER", "center");

define ("IL_SCREEN_SIDE", "");
define ("IL_SCREEN_CENTER", "center");
define ("IL_SCREEN_FULL", "full");

/**
* Column user interface class. This class is used on the personal desktop,
* the info screen class and witin container classes.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilColumnGUI: ilCalendarGUI 
* @ilCtrl_Calls ilColumnGUI:
*/
class ilColumnGUI
{
	protected $side = IL_COL_RIGHT;
	protected $type;
	protected $enableedit = false;
	protected $repositorymode = false;
	protected $repositoryitems = array();
	
	// all blocks that are repository objects
	protected $rep_block_types = array("feed","poll");
	protected $block_property = array();
	protected $admincommands = null;
	protected $movementmode = null;
	protected $enablemovement = false;

	/**
	 * @var ilAdvancedSelectionListGUI
	 */
	protected $action_menu;
	
	//
	// This two arrays may be replaced by some
	// xml or other magic in the future...
	//
	
	static protected $locations = array(
		"ilNewsForContextBlockGUI" => "Services/News/",
		"ilCalendarBlockGUI" => "Services/Calendar/",
		"ilPDCalendarBlockGUI" => "Services/Calendar/",
		"ilPDNotesBlockGUI" => "Services/Notes/",
		"ilPDMailBlockGUI" => "Services/Mail/",
		"ilUsersOnlineBlockGUI" => "Services/PersonalDesktop/",
		"ilPDSysMessageBlockGUI" => "Services/Mail/",
		"ilPDSelectedItemsBlockGUI" => "Services/PersonalDesktop/",
		"ilBookmarkBlockGUI" => "Services/Bookmarks/",
		"ilPDNewsBlockGUI" => "Services/News/",
		"ilExternalFeedBlockGUI" => "Services/Block/",
		"ilPDExternalFeedBlockGUI" => "Services/Feeds/",
		'ilPDTaggingBlockGUI' => 'Services/Tagging/',
		'ilChatroomBlockGUI' => 'Modules/Chatroom/',
		'ilPollBlockGUI' => 'Modules/Poll/',
		'ilClassificationBlockGUI' => 'Services/Classification/',	
	);
	
	static protected $block_types = array(
		"ilPDMailBlockGUI" => "pdmail",
		"ilPDNotesBlockGUI" => "pdnotes",
		"ilUsersOnlineBlockGUI" => "pdusers",
		"ilPDNewsBlockGUI" => "pdnews",
		"ilBookmarkBlockGUI" => "pdbookm",
		"ilNewsForContextBlockGUI" => "news",
		"ilCalendarBlockGUI" => "cal",
		"ilPDCalendarBlockGUI" => "pdcal",
		"ilExternalFeedBlockGUI" => "feed",
		"ilPDExternalFeedBlockGUI" => "pdfeed",
		"ilPDSysMessageBlockGUI" => "pdsysmess",
		"ilPDSelectedItemsBlockGUI" => "pditems",
		'ilPDTaggingBlockGUI' => 'pdtag',
		'ilChatroomBlockGUI' => 'chatviewer',
		'ilPollBlockGUI' => 'poll',
		'ilClassificationBlockGUI' => 'clsfct',
	);
	
		
	protected $default_blocks = array(
		"cat" => array(
			"ilNewsForContextBlockGUI" => IL_COL_RIGHT,
			"ilClassificationBlockGUI" => IL_COL_RIGHT
			),
		"crs" => array(
			"ilNewsForContextBlockGUI" => IL_COL_RIGHT,
			"ilCalendarBlockGUI" => IL_COL_RIGHT,
			"ilClassificationBlockGUI" => IL_COL_RIGHT
			),
		"grp" => array(
			"ilNewsForContextBlockGUI" => IL_COL_RIGHT,
			"ilCalendarBlockGUI" => IL_COL_RIGHT,
			"ilClassificationBlockGUI" => IL_COL_RIGHT
			),
		"frm" => array("ilNewsForContextBlockGUI" => IL_COL_RIGHT),
		"root" => array(),
		"info" => array(
			"ilNewsForContextBlockGUI" => IL_COL_RIGHT),
		"pd" => array(
			"ilPDCalendarBlockGUI" => IL_COL_RIGHT,
			"ilPDSysMessageBlockGUI" => IL_COL_LEFT,
			"ilPDNewsBlockGUI" => IL_COL_LEFT,
			"ilPDSelectedItemsBlockGUI" => IL_COL_CENTER,
			"ilPDMailBlockGUI" => IL_COL_RIGHT,
			"ilPDNotesBlockGUI" => IL_COL_RIGHT,
			"ilUsersOnlineBlockGUI" => IL_COL_RIGHT,
			"ilBookmarkBlockGUI" => IL_COL_RIGHT,
			"ilPDTaggingBlockGUI" => IL_COL_RIGHT,
			"ilChatroomBlockGUI" => IL_COL_RIGHT
			)
		);

	// these are only for pd blocks
	// other blocks are rep objects now
	protected $custom_blocks = array(
		"cat" => array(),
		"crs" => array(),
		"grp" => array(),
		"frm" => array(),
		"root" => array(),
		"info" => array(),
		"fold" => array(),
		"pd" => array("ilPDExternalFeedBlockGUI")
		);
		
	// check global activation for these block types
	// @todo: add calendar
	protected $check_global_activation = 
		array("news" => true,
			"cal"	=> true,
			"pdcal"	=> true,
			"pdnews" => true,
			"pdfeed" => true,			
			"pdusers" => true,
			"pdbookm" => true,
			"pdtag" => true,
			"pdnotes" => true,
			"chatviewer" => true,
			"tagcld" => true);
			
	protected $check_nr_limit =
		array("pdfeed" => true);

	/**
	* Constructor
	*
	* @param
	*/
	public function __construct($a_col_type = "", $a_side = "", $use_std_context = false)
	{
		$this->setColType($a_col_type);
		$this->setSide($a_side);
	}

	/**
	 *
	 * Adds location information of the custom block gui
	 *
	 * @access	public
	 * @static
	 * @param	string	The name of the custom block gui class
	 * @param	string	The path of the custom block gui class
	 *
	 */
	public static function addCustomBlockLocation($className, $path)
	{
		self::$locations[$className] = $path;
	}

	/**
	 *
	 * Adds the block type of the custom block gui
	 *
	 * @access	public
	 * @static
	 * @param	string	The name of the custom block gui class
	 * @param	string	The identifier (block type) of the custom block gui
	 *
	 */
	public static function addCustomBlockType($className, $identifier)
	{
		self::$block_types[$className] = $identifier;
	}

	/**
	* Get Column Side of Current Command
	*
	* @return	string	Column Side
	*/
	static function getCmdSide()
	{
		return $_GET["col_side"];
	}

	/**
	* Set Column Type.
	*
	* @param	string	$a_coltype	Column Type
	*/
	function setColType($a_coltype)
	{
		$this->coltype = $a_coltype;
	}

	/**
	* Get Column Type.
	*
	* @return	string	Column Type
	*/
	function getColType()
	{
		return $this->coltype;
	}

	/**
	* Set Side IL_COL_LEFT | IL_COL_RIGHT.
	*
	* @param	string	$a_side	Side IL_COL_LEFT | IL_COL_RIGHT
	*/
	function setSide($a_side)
	{
		$this->side = $a_side;
	}

	/**
	* Get Side IL_COL_LEFT | IL_COL_RIGHT.
	*
	* @return	string	Side IL_COL_LEFT | IL_COL_RIGHT
	*/
	function getSide()
	{
		return $this->side;
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
	* Set Movement Mode.
	*
	* @param	boolean	$a_movementmode	Movement Mode
	*/
	function setMovementMode($a_movementmode)
	{
		$this->movementmode = $a_movementmode;
	}

	/**
	* Get Movement Mode.
	*
	* @return	boolean	Movement Mode
	*/
	function getMovementMode()
	{
		return $this->movementmode;
	}

	/**
	* Set Enable Movement.
	*
	* @param	boolean	$a_enablemovement	Enable Movement
	*/
	function setEnableMovement($a_enablemovement)
	{
		$this->enablemovement = $a_enablemovement;
	}

	/**
	* Get Enable Movement.
	*
	* @return	boolean	Enable Movement
	*/
	function getEnableMovement()
	{
		return $this->enablemovement;
	}

	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		global $ilCtrl;

		if ($ilCtrl->getCmdClass() == "ilcolumngui")
		{
			switch ($ilCtrl->getCmd())
			{
				case "addBlock":
					return IL_SCREEN_CENTER;
			}
		}

		$cur_block_type = "";
		if (isset($_GET["block_type"]) && $_GET["block_type"])
		{
			$cur_block_type = $_GET["block_type"];
		}
		else if (isset($_POST["block_type"]))
		{
			$cur_block_type = $_POST["block_type"];
		}

		if ($class = array_search($cur_block_type, self::$block_types))
		{
			include_once("./".self::$locations[$class]."classes/".
				"class.".$class.".php");
			return call_user_func(array($class, 'getScreenMode'));
		}

		return IL_SCREEN_SIDE;
	}
	
	/**
	* This function is supposed to be used for block type specific
	* properties, that should be passed to ilBlockGUI->setProperty
	*
	* @param	string	$a_property		property name
	* @param	string	$a_value		property value
	*/
	function setBlockProperty($a_block_type, $a_property, $a_value)
	{
		$this->block_property[$a_block_type][$a_property] = $a_value;
	}
	
	function getBlockProperties($a_block_type)
	{
		return $this->block_property[$a_block_type];
	}

	function setAllBlockProperties($a_block_properties)
	{
		$this->block_property = $a_block_properties;
	}

	/**
	* Set Repository Items.
	*
	* @param	array	$a_repositoryitems	Repository Items
	*/
	function setRepositoryItems($a_repositoryitems)
	{
		$this->repositoryitems = $a_repositoryitems;
	}

	/**
	* Get Repository Items.
	*
	* @return	array	Repository Items
	*/
	function getRepositoryItems()
	{
		return $this->repositoryitems;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "col_side" ,$this->getSide());
		//$ilCtrl->saveParameter($this, "col_side");

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		$cur_block_type = ($_GET["block_type"])
			? $_GET["block_type"]
			: $_POST["block_type"];

		if ($next_class != "")
		{
			// forward to block
			if ($gui_class = array_search($cur_block_type, self::$block_types))
			{
				include_once("./".self::$locations[$gui_class]."classes/".
					"class.".$gui_class.".php");
				$ilCtrl->setParameter($this, "block_type", $cur_block_type);
				$block_gui = new $gui_class();
				$block_gui->setProperties($this->block_property[$cur_block_type]);
				$block_gui->setRepositoryMode($this->getRepositoryMode());
				$block_gui->setEnableEdit($this->getEnableEdit());
				$block_gui->setAdminCommands($this->getAdminCommands());

				if (in_array($gui_class, $this->custom_blocks[$this->getColType()]) ||
					in_array($cur_block_type, $this->rep_block_types))
				{
					$block_class = substr($gui_class, 0, strlen($gui_class)-3);
					include_once("./".self::$locations[$gui_class]."classes/".
						"class.".$block_class.".php");
					$app_block = new $block_class($_GET["block_id"]);
					$block_gui->setBlock($app_block);
				}
				$html = $ilCtrl->forwardCommand($block_gui);
				$ilCtrl->setParameter($this, "block_type", "");
				
				return $html;
			}
		}
		else
		{
			return $this->$cmd();
		}
	}

	/**
	* Get HTML for column.
	*/
	function getHTML()
	{
		global $ilCtrl, $ilBench;
		
		$ilBench->start("Column", "getHTML");
		
		$ilCtrl->setParameter($this, "col_side" ,$this->getSide());
		
		$this->tpl = new ilTemplate("tpl.column.html", true, true, "Services/Block");
		
		$ilBench->start("Column", "determineBlocks");
		$this->determineBlocks();
		$ilBench->stop("Column", "determineBlocks");
		
		$ilBench->start("Column", "showBlocks");
		$this->showBlocks();
		$ilBench->stop("Column", "showBlocks");
		
		if ($this->getEnableEdit() || !$this->getRepositoryMode())
		{
			$this->addHiddenBlockSelector();
		}

		$ilBench->stop("Column", "getHTML");
		
		return $this->tpl->get();
	}
	
	/**
	* Show blocks.
	*/
	function showBlocks()
	{
		global $ilCtrl, $lng, $ilUser, $ilBench;

		$i = 1;
		$sum_moveable = count($this->blocks[$this->getSide()]);

		foreach($this->blocks[$this->getSide()] as $block)
		{
			if ($ilCtrl->getContextObjType() != "user" ||
				ilBlockSetting::_lookupDetailLevel($block["type"],
					$ilUser->getId(), $block["id"]) > 0)
			{
				$gui_class = $block["class"];
				$block_class = substr($block["class"], 0, strlen($block["class"])-3);
				
				// get block gui class
				include_once("./".self::$locations[$gui_class]."classes/".
					"class.".$gui_class.".php");
				$ilBench->start("Column", "instantiate-".$block["type"]);
				$block_gui = new $gui_class();
				$ilBench->stop("Column", "instantiate-".$block["type"]);
				if (isset($this->block_property[$block["type"]]))
				{
					$block_gui->setProperties($this->block_property[$block["type"]]);
				}
				$block_gui->setRepositoryMode($this->getRepositoryMode());
				$block_gui->setEnableEdit($this->getEnableEdit());
				$block_gui->setAdminCommands($this->getAdminCommands());
				
				// get block for custom blocks
				if ($block["custom"])
				{
					$path = "./".self::$locations[$gui_class]."classes/".
						"class.".$block_class.".php";					
					if(file_exists($path))
					{
						include_once($path);
						$app_block = new $block_class($block["id"]);
					}
					else
					{
						// we only need generic block
						$app_block = new ilCustomBlock($block["id"]);
					}
					$block_gui->setBlock($app_block);											
					if (isset($block["ref_id"]))
					{
						$block_gui->setRefId($block["ref_id"]);
					}
				}
	
				$ilCtrl->setParameter($this, "block_type", $block_gui->getBlockType());
				$this->tpl->setCurrentBlock("col_block");
				
				$ilBench->start("Column", "showBlocks-".$block_gui->getBlockType());
				$html = $ilCtrl->getHTML($block_gui);
				$ilBench->stop("Column", "showBlocks-".$block_gui->getBlockType());
	
				// dummy block, if non visible, but movement is ongoing
				if ($html == "" && $this->getRepositoryMode() &&
					$this->getMovementMode())
				{
					include_once("./Services/Block/classes/class.ilDummyBlockGUI.php");
					$bl = new ilDummyBlockGUI();
					$bl->setBlockId($block["id"]);
					$bl->setBlockType($block["type"]);
					$bl->setTitle($lng->txt("invisible_block"));
					$bl->setConfigMode($this->getMovementMode());
					$html = $bl->getHTML();
				}
				
				// don't render a block if it's empty
				if ($html != "")
				{
					$this->tpl->setVariable("BLOCK", $html);
					$this->tpl->parseCurrentBlock();
					$ilCtrl->setParameter($this, "block_type", "");
				}
				
				// count (moveable) blocks
				if ($block["type"] != "pdsysmess" && $block["type"] != "pdfeedb" &&
					$block["type"] != "news")
				{
					$i++;
				}
				else
				{
					$sum_moveable--;
				}
			}
		}
	}
	
	/**
	* Add hidden block and create block selectors.
	*/
	function addHiddenBlockSelector()
	{
		/**
 		 * @var $lng ilLanguage
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl
		 * $var $tpl ilTemplate
		 */
		global $lng, $ilUser, $ilCtrl, $tpl;

		// show selector for hidden blocks
		include_once("Services/Block/classes/class.ilBlockSetting.php");
		$hidden_blocks = array();

		foreach($this->blocks[$this->getSide()] as $block)
		{
			include_once("./".self::$locations[$block["class"]]."classes/".
				"class.".$block["class"].".php");
				
			if ($block["custom"] == false)
			{
				if ($ilCtrl->getContextObjType() == "user")	// personal desktop
				{
					if (ilBlockSetting::_lookupDetailLevel($block["type"], $ilUser->getId()) == 0)
					{
						$hidden_blocks[$block["type"]] = $lng->txt('block_show_'.$block["type"]);
					}
				}
				else if ($ilCtrl->getContextObjType() != "")
				{
					if (ilBlockSetting::_lookupDetailLevel($block["type"], $ilUser->getId(),
						$ilCtrl->getContextObjId()) == 0)
					{
						$hidden_blocks[$block["type"]."_".$ilCtrl->getContextObjId()] = $lng->txt('block_show_'.$block["type"]);
					}
				}
			}
			else
			{
				if (ilBlockSetting::_lookupDetailLevel($block["type"], $ilUser->getId(),
					$block["id"]) == 0)
				{
					include_once("./Services/Block/classes/class.ilCustomBlock.php");
					$cblock = new ilCustomBlock($block["id"]);
					$hidden_blocks[$block["type"]."_".$block["id"]] = sprintf($lng->txt('block_show_x'), $cblock->getTitle());
				}
			}
		}
		if(count($hidden_blocks) > 0)
		{
			foreach($hidden_blocks as $id => $title)
			{
				$ilCtrl->setParameter($this, 'block', $id);
				$this->action_menu->addItem($title, '', $ilCtrl->getLinkTarget($this, 'activateBlock'));
				$ilCtrl->setParameter($this, 'block', '');
			}
		}
		
		// create block selection list
		if (!$this->getRepositoryMode() || $this->getEnableEdit())
		{
			$add_blocks = array();
			if ($this->getSide() == IL_COL_RIGHT)
			{
				if (is_array($this->custom_blocks[$this->getColType()]))
				{
					foreach($this->custom_blocks[$this->getColType()] as $block_class)
					{
						include_once("./".self::$locations[$block_class]."classes/".
							"class.".$block_class.".php");
						$block_type = call_user_func(array($block_class, 'getBlockType'));

						// check if block type is globally (de-)activated
						if ($this->isGloballyActivated($block_type))
						{
							// check if number of blocks is limited
							if (!$this->exceededLimit($block_type))
							{
								$add_blocks[$block_type] = $lng->txt('block_create_'.$block_type);
							}
						}
					}
				}
			}
			if(count($add_blocks) > 0)
			{
				foreach($add_blocks as $id => $title)
				{
					$ilCtrl->setParameter($this, 'block_type', $id);
					$this->action_menu->addItem($title, '', $ilCtrl->getLinkTarget($this, 'addBlock'));
					$ilCtrl->setParameter($this, 'block_type', '');
				}
			}
		}

		$this->addBlockSorting();
	}

	/**
 	 *
	 */
	protected function addBlockSorting()
	{
		if($this->getSide() == IL_COL_CENTER && $this->getEnableMovement())
		{
			/**
			 * @var $ilBrowser ilBrowser
			 * @var $tpl ilTemplate
			 * @var $ilCtrl ilCtrl
			 */
			global $ilBrowser, $tpl, $ilCtrl;

			include_once 'Services/jQuery/classes/class.iljQueryUtil.php';
			iljQueryUtil::initjQuery();
			iljQueryUtil::initjQueryUI();

			if($ilBrowser->isMobile() || $ilBrowser->isIpad())
			{
				$tpl->addJavaScript('./Services/jQuery/js/jquery.ui.touch-punch.min.js');
			}
			$tpl->addJavaScript('./Services/Block/js/block_sorting.js');

			// set the col_side parameter to pass the ctrl structure flow
			$ilCtrl->setParameter($this, 'col_side', IL_COL_CENTER);

			$this->tpl->setVariable('BLOCK_SORTING_STORAGE_URL', $ilCtrl->getLinkTarget($this, 'saveBlockSortingAsynch', '', true, false));
			$this->tpl->setVariable('BLOCK_COLUMNS', json_encode(array('il_left_col', 'il_right_col')));
			$this->tpl->setVariable('BLOCK_COLUMNS_SELECTOR', '#il_left_col,#il_right_col');
			$this->tpl->setVariable('BLOCK_COLUMNS_PARAMETERS', json_encode(array(IL_COL_LEFT, IL_COL_RIGHT)));

			// restore col_side parameter
			$ilCtrl->setParameter($this, 'col_side', $this->getSide());
 		}
	}

	/**
	* Update Block (asynchronous)
	*/
	function updateBlock()
	{
		global $ilCtrl, $ilBench;
		
		$this->determineBlocks();
		$i = 1;
		$sum_moveable = count($this->blocks[$this->getSide()]);

		foreach ($this->blocks[$this->getSide()] as $block)
		{

			include_once("./".self::$locations[$block["class"]]."classes/".
				"class.".$block["class"].".php");
				
			// set block id to context obj id,
			// if block is not a custom block and context is not personal desktop
			if (!$block["custom"] && $ilCtrl->getContextObjType() != "" && $ilCtrl->getContextObjType() != "user")
			{
				$block["id"] = $ilCtrl->getContextObjId();
			}
				
			//if (is_int(strpos($_GET["block_id"], "block_".$block["type"]."_".$block["id"])))

			if ($_GET["block_id"] == "block_".$block["type"]."_".$block["id"])
			{
				$gui_class = $block["class"];
				$block_class = substr($block["class"], 0, strlen($block["class"])-3);
				
				$block_gui = new $gui_class();
				$block_gui->setProperties($this->block_property[$block["type"]]);
				$block_gui->setRepositoryMode($this->getRepositoryMode());
				$block_gui->setEnableEdit($this->getEnableEdit());
				$block_gui->setAdminCommands($this->getAdminCommands());
				
				// get block for custom blocks
				if ($block["custom"])
				{
					include_once("./".self::$locations[$gui_class]."classes/".
						"class.".$block_class.".php");
					$app_block = new $block_class($block["id"]);
					$block_gui->setBlock($app_block);
					$block_gui->setRefId($block["ref_id"]);
				}

				$ilCtrl->setParameter($this, "block_type", $block["type"]);
				echo $ilCtrl->getHTML($block_gui);
				$ilBench->save();
				exit;
			}
			
			// count (moveable) blocks
			if ($block["type"] != "pdsysmess" && $block["type"] != "pdfeedb"
				&& $block["type"] != "news")
			{
				$i++;
			}
			else
			{
				$sum_moveable--;
			}
		}
		echo "Error: ilColumnGUI::updateBlock: Block '".
			$_GET["block_id"]."' unknown.";
		exit;
	}

	/**
	* Activate hidden block
	*/
	function activateBlock()
	{
		global $ilUser, $ilCtrl;

		if ($_GET["block"] != "")
		{
			$block = explode("_", $_GET["block"]);
			include_once("Services/Block/classes/class.ilBlockSetting.php");
			ilBlockSetting::_writeDetailLevel($block[0], 2, $ilUser->getId(), $block[1]);
		}

		$ilCtrl->returnToParent($this);
	}

	/**
	* Add a block
	*/
	function addBlock()
		
	{
		global $ilCtrl;
		
		$class = array_search($_GET["block_type"], self::$block_types);

		$ilCtrl->setCmdClass($class);
		$ilCtrl->setCmd("create");
		include_once("./".self::$locations[$class]."classes/class.".$class.".php");
		$block_gui = new $class();
		$block_gui->setProperties($this->block_property[$_GET["block_type"]]);
		$block_gui->setRepositoryMode($this->getRepositoryMode());
		$block_gui->setEnableEdit($this->getEnableEdit());
		$block_gui->setAdminCommands($this->getAdminCommands());
		
		$ilCtrl->setParameter($this, "block_type", $_GET["block_type"]);
		$html = $ilCtrl->forwardCommand($block_gui);
		$ilCtrl->setParameter($this, "block_type", "");
		return $html;
	}
	
	/**
	* Determine which blocks to show.
	*/
	function determineBlocks()
	{
		global $ilUser, $ilCtrl, $ilSetting;

		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		$this->blocks[IL_COL_LEFT] = array();
		$this->blocks[IL_COL_RIGHT] = array();
		$this->blocks[IL_COL_CENTER] = array();
		
		$user_id = ($this->getColType() == "pd")
			? $ilUser->getId()
			: 0;

		$def_nr = 1000;
		if (is_array($this->default_blocks[$this->getColType()]))
		{
			foreach($this->default_blocks[$this->getColType()] as $class => $def_side)
			{
				$type = self::$block_types[$class];

				if ($this->isGloballyActivated($type))
				{
					$nr = ilBlockSetting::_lookupNr($type, $user_id);
					if ($nr === false)
					{
						$nr = $def_nr++;
					}
					
					
					// extra handling for system messages, feedback block and news
					if ($type == "news")		// always show news first
					{
						$nr = -15;
					}
					if ($type == "cal")
					{
						$nr = -8;
					}
					if ($type == "pdsysmess")		// always show sys mess first
					{
//						$nr = -15;
					}
					if ($type == "pdfeedb")		// always show feedback request second
					{
						$nr = -10;
					}
					$side = ilBlockSetting::_lookupSide($type, $user_id);
					if ($side === false)
					{
						$side = $def_side;
					}
					
					$this->blocks[$side][] = array(
						"nr" => $nr,
						"class" => $class,
						"type" => $type,
						"id" => 0,
						"custom" => false);
				}
			}
		}
		
		if (!$this->getRepositoryMode())
		{
			include_once("./Services/Block/classes/class.ilCustomBlock.php");
			$custom_block = new ilCustomBlock();
			$custom_block->setContextObjId($ilCtrl->getContextObjId());
			$custom_block->setContextObjType($ilCtrl->getContextObjType());
			$c_blocks = $custom_block->queryBlocksForContext();
	
			foreach($c_blocks as $c_block)
			{
				$type = $c_block["type"];
				
				if ($this->isGloballyActivated($type))
				{
					$class = array_search($type, self::$block_types);
					$nr = ilBlockSetting::_lookupNr($type, $user_id, $c_block["id"]);
					if ($nr === false)
					{
						$nr = $def_nr++;
					}
					$side = ilBlockSetting::_lookupSide($type, $user_id, $c_block["id"]);
					if ($side === false)
					{
						$side = IL_COL_RIGHT;
					}
	
					$this->blocks[$side][] = array(
						"nr" => $nr,
						"class" => $class,
						"type" => $type,
						"id" => $c_block["id"],
						"custom" => true);
				}
			}
		}
		else	// get all subitems
		{
			include_once("./Services/Block/classes/class.ilCustomBlock.php");
			$rep_items = $this->getRepositoryItems();

			foreach($this->rep_block_types as $block_type)
			{
				if ($this->isGloballyActivated($block_type))
				{
					if (!is_array($rep_items[$block_type]))
					{
						continue;
					}
					foreach($rep_items[$block_type] as $item)
					{
						$costum_block = new ilCustomBlock();
						$costum_block->setContextObjId($item["obj_id"]);
						$costum_block->setContextObjType($block_type);
						$c_blocks = $costum_block->queryBlocksForContext();
						$c_block = $c_blocks[0];
						
						$type = $block_type;
						$class = array_search($type, self::$block_types);
						$nr = ilBlockSetting::_lookupNr($type, $user_id, $c_block["id"]);
						if ($nr === false)
						{
							$nr = $def_nr++;
						}
						$side = ilBlockSetting::_lookupSide($type, $user_id, $c_block["id"]);
						if ($side === false)
						{
							$side = IL_COL_RIGHT;
						}
			
						$this->blocks[$side][] = array(
							"nr" => $nr,
							"class" => $class,
							"type" => $type,
							"id" => $c_block["id"],
							"custom" => true,
							"ref_id" => $item["ref_id"]);
					}
				}
			}
										
			// repository object custom blocks
			include_once("./Services/Block/classes/class.ilCustomBlock.php");
			$custom_block = new ilCustomBlock();
			$custom_block->setContextObjId($ilCtrl->getContextObjId());
			$custom_block->setContextObjType($ilCtrl->getContextObjType());	
			$c_blocks = $custom_block->queryBlocksForContext(false); // get all sub-object types
			
			foreach($c_blocks as $c_block)
			{
				$type = $c_block["type"];
				$class = array_search($type, self::$block_types);				
				
				if($class)
				{				
					$nr = $def_nr++;					
					$side = IL_COL_RIGHT;
						
					$this->blocks[$side][] = array(
						"nr" => $nr,
						"class" => $class,
						"type" => $type,
						"id" => $c_block["id"],
						"custom" => true);
				}
			}			
		}
		
		
		$this->blocks[IL_COL_LEFT] =
			ilUtil::sortArray($this->blocks[IL_COL_LEFT], "nr", "asc", true);
		$this->blocks[IL_COL_RIGHT] =
			ilUtil::sortArray($this->blocks[IL_COL_RIGHT], "nr", "asc", true);
		$this->blocks[IL_COL_CENTER] =
			ilUtil::sortArray($this->blocks[IL_COL_CENTER], "nr", "asc", true);

	}

	/**
	* Check whether a block type is globally activated
	*/
	protected function isGloballyActivated($a_type)
	{

		global $ilSetting;
		if (isset($this->check_global_activation[$a_type]) && $this->check_global_activation[$a_type])
		{
			if ($a_type == 'pdbookm')
			{
				if (!$ilSetting->get("disable_bookmarks"))
				{
					return true;
				}
				return false;
			}
			else if ($a_type == 'pdnotes')
			{
				if (!$ilSetting->get("disable_notes"))
				{
					return true;
				}
				return false;
			}	
			elseif($a_type == 'news')
			{
				include_once 'Services/Container/classes/class.ilContainer.php';
				return 
					$ilSetting->get('block_activated_news') &&
					ilContainer::_lookupContainerSetting(
							$GLOBALS['ilCtrl']->getContextObjId(),
							'cont_show_news',
							true
					);
			}
			else if ($ilSetting->get("block_activated_".$a_type))
			{
				return true;
			}
			elseif($a_type == 'cal')
			{
				include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
				return ilCalendarSettings::lookupCalendarActivated($GLOBALS['ilCtrl']->getContextObjId());
				
			}
			elseif($a_type == 'pdcal')
			{
				include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
				return ilCalendarSettings::_getInstance()->isEnabled();
			}
			elseif($a_type == "tagcld")
			{
				$tags_active = new ilSetting("tags");
				return (bool)$tags_active->get("enable", false);			
			}
			return false;
		}
		return true;
	}

	/**
	* Check whether limit is not exceeded
	*/
	protected function exceededLimit($a_type)
	{
		global $ilSetting, $ilCtrl;

		if ($this->check_nr_limit[$a_type])
		{
			if (!$this->getRepositoryMode())
			{
				include_once("./Services/Block/classes/class.ilCustomBlock.php");
				$costum_block = new ilCustomBlock();
				$costum_block->setContextObjId($ilCtrl->getContextObjId());
				$costum_block->setContextObjType($ilCtrl->getContextObjType());
				$costum_block->setType($a_type);
				$res = $costum_block->queryCntBlockForContext();
				$cnt = (int) $res[0]["cnt"];
			}
			else
			{
				return false;		// not implemented for repository yet
			}
			
			
			if ($ilSetting->get("block_limit_".$a_type) > $cnt)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		return false;
	}

	/**
 	 * Stores the block sequence asynchronously
	 */
	public function saveBlockSortingAsynch()
	{
		/**
 		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		$response = new stdClass();
		$response->success = false;

		if(!isset($_POST[IL_COL_LEFT]['sequence']) && !isset($_POST[IL_COL_RIGHT]['sequence']))
		{
			echo json_encode($response);
			return;
		};

		if(in_array($this->getColType(), array('pd')))
		{
			$response->success = true;
			
			foreach(array(IL_COL_LEFT => (array)$_POST[IL_COL_LEFT]['sequence'], IL_COL_RIGHT => (array)$_POST[IL_COL_RIGHT]['sequence']) as $side => $blocks)
			{
				$i = 2;
				foreach($blocks as  $block)
				{
					$bid = explode('_', $block);
					ilBlockSetting::_writeNumber($bid[1], $i, $ilUser->getId(), $bid[2]);
					ilBlockSetting::_writeSide($bid[1], $side, $ilUser->getId(), $bid[2]);

					$i +=2;
				}
			}
		}

		echo json_encode($response);
		exit();
	}

	/**
	 * @param \ilAdvancedSelectionListGUI $action_menu
	 * @return ilColumnGUI
	 */
	public function setActionMenu($action_menu)
	{
		$this->action_menu = $action_menu;
		return $this;
	}

	/**
	 * @return \ilAdvancedSelectionListGUI
	 */
	public function getActionMenu()
	{
		return $this->action_menu;
	}
}
