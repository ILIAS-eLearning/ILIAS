<?php
include_once("Services/Object/exceptions/class.ilObjectException.php");

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings UI class for system styles. Acts as main router for the systems styles and handles permissions checks,
 * sets tabs and title as well as description of the content section.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>

 * @version $Id$
 * @ingroup ServicesStyle
 *
 * @ilCtrl_Calls ilSystemStyleMainGUI: ilSystemStyleOverviewGUI,ilSystemStyleSettingsGUI
 * @ilCtrl_Calls ilSystemStyleMainGUI: ilSystemStyleLessGUI,ilSystemStyleIconsGUI,ilSystemStyleDocumentationGUI
 *
 */
class ilSystemStyleMainGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;


	/**
	 * @var ILIAS\DI\Container
	 */
	protected $DIC;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;


	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tabs = $DIC->tabs();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->tpl = $DIC["tpl"];

		$this->ref_id = (int) $_GET["ref_id"];
	}


	/**
	 * Main routing of the system styles. Resets ilCtrl Parameter for all subsequent generation of links.
	 *
	 * @throws ilCtrlException
	 * @throws ilObjectException
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);

		$this->ctrl->setParameterByClass('ilsystemstylesettingsgui','skin_id',$_GET["skin_id"]);
		$this->ctrl->setParameterByClass('ilsystemstylesettingsgui','style_id',$_GET["style_id"]);
		$this->ctrl->setParameterByClass('ilsystemstylelessgui','skin_id',$_GET["skin_id"]);
		$this->ctrl->setParameterByClass('ilsystemstylelessgui','style_id',$_GET["style_id"]);
		$this->ctrl->setParameterByClass('ilsystemstyleiconsgui','skin_id',$_GET["skin_id"]);
		$this->ctrl->setParameterByClass('ilsystemstyleiconsgui','style_id',$_GET["style_id"]);
		$this->ctrl->setParameterByClass('ilsystemstyledocumentationgui','skin_id',$_GET["skin_id"]);
		$this->ctrl->setParameterByClass('ilsystemstyledocumentationgui','style_id',$_GET["style_id"]);

		try{
			switch ($next_class)
			{

				case "ilsystemstylesettingsgui":
					$this->checkPermission("sty_management");
					$this->setUnderworldTabs('settings');
					$this->setUnderworldTitle();
					include_once("Settings/class.ilSystemStyleSettingsGUI.php");
					$system_styles_settings = new ilSystemStyleSettingsGUI();
					$this->ctrl->forwardCommand($system_styles_settings);
					break;
				case "ilsystemstylelessgui":
					$this->checkPermission("sty_management");
					$this->setUnderworldTabs('less');
					$this->setUnderworldTitle();
					include_once("Less/class.ilSystemStyleLessGUI.php");
					$system_styles_less = new ilSystemStyleLessGUI();
					$this->ctrl->forwardCommand($system_styles_less);
					break;
				case "ilsystemstyleiconsgui":
					$this->checkPermission("sty_management");
					$this->setUnderworldTabs('icons');
					$this->setUnderworldTitle();
					include_once("Icons/class.ilSystemStyleIconsGUI.php");
					$system_styles_icons = new ilSystemStyleIconsGUI();
					$this->ctrl->forwardCommand($system_styles_icons);
					break;
				case "ilsystemstyledocumentationgui":
					$this->checkPermission("sty_management");
					$this->setUnderworldTabs('documentation');
					$this->setUnderworldTitle();
					include_once("Documentation/class.ilSystemStyleDocumentationGUI.php");
					$system_styles_documentation = new ilSystemStyleDocumentationGUI();
					$this->ctrl->forwardCommand($system_styles_documentation);
					break;
				case "ilsystemstyleoverviewgui":
				default:
					$this->checkPermission("visible,read");
					include_once("Overview/class.ilSystemStyleOverviewGUI.php");
					$system_styles_overview = new ilSystemStyleOverviewGUI(!$this->checkPermission("sty_write_system",false)
							,$this->checkPermission("sty_management",false));
					$this->ctrl->forwardCommand($system_styles_overview);
					break;
			}
		}catch(ilObjectException $e){
			ilUtil::sendFailure($e->getMessage());
			$this->checkPermission("visible,read");
			include_once("Overview/class.ilSystemStyleOverviewGUI.php");
			$this->ctrl->setCmd("");
			$system_styles_overview = new ilSystemStyleOverviewGUI(!$this->checkPermission("sty_write_system",false),$this->checkPermission("sty_management",false));
			$this->ctrl->forwardCommand($system_styles_overview);
		}
	}

	/**
	 * Checks permission for system styles. Permissions work on two levels, ordinary rbac and the
	 * 'enable_system_styles_management' setting in the tools section of the ilias.ini.php
	 *
	 * @param $a_perm
	 * @param bool|true $a_throw_exc
	 * @return bool
	 * @throws ilObjectException
	 */
	public function checkPermission($a_perm, $a_throw_exc = true)
	{
		global $ilIliasIniFile;

		$has_perm = true;

		if($a_perm == "sty_management"){
			$has_perm = $ilIliasIniFile->readVariable("tools","enable_system_styles_management")== "1" ? true:false;
			$a_perm = "sty_write_system";
		}

		if($has_perm){
			$has_perm = $this->rbacsystem->checkAccess($a_perm, $this->ref_id);
		}

		if (!$has_perm)
		{
			if ($a_throw_exc)
			{
				include_once "Services/Object/exceptions/class.ilObjectException.php";
				throw new ilObjectException($this->lng->txt("sty_permission_denied"));
			}
			return false;
		}
		return $has_perm;
	}

	/**
	 * Sets the tab correctly if one system style is open (navigational underworld opened)
	 *
	 * @param string $active
	 */
	protected function setUnderworldTabs($active = "") {
		$this->tabs->clearTargets();

		$this->tabs->setBackTarget($this->lng->txt("back"),$this->ctrl->getLinkTarget($this));
		$this->tabs->addTab('settings', $this->lng->txt('settings'), $this->ctrl->getLinkTargetByClass('ilsystemstylesettingsgui'));
		$this->tabs->addTab('less', $this->lng->txt('less'), $this->ctrl->getLinkTargetByClass('ilsystemstylelessgui'));
		$this->tabs->addTab('icons', $this->lng->txt('icons'), $this->ctrl->getLinkTargetByClass('ilsystemstyleiconsgui'));
		$this->tabs->addTab('documentation', $this->lng->txt('documentation'), $this->ctrl->getLinkTargetByClass('ilsystemstyledocumentationgui'));

		$this->tabs->activateTab($active);
	}


	/**
	 * Sets title correctly if one system style is opened
	 *
	 * @throws ilSystemStyleException
	 */
	protected function setUnderworldTitle() {
		$skin_id = $_GET["skin_id"];
		$style_id = $_GET["style_id"];

		$skin = ilSystemStyleSkinContainer::generateFromId($skin_id)->getSkin();
		$style = $skin->getStyle($style_id);

		$this->tpl->setTitle($style->getName());
		if($style->isSubstyle()){
			$this->tpl->setDescription($this->lng->txt("settings_of_substyle")." '".$style->getName()."' ".
					$this->lng->txt("of_parent")." '".$skin->getStyle($style->getSubstyleOf())->getName()."' ".
					$this->lng->txt("from_skin")." ".$skin->getName()
			);
		}else{
			$this->tpl->setDescription($this->lng->txt("settings_of_style")." '".$style->getName()."' ".
					$this->lng->txt("from_skin")." '".$skin->getName()."'"
			);
		}
	}
}