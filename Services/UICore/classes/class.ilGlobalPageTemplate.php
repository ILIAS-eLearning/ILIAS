<?php

use ILIAS\DI\HTTPServices;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\View\StandardView;
use ILIAS\GlobalScreen\Scope\View\View;
use ILIAS\GlobalScreen\Services;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\Css;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\Js;
use ILIAS\GlobalScreen\Scope\View\MetaContent\Media\OnLoadCode;
use ILIAS\Services\UICore\MetaTemplate\PageContentGUI;
use ILIAS\Services\UICore\MetaTemplate\PageInfo;
use ILIAS\UI\Component\Layout\Page\Standard;
use ILIAS\UI\NotImplementedException;

/**
 * Class ilGlobalPageTemplate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGlobalPageTemplate implements ilGlobalTemplateInterface {

	//
	// SERVICES
	//
	/**
	 * @var HTTPServices
	 */
	private $http;
	/**
	 * @var Services
	 */
	private $gs;
	/**
	 * @var UIServices
	 */
	private $ui;
	/**
	 * @var StandardView
	 */
	private $view;
	/**
	 * @var PageInfo
	 */
	private $page_info;
	/**
	 * @var ilTemplate
	 */
	private $legacy_content_template;
	/**
	 * @var ilLanguage
	 */
	private $lng;


	/**
	 * @inheritDoc
	 */
	public function __construct(Services $gs, UIServices $ui, HTTPServices $http) {
		global $DIC;
		$this->lng = $DIC->language();
		$this->ui = $ui;
		$this->gs = $gs;
		$this->http = $http;
		$this->view = new StandardView($ui->factory()->layout()->page());
		$this->page_info = new PageInfo();
		$this->legacy_content_template = new PageContentGUI($this->page_info, "tpl.page_content.html", true, true);
	}


	private function prepareOutputHeaders() {
		$response = $this->http->response();
		$this->http->saveResponse($response->withAddedHeader('P3P', 'CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"'));
		$this->http->saveResponse($response->withAddedHeader('Content-type', 'text/html; charset=UTF-8'));
	}


	private function prepareBasicJS() {
		\iljQueryUtil::initjQuery($this);
		\iljQueryUtil::initjQueryUI($this);
		$this->view->metaContent()->addJs("./Services/JavaScript/js/Basic.js", true, 1);
		\ilUIFramework::init($this);
	}


	private function prepareBasicCSS() {
		$this->view->metaContent()->addCss(\ilUtil::getStyleSheetLocation("filesystem", "delos.css"));
		$this->view->metaContent()->addCss(\ilUtil::getNewContentStyleSheetLocation());
	}


	/**
	 * @return View
	 * @deprecated
	 */
	public function getView(): View {
		return $this->view;
	}


	/**
	 * @inheritDoc
	 */
	public function printToStdout($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false) {
		$this->prepareOutputHeaders();
		$this->prepareBasicJS();
		$this->prepareBasicCSS();

		$content = $this->legacy_content_template->renderPage($part, $a_fill_tabs, $a_skip_main_menu);
		$this->view->setContent($this->ui->factory()->legacy($content));

		print $this->ui->renderer()->render([$this->view->getPageForViewWithContent()]);
	}






	//
	// LEGACY METHODS
	//

	// NEEDED
	/**
	 * @inheritDoc
	 */
	public function setContent($a_html) { // //
		$this->page_info->setCenterContent($this->ui->factory()->legacy($a_html));
		// $this->legacy_content_template->setContent($a_html);
	}


	/**
	 * @inheritDoc
	 */
	public function setLeftContent($a_html) { // //
		$this->page_info->setLeftContent($this->ui->factory()->legacy($a_html));
		// $this->legacy_content_template->setLeftContent($a_html);
	}


	/**
	 * @inheritDoc
	 */
	public function setRightContent($a_html) { // //
		$this->page_info->setRightContent($this->ui->factory()->legacy($a_html));
		// $this->legacy_content_template->setRightContent($a_html);
	}


	public function setVariable($variable, $value = '') {
		$this->legacy_content_template->setVariable($variable, $value);
	}


	/**
	 * @inheritDoc
	 */
	public function loadStandardTemplate() {
		// $this->legacy_content_template->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		// $this->legacy_content_template->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}


	// JS & CSS


	/**
	 * @inheritDoc
	 */
	public function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2) { // //
		$this->view->metaContent()->addJs($a_js_file, $a_add_version_parameter, $a_batch);
	}


	/**
	 * @inheritDoc
	 */
	public function addCss($a_css_file, $media = "screen") { // //
		$this->view->metaContent()->addCss($a_css_file, $media);
	}


	/**
	 * @inheritDoc
	 */
	public function addOnLoadCode($a_code, $a_batch = 2) { // //
		$this->view->metaContent()->addOnloadCode($a_code, $a_batch);
	}


	/**
	 * @inheritDoc
	 */
	public function setLocator() {
		//$this->legacy_content_template->setLocator();
	}


	// MAIN INFOS


	/**
	 * @inheritDoc
	 */
	public function setTitle($a_title) { // //
		$this->page_info->setTitle($a_title);
		// $this->legacy_content_template->setVariable("HEADER", $a_title);
	}


	/**
	 * @inheritDoc
	 */
	public function setDescription($a_descr) { // //
		$this->page_info->setDescription($a_descr);
		// $this->legacy_content_template->setVariable("H_DESCRIPTION", $a_descr);
	}


	/**
	 * @inheritDoc
	 */
	public function setTitleIcon($a_icon_path, $a_icon_desc = "") { // //
		$this->page_info->setTitleIcon($this->ui->factory()->icon()->custom($a_icon_path, $a_icon_desc));

	}


	// ALERTS


	/**
	 * @inheritDoc
	 */
	public function setAlertProperties(array $a_props) { // //
		$this->page_info->setAlertProperties($a_props);
	}


	// NEEDS ADJUSTMENT

	public function setPermanentLink($a_type, $a_id, $a_append = "", $a_target = "", $a_title = "") {
		// $this->legacy_content_template->setPermanentLink($a_type, $a_id, $a_append, $a_target, $a_title);
	}


	/**
	 * @inheritDoc
	 */
	public function enableDragDropFileUpload($a_ref_id) { // //
		$this->page_info->enableFileDragAndDrop((int)$a_ref_id); // TODO: is ref_id needed?
		// $this->legacy_content_template->enableDragDropFileUpload($a_ref_id);
	}


	/**
	 * @inheritDoc
	 */
	public function setTreeFlatIcon($a_link, $a_mode) {
		// $this->legacy_content_template->setTreeFlatIcon($a_link, $a_mode);
	}


	// NOT SURE


	/**
	 * @inheritDoc
	 */
	public function hideFooter() {
		// $this->legacy_content_template->hideFooter();
	}


	/**
	 * @inheritDoc
	 */
	public function setOnScreenMessage($a_type, $a_txt, $a_keep = false) {
		$this->legacy_content_template->setOnScreenMessage($a_type, $a_txt, $a_keep);
	}


	/**
	 * @inheritDoc
	 */
	public function getOnLoadCodeForAsynch() {
		// return $this->legacy_content_template->getOnLoadCodeForAsynch();
	}


	/**
	 * @inheritDoc
	 */
	public function resetJavascript() {
		$this->page_info->getJs()->clear();
	}


	/**
	 * @param bool $a_force
	 */
	public function fillJavaScriptFiles($a_force = false) { //internal
		// $this->legacy_content_template->fillJavaScriptFiles($a_force);
	}


	/**
	 * @inheritDoc
	 */
	public function addInlineCss($a_css, $media = "screen") { // //
		$this->page_info->addInlineCss(new InlineCss($a_css, $media));
		// $this->legacy_content_template->addInlineCss($a_css, $media);
	}


	public function setBodyClass($a_class = "") { // //
		$this->page_info->setBodyClass($a_class);
		// $this->legacy_content_template->setBodyClass($a_class);
	}


	/**
	 * @inheritDoc
	 */
	public function clearHeader() {
		// $this->legacy_content_template->clearHeader();
	}


	/**
	 * @inheritDoc
	 */
	public function setHeaderActionMenu($a_header) { // //
		$this->page_info->setHeaderActionMenu($a_header);
		// $this->legacy_content_template->setHeaderActionMenu($a_header);
	}


	/**
	 * @inheritDoc
	 */
	public function setHeaderPageTitle($a_title) {
		// $this->page_info->setHeaderPageTitle($a_title);
	}


	/**
	 * @inheritDoc
	 */
	public function setTabs($a_tabs_html) { // //
		$this->page_info->setTabs($this->ui->factory()->legacy($a_tabs_html));
		// $this->legacy_content_template->setTabs($a_tabs_html);
	}


	/**
	 * @inheritDoc
	 */
	public function setSubTabs($a_tabs_html) { // //
		$this->page_info->setSubTabs($this->ui->factory()->legacy($a_tabs_html));
		// $this->legacy_content_template->setSubTabs($a_tabs_html);
	}


	/**
	 * @inheritDoc
	 */
	public function setLeftNavContent($a_content) {
		// $this->legacy_content_template->setLeftContent($a_content);
	}


	/**
	 * @param $a_action
	 */
	public function setPageFormAction($a_action) { // //
		$this->page_info->setPageFormAction($a_action);
		// $this->legacy_content_template->setPageFormAction($a_action);
	}


	/**
	 * @inheritDoc
	 */
	public function setLoginTargetPar($a_val) {
		// $this->legacy_content_template->setLoginTargetPar($a_val);
	}


	/**
	 * @inheritDoc
	 */
	public function getSpecial($part = "DEFAULT", $add_error_mess = false, $handle_referer = false, $add_ilias_footer = false, $add_standard_elements = false, $a_main_menu = true, $a_tabs = true) { //
		// return $this->legacy_content_template->getSpecial($part, $add_error_mess, $handle_referer, $add_ilias_footer, $add_standard_elements, $a_main_menu, $a_tabs);
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function addLightbox($a_html, $a_id) { //
		$this->page_info->addLightbox($a_id, $this->ui->factory()->legacy($a_html));
	}


	/**
	 * @inheritDoc
	 */
	public function addAdminPanelToolbar(ilToolbarGUI $toolb, $a_bottom_panel = true, $a_arrow = false) { // //
		$this->page_info->setAdministrationToolbarArrow($a_arrow);
		$this->page_info->setAdministrationToolbar($toolb);
		$this->page_info->setAdministrationToolbarBottom($a_bottom_panel);
		// $this->legacy_content_template->addAdminPanelToolbar($toolb, $a_bottom_panel, $a_arrow);
	}


	/**
	 * @inheritDoc
	 */
	public function resetHeaderBlock($a_reset_header_action = true) {
		// $this->legacy_content_template->resetHeaderBlock($a_reset_header_action);
	}


	/**
	 * @inheritDoc
	 */
	public function get($part = "DEFAULT") {
		return $this->legacy_content_template->get($part);
	}


	/**
	 * @inheritDoc
	 */
	public function setCurrentBlock($part = "DEFAULT") {
		if ($this->blockExists($part)) {
			$this->legacy_content_template->setCurrentBlock($part);
		}
	}


	/**
	 * @inheritDoc
	 */
	public function touchBlock($block) {
		$this->legacy_content_template->touchBlock($block);
	}


	/**
	 * @inheritDoc
	 */
	public function parseCurrentBlock($part = "DEFAULT") {
		return $this->legacy_content_template->parseCurrentBlock($part);
	}


	/**
	 * @inheritDoc
	 */
	public function addBlockFile($var, $block, $tplname, $in_module = false) {
		return $this->legacy_content_template->addBlockFile($var, $block, $tplname, $in_module);
	}


	/**
	 * @inheritDoc
	 */
	public function blockExists($a_blockname) {
		return $this->legacy_content_template->blockExists($a_blockname);
	}
}