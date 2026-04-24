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

namespace ILIAS\Glossary\Export;

use ilFileUtils;
use ILIAS\components\Export\HTML\Util;
use ILIAS\components\Export\HTML\ExportCollector;

class GlossaryHtmlExport
{
    protected ExportCollector $collector;
    protected \ilGlossaryPresentationGUI $glo_gui;
    protected \ilObjGlossary $glossary;
    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected Util $export_util;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style;
    protected \ILIAS\Glossary\InternalService $service;
    protected \ilPresentationFullGUI $glo_full_gui;
    protected \ilPresentationTableGUI $glo_table_gui;

    public function __construct(
        \ilObjGlossary $glo,
        string $exp_dir,
        string $sub_dir
    ) {
        global $DIC;

        $this->glossary = $glo;
        $this->export_dir = $exp_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $exp_dir . "/" . $sub_dir;

        $this->service = $DIC->glossary()
                             ->internal();
        $this->global_screen = $DIC->globalScreen();

        $this->collector = $DIC->export()->domain()->html()->collector($glo->getId());
        $this->collector->init();
        $this->export_util = new Util("", "", $this->collector);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, null, 0, $this->collector);

        // get glossary presentation gui classes
        $this->glo_gui = new \ilGlossaryPresentationGUI("html", $this->target_dir);
        $this->glo_full_gui = $this->service
                                ->gui()
                                ->presentation()
                                ->PresentationFullGUI($this->glo_gui, $this->glossary, true);
        $this->glo_table_gui = $this->service
                                ->gui()
                                ->presentation()
                                ->PresentationTableGUI($this->glo_gui, $this->glossary, true);

        $this->global_screen->tool()->context()->current()->addAdditionalData(\ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING, true);
        $this->content_style = $DIC
            ->contentStyle()
            ->domain()
            ->styleForRefId($glo->getRefId());
    }

    public function exportHTML(): void
    {
        $this->export_util->exportSystemStyle(
            [
                "icon_glo.svg"
            ]
        );
        $this->export_util->exportCOPageFiles($this->content_style->getEffectiveStyleId(), "glo");

        // export terms
        $this->exportHTMLGlossaryTerms();

        $this->co_page_html_export->exportPageElements();
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
     * Init page
     * @throws \ilGlossaryException
     */
    protected function initScreen(int $term_id): \ilGlobalPageTemplate
    {
        $this->export_util->resetGlobalScreen();

        // load style sheet depending on user's settings
        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getContentStylePath($this->content_style->getEffectiveStyleId())
        );

        //$this->addSupplyingExportFiles();

        // template workaround: reset of template
        $tpl = $this->getInitialisedTemplate();
        \ilPCQuestion::resetInitialState();

        $params = [
            "term_id" => $term_id,
            "ref_id" => $this->glossary->getRefId(),
            "frame" => "_blank"
        ];

        $this->glo_gui->initByRequest($params);

        $this->glo_gui->injectTemplate($tpl);
        return $tpl;
    }


    /**
     * @throws \ilGlossaryException
     */
    public function exportHTMLGlossaryTerms(): void
    {
        $tpl = $this->initScreen(0);
        $tpl->setTitle($this->glossary->getTitle());
        if ($this->glossary->getPresentationMode() == "full_def") {
            $content = $this->glo_full_gui->renderPanelForOffline();
        } else {
            $content = $this->glo_table_gui->renderPresentationTableForOffline();
        }
        $tpl->setContent($content);
        $this->collector->addString($tpl->printToString(), "index.html");
        $this->export_util->exportResourceFiles();  // same level as init screen which calls reset

        $terms = $this->glossary->getTermList();
        foreach ($terms as $term) {
            $this->initScreen($term["id"]);
            $content = $this->glo_gui->listDefinitions($this->glossary->getRefId(), $term["id"], false);
            $this->collector->addString($content, "term_" . $term["id"] . ".html");
            $this->export_util->exportResourceFiles();  // same level as init screen which calls reset

            // store linked/embedded media objects of glosssary term
            $this->co_page_html_export->collectPageElements("term:pg", $term["id"], "");
        }
    }
}
