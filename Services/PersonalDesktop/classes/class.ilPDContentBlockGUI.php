<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* BlockGUI class for (centered) Content on Personal Desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilPDContentBlockGUI extends ilBlockGUI
{
	static $block_type = "pdcontent";
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();

		parent::__construct();
		
		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
		$this->setBigMode(true);
		$this->allow_moving = false;
	}

	/**
	 * @inheritdoc
	 */
	public function getBlockType(): string 
	{
		return self::$block_type;
	}

	/**
	* Set Current Item Number.
	*
	* @param	int	$a_currentitemnumber	Current Item Number
	*/
	function setCurrentItemNumber($a_currentitemnumber)
	{
		$this->currentitemnumber = $a_currentitemnumber;
	}

	/**
	* Get Current Item Number.
	*
	* @return	int	Current Item Number
	*/
	function getCurrentItemNumber()
	{
		return $this->currentitemnumber;
	}

	/**
	 * @inheritdoc
	 */
	protected function isRepositoryObject(): bool 
	{
		return false;
	}

	function getHTML()
	{
		return parent::getHTML();
	}
	
	function getContent()
	{
		return $this->content;
	}
	
	function setContent($a_content)
	{
		$this->content = $a_content;
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		$this->tpl->setVariable("BLOCK_ROW", $this->getContent());
	}

	/**
	* block footer
	*/
	function fillFooter()
	{
		//$this->fillFooterLinks();
		$lng = $this->lng;

		if (is_array($this->data))
		{
			$this->max_count = count($this->data);
		}
				
		// table footer numinfo
		if ($this->getEnableNumInfo())
		{
			$numinfo = "(".$this->getCurrentItemNumber()." ".
				strtolower($lng->txt("of"))." ".$this->max_count.")";
	
			if ($this->max_count > 0)
			{
				$this->tpl->setVariable("NUMINFO", $numinfo);
			}
		}
	}
	
	function fillPreviousNext()
	{
	}
}

?>
