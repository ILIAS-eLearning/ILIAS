<?php

use ILIAS\DI\HTTPServices;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\View\StandardView;
use ILIAS\GlobalScreen\Services;
use ILIAS\Services\UICore\Page\Media\Css;
use ILIAS\Services\UICore\Page\Media\InlineCss;
use ILIAS\Services\UICore\Page\Media\Js;
use ILIAS\Services\UICore\Page\Media\OnLoadCode;
use ILIAS\Services\UICore\Page\PageInfo;
use ILIAS\UI\Component\Layout\Page\Standard;
use ILIAS\UI\NotImplementedException;

/**
 * Class ilGlobalPageTemplate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGlobalPageTemplate extends ilGlobalTemplate implements ilGlobalTemplateInterface {

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
	//
	// JS&CSS
	//
	/**
	 * @var array
	 */
	public $js_files = [];
	/**
	 * @var array
	 */
	public $css_files = [];
	/**
	 * @var array
	 */
	public $inline_css = [];
	/**
	 * @var array
	 */
	public $on_load_code = [];
	/**
	 * @var array
	 */
	public $js_files_batch = [];
	//
	// CONTENT
	//
	/**
	 * @var string
	 */
	private $center_content_html = "";
	/**
	 * @var string
	 */
	private $left_content_html = "";
	/**
	 * @var string
	 */
	private $right_content_html = "";
	//
	// NEW
	//
	/**
	 * @var StandardView
	 */
	private $view;
	/**
	 * @var PageInfo
	 */
	private $page_info;


	/**
	 * @inheritDoc
	 */
	public function __construct(Services $gs, UIServices $ui, HTTPServices $http) {
		$this->ui = $ui;
		$this->gs = $gs;
		$this->http = $http;
		$this->view = new StandardView($ui->factory()->layout()->page());
		$this->page_info = new PageInfo();
		parent::__construct("tpl.main.html", true, true); // TODO remove
	}


	private function prepareOutputHeaders() {
		$response = $this->http->response();
		$this->http->saveResponse($response->withAddedHeader('P3P', 'CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"'));
		$this->http->saveResponse($response->withAddedHeader('Content-type', 'text/html; charset=UTF-8'));
	}


	private function prepareBasicJS() {
		\iljQueryUtil::initjQuery($this);
		ilYuiUtil::initDom();
		$this->page_info->addJs(new Js("./Services/JavaScript/js/Basic.js", true, 1));
		\ilUIFramework::init($this);

	}


	private function prepareBasicCSS() {
		$this->page_info->addCss(new Css(\ilUtil::getStyleSheetLocation("filesystem", "delos.css")));
		$this->page_info->addCss(new Css(\ilUtil::getNewContentStyleSheetLocation()));
	}


	/**
	 * @return PageInfo
	 * @deprecated
	 */
	public function getPageInfo(): PageInfo {
		return $this->page_info;
	}


	/**
	 * @inheritDoc
	 */
	public function printToStdout($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false) {
		$this->prepareOutputHeaders();
		$this->prepareBasicJS();
		$this->prepareBasicCSS();

		$metabar = $this->initMetaBar();
		$mainbar = $this->initMainBar();
		$this->view->addContent($this->ui->factory()->legacy($this->center_content_html));
		/**
		 * @var $page  Standard
		 */
		$page = $this->view->getPageForViewWithContent();
		$page = $page->withMainbar($mainbar)->withMetabar($metabar);

		print $this->ui->renderer()->render([$page]);
	}


	/**
	 * @return \ILIAS\UI\Component\MainControls\MetaBar
	 */
	private function initMetaBar(): \ILIAS\UI\Component\MainControls\MetaBar {
		$slate = $this->ui->factory()->mainControls()->slate()->legacy('lorem', $this->ui->factory()->icon()->standard('65', '65'), $this->ui->factory()->legacy("CONTENT"));
		$metabar = $this->ui->factory()->mainControls()->metaBar()->withAdditionalEntry('anid', $slate);

		return $metabar;
	}


	/**
	 * @return \ILIAS\UI\Component\MainControls\MainBar
	 * @throws Throwable
	 */
	private function initMainBar(): \ILIAS\UI\Component\MainControls\MainBar {
		$f = $this->ui->factory();
		$main_bar = $f->mainControls()->mainBar();

		$ilMMItemRepository = new ilMMItemRepository($this->gs->storage());
		foreach ($ilMMItemRepository->getStackedTopItemsForPresentation() as $item) {
			$slate = $item->getTypeInformation()->getRenderer()->getComponentForItem($item);
			$identifier = $item->getProviderIdentification()->getInternalIdentifier();
			$main_bar = $main_bar->withAdditionalEntry($identifier, $slate);
		}

		$main_bar = $main_bar->withMoreButton(
			$f->button()->bulky(
				$f
					->glyph()
					->add(), 'more', "#"
			)
		);

		return $main_bar;
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
		$this->center_content_html = $a_html;
	}


	/**
	 * @inheritDoc
	 */
	public function setLeftContent($a_html) { // //
		$this->page_info->setLeftContent($this->ui->factory()->legacy($a_html));
		$this->left_content_html = $a_html;
	}


	/**
	 * @inheritDoc
	 */
	public function setRightContent($a_html) { // //
		$this->page_info->setRightContent($this->ui->factory()->legacy($a_html));
		$this->right_content_html = $a_html;
	}


	public function setVariable($variable, $value = '') {
		// throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function loadStandardTemplate() {
		parent::loadStandardTemplate();
		// always load jQuery
		iljQueryUtil::initjQuery();
		iljQueryUtil::initjQueryUI();

		// always load ui framework
		ilUIFramework::init();
		// throw new NotImplementedException();

		// Hier müssten die beiden Blockfiles eingefügt werden
		// $this->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		// $this->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	}


	// JS & CSS


	/**
	 * @inheritDocphpphp
	 */
	public function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2) { // //
		$this->page_info->addJs(new Js($a_js_file, $a_add_version_parameter, $a_batch));
		parent::addJavaScript($a_js_file, $a_add_version_parameter, $a_batch);
	}


	/**
	 * @inheritDoc
	 */
	public function addCss($a_css_file, $media = "screen") { // //
		$this->page_info->addCss(new Css($a_css_file, $media));
		parent::addCss($a_css_file, $media);
	}


	/**
	 * @inheritDoc
	 */
	public function addOnLoadCode($a_code, $a_batch = 2) { // //
		$this->page_info->addOnloadCode(new OnLoadCode($a_code, $a_batch));
		parent::addOnLoadCode($a_code, $a_batch);
	}


	/**
	 * @inheritDoc
	 */
	public function setLocator() {
		// throw new NotImplementedException();
		parent::setLocator();
	}


	// MAIN INFOS


	/**
	 * @inheritDoc
	 */
	public function setTitle($a_title) { // //
		$this->page_info->setTitle($a_title);
	}


	/**
	 * @inheritDoc
	 */
	public function setDescription($a_descr) { // //
		$this->page_info->setDescription($a_descr);
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
	public function setAlertProperties(array $a_props) {
		// throw new NotImplementedException();
	}


	// NEEDS ADJUSTMENT

	public function setPermanentLink($a_type, $a_id, $a_append = "", $a_target = "", $a_title = "") { //
		// throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function enableDragDropFileUpload($a_ref_id) { // //
		$this->page_info->enableFileDragAndDrop(); // TODO: is ref_id needed?
	}


	/**
	 * @inheritDoc
	 */
	public function setTreeFlatIcon($a_link, $a_mode) {
		// throw new NotImplementedException();
	}


	// NOT SURE


	/**
	 * @inheritDoc
	 */
	public function hideFooter() {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setOnScreenMessage($a_type, $a_txt, $a_keep = false) {
	}


	/**
	 * @inheritDoc
	 */
	public function getOnLoadCodeForAsynch() {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function resetJavascript() {
		throw new NotImplementedException();
	}


	public function fillJavaScriptFiles($a_force = false) { //internal
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function addInlineCss($a_css, $media = "screen") { // //
		$this->page_info->addInlineCss(new InlineCss($a_css, $media));
	}


	public function setBodyClass($a_class = "") { // //
		$this->page_info->setBodyClass($a_class);
		parent::setBodyClass($a_class);
	}


	/**
	 * @inheritDoc
	 */
	public function clearHeader() {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setHeaderActionMenu($a_header) { // //
		$this->page_info->setHeaderActionMenu($this->ui->factory()->legacy($a_header));
	}


	/**
	 * @inheritDoc
	 */
	public function setHeaderPageTitle($a_title) {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setTabs($a_tabs_html) { // //
		$this->page_info->setTabs($this->ui->factory()->legacy($a_tabs_html));
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setSubTabs($a_tabs_html) { //
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setLeftNavContent($a_content) {
		// throw new NotImplementedException();
	}


	public function setPageFormAction($a_action) { //
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setLoginTargetPar($a_val) {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function getSpecial($part = "DEFAULT", $add_error_mess = false, $handle_referer = false, $add_ilias_footer = false, $add_standard_elements = false, $a_main_menu = true, $a_tabs = true) { //
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function addLightbox($a_html, $a_id) { //
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function addAdminPanelToolbar(ilToolbarGUI $toolb, $a_bottom_panel = true, $a_arrow = false) { //
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function resetHeaderBlock($a_reset_header_action = true) {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function get($part = "DEFAULT") {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function setCurrentBlock($part = "DEFAULT") {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function touchBlock($block) {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function parseCurrentBlock($part = "DEFAULT") {
		throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function addBlockFile($var, $block, $tplname, $in_module = false) {
		parent::addBlockFile($var, $block, $tplname, $in_module);
		// throw new NotImplementedException();
	}


	/**
	 * @inheritDoc
	 */
	public function blockExists($a_blockname) {
		throw new NotImplementedException();
	}
}
