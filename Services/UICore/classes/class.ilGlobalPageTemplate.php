<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Services;
use ILIAS\Notifications\ilNotificationOSDGUI;
use ILIAS\Services\UICore\MetaTemplate\PageContentGUI;
use ILIAS\UI\NotImplementedException;
use ILIAS\UICore\PageContentProvider;
use ILIAS\Accessibility\GlobalPageHandler;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilGlobalPageTemplate implements ilGlobalTemplateInterface
{
    protected HTTPServices $http;
    protected Services $gs;
    protected UIServices $ui;
    protected PageContentGUI $legacy_content_template;
    protected ilLanguage $lng;
    protected ilSetting $il_settings;
    protected Refinery $refinery;

    /**
     * @var string[]
     */
    protected static array $ignored_blocks = [
        self::DEFAULT_BLOCK,
        'ContentStyle',
        "SyntaxStyle",
        "",
    ];

    public function __construct(Services $gs, UIServices $ui, HTTPServices $http)
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ui = $ui;
        $this->gs = $gs;
        $this->http = $http;
        $this->legacy_content_template = new PageContentGUI("tpl.page_content.html", true, true);
        $this->il_settings = $DIC->settings();
        $this->refinery = $DIC->refinery();
    }

    protected function prepareOutputHeaders(): void
    {
        $this->http->saveResponse(
            $this->http->response()->withAddedHeader(
                'P3P',
                'CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"'
            )
        );

        $this->http->saveResponse(
            $this->http->response()->withAddedHeader('Content-type', 'text/html; charset=UTF-8')
        );

        if (defined("ILIAS_HTTP_PATH")) {
            $this->gs->layout()->meta()->setBaseURL(
                (substr(ILIAS_HTTP_PATH, -1) === '/' ? ILIAS_HTTP_PATH : ILIAS_HTTP_PATH . '/')
            );
        }

        $this->gs->layout()->meta()->setTextDirection($this->lng->getTextDirection());
    }

    /**
     * @throws JsonException
     */
    protected function prepareBasicJS(): void
    {
        iljQueryUtil::initjQuery($this);
        iljQueryUtil::initjQueryUI($this);
        $this->gs->layout()->meta()->addJs("./Services/JavaScript/js/Basic.js", true, 1);
        ilUIFramework::init($this);
        ilBuddySystemGUI::initializeFrontend($this);
        ilOnScreenChatGUI::initializeFrontend($this);
        GlobalPageHandler::initPage($this);

        $sessionReminder = new ilSessionReminderGUI(
            ilSessionReminder::byLoggedInUser(),
            $this,
            $this->lng
        );

        $sessionReminder->populatePage();
        $onScreenNotifier = new ilNotificationOSDGUI($this, $this->lng);
        $onScreenNotifier->populatePage();
    }

    protected function prepareBasicCSS(): void
    {
        $this->gs->layout()->meta()->addCss(ilUtil::getStyleSheetLocation());
        $this->gs->layout()->meta()->addCss(ilUtil::getNewContentStyleSheetLocation());
    }

    public function printToStdout(
        string $part = self::DEFAULT_BLOCK,
        bool $a_fill_tabs = true,
        bool $a_skip_main_menu = false
    ): void {
        $this->prepareOutputHeaders();
        $this->prepareBasicJS();
        $this->prepareBasicCSS();

        PageContentProvider::setContent($this->legacy_content_template->renderPage(self::DEFAULT_BLOCK, true));
        print $this->ui->renderer()->render($this->gs->collector()->layout()->getFinalPage());

        // save language usages as late as possible
        ilObjLanguageAccess::_saveUsages();
    }

    public function printToString(): string
    {
        $this->prepareOutputHeaders();
        $this->prepareBasicJS();
        $this->prepareBasicCSS();

        PageContentProvider::setContent($this->legacy_content_template->renderPage(self::DEFAULT_BLOCK, true));

        return $this->ui->renderer()->render($this->gs->collector()->layout()->getFinalPage());
    }

    public function addJavaScript(string $a_js_file, bool $a_add_version_parameter = true, int $a_batch = 2): void
    {
        $this->gs->layout()->meta()->addJs($a_js_file, $a_add_version_parameter, $a_batch);
    }

    public function addCss(string $a_css_file, string $media = "screen"): void
    {
        $this->gs->layout()->meta()->addCss($a_css_file, $media);
    }

    public function addOnLoadCode(string $a_code, int $a_batch = 2): void
    {
        $this->gs->layout()->meta()->addOnloadCode($a_code, $a_batch);
    }

    public function addInlineCss(string $a_css, string $media = "screen"): void
    {
        $this->gs->layout()->meta()->addInlineCss($a_css, $media);
    }

    public function setContent(string $a_html): void
    {
        $this->legacy_content_template->setMainContent($a_html);
    }

    public function setLeftContent(string $a_html): void
    {
        $this->legacy_content_template->setLeftContent($a_html);
    }

    public function setRightContent(string $a_html): void
    {
        $this->legacy_content_template->setRightContent($a_html);
    }

    public function setFilter(string $filter): void
    {
        $this->legacy_content_template->setFilter($filter);
    }

    public function setTitle(string $a_title, bool $hidden = false): void
    {
        $this->legacy_content_template->setTitle($a_title, $hidden);

        $short_title = (string) $this->il_settings->get('short_inst_name');
        if (trim($short_title) === "") {
            $short_title = 'ILIAS';
        }

        PageContentProvider::setShortTitle($short_title);
        PageContentProvider::setViewTitle($a_title);
        $header_title = ilObjSystemFolder::_getHeaderTitle();
        PageContentProvider::setTitle($header_title);
    }

    public function setDescription(string $a_descr): void
    {
        $this->legacy_content_template->setTitleDesc($a_descr);
    }

    public function setTitleIcon(string $a_icon_path, string $a_icon_desc = ""): void
    {
        $this->legacy_content_template->setIconPath($a_icon_path);
        $this->legacy_content_template->setIconDesc($a_icon_desc);
    }

    public function setBanner(string $img_src): void
    {
        $this->legacy_content_template->setBanner($img_src);
    }

    public function setAlertProperties(array $a_props): void
    {
        $this->legacy_content_template->setTitleAlerts($a_props);
    }

    public function setOnScreenMessage(string $a_type, string $a_txt, bool $a_keep = false): void
    {
        $this->legacy_content_template->setOnScreenMessage($a_type, $a_txt, $a_keep);
    }

    public function setFileUploadRefId(int $a_ref_id): void
    {
        $this->legacy_content_template->setFileUploadRefId($a_ref_id);
    }

    public function setHeaderActionMenu(string $a_header): void
    {
        $this->legacy_content_template->setHeaderAction($a_header);
    }

    public function setHeaderPageTitle(string $a_title): void
    {
        $this->legacy_content_template->setHeaderPageTitle($a_title);
    }

    public function addLightbox(string $a_html, string $a_id): void
    {
        $this->legacy_content_template->addLightbox($a_html, $a_id);
    }

    public function setPageFormAction(string $a_action): void
    {
        $this->legacy_content_template->setPageFormAction($a_action);
    }

    public function addAdminPanelToolbar(ilToolbarGUI $toolb, bool $a_bottom_panel = true, bool $a_arrow = false): void
    {
        $this->legacy_content_template->setAdminPanelCommandsToolbar($toolb);
        $this->legacy_content_template->setAdminPanelArrow($a_arrow);
        $this->legacy_content_template->setAdminPanelBottom($a_bottom_panel);
    }

    public function setVariable(string $variable, $value = ''): void
    {
        if ($variable === "LOCATION_CONTENT_STYLESHEET" || $variable === "LOCATION_SYNTAX_STYLESHEET") {
            $this->addCss($value);

            return;
        }

        $this->legacy_content_template->setVariable(
            $variable,
            $this->refinery->kindlyTo()->string()->transform($value)
        );
    }

    public function resetJavascript(): void
    {
        $this->gs->layout()->meta()->getJs()->clear();
    }

    public function get(string $part = self::DEFAULT_BLOCK): string
    {
        return $this->legacy_content_template->get($part);
    }

    public function setCurrentBlock(string $blockname = self::DEFAULT_BLOCK): bool
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return false;
        }

        if ($this->blockExists($blockname)) {
            return $this->legacy_content_template->setCurrentBlock($blockname);
        }
        throw new ilTemplateException("block " . var_export($blockname, true) . " not found");
    }

    public function touchBlock(string $blockname): bool
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return false;
        }

        if ($this->blockExists($blockname)) {
            $this->legacy_content_template->touchBlock($blockname);
            return true;
        }

        throw new ilTemplateException("block " . var_export($blockname, true) . " not found");
    }

    public function parseCurrentBlock(string $blockname = self::DEFAULT_BLOCK): bool
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return false; // TODO why is this needed?
        }
        if ($this->blockExists($blockname)) {
            return $this->legacy_content_template->parseCurrentBlock($blockname);
        }

        throw new ilTemplateException("block " . var_export($blockname, true) . " not found");
    }

    public function addBlockFile(string $var, string $block, string $template_name, string $in_module = null): bool
    {
        if ($this->blockExists($block)) {
            $this->legacy_content_template->removeBlockData($block);
        }

        return $this->legacy_content_template->addBlockFile($var, $block, $template_name, $in_module);
    }

    public function blockExists(string $blockname): bool
    {
        if (in_array($blockname, self::$ignored_blocks)) {
            return false;
        }

        return $this->legacy_content_template->blockExists($blockname);
    }

    public function loadStandardTemplate(): void
    {
        // Nothing to do
    }

    public function setLocator(): void
    {
        // Nothing to do
    }

    public function setPermanentLink(
        string $a_type,
        ?int $a_id,
        string $a_append = "",
        string $a_target = "",
        string $a_title = ""
    ): void {
        $href = ilLink::_getStaticLink($a_id, $a_type, true, $a_append);
        PageContentProvider::setPermaLink($href);
    }

    public function setTreeFlatIcon(string $a_link, string $a_mode): void
    {
        // Nothing to do
    }

    public function hideFooter(): void
    {
        // Nothing to do
    }

    public function setLeftNavContent(string $a_content): void
    {
        // Nothing to do, this should be handled in Slates later
    }

    public function resetHeaderBlock(bool $a_reset_header_action = true): void
    {
        // Nothing to do
    }

    public function setLoginTargetPar(string $a_val): void
    {
        // Nothing to do
    }

    public function getOnLoadCodeForAsynch(): string
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

    public function fillJavaScriptFiles(bool $a_force = false): void
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }

    public function setBodyClass(string $a_class = ""): void
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }

    public function clearHeader(): void
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }

    public function setTabs(string $a_tabs_html): void
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }

    public function setSubTabs(string $a_tabs_html): void
    {
        throw new NotImplementedException("This Method is no longer available in GlobalTemplate");
    }

    public function getSpecial(
        string $part = self::DEFAULT_BLOCK,
        bool $add_error_mess = false,
        bool $handle_referer = false,
        bool $add_ilias_footer = false,
        bool $add_standard_elements = false,
        bool $a_main_menu = true,
        bool $a_tabs = true
    ): string {
        throw new NotImplementedException();
    }
}
