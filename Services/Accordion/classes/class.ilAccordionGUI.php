<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Accordion user interface class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id:$
*/
class ilAccordionGUI
{
	protected $items = array();
	protected static $accordion_cnt = 0;
	const VERTICAL = "vertical";
	const HORIZONTAL = "horizontal";
	const FORCE_ALL_OPEN = "ForceAllOpen";
	const FIRST_OPEN = "FirstOpen";
	const ONE_OPEN_SESSION = "OneOpenSession";
	
	/**
	* Constructor
	*/
	function __construct()
	{
		$this->setOrientation(ilAccordionGUI::VERTICAL);
	}
	
	/**
	* Set id
	*
	* @param	string	 id
	*/
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	* Get id
	*
	* @return	string	id
	*/
	function getId()
	{
		return $this->id;
	}
	
	/**
	* Set Orientation.
	*
	* @param	string	$a_orientation	Orientation
	*/
	function setOrientation($a_orientation)
	{
		if (in_array($a_orientation,
			array(ilAccordionGUI::VERTICAL, ilAccordionGUI::HORIZONTAL)))
		{
			$this->orientation = $a_orientation;
		}
	}

	/**
	* Get Orientation.
	*
	* @return	string	Orientation
	*/
	function getOrientation()
	{
		return $this->orientation;
	}

	/**
	 * Set Container CSS Class.
	 *
	 * @param	string	$a_containerclass	Container CSS Class
	 */
	function setContainerClass($a_containerclass)
	{
		$this->containerclass = $a_containerclass;
	}

	/**
	 * Get Container CSS Class.
	 *
	 * @return	string	Container CSS Class
	 */
	function getContainerClass()
	{
		return $this->containerclass;
	}

	/**
	 * Set inner Container CSS Class.
	 *
	 * @param	string	$a_containerclass	Container CSS Class
	 */
	function setInnerContainerClass($a_containerclass)
	{
		$this->icontainerclass = $a_containerclass;
	}

	/**
	 * Get inner Container CSS Class.
	 *
	 * @return	string	Container CSS Class
	 */
	function getInnerContainerClass()
	{
		return $this->icontainerclass;
	}

	/**
	* Set Header CSS Class.
	*
	* @param	string	$a_headerclass	Header CSS Class
	*/
	function setHeaderClass($a_headerclass)
	{
		$this->headerclass = $a_headerclass;
	}

	/**
	* Get Header CSS Class.
	*
	* @return	string	Header CSS Class
	*/
	function getHeaderClass()
	{
		return $this->headerclass;
	}

	 /**
	  * Set active header class
	  *
	  * @param	string	$a_h_class	Active Header CSS Class
	  */
	function setActiveHeaderClass($a_h_class)
	{
		$this->active_headerclass = $a_h_class;
	}

	/**
	 * Get active Header CSS Class.
	 *
	 * @return	string	Active header CSS Class
	 */
	function getActiveHeaderClass()
	{
		return $this->active_headerclass;
	}

	/**
	* Set Content CSS Class.
	*
	* @param	string	$a_contentclass	Content CSS Class
	*/
	function setContentClass($a_contentclass)
	{
		$this->contentclass = $a_contentclass;
	}

	/**
	* Get Content CSS Class.
	*
	* @return	string	Content CSS Class
	*/
	function getContentClass()
	{
		return $this->contentclass;
	}

		/**
	* Set ContentWidth.
	*
	* @param	integer	$a_contentwidth	ContentWidth
	*/
	function setContentWidth($a_contentwidth)
	{
		$this->contentwidth = $a_contentwidth;
	}

	/**
	* Get ContentWidth.
	*
	* @return	integer	ContentWidth
	*/
	function getContentWidth()
	{
		return $this->contentwidth;
	}

	/**
	* Set ContentHeight.
	*
	* @param	integer	$a_contentheight	ContentHeight
	*/
	function setContentHeight($a_contentheight)
	{
		$this->contentheight = $a_contentheight;
	}

	/**
	* Get ContentHeight.
	*
	* @return	integer	ContentHeight
	*/
	function getContentHeight()
	{
		return $this->contentheight;
	}

	/**
	 * Set behaviour "ForceAllOpen" | "FirstOpen" | "OneOpenSession"
	 *
	 * @param	string	behaviour
	 */
	function setBehaviour($a_val)
	{
		$this->behaviour = $a_val;
	}
	
	/**
	 * Get behaviour
	 *
	 * @return	
	 */
	function getBehaviour()
	{
		return $this->behaviour;
	}
	
