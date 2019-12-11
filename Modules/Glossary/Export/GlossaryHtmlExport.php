<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Glossary\Export;

/**
 * Glossary HTML Export
 *
 * @author killing@leifos.de
 */
class GlossaryHtmlExport
{
    /**
     * @var \ilObjGlossary
     */
    protected $glossary;

    /**
     * @var string
     */
    protected $export_dir;

    /**
     * @var string
     */
    protected $sub_dir;

    /**
     * @var string
     */
    protected $target_dir;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    /**
     * @var \ILIAS\Services\Export\HTML\Util
     */
    protected $export_util;

    /**
     * @var \ilCOPageHTMLExport
     */
    protected $co_page_html_export;

    /**
     * GlossaryHtmlExport constructor.
     * @param \ilObjGlossary $glo
     * @param string $exp_dir
     * @param string $sub_dir
     */
    public function __construct(\ilObjGlossary $glo, string $exp_dir, string $sub_dir)
    {
        global $DIC;

        $this->glossary = $glo;
        $this->export_dir = $exp_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $exp_dir."/".$sub_dir;

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($exp_dir, $sub_dir);
        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);

        // get glossary presentation gui class
        $this->glo_gui = new \ilGlossaryPresentationGUI("html", $this->target_dir);

        $this->global_screen->tool()->context()->current()->addAdditionalData(\ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING, true);
    }

    /**
     * Initialize directories
     */
    protected function initDirectories()
    {
        // initialize temporary target directory
        \ilUtil::delDir($this->target_dir);
        \ilUtil::makeDir($this->target_dir);
    }


    /**
     * export html package
     */
    function exportHTML()
    {
        $this->initDirectories();
        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->glossary->getStyleSheetId());

        // export terms
        $this->exportHTMLGlossaryTerms();

        $this->export_util->exportResourceFiles();

        $this->co_page_html_export->exportPageElements();

        $this->zipPackage();

    }

    /**
     * Zip package
     */
    protected function zipPackage()
    {
        // zip it all
        $date = time();
        $zip_file = $this->glossary->getExportDirectory("html")."/".$date."__".IL_INST_ID."__".
            $this->glossary->getType()."_".$this->glossary->getId().".zip";
        \ilUtil::zip($this->target_dir, $zip_file);
        \ilUtil::delDir($this->target_dir);
    }

    /**
     * Get initialised template
     * @return \ilGlobalPageTemplate
     */
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
     * @param int $term_id
     * @throws \ilGlossaryException
     */
    protected function initScreen(int $term_id)
    {
        $this->global_screen->layout()->meta()->reset();

        // load style sheet depending on user's settings
        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);
        $this->global_screen->layout()->meta()->addCss(
            \ilObjStyleSheet::getContentStylePath($this->glossary->getStyleSheetId()));


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
    function exportHTMLGlossaryTerms()
    {
        $tpl = $this->initScreen(0);
        $tpl->setTitle($this->glossary->getTitle());
        $content = $this->glo_gui->listTerms();
        $file = $this->target_dir."/index.html";

        // open file
        $fp = @fopen($file,"w+");
        fwrite($fp, $content);
        fclose($fp);

        $terms = $this->glossary->getTermList();
        foreach($terms as $term)
        {
            $this->initScreen($term["id"]);
            $content = $this->glo_gui->listDefinitions($this->glossary->getRefId(), $term["id"],false);
            $file = $this->target_dir."/term_".$term["id"].".html";

            // open file
            $fp = @fopen($file,"w+");
            fwrite($fp, $content);
            fclose($fp);

            // store linked/embedded media objects of glosssary term
            $defs = \ilGlossaryDefinition::getDefinitionList($term["id"]);
            foreach($defs as $def)
            {
                $this->co_page_html_export->collectPageElements("gdf:pg", $def["id"], "");
            }
        }
    }
}