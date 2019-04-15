<?php namespace ILIAS\Services\UICore\Page;

use ILIAS\Services\UICore\Page\Media\Css;
use ILIAS\Services\UICore\Page\Media\CssCollection;
use ILIAS\Services\UICore\Page\Media\InlineCss;
use ILIAS\Services\UICore\Page\Media\Js;
use ILIAS\Services\UICore\Page\Media\JsCollection;
use ILIAS\Services\UICore\Page\Media\OnLoadCode;
use ILIAS\UI\Component\Icon\Icon;
use ILIAS\UI\Component\Legacy\Legacy;

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
	 * @var bool
	 */
	private $file_drag_and_drop = false;
	/**
	 * @var string
	 */
	private $page_form_action = "";
	/**
	 * @var Legacy
	 */
	private $lightboxes = [];
	//
	// Media
	//
	/**
	 * @var JsCollection
	 */
	private $js = null;
	/**
	 * @var InlineCss[]
	 */
	private $inline_css = [];
	/**
	 * @var CssCollection
	 */
	private $css = null;
	/**
	 * @var OnLoadCode[]
	 */
	private $on_load_code = [];
	//
	// Parts of the Page
	//
	/**
	 * @var Legacy
	 */
	private $header_action_menu = null;
	/**
	 * @var Legacy
	 */
	private $administration_toolbar = null;
	/**
	 * @var Legacy
	 */
	private $tabs = null;
	/**
	 * @var Legacy
	 */
	private $sub_tabs = null;
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
	 * PageInfo constructor.
	 */
	public function __construct() {
		$this->js = new JsCollection();
		$this->css = new CssCollection();
	}


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
		return $this->file_drag_and_drop;
	}


	public function enableFileDragAndDrop() {
		$this->file_drag_and_drop = true;
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
	 * @return Legacy
	 */
	public function getLightboxes(): Legacy {
		return $this->lightboxes;
	}


	/**
	 * @param Legacy $lightboxes
	 */
	public function addLightbox(string $id, Legacy $lightboxes) {
		$this->lightboxes[$id] = $lightboxes;
	}


	/**
	 * @return JsCollection
	 */
	public function getJs(): JsCollection {
		return $this->js;
	}


	/**
	 * @param Js $js
	 */
	public function addJs(Js $js) {
		$this->js->addItem($js);
	}


	/**
	 * @return InlineCss[]
	 */
	public function getInlineCss(): array {
		return $this->inline_css;
	}


	/**
	 * @param InlineCss $inline_css
	 */
	public function addInlineCss(InlineCss $inline_css) {
		$this->inline_css[] = $inline_css;
	}


	/**
	 * @return CssCollection
	 */
	public function getCss(): CssCollection {
		return $this->css;
	}


	/**
	 * @param Css $css
	 */
	public function addCss(Css $css) {
		$this->css->addItem($css);
	}


	/**
	 * @return OnLoadCode[]
	 */
	public function getOnloadCode(): array {
		return $this->on_load_code;
	}


	/**
	 * @param OnLoadCode $onload
	 */
	public function addOnloadCode(OnLoadCode $onload) {
		$this->on_load_code[] = $onload;
	}


	/**
	 * @return Legacy
	 */
	public function getHeaderActionMenu(): Legacy {
		return $this->header_action_menu;
	}


	/**
	 * @param Legacy $header_action_menu
	 */
	public function setHeaderActionMenu(Legacy $header_action_menu) {
		$this->header_action_menu = $header_action_menu;
	}


	/**
	 * @return Legacy
	 */
	public function getAdministrationToolbar(): Legacy {
		return $this->administration_toolbar;
	}


	/**
	 * @param Legacy $administration_toolbar
	 */
	public function setAdministrationToolbar(Legacy $administration_toolbar) {
		$this->administration_toolbar = $administration_toolbar;
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
