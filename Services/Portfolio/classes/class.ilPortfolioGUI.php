<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Portfolio/classes/class.ilPortfolio.php");

/**
 * Portfolio view gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPortfolioGUI: 
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolioGUI 
{
	protected $user_id; // [int]
	protected $portfolio; // [ilPortfolio]
	
	/**
	 * Constructor
	 *
	 * @param int $a_user_id
	 */
	function __construct($a_user_id)
	{
		global $ilCtrl;

		$this->user_id = (int)$a_user_id;

		$portfolio_id = $_REQUEST["prt_id"];
		$ilCtrl->setParameter($this, "prt_id", $portfolio_id);

		if($portfolio_id)
		{
			$this->initPortfolioObject($portfolio_id);
		}
	}

	/**
	 * Init portfolio object
	 *
	 * @param int $a_id
	 */
	function initPortfolioObject($a_id)
	{
		$portfolio = new ilPortfolio($a_id);
		if($portfolio->getId() && $portfolio->getUserId() == $this->user_id)
		{
			$this->portfolio = $portfolio;
		}
	}

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();

		switch($next_class)
		{				
			default:				
				$this->$cmd();
				break;
		}

		return true;
	}
	
	/**
	 * Set all tabs
	 *
	 * @param
	 * @return
	 */
	function setTabs()
	{
		
	
	}

	protected function show()
	{
		global $tpl, $lng, $ilToolbar, $ilCtrl;

		$ilToolbar->addButton($lng->txt("add_portfolio"),
			$ilCtrl->getLinkTarget($this, "add"));

		include_once "Services/Portfolio/classes/class.ilPortfolioTableGUI.php";
		$table = new ilPortfolioTableGUI($this, "show", $this->user_id);

		$tpl->setContent($table->getHTML());
	}

	protected function add()
	{
		global $tpl;

		$form = $this->initForm();

		$tpl->setContent($form->getHTML());
	}

	protected function save()
	{
		global $tpl, $lng, $ilCtrl;
		
		$form = $this->initForm();
		if($form->checkInput())
		{
			$portfolio = new ilPortfolio();
			$portfolio->setTitle($form->getInput("title"));
			$portfolio->setDescription($form->getInput("desc"));
			$portfolio->create();

			ilUtil::sendSuccess($lng->txt("portfolio_created"), true);
			$ilCtrl->redirect($this, "show");
		}

		$form->setValuesByPost();
		$tpl->setContent($form->getHTML());
	}

	protected function edit()
	{
		global $tpl;

		$form = $this->initForm("edit");

		$tpl->setContent($form->getHTML());
	}

	protected function update()
	{
		global $tpl, $lng, $ilCtrl;

		$form = $this->initForm("edit");
		if($form->checkInput())
		{
			$this->portfolio->setTitle($form->getInput("title"));
			$this->portfolio->setDescription($form->getInput("desc"));
			$this->portfolio->setOnline($form->getInput("online"));
			$this->portfolio->setDefault($form->getInput("default"));
			$this->portfolio->update();

			ilUtil::sendSuccess($lng->txt("portfolio_updated"), true);
			$ilCtrl->redirect($this, "show");
		}

		$form->setValuesByPost();
		$tpl->setContent($form->getHTML());
	}

	/**
	 * Init portfolio form
	 *
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	protected function initForm($a_mode = "create")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));		

		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);

		if($a_mode == "create")
		{
			$form->setTitle($lng->txt("portfolio_create"));
			$form->addCommandButton("save", $lng->txt("save"));
			$form->addCommandButton("show", $lng->txt("cancel"));
		}
		else
		{
			// online
			$online = new ilCheckboxInputGUI($lng->txt("online"), "online");
			$form->addItem($online);

			// default
			$default = new ilCheckboxInputGUI($lng->txt("default"), "default");
			$form->addItem($default);

			$ti->setValue($this->portfolio->getTitle());
			$ta->setValue($this->portfolio->getDescription());
			$online->setChecked($this->portfolio->isOnline());
			$default->setChecked($this->portfolio->isDefault());

			$form->setTitle($lng->txt("portfolio_edit"));
			$form->addCommandButton("update", $lng->txt("save"));
			$form->addCommandButton("show", $lng->txt("cancel"));
		}

		return $form;		
	}
}

?>