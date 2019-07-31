<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");
include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockViewSettings.php';

/**
* News Settings.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjPersonalDesktopSettingsGUI: ilPermissionGUI
*
* @ingroup ServicesPersonalDesktop
*/
class ilObjPersonalDesktopSettingsGUI extends ilObjectGUI
{
	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilErrorHandling
	 */
	protected $error;

    private static $ERROR_MESSAGE;

	/**
	 * @var \ILIAS\UI\Factory
	 */
    protected $ui_factory;

	/**
	 * @var \ILIAS\UI\Renderer
	 */
    protected $ui_renderer;

	/**
	 * @var ilPDSelectedItemsBlockViewSettings
	 */
	protected $viewSettings;

	/**
	 * @var \Psr\Http\Message\ServerRequestInterface
	 */
	protected $request;

	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->rbacsystem = $DIC->rbac()->system();
		$this->error = $DIC["ilErr"];
		$this->access = $DIC->access();
		$this->ctrl = $DIC->ctrl();
		$this->settings = $DIC->settings();
		$lng = $DIC->language();
		$this->ui_factory = $DIC->ui()->factory();
		$this->ui_renderer = $DIC->ui()->renderer();
		$this->request = $DIC->http()->request();
		
		$this->type = 'pdts';
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$lng->loadLanguageModule("pd");

		$this->viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user());
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId()))
		{
			$this->error->raiseError($this->lng->txt('no_permission'),$this->error->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSettings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		$rbacsystem = $this->rbacsystem;
		$ilAccess = $this->access;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("pd_settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));
			
			$this->tabs_gui->addTarget("pd_personal_workspace",
				$this->ctrl->getLinkTarget($this, "editWsp"),
				array("editWsp"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	* Edit personal desktop settings.
	*/
	public function editSettings()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilSetting = $this->settings;
		$ilAccess = $this->access;
		
		$pd_set = new ilSetting("pd");

		$this->setSettingsSubTabs("general");
		
		$enable_calendar = ilCalendarSettings::_getInstance()->isEnabled();
		#$enable_calendar = $ilSetting->get("enable_calendar");		
		$enable_block_moving = $pd_set->get("enable_block_moving");
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("pd_settings"));
		
		// Enable calendar
		$cb_prop = new ilCheckboxInputGUI($lng->txt("enable_calendar"), "enable_calendar");
		$cb_prop->setValue("1");
		//$cb_prop->setInfo($lng->txt("pd_enable_block_moving_info"));
		$cb_prop->setChecked($enable_calendar);
		$form->addItem($cb_prop);

		// Enable bookmarks
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_bookmarks"), "enable_bookmarks");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_bookmarks") ? "0" : "1"));
		$form->addItem($cb_prop);

		// Enable notes
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_notes"), "enable_notes");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_notes") ? "0" : "1"));
		$form->addItem($cb_prop);
		
		// Enable comments
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_comments"), "enable_comments");
		$cb_prop->setValue("1");
		$cb_prop->setChecked(($ilSetting->get("disable_comments") ? "0" : "1"));
		$form->addItem($cb_prop);

		$comm_del_user =  new ilCheckboxInputGUI($lng->txt("pd_enable_comments_del_user"), "comm_del_user");		
		$comm_del_user->setChecked($ilSetting->get("comments_del_user", 0));
		$cb_prop->addSubItem($comm_del_user);		
		
		$comm_del_tutor =  new ilCheckboxInputGUI($lng->txt("pd_enable_comments_del_tutor"), "comm_del_tutor");
		$comm_del_tutor->setInfo($lng->txt("pd_enable_comments_del_tutor_info"));
		$comm_del_tutor->setChecked($ilSetting->get("comments_del_tutor", 1));
		$cb_prop->addSubItem($comm_del_tutor);

		$ti = new ilTextInputGUI($this->lng->txt("pd_comments_notification"), "comments_noti_recip");
		$ti->setValue($ilSetting->get("comments_noti_recip"));
		$ti->setMaxLength(200);
		$ti->setInfo($this->lng->txt("pd_comments_notification_info"));
		$cb_prop->addSubItem($ti);

		// Enable learning history
		$learn_hist = new ilCheckboxInputGUI($lng->txt("pd_enable_learning_history"), "enable_learning_history");
		$learn_hist->setValue("1");
		$learn_hist->setInfo($lng->txt("pd_enable_learning_history_info"));
		$learn_hist->setChecked(($ilSetting->get("enable_learning_history") ? "1" : "0"));
		$form->addItem($learn_hist);

		// Enable block moving
		$cb_prop = new ilCheckboxInputGUI($lng->txt("pd_enable_block_moving"),
			"enable_block_moving");
		$cb_prop->setValue("1");
		$cb_prop->setInfo($lng->txt("pd_enable_block_moving_info"));
		$cb_prop->setChecked($enable_block_moving);
		$form->addItem($cb_prop);		


		#22357
		$this->ctrl->setParameterByClass("iluserstartingpointgui", "ref_id", USER_FOLDER_ID);
		$url = $this->ctrl->getLinkTargetByClass(array("iladministrationgui","ilobjuserfoldergui", "iluserstartingpointgui"), "startingpoints");
		$this->ctrl->setParameterByClass("iluserstartingpointgui", "ref_id", $_GET['ref_id']);

		$lng->loadLanguageModule("user");
		$starting_point_button = $this->ui_factory->button()->shy($lng->txt("starting_points"), $url);
		$button = new ilCustomInputGUI($lng->txt('pd_personal_items_default_view'),'');
		$button->setHtml($this->ui_renderer->render($starting_point_button));
		$form->addItem($button);

		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			// command buttons
			$form->addCommandButton("saveSettings", $lng->txt("save"));
			$form->addCommandButton("view", $lng->txt("cancel"));
		}

		$this->tpl->setContent($form->getHTML());
	}

	/**
	* Save personal desktop settings
	*/
	public function saveSettings()
	{
		$ilCtrl = $this->ctrl;
		$ilSetting = $this->settings;
		$ilAccess = $this->access;
		
		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilCtrl->redirect($this, "view");
		}
		
		$pd_set = new ilSetting("pd");
		
		ilCalendarSettings::_getInstance()->setEnabled( $_POST["enable_calendar"]);
		ilCalendarSettings::_getInstance()->save();
			
		#$ilSetting->set("enable_calendar", $_POST["enable_calendar"]);
		$ilSetting->set("disable_bookmarks", (int) ($_POST["enable_bookmarks"] ? 0 : 1));

		$ilSetting->set("disable_notes", (int) ($_POST["enable_notes"] ? 0 : 1));
		$ilSetting->set("disable_comments", (int) ($_POST["enable_comments"] ? 0 : 1));

		$ilSetting->set("enable_learning_history", (int) $_POST["enable_learning_history"]);
	
		$ilSetting->set("comments_del_user", (int) ($_POST["comm_del_user"] ? 1 : 0));
		$ilSetting->set("comments_del_tutor", (int) ($_POST["comm_del_tutor"] ? 1 : 0));
		$ilSetting->set("comments_noti_recip", ilUtil::stripSlashes($_POST["comments_noti_recip"]));

		$pd_set->set("enable_block_moving", $_POST["enable_block_moving"]);
