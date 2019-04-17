<?php namespace ILIAS\Services\UICore\MetaTemplate;

use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\Css;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\CssCollection;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\Js;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\JsCollection;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\OnLoadCode;
use ILIAS\UI\Component\Icon\Icon;
use ILIAS\UI\Component\Legacy\Legacy;
use ilToolbarGUI;

/**
 * Class PageInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PageInfo {

	//
	// Meta
	//
	/**
	 * @var string
	 */
	private $title = "";
	/**
	 * @var string
	 */
	private $description = "";
	/**
	 * @var Icon
	 */
	private $title_icon = null;
	/**
	 * @var string
	 */
	private $permanent_link = "";
	/**
	 * @var string
	 */
	private $body_class = "std";
	//
	// Features
	//
	/**
	 * @var int
	 */
	private $file_drag_and_drop = null;
	/**
	 * @var string
	 */
	private $page_form_action = "";
	/**
	 * @var Legacy
	 */
	private $lightboxes = [];
	//
	// Parts of the Page
	//
	/**
	 * @var string
	 */
	private $header_action_menu = null;
	/**
	 * @var ilToolbarGUI
	 */
	private $administration_toolbar = null;
	/**
	 * @var bool
	 */
	private $administration_toolbar_bottom = false;
	/**
	 * @var bool
	 */
	private $administration_toolbar_arrow = false;
	/**
	 * @var Legacy
	 */
	private $tabs = null;
	/**
	 * @var Legacy
	 */
	private $sub_tabs = null;
	/**
	 * @var array
	 */
	private $alert_properties = [];
	//
	// CONTENT
	//
	/**
	 * @var Legacy
	 */
	private $center_content = null;
	/**
	 * @var Legacy
	 */
	private $left_content = null;
	/**
	 * @var Legacy
	 */
	private $right_content = null;


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle(string $title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription(string $description) {
		$this->description = $description;
	}


	/**
	 * @return bool
	 */
	public function hasTitleIcon(): bool {
		return ($this->title_icon instanceof Icon);
	}


	/**
	 * @return Icon
	 */
	public function getTitleIcon(): Icon {
		return $this->title_icon;
	}


	/**
	 * @param Icon $title_icon
	 */
	public function setTitleIcon(Icon $title_icon) {
		$this->title_icon = $title_icon;
	}


	/**
	 * @return string
	 */
	public function getPermanentLink(): string {
		return $this->permanent_link;
	}


	/**
	 * @param string $permanent_link
	 */
	public function setPermanentLink(string $permanent_link) {
		$this->permanent_link = $permanent_link;
	}


	/**
	 * @return string
	 */
	public function getBodyClass(): string {
		return $this->body_class;
	}


	/**
	 * @param string $body_class
	 */
	public function setBodyClass(string $body_class) {
		$this->body_class = $body_class;
	}


	/**
	 * @return bool
	 */
	public function isFileDragAndDropEnabled(): bool {
		return $this->file_drag_and_drop > 0;
	}


	/**
	 * @param int $ref_id
	 */
	public function enableFileDragAndDrop(int $ref_id) {
		$this->file_drag_and_drop = $ref_id;
	}


	/**
	 * @return string
	 */
	public function getPageFormAction(): string {
		return $this->page_form_action;
	}


	/**
	 * @param string $page_form_action
	 */
	public function setPageFormAction(string $page_form_action) {
		$this->page_form_action = $page_form_action;
	}


	/**
	 * @return Legacy[]
	 */
	public function getLightboxes(): array {
		return $this->lightboxes;
	}


	/**
	 * @param string $id
	 * @param Legacy $lightboxes
	 */
	public function addLightbox(string $id, Legacy $lightboxes) {
		$this->lightboxes[$id] = $lightboxes;
	}


	/**
	 * @return string
	 */
	public function getHeaderActionMenu(): string {
		return $this->header_action_menu;
	}


	/**
	 * @return bool
	 */
	public function hasHeaderActionMenu(): bool {
		return (is_string($this->header_action_menu) && strlen($this->header_action_menu) > 0);
	}


	/**
	 * @param string $header_action_menu
	 */
	public function setHeaderActionMenu(string $header_action_menu) {
		$this->header_action_menu = $header_action_menu;
	}


	/**
	 * @return bool
	 */
	public function hasAdministrationToolbar(): bool {
		return ($this->administration_toolbar instanceof ilToolbarGUI);
	}


	/**
	 * @return Legacy
	 */
	public function getAdministrationToolbar(): ilToolbarGUI {
		return $this->administration_toolbar;
	}


	/**
	 * @param ilToolbarGUI $administration_toolbar
	 */
	public function setAdministrationToolbar(ilToolbarGUI $administration_toolbar) {
		$this->administration_toolbar = $administration_toolbar;
	}


	/**
	 * @param bool $bool
	 */
	public function setAdministrationToolbarArrow(bool $bool) {
		$this->administration_toolbar_arrow = $bool;
	}


	/**
	 * @return bool
	 */
	public function hasAdministrationToolbarArrow(): bool {
		return $this->administration_toolbar_arrow;
	}


	/**
	 * @param bool $bool
	 */
	public function setAdministrationToolbarBottom(bool $bool) {
		$this->administration_toolbar_bottom = $bool;
	}


	/**
	 * @return bool
	 */
	public function hasAdministrationToolbarBottom(): bool {
		return $this->administration_toolbar_bottom;
	}


	/**
	 * @return bool
	 */
	public function hasTabs(): bool {
		return ($this->tabs instanceof Legacy);
	}


	/**
	 * @return Legacy
	 */
	public function getTabs(): Legacy {
		return $this->tabs;
	}


	/**
	 * @param Legacy $tabs
	 */
	public function setTabs(Legacy $tabs) {
		$this->tabs = $tabs;
	}


	/**
	 * @return bool
	 */
	public function hasSubTabs(): bool {
		return ($this->sub_tabs instanceof Legacy);
	}


	/**
	 * @return Legacy
	 */
	public function getSubTabs(): Legacy {
		return $this->sub_tabs;
	}


	/**
	 * @param Legacy $sub_tabs
	 */
	public function setSubTabs(Legacy $sub_tabs) {
		$this->sub_tabs = $sub_tabs;
	}


	/**
	 * @return bool
	 */
	public function hasAlertProperties(): bool {
		return (is_array($this->alert_properties) && count($this->alert_properties) > 0);
	}


	/**
	 * @return array
	 */
	public function getAlertProperties(): array {
		return $this->alert_properties;
	}


	/**
	 * @param array $alert_properties
	 */
	public function setAlertProperties(array $alert_properties) {
		$this->alert_properties = $alert_properties;
	}


	/**
	 * @return bool
	 */
	public function hasCenterContent(): bool {
		return ($this->center_content instanceof Legacy);
	}


	/**
	 * @return Legacy
	 */
	public function getCenterContent(): Legacy {
		return $this->center_content;
	}


	/**
	 * @param Legacy $center_content
	 */
	public function setCenterContent(Legacy $center_content) {
		$this->center_content = $center_content;
	}


	/**
	 * @return bool
	 */
	public function hasLeftContent(): bool {
		return ($this->left_content instanceof Legacy);
	}


	/**
	 * @return Legacy
	 */
	public function getLeftContent(): Legacy {
		return $this->left_content;
	}


	/**
	 * @param Legacy $left_content
	 */
	public function setLeftContent(Legacy $left_content) {
		$this->left_content = $left_content;
	}


	/**
	 * @return bool
	 */
	public function hasRightContent(): bool {
		return ($this->right_content instanceof Legacy);
	}


	/**
	 * @return Legacy
	 */
	public function getRightContent(): Legacy {
		return $this->right_content;
	}


	/**
	 * @param Legacy $right_content
	 */
	public function setRightContent(Legacy $right_content) {
		$this->right_content = $right_content;
	}
}
