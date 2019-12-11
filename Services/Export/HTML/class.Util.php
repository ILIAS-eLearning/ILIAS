<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Services\Export\HTML;

/**
 * Util
 *
 * This class is an interim solution for the HTML export handling with
 * 6.0. Parts of it might move to the GlobalScreen service or other places.
 *
 * @author killing@leifos.de
 */
class Util
{
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
     * Constructor
     */
    public function __construct(string $export_dir, string $sub_dir)
    {
        global $DIC;

        $this->export_dir = $export_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $export_dir . "/" . $sub_dir;

        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir);
        $this->global_screen = $DIC->globalScreen();
    }

    /**
     * Export system style
     */
    public function exportSystemStyle()
    {
        // system style html exporter
        $sys_style_html_export = new \ilSystemStyleHTMLExport($this->target_dir);
        $sys_style_html_export->export();
    }

    /**
     * Export content style
     */
    public function exportCOPageFiles($style_sheet_id = 0)
    {
        \ilMathJax::getInstance()->init(\ilMathJax::PURPOSE_EXPORT);

        // init co page html exporter
        $this->co_page_html_export->setContentStyleId($style_sheet_id);
        $this->co_page_html_export->createDirectories();
        $this->co_page_html_export->exportStyles();
        $this->co_page_html_export->exportSupportScripts();
    }

    /**
     * Init global screen
     */
    protected function initGlobalScreen()
    {
        // set global
        $this->global_screen->tool()->context()->current()->addAdditionalData(
            \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
            true
        );
    }


    /**
     * Export resource files collected by global screen service
     */
    public function exportResourceFiles()
    {
        $global_screen = $this->global_screen;
        $target_dir = $this->target_dir;
        $css = $global_screen->layout()->meta()->getCss();
        foreach ($css->getItemsInOrderOfDelivery() as $item) {
            $this->exportResourceFile($target_dir, $item->getContent());
        }
        $js = $global_screen->layout()->meta()->getJs();
        foreach ($js->getItemsInOrderOfDelivery() as $item) {
            $this->exportResourceFile($target_dir, $item->getContent());
        }
    }

    /**
     * Export resource file
     *
     * @param string $target_dir
     * @param string $file
     */
    protected function exportResourceFile(string $target_dir, string $file)
    {
        if (is_int(strpos($file, "?"))) {
            $file = substr($file, 0, strpos($file, "?"));
        }
        if (is_file($file)) {
            $dir = dirname($file);
            \ilUtil::makeDirParents($target_dir . "/" . $dir);
            if (!is_file($target_dir . "/" . $file)) {
                copy($file, $target_dir . "/" . $file);
            }
        }
    }
}