//		$pd_set->set("user_activity_time", (int) $_POST["time_removal"]);


		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$ilCtrl->redirect($this, "view");
	}
	
	/**
	* Edit personal workspace settings.
	*/
	public function editWsp()
	{
		$ilCtrl = $this->ctrl;
		$lng = $this->lng;
		$ilSetting = $this->settings;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveWsp"));
		$form->setTitle($lng->txt("pd_personal_workspace"));
		
		// Enable 'Personal Workspace'
		$wsp_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_personal_workspace'), 'wsp');
		$wsp_prop->setValue('1');
		$wsp_prop->setChecked(($ilSetting->get('disable_personal_workspace') ? '0' : '1'));
		$form->addItem($wsp_prop);
		
		// Enable 'Blogs'
		$blog_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_blogs'), 'blog');
		$blog_prop->setValue('1');
		$blog_prop->setChecked(($ilSetting->get('disable_wsp_blogs') ? '0' : '1'));
		$wsp_prop->addSubItem($blog_prop);
		
		// Enable 'Files'
		$file_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_files'), 'file');
		$file_prop->setValue('1');
		$file_prop->setChecked(($ilSetting->get('disable_wsp_files') ? '0' : '1'));
		$wsp_prop->addSubItem($file_prop);
		
		// Enable 'Certificates'
		$cert_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_certificates'), 'cert');
		$cert_prop->setValue('1');
		$cert_prop->setChecked(($ilSetting->get('disable_wsp_certificates') ? '0' : '1'));
		$wsp_prop->addSubItem($cert_prop);
		
		// Enable 'Links'
		$link_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_wsp_links'), 'link');
		$link_prop->setValue('1');
		$link_prop->setChecked(($ilSetting->get('disable_wsp_links') ? '0' : '1'));
		$wsp_prop->addSubItem($link_prop);			
		
		/*
		// Enable 'Portfolios'
		$lng->loadLanguageModule('user');
		$prtf_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_prtf'), 'prtf');
		$prtf_prop->setValue('1');
		$prtf_prop->setInfo($lng->txt('user_portfolios_desc'));
		$prtf_prop->setChecked(($ilSetting->get('user_portfolios') ? '1' : '0'));
		$form->addItem($prtf_prop);
		*/
		
		// Load the disk quota settings object
		$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
		
		// Enable disk quota
		$lng->loadLanguageModule("file");
		$cb_prop = new ilCheckboxInputGUI($lng->txt("personal_workspace_disk_quota"), "enable_personal_workspace_disk_quota");
		$cb_prop->setValue('1');
		$cb_prop->setChecked($disk_quota_obj->isPersonalWorkspaceDiskQuotaEnabled());
		$cb_prop->setInfo($lng->txt('enable_personal_workspace_disk_quota_info'));
		$form->addItem($cb_prop);
				
		require_once 'Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php';
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_WSP,
			$form,
			$this
		);
		
		if($this->rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			// command buttons
			$form->addCommandButton("saveWsp", $lng->txt("save"));
			$form->addCommandButton("editWsp", $lng->txt("cancel"));
		}

		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Save personal desktop settings	 
	 */
	public function saveWsp()
	{
		$ilCtrl = $this->ctrl;
		$ilSetting = $this->settings;
		$ilAccess = $this->access;
		
		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilCtrl->redirect($this, "view");
		}
		
		// without personal workspace we have to disable to sub-items
		if(!$_POST["wsp"])
		{
			$_POST["blog"] = 0;
			$_POST["file"] = 0;
			$_POST["cert"] = 0;
			$_POST["link"] = 0;
		}
		
		$ilSetting->set('disable_personal_workspace', (int)($_POST['wsp'] ? 0 : 1));
		$ilSetting->set('disable_wsp_blogs', (int)($_POST['blog'] ? 0 : 1));
		$ilSetting->set('disable_wsp_files', (int)($_POST['file'] ? 0 : 1));
		$ilSetting->set('disable_wsp_certificates', (int)($_POST['cert'] ? 0 : 1));
		$ilSetting->set('disable_wsp_links', (int)($_POST['link'] ? 0 : 1));
		// $ilSetting->set('user_portfolios', (int)($_POST['prtf'] ? 1 : 0));
		
		// Load the disk quota settings object
		$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
		$disk_quota_obj->setPersonalWorkspaceDiskQuotaEnabled($_POST['enable_personal_workspace_disk_quota'] == '1');
		$disk_quota_obj->update();
		
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);		
		$ilCtrl->redirect($this, "editWsp");
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{				
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_FILES_QUOTA:
				
				$disk_quota_obj = ilObjDiskQuotaSettings::getInstance();
				
				$fields = array('personal_workspace_disk_quota' => array($disk_quota_obj->isPersonalWorkspaceDiskQuotaEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
				
				return array(array("editWsp", $fields));			
		}
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function setSettingsSubTabs($a_active)
	{
		$rbacsystem = $this->rbacsystem;
		$ilAccess = $this->access;

		$tabs = $this->tabs_gui;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs->addSubtab("general", $lng->txt("general_settings"),
				$ctrl->getLinkTarget($this, "editSettings"));

			$tabs->addSubtab("view_favourites", $lng->txt("pd_view_favourites"),
				$ctrl->getLinkTarget($this, "editViewFavourites"));

			$tabs->addSubtab("view_courses_groups", $lng->txt("pd_view_courses_groups"),
				$ctrl->getLinkTarget($this, "editViewCoursesGroups"));
		}

		$tabs->activateSubtab($a_active);
	}

	/**
	 * Edit settings of courses and groups overview
	 */
	protected function editViewCoursesGroups()
	{
		$main_tpl = $this->tpl;
		$tabs = $this->tabs_gui;
		$ui_renderer = $this->ui_renderer;

		$tabs->activateTab("pd_settings");
		$this->setSettingsSubTabs("view_courses_groups");

		$form = $this->getViewSettingsForm($this->viewSettings->getMembershipsView());

		$main_tpl->setContent($ui_renderer->render($form));
	}

	/**
	 * Get view courses and groups settings form
	 *
	 * @return \ILIAS\UI\Component\Input\Container\Form\Standard
	 */
	protected function getViewSettingsForm(int $view)
	{
		$ctrl = $this->ctrl;
		$lng = $this->lng;
		$ui_factory = $this->ui_factory;

		if ($view == $this->viewSettings->getSelectedItemsView())
		{
			$activation_text = $lng->txt("pd_enable_my_offers");
			$activation_value = $this->viewSettings->enabledSelectedItems();
			$save_cmd = "saveViewFavourites";
		}
		else
		{
			$activation_text = $lng->txt("pd_enable_my_memberships");
			$activation_value = $this->viewSettings->enabledMemberships();
			$save_cmd = "saveViewCoursesGroups";
		}

		// activation
		$cb_activate = $ui_factory->input()->field()->checkbox($activation_text)
			->withValue($activation_value);
		$sec_activation = $ui_factory->input()->field()->section(
			["active" => $cb_activate],
			$lng->txt("pd_activation"));

		// presentation
		$ops = $this->viewSettings->getAvailablePresentationsByView($view);
		$pres_options = array_column(array_map(function ($k, $v) use ($lng) {
			return [$v, $lng->txt("pd_".$v)];
		}, array_keys($ops), $ops), 1, 0);
		$avail_pres = $ui_factory->input()->field()->multiselect($lng->txt("pd_avail_presentation"), $pres_options)
			->withValue($this->viewSettings->getActivePresentationsByView($view));
		$default_pres = $ui_factory->input()->field()->radio($lng->txt("pd_default_presentation"))
			->withOption('list', $lng->txt("pd_list"))
			->withOption('tile', $lng->txt("pd_tile"));
		$default_pres = $default_pres->withValue((string) $this->viewSettings->getDefaultPresentationByView($view));
		$sec_presentation = $ui_factory->input()->field()->section(
			["avail_pres" => $avail_pres, "default_pres" => $default_pres],
			$lng->txt("pd_presentation"));

		// sortation
		$ops = $this->viewSettings->getAvailableSortOptionsByView($view);
		$sortation_options = array_column(array_map(function ($k, $v) use ($lng) {
				return [$v, $lng->txt("pd_sort_by_".$v)];
			}, array_keys($ops), $ops), 1, 0);
		$avail_sort = $ui_factory->input()->field()->multiselect($lng->txt("pd_avail_sortation"), $sortation_options)
			->withValue($this->viewSettings->getActiveSortingsByView($view));
		$default_sort = $ui_factory->input()->field()->radio($lng->txt("pd_default_sortation"));
		foreach ($sortation_options as $k => $text)
		{
			$default_sort = $default_sort->withOption($k, $text);
		}
		$default_sort = $default_sort->withValue((string) $this->viewSettings->getDefaultSortingByView($view));
		$sec_sortation = $ui_factory->input()->field()->section(
			["avail_sort" => $avail_sort, "default_sort" => $default_sort],
			$lng->txt("pd_sortation"));

		$form = $ui_factory->input()->container()->form()->standard($ctrl->getFormAction($this, $save_cmd),
			["activation" => $sec_activation, "presentation" => $sec_presentation, "sortation" => $sec_sortation]);

		return $form;
	}


	/**
	 * Save settings of courses and groups overview
	 */
	protected function saveViewCoursesGroups()
	{
		$this->saveViewSettings($this->viewSettings->getMembershipsView(),
			"editViewCoursesGroups");
	}

	/**
	 * Edit favourites view
	 */
	protected function editViewFavourites()
	{
		$main_tpl = $this->tpl;
		$tabs = $this->tabs_gui;
		$ui_renderer = $this->ui_renderer;

		$tabs->activateTab("pd_settings");
		$this->setSettingsSubTabs("view_favourites");

		$view = $this->viewSettings->getSelectedItemsView();

		$form = $this->getViewSettingsForm($view);

		$main_tpl->setContent($ui_renderer->render($form));
	}

	/**
	 * Save settings of favourites overview
	 */
	protected function saveViewFavourites()
	{
		$this->saveViewSettings($this->viewSettings->getSelectedItemsView(),
			"editViewFavourites");
	}

	/**
	 * Save settings of favourites overview
	 */
	protected function saveViewSettings(int $view, string $redirect_cmd)
	{
		$request = $this->request;
		$lng = $this->lng;
		$ctrl = $this->ctrl;

		$form = $this->getViewSettingsForm($view);
		$form = $form->withRequest($request);
		$form_data = $form->getData();
		if ($view == $this->viewSettings->getSelectedItemsView())
		{
			$this->viewSettings->enableSelectedItems((int)($form_data['activation']['active'] != ""));
		}
		else
		{
			$this->viewSettings->enableMemberships((int)($form_data['activation']['active'] != ""));
		}
		$this->viewSettings->storeViewSorting($view,
			$form_data['sortation']['default_sort'],
			$form_data['sortation']['avail_sort'] ?: []);
		$this->viewSettings->storeViewPresentation($view,
			$form_data['presentation']['default_pres'],
			$form_data['presentation']['avail_pres'] ?: []);

		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ctrl->redirect($this, $redirect_cmd);
	}

}

?>
