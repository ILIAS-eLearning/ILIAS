<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Modal class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilModalGUI
{
	protected $heading = "";
	protected $body = "";
	protected $id = "";
	const TYPE_LARGE = "large";
	const TYPE_MEDIUM = "medium";
	const TYPE_SMALL = "small";

	protected $type = self::TYPE_MEDIUM;
	protected $buttons = array();

	/**
	 * Constructor
	 */
	protected function  __construct()
	{

	}

	/**
	 * Get instance
	 *
	 * @return ilModalGUI panel instance
	 */
	static function getInstance()
	{
		return new ilModalGUI();
	}

	/**
	 * Set id
	 *
	 * @param string $a_val id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}

	/**
	 * Get id
	 *
	 * @return string id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set heading
	 *
	 * @param string $a_val heading
	 */
	function setHeading($a_val)
	{
		$this->heading = $a_val;
	}

	/**
	 * Get heading
	 *
	 * @return string heading
	 */
	function getHeading()
	{
		return $this->heading;
	}

	/**
	 * Set body
	 *
	 * @param string $a_val body
	 */
	function setBody($a_val)
	{
		$this->body = $a_val;
	}

	/**
	 * Get body
	 *
	 * @return string body
	 */
	function getBody()
	{
		return $this->body;
	}
	
	/**
	 * Set type
	 *
	 * @param string $a_val type const ilModalGUI::TYPE_SMALL|ilModalGUI::TYPE_MEDIUM|ilModalGUI::TYPE_LARGE
	 */
	function setType($a_val)
	{
		$this->type = $a_val;
	}
	
	/**
	 * Get type
	 *
	 * @return string type
	 */
	function getType()
	{
		return $this->type;
	}

	/**
	 * Add button
	 *
	 * @param ilButtonBase $but button
	 */
	function addButton(ilButtonBase $but)
	{
		$this->buttons[] = $but;
	}

	/**
	 * Get buttons
	 *
	 * @return ilButtonBase[]
	 */
	function getButtons()
	{
		return $this->buttons;
	}


	/**
	 * Get HTML
	 *
	 * @return string html
	 */
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.modal.html", true, true, "Services/UIComponent/Modal");

		if (count($this->getButtons()) > 0)
		{
			foreach ($this->getButtons() as $b)
			{
				$tpl->setCurrentBlock("button");
				$tpl->setVariable("BUTTON", $b->render());
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("footer");
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("HEADING", $this->getHeading());

		$tpl->setVariable("MOD_ID", $this->getId());
		$tpl->setVariable("BODY", $this->getBody());

		switch ($this->getType())
		{
			case self::TYPE_LARGE:
				$tpl->setVariable("CLASS", "modal-lg");
				break;

			case self::TYPE_SMALL:
				$tpl->setVariable("CLASS", "modal-sm");
				break;
		}

		return $tpl->get();
	}

	/**
	 * Init javascript
	 */
	static function initJS(ilGlobalTemplateInterface $a_main_tpl = null)
	{
		global $DIC;

		if ($a_main_tpl != null)
		{
			$tpl = $a_main_tpl;
		}
		else
		{
			$tpl = $DIC["tpl"];
		}

		$tpl->addJavascript("./Services/UIComponent/Modal/js/Modal.js");
	}


}

?>
