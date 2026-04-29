<?php

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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\LearningModule\Export;

use ILIAS\COPage\PageLinker;
use ILIAS\ILIASObject\Properties\Translations\Translations;
use ilFileUtils;
use ILIAS\components\Export\HTML\Util;
use ILIAS\components\Export\HTML\ExportCollector;

class LMHtmlExport
{
    protected \ILIAS\DI\UIServices $ui;
    protected ExportCollector $collector;
    protected Util $export_util;
    protected \ilLogger $log;
    protected string $target_dir = "";
    protected string $sub_dir = "";
    protected string $export_dir = "";
    protected \ilObjLearningModule $lm;
    protected \ilGlobalTemplateInterface $main_tpl;
    protected \ilObjUser $user;
    protected \ilLocatorGUI $locator;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected string $export_format = "";
    protected \ilLMPresentationGUI $lm_gui;
    protected Translations $obj_transl;
    protected string $lang = "";
    protected \ilSetting $lm_settings;
    protected array $offline_files = [];
    protected string $initial_user_language = "";
    protected string $initial_current_user_language = "";
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        \ilObjLearningModule $lm,
        string $export_dir,
        string $sub_dir,
        string $export_format = "html",
        string $lang = ""
    ) {
        global $DIC;

        $this->locator = $DIC["ilLocator"];
        $this->user = $DIC->user();
        $this->lm = $lm;
        $this->export_dir = $export_dir;
        $this->sub_dir = $sub_dir;
        $this->lang = $lang;
        $this->target_dir = $export_dir . "/" . $sub_dir;
        $cs = $DIC->contentStyle();
        $this->ui = $DIC->ui();
        $this->content_style_domain = $cs->domain()->styleForRefId($this->lm->getRefId());
        $this->collector = $DIC->export()->domain()->html()->collector($this->lm->getId());
        $this->collector->init();

        $this->export_util = new Util("", "", $this->collector);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, $this->getLinker(), $lm->getRefId(), $this->collector);

        $this->co_page_html_export->setContentStyleId(
            $this->content_style_domain->getEffectiveStyleId()
        );
        $this->export_format = $export_format;

        // get learning module presentation gui class
        $this->lm_gui = new \ilLMPresentationGUI($export_format, ($lang == "all"), $this->target_dir, false);
        $this->obj_transl = $lm->getObjectProperties()->getPropertyTranslations();

        $this->lm_settings = new \ilSetting("lm");

        $this->log = \ilLoggerFactory::getLogger("lm");

        $this->initial_user_language = $this->user->getLanguage();
        $this->initial_current_user_language = $this->user->getCurrentLanguage();

        $this->global_screen = $DIC->globalScreen();

        $this->setAdditionalContextData(\ilLMEditGSToolProvider::SHOW_TREE, false);
    }

    protected function getLinker(): PageLinker
    {
        return new \ilLMPresentationLinker(
            $this->lm,
            new \ilLMTree($this->lm->getId()),
            0,
            $this->lm->getRefId(),
            $this->lang,
            "",
            "",
            true,
            "html",
            false
        );
    }

    /**
     * @param mixed $data
     */
    protected function setAdditionalContextData(string $key, $data): void
    {
        $additional_data = $this->global_screen->tool()->context()->current()->getAdditionalData();
        if ($additional_data->exists($key)) {
            $additional_data->replace($key, $data);
        } else {
            $additional_data->add($key, $data);
        }
    }

    protected function resetUserLanguage(): void
    {
        $this->user->setLanguage($this->initial_user_language);
        $this->user->setCurrentLanguage($this->initial_current_user_language);
    }


    protected function getLanguageIterator(): \Iterator
    {
        return new class ($this->lang, $this->obj_transl) implements \Iterator {
            private int $position = 0;
            /** @var string[] */
            private array $langs = [];

            public function __construct(
                string $lang,
                Translations $obj_transl
            ) {
                $this->position = 0;
                if ($lang != "all") {
                    $this->langs = [$lang];
                } else {
                    foreach ($obj_transl->getLanguages() as $otl) {
                        $this->langs[] = $otl->getLanguageCode();
                    }
                }
            }

            public function rewind(): void
            {
                $this->position = 0;
            }

            public function current(): string
            {
                return $this->langs[$this->position];
            }

            public function key(): int
            {
                return $this->position;
            }

            public function next(): void
            {
                ++$this->position;
            }

            public function valid(): bool
            {
                return isset($this->langs[$this->position]);
            }
        };
    }

    protected function initLanguage(
        \ilObjUser $user,
        \ilLMPresentationGUI $lm_gui,
        string $lang
    ): void {
        $user_lang = $user->getLanguage();

        if ($lang != "") {
            $user->setLanguage($lang);
            $user->setCurrentLanguage($lang);
        } else {
            $user->setLanguage($user_lang);
            $user->setCurrentLanguage($user_lang);
        }

        if ($lang != "") {
            if ($lang == $this->obj_transl->getBaseLanguage()) {
                $lm_gui->lang = "";
            } else {
                $lm_gui->lang = $lang;
            }
        }
    }

    protected function initGlobalScreen(): void
    {
        // set global
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilLMHtmlExportViewLayoutProvider::LM_HTML_EXPORT_RENDERING,
            true
        );
    }


    /**
     * @param bool $zip perform a zip at the end
     */
    public function exportHTML(bool $zip = true): void
    {
        $this->initGlobalScreen();

        $this->export_util->exportSystemStyle(
            [
                "icon_lm.svg"
            ]
        );
        $this->export_util->exportCOPageFiles($this->content_style_domain->getEffectiveStyleId(), "lm");

        $lang_iterator = $this->getLanguageIterator();

        // iterate all languages
        foreach ($lang_iterator as $lang) {
            $this->initLanguage($this->user, $this->lm_gui, $lang);
            $this->exportHTMLPages();
        }

        $this->resetUserLanguage();

        $this->addSupplyingExportFiles();

        $this->co_page_html_export->exportPageElements();

    }


    /**
     * Add supplying export files
     */
    protected function addSupplyingExportFiles(): void
    {
        foreach ($this->getSupplyingExportFiles() as $f) {
            if ($f["source"] != "") {
                if ($f["type"] == "js") {
                    $this->global_screen->layout()->meta()->addJs($f["source"]);
                }
                if ($f["type"] == "css") {
                    $this->global_screen->layout()->meta()->addCss($f["source"]);
                }
            }
        }
    }

    /**
     * @todo modularize!
     */
    protected function getSupplyingExportFiles(string $a_target_dir = "."): array
    {
        $scripts = array(
            array("source" => './components/ILIAS/Accordion/css/accordion.css',
                "target" => $a_target_dir . '/css/accordion.css',
                "type" => "css"),
            array("source" => './components/ILIAS/TestQuestionPool/resources/js/dist/pure_rendering.js',
                "target" => $a_target_dir . '/js/pure.js',
                "type" => "js"),
            array("source" => './components/ILIAS/TestQuestionPool/resources/js/dist/question_handling.js',
                "target" => $a_target_dir . '/js/question_handling.js',
                "type" => "js"),
            array("source" => './components/ILIAS/TestQuestionPool/resources/js/dist/question_handling.css',
                "target" => $a_target_dir . '/css/question_handling.css',
                "type" => "css"),
            array("source" => './components/ILIAS/TestQuestionPool/templates/default/test_javascript.css',
                "target" => $a_target_dir . '/css/test_javascript.css',
                "type" => "css"),
            array("source" => \ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "target" => $a_target_dir . "/" . \ilExplorerBaseGUI::getLocalExplorerJsPath(),
                "type" => "js"),
            array("source" => \ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "target" => $a_target_dir . "/" . \ilExplorerBaseGUI::getLocalJsTreeJsPath(),
                "type" => "js"),
            array("source" => \ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "target" => $a_target_dir . "/" . \ilExplorerBaseGUI::getLocalJsTreeCssPath(),
                "type" => "css"),
            array("source" => './components/ILIAS/LearningModule/js/LearningModule.js',
                "target" => $a_target_dir . '/js/LearningModule.js',
                "type" => "js")
        );

        $mathJaxSetting = new \ilSetting("MathJax");
        $use_mathjax = (bool) $mathJaxSetting->get("enable");
        if ($use_mathjax) {
            $scripts[] = array("source" => "",
                "target" => $mathJaxSetting->get("path_to_mathjax"),
                "type" => "js");
        }

        // auto linking js
        foreach (\ilLinkifyUtil::getLocalJsPaths() as $p) {
            if (is_int(strpos($p, "ExtLink"))) {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir . '/js/ilExtLink.js',
                    "type" => "js");
            }
            if (is_int(strpos($p, "linkify"))) {
                $scripts[] = array("source" => $p,
                    "target" => $a_target_dir . '/js/linkify.js',
                    "type" => "js");
            }
        }

        // check, why these do not come with the gs meta collector
        $scripts[] = [
            "source" => "assets/js/mainbar.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "assets/js/metabar.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "assets/js/slate.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "assets/js/stdpage.js",
            "type" => "js"
        ];
        $scripts[] = [
            "source" => "assets/js/GS.js",
            "type" => "js"
        ];

        return $scripts;
    }



    /**
     * export all pages of learning module to html file
     */
    public function exportHTMLPages(): void
    {
        $lm = $this->lm;
        $lm_gui = $this->lm_gui;
        $lang = $lm_gui->lang;
        $all_languages = ($this->lang == "all");
        $lm_set = $this->lm_settings;
        $ilLocator = $this->locator;

        $pages = \ilLMPageObject::getPageList($lm->getId());

        $lm_tree = $lm->getLMTree();
        $first_page = $lm_tree->fetchSuccessorNode($lm_tree->getRootId(), "pg");
        $first_page_id = $first_page["child"];

        // iterate all learning module pages
        $mobs = [];
        $int_links = [];
        $this->offline_files = [];

        // get html export id mapping

        $exp_id_map = array();

        if ($lm_set->get("html_export_ids")) {
            foreach ($pages as $page) {
                $exp_id = \ilLMPageObject::getExportId($this->lm->getId(), $page["obj_id"]);
                if (trim($exp_id) != "") {
                    $exp_id_map[$page["obj_id"]] = trim($exp_id);
                }
            }
        }

        if ($lang == "") {
            $lang = "-";
        }

        reset($pages);
        foreach ($pages as $page) {
            if (\ilLMPage::_exists($this->lm->getType(), $page["obj_id"])) {
                $ilLocator->clearItems();
                $this->exportPageHTML($page["obj_id"], ($first_page_id == $page["obj_id"]), $lang, "", $exp_id_map);
                $this->co_page_html_export->collectPageElements("lm:pg", $page["obj_id"], $lang);
            }
        }
    }

    protected function getInitialisedTemplate(): \ilGlobalPageTemplate
    {
        global $DIC;

        $tabs = $DIC->tabs();

        $tabs->clearTargets();
        $tabs->clearSubTabs();
        $tpl = new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());

        $this->co_page_html_export->getPreparedMainTemplate($tpl);

        return $tpl;
    }


    /**
     * Init global screen and learning module presentation gui for page
     */
    protected function initScreen(
        int $lm_page_id,
        string $frame
    ): void {
        $this->export_util->resetGlobalScreen();
        // load style sheet depending on user's settings
        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);

        $this->addSupplyingExportFiles();

        // template workaround: reset of template
        $tpl = $this->getInitialisedTemplate();
        \ilPCQuestion::resetInitialState();

        $params = [
            "obj_id" => $lm_page_id,
            "ref_id" => $this->lm->getRefId(),
            "frame" => $frame
        ];

        $this->lm_gui->initByRequest($params);

        $this->setAdditionalContextData(\ilLMGSToolProvider::LM_QUERY_PARAMS, $params);
        $this->setAdditionalContextData(\ilLMGSToolProvider::LM_OFFLINE, true);

        $this->lm_gui->injectTemplate($tpl);
    }


    /**
     * export single page to file
     */
    public function exportPageHTML(
        int $lm_page_id,
        bool $is_first = false,
        string $lang = "-",
        string $frame = "",
        array $exp_id_map = []
    ): void {
        $target_dir = $this->target_dir;
        $lang_suffix = "";
        if (!in_array($lang, ["-", ""]) && $this->lang === "all") {
            $lang_suffix = "_" . $lang;
        }
        // Init template, lm_gui
        $this->initScreen($lm_page_id, $frame);

        if ($frame == "") {
            if (is_array($exp_id_map) && isset($a_exp_id_map[$lm_page_id])) {
                $file = "lm_pg_" . $exp_id_map[$lm_page_id] . $lang_suffix . ".html";
            } else {
                $file = "lm_pg_" . $lm_page_id . $lang_suffix . ".html";
            }
        } else {
            if ($frame != "toc") {
                $file = "frame_" . $lm_page_id . "_" . $frame . $lang_suffix . ".html";
            } else {
                $file = "frame_" . $frame . $lang_suffix . ".html";
            }
        }

        // return if file is already existing
        /*
        if (is_file($file)) {
            return;
        }*/

        $content = $this->lm_gui->layout("main.xml", false);

        $this->collector->addString($content, $file);

        if ($is_first && $frame == "") {
            $this->collector->addString($content, "index" . $lang_suffix . ".html");
        }
        $this->export_util->exportResourceFiles();  // same iteration level as initScreen which calls reset
    }
}
