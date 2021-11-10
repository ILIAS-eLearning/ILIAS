<?php

use ILIAS\DI\HTTPServices;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Services;
use ILIAS\Services\UICore\MetaTemplate\PageContentGUI;
use ILIAS\UI\NotImplementedException;
use ILIAS\UICore\PageContentProvider;

/**
 * Class ilGlobalPageTemplate
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilGlobalPageTemplate implements ilGlobalTemplateInterface
{

    /**
     * @var array
     */
    private static $ignored_blocks = ['ContentStyle', "DEFAULT", "SyntaxStyle", ""];
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
     * @var PageContentGUI
     */
    private $legacy_content_template;
    /**
     * @var ilLanguage
     */
    private $lng;


    /**
     * @inheritDoc
     */
    public function __construct(Services $gs, UIServices $ui, HTTPServices $http)
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ui = $ui;
        $this->gs = $gs;
        $this->http = $http;
        $this->legacy_content_template = new PageContentGUI("tpl.page_content.html", true, true);
        $this->il_settings = $DIC->settings();
    }


    private function prepareOutputHeaders()
    {
        $this->http->saveResponse($this->http->response()->withAddedHeader('P3P', 'CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"'));
        $this->http->saveResponse($this->http->response()->withAddedHeader('Content-type', 'text/html; charset=UTF-8'));

        if (defined("ILIAS_HTTP_PATH")) {
            $this->gs->layout()->meta()->setBaseURL((substr(ILIAS_HTTP_PATH, -1) == '/' ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/'));
        }
        $this->gs->layout()->meta()->setTextDirection($this->lng->getTextDirection());
    }


    private function prepareBasicJS()
    {
        \iljQueryUtil::initjQuery($this);
        \iljQueryUtil::initjQueryUI($this);
        $this->gs->layout()->meta()->addJs("./Services/JavaScript/js/Basic.js", true, 1);
        ilYuiUtil::initConnection();
        \ilUIFramework::init($this);
        \ilBuddySystemGUI::initializeFrontend($this);
        \ilOnScreenChatGUI::initializeFrontend($this);
        \ILIAS\Accessibility\GlobalPageHandler::initPage($this);

        $sessionReminder = new ilSessionReminderGUI(
            ilSessionReminder::createInstanceWithCurrentUserSession(),
            $this,
            $this->lng
        );
        $sessionReminder->populatePage();

        $onScreenNotifier = new ilNotificationOSDGUI($this, $this->lng);
        $onScreenNotifier->populatePage();
    }


    private function prepareBasicCSS()
    {
        $this->gs->layout()->meta()->addCss(\ilUtil::getStyleSheetLocation());
        $this->gs->layout()->meta()->addCss(\ilUtil::getNewContentStyleSheetLocation());
    }


    /**
     * @inheritDoc
     */
    public function printToStdout($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false)
    {
        $this->prepareOutputHeaders();
        $this->prepareBasicJS();
        $this->prepareBasicCSS();

        PageContentProvider::setContent($this->legacy_content_template->renderPage("DEFAULT", true, false));
        print $this->ui->renderer()->render($this->gs->collector()->layout()->getFinalPage());

        // save language usages as late as possible
        \ilObjLanguageAccess::_saveUsages();

        // see #26968
        $this->handleReferer();
    }


    /**
     * @inheritDoc
     */
    public function printToString() : string
    {
        $this->prepareOutputHeaders();
        $this->prepareBasicJS();
        $this->prepareBasicCSS();

        PageContentProvider::setContent($this->legacy_content_template->renderPage("DEFAULT", true, false));

        return $this->ui->renderer()->render($this->gs->collector()->layout()->getFinalPage());
    }


    //
    // NEEDED METHODS, but wrapped and will triage to the internal template or to the
    //

    // CSS & JS
    /**
     * @inheritDoc
     */
    public function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2)
    {
        $this->gs->layout()->meta()->addJs($a_js_file, $a_add_version_parameter, $a_batch);
    }


    /**
     * @inheritDoc
     */
    public function addCss($a_css_file, $media = "screen")
    {
        $this->gs->layout()->meta()->addCss($a_css_file, $media);
    }


    /**
     * @inheritDoc
     */
    public function addOnLoadCode($a_code, $a_batch = 2)
    {
        $this->gs->layout()->meta()->addOnloadCode($a_code, $a_batch);
    }


    /**
     * @inheritDoc
     */
    public function addInlineCss($a_css, $media = "screen")
    {
        $this->gs->layout()->meta()->addInlineCss($a_css, $media);
    }


    // CONTENT


    /**
     * @inheritDoc
     */
    public function setContent($a_html)
    {
        $this->legacy_content_template->setMainContent($a_html);
    }


    /**
     * @inheritDoc
     */
    public function setLeftContent($a_html)
    {
        $this->legacy_content_template->setLeftContent($a_html);
    }


    /**
     * @inheritDoc
     */
    public function setRightContent($a_html)
    {
        $this->legacy_content_template->setRightContent($a_html);
    }

    // Filter section


    /**
     * @param string $filter
     */
    public function setFilter(string $filter)
    {
        $this->legacy_content_template->setFilter($filter);
    }


    // MAIN INFOS


    /**
     * @inheritDoc
     */
    public function setTitle($a_title, $hidden = false)
    {
        $this->legacy_content_template->setTitle((string) $a_title, $hidden);

        $short_title = (string) $this->il_settings->get('short_inst_name');
        if (trim($short_title) === "") {
            $short_title = 'ILIAS';
        }

        PageContentProvider::setShortTitle($short_title);
        PageContentProvider::setViewTitle((string) $a_title);
        $header_title = ilObjSystemFolder::_getHeaderTitle();
        PageContentProvider::setTitle($header_title);
    }


    /**
     * @inheritDoc
     */
    public function setDescription($a_descr)
    {
        $this->legacy_content_template->setTitleDesc($a_descr);
    }


    /**
     * @inheritDoc
     */
    public function setTitleIcon($a_icon_path, $a_icon_desc = "")
    {
        $this->legacy_content_template->setIconPath($a_icon_path);
        $this->legacy_content_template->setIconDesc($a_icon_desc);
    }


    public function setBanner(string $img_src)
    {
        $this->legacy_content_template->setBanner($img_src);
    }

    // ALERTS & OS-MESSAGES


    /**
     * @inheritDoc
     */
    public function setAlertProperties(array $a_props)
    {
        $this->legacy_content_template->setTitleAlerts($a_props);
    }


    /**
     * @inheritDoc
     */
    public function setOnScreenMessage($a_type, $a_txt, $a_keep = false)
    {
        $this->legacy_content_template->setOnScreenMessage($a_type, $a_txt, $a_keep);
    }

    // SPECIAL FEATURES


    /**
     * @inheritDoc
     */
    public function enableDragDropFileUpload($a_ref_id)
    {
        $this->legacy_content_template->setEnableFileupload((int) $a_ref_id);
    }


    /**
     * @inheritDoc
     */
    public function setHeaderActionMenu($a_header)
    {
        $this->legacy_content_template->setHeaderAction($a_header);
    }


    /**
     * @inheritDoc
     */
    public function setHeaderPageTitle($a_title)
    {
        $this->legacy_content_template->setHeaderPageTitle($a_title);
    }


    /**
     * @inheritDoc
     */
    public function addLightbox($a_html, $a_id)
    { //
        $this->legacy_content_template->addLightbox($a_html, $a_id);
    }


    /**
     * @param $a_action
     */
    public function setPageFormAction($a_action)
    {
        $this->legacy_content_template->setPageFormAction($a_action);
    }


    /**
     * @inheritDoc
     */
    public function addAdminPanelToolbar(ilToolbarGUI $toolb, $a_bottom_panel = true, $a_arrow = false)
    {
        $this->legacy_content_template->setAdminPanelCommandsToolbar($toolb);
        $this->legacy_content_template->setAdminPanelArrow($a_arrow);
        $this->legacy_content_template->setAdminPanelBottom($a_bottom_panel);
    }



    //
    // Currently needed but should vanish soon
    //

    /**
     * @param        $variable
     * @param string $value
     */
    public function setVariable($variable, $value = '')
    {
        if ($variable === "LOCATION_CONTENT_STYLESHEET" || $variable === "LOCATION_SYNTAX_STYLESHEET") {
            $this->addCss($value);

            return;
        }
        $this->legacy_content_template->setVariable($variable, $value);
    }


    /**
     * @inheritDoc
     */
    public function resetJavascript()
    {
        $this->gs->layout()->meta()->getJs()->clear();
    }


    /**
     * @inheritDoc
     */
    public function get($part = "DEFAULT")
    {
        return $this->legacy_content_template->get($part);
    }


    /**
     * @inheritDoc
     */
    public function setCurrentBlock($blockname = "DEFAULT")
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return; // TODO why is this needed?
        }

        if ($this->blockExists($blockname)) {
            $this->legacy_content_template->setCurrentBlock($blockname);
        } else {
            throw new ilTemplateException("block " . var_export($blockname, true) . " not found");
        }
    }


    /**
     * @inheritDoc
     */
    public function touchBlock($blockname)
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return; // TODO why is this needed?
        }
        if ($this->blockExists($blockname)) {
            $this->legacy_content_template->touchBlock($blockname);
        } else {
            throw new ilTemplateException("block " . var_export($blockname, true) . " not found");
        }
    }


    /**
     * @inheritDoc
     */
    public function parseCurrentBlock($blockname = "DEFAULT")
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return; // TODO why is this needed?
        }
        if ($this->blockExists($blockname)) {
            return $this->legacy_content_template->parseCurrentBlock($blockname);
        } else {
            throw new ilTemplateException("block " . var_export($blockname, true) . " not found");
        }
    }


    /**
     * @inheritDoc
     */
    public function addBlockFile($var, $block, $tplname, $in_module = false)
    {
        if ($this->blockExists($block)) {
            $this->legacy_content_template->removeBlockData($block);
        }

        return $this->legacy_content_template->addBlockFile($var, $block, $tplname, $in_module);
    }


    /**
     * @inheritDoc
     */
    public function blockExists($blockname)
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return false; // TODO why is this needed?
        }

        return $this->legacy_content_template->blockExists($blockname);
    }




    //
    // Currently part of the interface but no applicable in ilGlobalPageTemplate
    //

    /**
     * @inheritDoc
     */
    public function loadStandardTemplate()
    {
        // Nothing to do
    }


    /**
     * @inheritDoc
     */
    public function setLocator()
    {
        // Nothing to do
    }


    /**
     * @inheritDoc
     */
    public function setPermanentLink($a_type, $a_id, $a_append = "", $a_target = "", $a_title = "")
    {
        $href = ilLink::_getStaticLink($a_id, $a_type, true, $a_append);
        PageContentProvider::setPermaLink($href);
    }


    /**
     * @inheritDoc
     */
    public function setTreeFlatIcon($a_link, $a_mode)
    {
        // Nothing to do
    }


    /**
     * @inheritDoc
     */
    public function hideFooter()
    {
        // Nothing to do
    }


    /**
     * @inheritDoc
     */
    public function setLeftNavContent($a_content)
    {
        // Nothing to do, this should be handled in Slates later
    }


    /**
     * @inheritDoc
     */
    public function resetHeaderBlock($a_reset_header_action = true)
    {
        // Nothing to do
    }


    /**
     * @inheritDoc
     */
    public function setLoginTargetPar($a_val)
    {
        // Nothing to do
    }

    //
    // NO LONGER AVAILABLE
    //

    /**
     * @inheritDoc
     */
    public function getOnLoadCodeForAsynch()
    {
        // see e.g. bug #26413
        $js = "";
        foreach ($this->gs->layout()->meta()->getOnLoadCode()->getItemsInOrderOfDelivery() as $code) {
            $js .= $code->getContent() . "\n";
        }
        if ($js) {
            return '<script type="text/javascript">' . "\n" .
                $js .
                '</script>' . "\n";
        }
        return "";
    }


    /**
     * @param bool $a_force
     */
    public function fillJavaScriptFiles($a_force = false)
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }


    /**
     * @inheritDoc
     */
    public function setBodyClass($a_class = "")
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }


    /**
     * @inheritDoc
     */
    public function clearHeader()
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }


    /**
     * @inheritDoc
     */
    public function setTabs($a_tabs_html)
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }


    /**
     * @inheritDoc
     */
    public function setSubTabs($a_tabs_html)
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }


    /**
     * @inheritDoc
     */
    public function getSpecial($part = "DEFAULT", $add_error_mess = false, $handle_referer = false, $add_ilias_footer = false, $add_standard_elements = false, $a_main_menu = true, $a_tabs = true)
    { //
        throw new NotImplementedException();
    }

    /**
     * Old method from global template
     * fixing #26968
     */
    private function handleReferer()
    {
        if (((substr(strrchr($_SERVER["PHP_SELF"], "/"), 1) != "error.php")
            && (substr(strrchr($_SERVER["PHP_SELF"], "/"), 1) != "adm_menu.php")
            && (substr(strrchr($_SERVER["PHP_SELF"], "/"), 1) != "chat.php"))) {
            // referer is modified if query string contains cmd=gateway and $_POST is not empty.
            // this is a workaround to display formular again in case of error and if the referer points to another page
            $url_parts = @parse_url($_SERVER["REQUEST_URI"]);
            if (!$url_parts) {
                $protocol = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://';
                $host = $_SERVER['HTTP_HOST'];
                $path = $_SERVER['REQUEST_URI'];
                $url_parts = @parse_url($protocol . $host . $path);
            }

            if (isset($url_parts["query"]) && preg_match("/cmd=gateway/", $url_parts["query"]) && (isset($_POST["cmd"]["create"]))) {
                foreach ($_POST as $key => $val) {
                    if (is_array($val)) {
                        $val = key($val);
                    }

                    $str .= "&" . $key . "=" . $val;
                }

                $_SESSION["referer"] = preg_replace("/cmd=gateway/", substr($str, 1), $_SERVER["REQUEST_URI"]);
                $_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
            } elseif (isset($url_parts["query"]) && preg_match("/cmd=post/", $url_parts["query"]) && (isset($_POST["cmd"]["create"]))) {
                foreach ($_POST as $key => $val) {
                    if (is_array($val)) {
                        $val = key($val);
                    }

                    $str .= "&" . $key . "=" . $val;
                }

                $_SESSION["referer"] = preg_replace("/cmd=post/", substr($str, 1), $_SERVER["REQUEST_URI"]);
                if (isset($_GET['ref_id'])) {
                    $_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
                } else {
                    $_SESSION['referer_ref_id'] = 0;
                }
            } else {
                $_SESSION["referer"] = $_SERVER["REQUEST_URI"];
                if (isset($_GET['ref_id'])) {
                    $_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
                } else {
                    $_SESSION['referer_ref_id'] = 0;
                }
            }

            unset($_SESSION["error_post_vars"]);
        }
    }
}