	/**
	* Add javascript files that are necessary to run accordion
	*/
	static function addJavaScript()
	{
		global $tpl;
		
		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initEvent();
		ilYuiUtil::initDom();
		ilYuiUtil::initAnimation();
		ilYuiUtil::initConnection();
		$tpl->addJavaScript("./Services/Accordion/js/accordion.js", true, 3);
	}
	
	/**
	* Add required css
	*/
	static function addCss()
	{
		global $tpl;
		
		$tpl->addCss("./Services/Accordion/css/accordion.css");
	}

	/**
	* Add item
	*/
	function addItem($a_header, $a_content)
	{
		$this->items[] = array("header" => $a_header,
			"content" => $a_content);
	}
	
	/**
	* Get all items
	*/
	function getItems()
	{
		return $this->items;
	}
	
	/**
	* Get accordion html
	*/
	function getHTML()
	{
		global $ilUser;
		
		self::$accordion_cnt++;
		
		$or_short = ($this->getOrientation() == ilAccordionGUI::HORIZONTAL)
			? "H"
			: "V";
			
		$width = (int) $this->getContentWidth();
		$height = (int) $this->getContentHeight();
		if ($this->getOrientation() == ilAccordionGUI::HORIZONTAL)
		{
			if ($width == 0)
			{
				$width = 200;
			}
			if ($height == 0)
			{
				$height = 100;
			}
		}
		
		$this->addJavascript();
		$this->addCss();
		
		$tpl = new ilTemplate("tpl.accordion.html", true, true, "Services/Accordion");
		foreach ($this->getItems() as $item)
		{
			$tpl->setCurrentBlock("item");
			$tpl->setVariable("HEADER", $item["header"]);
			$tpl->setVariable("CONTENT", $item["content"]);
			$tpl->setVariable("HEADER_CLASS", $this->getHeaderClass()
				? $this->getHeaderClass() : "il_".$or_short."AccordionHead");
			$tpl->setVariable("CONTENT_CLASS", $this->getContentClass()
				? $this->getContentClass() : "il_".$or_short."AccordionContent");
			$tpl->setVariable("OR_SHORT", $or_short);
			
			$tpl->setVariable("INNER_CONTAINER_CLASS", $this->getInnerContainerClass()
				? $this->getInnerContainerClass() : "il_".$or_short."AccordionInnerContainer");


			if ($height > 0)
			{
				$tpl->setVariable("HEIGHT", "height:".$height."px;");
			}
			if ($height > 0 && $this->getOrientation() == ilAccordionGUI::HORIZONTAL)
			{
				$tpl->setVariable("HHEIGHT", "height:".$height."px;");
			}
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("CNT", self::$accordion_cnt);
		$tpl->setVariable("CONTAINER_CLASS", $this->getContainerClass()
			? $this->getContainerClass() : "il_".$or_short."AccordionContainer");
		$tpl->setVariable("ORIENTATION", $this->getOrientation());
		$tpl->setVariable("ID", $this->getId());
		if ($this->getBehaviour() == "OneOpenSession" && $this->getId() != "")
		{
			include_once("./Services/Accordion/classes/class.ilAccordionPropertiesStorage.php");
			$stor = new  ilAccordionPropertiesStorage();
			$ctab = $stor->getProperty($this->getId(), $ilUser->getId(),
				"opened");
			$tpl->setVariable("BEHAVIOUR", $ctab);
			$tpl->setVariable("SAVE_URL", "./ilias.php?baseClass=ilaccordionpropertiesstorage&cmd=setOpenedTab".
				"&accordion_id=".$this->getId()."&user_id=".$ilUser->getId());
		}
		else if ($this->getBehaviour() != "")
		{
			$tpl->setVariable("BEHAVIOUR", $this->getBehaviour());
		}
		$tpl->setVariable("OR2_SHORT", $or_short);
		if ($width > 0)
		{
			$tpl->setVariable("WIDTH", $width);
		}
		else
		{
			$tpl->setVariable("WIDTH", "null");
		}
		if ($width > 0 && $this->getOrientation() == ilAccordionGUI::VERTICAL)
		{
			$tpl->setVariable("CWIDTH", 'style="width:'.$width.'px;"');
		}

		if ($this->head_class_set)
		{
			$tpl->setVariable("ACTIVE_HEAD_CLASS", $this->getActiveHeaderClass());
		}
		else
		{
			if ($this->getOrientation() == ilAccordionGUI::VERTICAL)
			{
				$tpl->setVariable("ACTIVE_HEAD_CLASS", "il_HAccordionHeadActive");
			}
			else
			{
				$tpl->setVariable("ACTIVE_HEAD_CLASS", "il_VAccordionHeadActive");
			}
		}

		return $tpl->get();
	}
	
}
?>
