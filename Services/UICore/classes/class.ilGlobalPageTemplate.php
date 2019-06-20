<?php

use ILIAS\DI\HTTPServices;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Scope\Layout\Builder\PageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media\InlineCss;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\BreadCrumbsModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\LogoModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\MainBarModifier;
use ILIAS\GlobalScreen\Scope\Layout\Modifier\MetaBarModifier;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePartProvider;
use ILIAS\GlobalScreen\Services;
use ILIAS\Services\UICore\MetaTemplate\PageContentGUI;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\NotImplementedException;

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
    }


    private function prepareOutputHeaders()
    {
        $response = $this->http->response();
        $this->http->saveResponse($response->withAddedHeader('P3P', 'CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"'));
        $this->http->saveResponse($response->withAddedHeader('Content-type', 'text/html; charset=UTF-8'));

        if (defined("ILIAS_HTTP_PATH")) {
            $this->gs->layout()->meta()->setBaseURL((substr(ILIAS_HTTP_PATH, -1) == '/' ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/'));
        }
    }


    private function prepareBasicJS()
    {
        \iljQueryUtil::initjQuery($this);
        \iljQueryUtil::initjQueryUI($this);
        $this->gs->layout()->meta()->addJs("./Services/JavaScript/js/Basic.js", true, 1);
        \ilUIFramework::init($this);
    }


    private function prepareBasicCSS()
    {
        $this->gs->layout()->meta()->addCss(\ilUtil::getStyleSheetLocation("filesystem", "delos.css"));
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

        //
        // PAGE
        //
        $this->gs->layout()->modifiers()->modifyPageWithClosure(function (Page $current) : Page {
            return $this->ui->factory()->layout()->page()->standard([]);
        });

        $this->gs->layout()->modifiers()->modifyPageWithInstance(new class implements PageBuilder
        {

            public function build(PagePartProvider $parts) : Page
            {
                global $DIC;

                return $DIC->ui()->factory()->layout()->page()->standard(
                    [$parts->getContent()],
                    $parts->getMetaBar(),
                    $parts->getMainBar(),
                    $parts->getBreadCrumbs(),
                    $parts->getLogo());
            }
        });

        //
        // MetaBar
        //
        $this->gs->layout()->modifiers()->modifyMetaBarWithClosure(function (MetaBar $current) : MetaBar {
            $f = $this->ui->factory();

            $symbol = $f->symbol()->glyph()->sortDescending();
            $content = $f->legacy('This is a completely replaced MetaBar');
            $entry = $f->mainControls()->slate()->legacy('test', $symbol, $content);

            return $f->mainControls()->metaBar()
                ->withAdditionalEntry('lorem', $entry);
        });

        $this->gs->layout()->modifiers()->modifyMetaBarWithInstance(new class implements MetaBarModifier
        {

            public function getMetaBar(MetaBar $current) : MetaBar
            {
                global $DIC;
                $f = $DIC->ui()->factory();

                $symbol = $f->symbol()->glyph()->sortDescending();
                $content = $f->legacy('This is a completely replaced MetaBar');
                $entry = $f->mainControls()->slate()->legacy('test', $symbol, $content);

                return $f->mainControls()->metaBar()
                    ->withAdditionalEntry('lorem', $entry);
            }
        });

        //
        // MainBar
        //
        $this->gs->layout()->modifiers()->modifyMainBarWithClosure(function (MainBar $current) : MainBar {
            $f = $this->ui->factory();

            $symbol = $f->symbol()->glyph()->up();
            $content = $f->legacy("Hi there!");
            $entry = $f->mainControls()->slate()->legacy('entry', $symbol, $content);

            return $current->withAdditionalEntry('lorem', $entry);
        });

        $this->gs->layout()->modifiers()->modifyMainBarWithInstance(new class implements MainBarModifier
        {

            public function getMainBar(MainBar $current) : MainBar
            {
                global $DIC;
                $f = $DIC->ui()->factory();

                $symbol = $f->symbol()->glyph()->up();
                $content = $f->legacy("Hi there!");
                $entry = $f->mainControls()->slate()->legacy('entry', $symbol, $content);

                return $current->withAdditionalEntry('lorem2', $entry);
            }
        });

        //
        // BreadCrumbs
        //

        $this->gs->layout()->modifiers()->modifyBreadCrumbsWithClosure(function (Breadcrumbs $current) : Breadcrumbs {
            return $current->withAppendedItem($this->ui->factory()->link()->standard("Additional Item!", "#"));
        });

        $this->gs->layout()->modifiers()->modifyBreadCrumbsWithInstance(new class implements BreadCrumbsModifier
        {

            public function getBreadCrumbs(Breadcrumbs $current) : Breadcrumbs
            {
                global $DIC;

                return $current->withAppendedItem($DIC->ui()->factory()->link()->standard("another Item!", "#"));
            }
        });

        //
        // Logo
        //
        $this->gs->layout()->modifiers()->modifyLogoWithClosure(function (Image $current) : Image {
            return $this->ui->factory()->image()->responsive("https://brandmark.io/logo-rank/random/apple.png", "ILIAS");
        });

        $this->gs->layout()->modifiers()->modifyLogoWithInstance(new class implements LogoModifier
        {

            /**
             * @inheritDoc
             */
            public function getLogo(Image $current) : Image
            {
                global $DIC;

                return $DIC->ui()->factory()->image()->responsive("https://brandmark.io/logo-rank/random/apple.png", "ILIAS");
            }
        });

        print $this->ui->renderer()->render($this->gs->layout()->final());
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
        $this->gs->layout()->meta()->addInlineCss(new InlineCss($a_css, $media));
    }


    // CONTENT


    /**
     * @inheritDoc
     */
    public function setContent($a_html)
    {
        $this->legacy_content_template->setMainContent($a_html);

        $this->gs->layout()->modifiers()->modifyContentWithClosure(function (Legacy $original_content) : Legacy {
            return $this->ui->factory()->legacy($this->legacy_content_template->renderPage("DEFAULT", true, false));
        });
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
    public function setTitle($a_title)
    {
        $this->legacy_content_template->setTitle($a_title);
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
        $this->legacy_content_template->addLightbox($a_id, $a_html);
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
        // Nothing to do
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
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
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
}