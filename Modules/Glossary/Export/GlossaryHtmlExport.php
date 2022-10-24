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

namespace ILIAS\Glossary\Export;

use ilFileUtils;

/**
 * Glossary HTML Export
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class GlossaryHtmlExport
{
    protected \ilGlossaryPresentationGUI $glo_gui;
    protected \ilObjGlossary $glossary;
    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Services\Export\HTML\Util $export_util;
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style;

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

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($exp_dir, $sub_dir);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);

        // get glossary presentation gui class
        $this->glo_gui = new \ilGlossaryPresentationGUI("html", $this->target_dir);

        $this->global_screen->tool()->context()->current()->addAdditionalData(\ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING, true);
        $this->content_style = $DIC
            ->contentStyle()
            ->domain()
            ->styleForRefId($glo->getRefId());
    }

    protected function initDirectories(): void
    {
        // initialize temporary target directory
        ilFileUtils::delDir($this->target_dir);
        ilFileUtils::makeDir($this->target_dir);
    }

    public function exportHTML(): string
    {
        $this->initDirectories();
        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->content_style->getEffectiveStyleId(), "glo");

        // export terms
        $this->exportHTMLGlossaryTerms();

        $this->export_util->exportResourceFiles();

        $this->co_page_html_export->exportPageElements();

        return $this->zipPackage();
    }

    protected function zipPackage(): string
    {
        // zip it all
        $date = time();
        $zip_file = $this->glossary->getExportDirectory("html") . "/" . $date . "__" . IL_INST_ID . "__" .
            $this->glossary->getType() . "_" . $this->glossary->getId() . ".zip";
        ilFileUtils::zip($this->target_dir, $zip_file);
        ilFileUtils::delDir($this->target_dir);
        return $zip_file;
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
        $this->global_screen->layout()->meta()->reset();

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
        $content = $this->glo_gui->listTerms();
        $file = $this->target_dir . "/index.html";

        // open file
        $fp = fopen($file, "w+");
        fwrite($fp, $content);
        fclose($fp);

        $terms = $this->glossary->getTermList();
        foreach ($terms as $term) {
            $this->initScreen($term["id"]);
            $content = $this->glo_gui->listDefinitions($this->glossary->getRefId(), $term["id"], false);
            $file = $this->target_dir . "/term_" . $term["id"] . ".html";

            // open file
            $fp = fopen($file, "w+");
            fwrite($fp, $content);
            fclose($fp);

            // store linked/embedded media objects of glosssary term
            $defs = \ilGlossaryDefinition::getDefinitionList($term["id"]);
            foreach ($defs as $def) {
                $this->co_page_html_export->collectPageElements("gdf:pg", $def["id"], "");
            }
        }
    }
}
