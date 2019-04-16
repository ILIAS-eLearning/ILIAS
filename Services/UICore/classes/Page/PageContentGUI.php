<?php namespace ILIAS\Services\UICore\Page;

use ILIAS\UI\Component\Component;
use ilTemplate;

/**
 * Class PageContentGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PageContentGUI extends ilTemplate {

	const MESSAGE_TYPE_FAILURE = 'failure';
	const MESSAGE_TYPE_INFO = "info";
	const MESSAGE_TYPE_SUCCESS = "success";
	const MESSAGE_TYPE_QUESTION = "question";
	/**
	 * @var array  available Types for Messages
	 */
	protected static $message_types
		= array(
			self::MESSAGE_TYPE_FAILURE,
			self::MESSAGE_TYPE_INFO,
			self::MESSAGE_TYPE_SUCCESS,
			self::MESSAGE_TYPE_QUESTION,
		);
	/**
	 * @var PageInfo
	 */
	private $page_info;
	private $tree_flat_link;
	private $page_form_action;
	private $permanent_link;
	private $main_content;
	private $lightbox;
	private $standard_template_loaded;
	private $translation_linked;
	private $show_footer;
	private $main_menu;
	private $main_menu_spacer;
	private $message;
	private $js_files;
	private $js_files_vp;
	private $js_files_batch;
	private $css_files;
	private $inline_css;
	private $header_page_title;
	private $title;
	private $title_desc;
	private $title_alerts;
	private $header_action;
	private $tabs_html;
	private $sub_tabs_html;
	private $admin_panel_commands_toolbar;
	private $admin_panel_arrow;
	private $admin_panel_bottom;
	private $body_class;
	private $on_load_code;
	private $right_content;
	private $left_content;
	private $icon_path;
	private $icon_desc;
	private $enable_fileupload;
	private $tree_flat_mode;


	/**
	 * PageContentGUI constructor.
	 *
	 * @param PageInfo $page_info
	 * @param string   $file
	 * @param bool     $flag1
	 * @param bool     $flag2
	 * @param bool     $in_module
	 * @param string   $vars
	 * @param bool     $plugin
	 * @param bool     $a_use_cache
	 */
	public function __construct(
		PageInfo $page_info,
		string $file,
		bool $flag1,
		bool $flag2,
		bool $in_module = false,
		$vars = "DEFAULT",
		bool $plugin = false,
		bool $a_use_cache = true
	) {
		parent::__construct($file, $flag1, $flag2, $in_module, $vars, $plugin, $a_use_cache);
		$this->page_info = $page_info;
		// $this->mapMembers();
	}


	private function mapMembers() {
		global $DIC;
		$r = function (Component $c) use ($DIC) {
			return $DIC->ui()->renderer()->render($c);
		};

		$this->page_form_action = $this->page_info->getPageFormAction();
		if ($this->page_info->hasCenterContent()) {
			$this->main_content = $r($this->page_info->getCenterContent());
		}

		foreach ($this->page_info->getLightboxes() as $id => $lightbox) {
			$this->lightbox[$id] = $r($lightbox);
		}

		$this->title = $this->page_info->getTitle();
		$this->title_desc = $this->page_info->getDescription();
		$this->title_alerts = $this->page_info->getAlertProperties();
		if ($this->page_info->hasHeaderActionMenu()) {
			$this->header_action = $this->page_info->getHeaderActionMenu();
		}
		// $this->tabs_html = $r($this->page_info->getTabs());
		// $this->sub_tabs_html = $r($this->page_info->getSubTabs());
		if ($this->page_info->hasAdministrationToolbar()) {
			$this->admin_panel_commands_toolbar = $this->page_info->getAdministrationToolbar();
			$this->admin_panel_arrow = $this->page_info->hasAdministrationToolbarArrow();
			$this->admin_panel_bottom = $this->page_info->hasAdministrationToolbarBottom();
		}

		$this->body_class = $this->page_info->getBodyClass();
		if ($this->page_info->hasRightContent()) {
			$this->right_content = $r($this->page_info->getRightContent());
		}
		if ($this->page_info->hasLeftContent()) {
			$this->left_content = $r($this->page_info->getLeftContent());
		}
		if ($this->page_info->hasTitleIcon()) {
			$this->icon_path = $this->page_info->getTitleIcon()->getIconPath();
			$this->icon_desc = $this->page_info->getTitleIcon()->getName();
		}
	}


	public function renderPage($part, $a_fill_tabs, $a_skip_main_menu): string {
		$this->mapMembers();

		//
		// Copied from old ilGlobalTemplate and modified
		//
		$this->fillMessage();

		// display ILIAS footer
		// if ($part !== false) {
		// 	$this->fillFooter();
		// }

		// set standard parts (tabs and title icon)
		// $this->fillBodyClass();

		// see #22992
		// $this->fillContentLanguage();

		$this->fillPageFormAction();

		if ($a_fill_tabs) {
			if ($this->blockExists("content")) {
				// determine default screen id
				$this->getTabsHTML();
			}

			// to get also the js files for the main menu
			if (!$a_skip_main_menu) {
				// $this->getMainMenu();
				//$this->initHelp();
			}

			$this->initHelp();

			// TODO SessionReminder
			// if ($this->blockExists("content") && $this->variableExists('MAINMENU')) {
			// 	$tpl = $DIC["tpl"];
			//
			// 	include_once 'Services/Authentication/classes/class.ilSessionReminderGUI.php';
			// 	$session_reminder_gui = new ilSessionReminderGUI(ilSessionReminder::createInstanceWithCurrentUserSession());
			// 	$tpl->setVariable('SESSION_REMINDER', $session_reminder_gui->getHtml());
			// }

			// these fill blocks in tpl.main.html
			// $this->fillCssFiles();
			// $this->fillInlineCss();
			//$this->fillJavaScriptFiles();

			// these fill just plain placeholder variables in tpl.main.html
			// $this->setCurrentBlock("DEFAULT");
			// $this->fillNewContentStyle();
			// $this->fillWindowTitle();

			// these fill blocks in tpl.adm_content.html
			$this->fillHeader();
			// $this->fillSideIcons();
			// $this->fillScreenReaderFocus(); // TODO
			$this->fillLeftContent();
			// $this->fillLeftNav();
			$this->fillRightContent();
			$this->fillAdminPanel();
			$this->fillToolbar();
			// $this->fillPermanentLink(); //TODO

			$this->setCenterColumnClass();

			// late loading of javascipr files, since operations above may add files
			// $this->fillJavaScriptFiles();
			// $this->fillOnLoadCode();

			// these fill just plain placeholder variables in tpl.adm_content.html
			if ($this->blockExists("content")) {
				$this->setCurrentBlock("content");
				$this->fillTabs();
				$this->fillMainContent();
				// $this->fillMainMenu();
				// $this->fillLightbox(); // TODO
				$this->parseCurrentBlock();
			}
		}

		if ($part == "DEFAULT" or is_bool($part)) {
			$html = $this->getUnmodified();
		} else {
			$html = $this->getUnmodified($part);
		}

		global $DIC;

		// Modification of html is done inline here and can't be done
		// by ilTemplate, because the "phase" is template_show in this
		// case here.
		$ilPluginAdmin = $DIC["ilPluginAdmin"];
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl) {
			$ui_plugin = \ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();

			$resp = $gui_class->getHTML(
				"", "template_show",
				array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $html)
			);

			if ($resp["mode"] != \ilUIHookPluginGUI::KEEP) {
				$html = $gui_class->modifyHTML($html, $resp);
			}
		}

		// fix #9992: save language usages as late as possible
		if ($this->translation_linked) {
			\ilObjLanguageAccess::_saveUsages();
		}

		return $html;
	}


	/**
	 * Set a message to be displayed to the user. Please use ilUtil::sendInfo(),
	 * ilUtil::sendSuccess() and ilUtil::sendFailure()
	 *
	 * @param  string  $a_type \ilTemplate::MESSAGE_TYPE_SUCCESS,
	 *                         \ilTemplate::MESSAGE_TYPE_FAILURE,,
	 *                         \ilTemplate::MESSAGE_TYPE_QUESTION,
	 *                         \ilTemplate::MESSAGE_TYPE_INFO
	 * @param   string $a_txt  The message to be sent
	 * @param bool     $a_keep Keep this message over one redirect
	 */
	public function setOnScreenMessage($a_type, $a_txt, $a_keep = false) {
		if (!in_array($a_type, self::$message_types) || $a_txt == "") {
			return;
		}
		if (!$a_keep) {
			$this->message[$a_type] = $a_txt;
		} else {
			$_SESSION[$a_type] = $a_txt;
		}
	}


	private function fillMessage() {
		global $DIC;

		$out = "";

		foreach (self::$message_types as $m) {
			$txt = $this->getMessageTextForType($m);

			if ($txt != "") {
				$out .= \ilUtil::getSystemMessageHTML($txt, $m);
			}

			$request = $DIC->http()->request();
			$accept_header = $request->getHeaderLine('Accept');
			if (isset($_SESSION[$m]) && $_SESSION[$m] && ($accept_header !== 'application/json')) {
				unset($_SESSION[$m]);
			}
		}

		if ($out != "") {
			$this->setVariable("MESSAGE", $out);
		}
	}


	/**
	 * @param $m
	 *
	 * @return mixed|string
	 */
	private function getMessageTextForType($m) {
		$txt = "";
		if (isset($_SESSION[$m]) && $_SESSION[$m] != "") {
			$txt = $_SESSION[$m];
		} else {
			if (isset($this->message[$m])) {
				$txt = $this->message[$m];
			}
		}

		return $txt;
	}


	private function getTabsHTML() {
		global $DIC;

		$ilTabs = $DIC["ilTabs"];

		if ($this->blockExists("tabs_outer_start")) {
			$this->sub_tabs_html = $ilTabs->getSubTabHTML();
			$this->tabs_html = $ilTabs->getHTML(true);
		}
	}


	/**
	 * Init help
	 */
	private function initHelp() {
		include_once("./Services/Help/classes/class.ilHelpGUI.php");
		\ilHelpGUI::initHelp($this);
	}


	private function fillHeader() {
		global $DIC;

		$lng = $DIC->language();

		$icon = false;
		if ($this->icon_path != "") {
			$icon = true;
			$this->setCurrentBlock("header_image");
			if ($this->icon_desc != "") {
				$this->setVariable("IMAGE_DESC", $lng->txt("icon") . " " . $this->icon_desc);
				$this->setVariable("IMAGE_ALT", $lng->txt("icon") . " " . $this->icon_desc);
			}

			$this->setVariable("IMG_HEADER", $this->icon_path);
			$this->parseCurrentBlock();
			$header = true;
		}

		if ($this->title != "") {
			$title = \ilUtil::stripScriptHTML($this->title);
			$this->setVariable("HEADER", $title);

			$header = true;
		}

		if ($header) {
			$this->setCurrentBlock("header_image");
			$this->parseCurrentBlock();
		}

		if ($this->title_desc != "") {
			$this->setCurrentBlock("header_desc");
			$this->setVariable("H_DESCRIPTION", $this->title_desc);
			$this->parseCurrentBlock();
		}

		$header = $this->header_action;
		if ($header) {
			$this->setCurrentBlock("head_action_inner");
			$this->setVariable("HEAD_ACTION", $header);
			$this->parseCurrentBlock();
			$this->touchBlock("head_action");
		}

		if (count((array)$this->title_alerts)) {
			foreach ($this->title_alerts as $alert) {
				$this->setCurrentBlock('header_alert');
				if (!($alert['propertyNameVisible'] === false)) {
					$this->setVariable('H_PROP', $alert['property'] . ':');
				}
				$this->setVariable('H_VALUE', $alert['value']);
				$this->parseCurrentBlock();
			}
		}

		// add file upload drop zone in header
		if ($this->enable_fileupload != null) {
			$ref_id = $this->enable_fileupload;
			$upload_id = "dropzone_" . $ref_id;

			include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
			$upload = new \ilFileUploadGUI($upload_id, $ref_id, true);

			$this->setVariable("FILEUPLOAD_DROPZONE_ID", " id=\"$upload_id\"");

			$this->setCurrentBlock("header_fileupload");
			$this->setVariable("HEADER_FILEUPLOAD_SCRIPT", $upload->getHTML());
			$this->parseCurrentBlock();
		}
	}


	public function setRightContent($a_html) {
		$this->right_content = $a_html;
	}


	private function setCenterColumnClass() {
		if (!$this->blockExists("center_col_width")) {
			return;
		}
		$center_column_class = "";
		if (trim($this->right_content) != "" && trim($this->left_content) != "") {
			$center_column_class = "two_side_col";
		} else {
			if (trim($this->right_content) != "" || trim($this->left_content) != "") {
				$center_column_class = "one_side_col";
			}
		}

		switch ($center_column_class) {
			case "one_side_col":
				$center_column_class = "col-sm-9";
				break;
			case "two_side_col":
				$center_column_class = "col-sm-6";
				break;
			default:
				$center_column_class = "col-sm-12";
				break;
		}
		if (trim($this->left_content) != "") {
			$center_column_class .= " col-sm-push-3";
		}

		$this->setCurrentBlock("center_col_width");
		$this->setVariable("CENTER_COL", $center_column_class);
		$this->parseCurrentBlock();
	}


	private function fillMainContent() {
		if (trim($this->main_content) != "") {
			$this->setVariable("ADM_CONTENT", $this->main_content);
		}
	}


	private function fillLeftContent() {
		if (trim($this->left_content) != "") {
			$this->setCurrentBlock("left_column");
			$this->setVariable("LEFT_CONTENT", $this->left_content);
			$left_col_class = (trim($this->right_content) == "")
				? "col-sm-3 col-sm-pull-9"
				: "col-sm-3 col-sm-pull-6";
			$this->setVariable("LEFT_COL_CLASS", $left_col_class);
			$this->parseCurrentBlock();
		}
	}


	private function fillRightContent() {
		if (trim($this->right_content) != "") {
			$this->setCurrentBlock("right_column");
			$this->setVariable("RIGHT_CONTENT", $this->right_content);
			$this->parseCurrentBlock();
		}
	}


	private function fillAdminPanel() {
		global $DIC;
		$lng = $DIC->language();

		if ($this->admin_panel_commands_toolbar === null) {
			return;
		}

		$toolb = $this->admin_panel_commands_toolbar;
		assert($toolb instanceof \ilToolbarGUI);

		// Add arrow if desired.
		if ($this->admin_panel_arrow) {
			$toolb->setLeadingImage(\ilUtil::getImagePath("arrow_upright.svg"), $lng->txt("actions"));
		}

		$this->fillPageFormAction();

		// Add top admin bar.
		$this->setCurrentBlock("adm_view_components");
		$this->setVariable("ADM_PANEL1", $toolb->getHTML());
		$this->parseCurrentBlock();

		// Add bottom admin bar if user wants one.
		if ($this->admin_panel_bottom) {
			$this->setCurrentBlock("adm_view_components2");

			// Replace previously set arrow image.
			if ($this->admin_panel_arrow) {
				$toolb->setLeadingImage(\ilUtil::getImagePath("arrow_downright.svg"), $lng->txt("actions"));
			}

			$this->setVariable("ADM_PANEL2", $toolb->getHTML());
			$this->parseCurrentBlock();
		}
	}


	private function fillPageFormAction() {
		if ($this->page_form_action != "") {
			$this->setCurrentBlock("page_form_start");
			$this->setVariable("PAGE_FORM_ACTION", $this->page_form_action);
			$this->parseCurrentBlock();
			$this->touchBlock("page_form_end");
		}
	}


	private function fillToolbar() {
		global $DIC;

		$ilToolbar = $DIC["ilToolbar"];;

		$thtml = $ilToolbar->getHTML();
		if ($thtml != "") {
			$this->setCurrentBlock("toolbar_buttons");
			$this->setVariable("BUTTONS", $thtml);
			$this->parseCurrentBlock();
		}
	}


	private function fillTabs() {
		if ($this->blockExists("tabs_outer_start")) {
			$this->touchBlock("tabs_outer_start");
			$this->touchBlock("tabs_outer_end");
			$this->touchBlock("tabs_inner_start");
			$this->touchBlock("tabs_inner_end");

			if ($this->tabs_html != "") {
				$this->setVariable("TABS", $this->tabs_html);
			}
			$this->setVariable("SUB_TABS", $this->sub_tabs_html);
		}
	}


	private function fillLightbox() {
		$html = "";

		foreach ($this->lightbox as $lb) {
			$html .= $lb;
		}
		$this->setVariable("LIGHTBOX", $html);
	}
}
