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
 *
 *********************************************************************/

namespace ILIAS\Services\Export\HTML;

use ILIAS\GlobalScreen\Services;
use ilFileUtils;

/**
 * Util
 * This class is an interim solution for the HTML export handling with
 * 6.0. Parts of it might move to the GlobalScreen service or other places.
 * @author killing@leifos.de
 */
class Util
{
    protected \ilCOPageHTMLExport $co_page_html_export;
    protected Services $global_screen;

    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;

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
    public function exportSystemStyle(): void
    {
        // system style html exporter
        $sys_style_html_export = new \ilSystemStyleHTMLExport($this->target_dir);
        $sys_style_html_export->export();
    }

    /**
     * Export content style
     */
    public function exportCOPageFiles(int $style_sheet_id = 0, string $obj_type = ""): void
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
    protected function initGlobalScreen(): void
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
    public function exportResourceFiles(): void
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
     */
    protected function exportResourceFile(string $target_dir, string $file): void
    {
        if (is_int(strpos($file, "?"))) {
            $file = substr($file, 0, strpos($file, "?"));
        }
        if (is_file($file)) {
            $dir = dirname($file);
            ilFileUtils::makeDirParents($target_dir . "/" . $dir);
            if (!is_file($target_dir . "/" . $file)) {
                copy($file, $target_dir . "/" . $file);
            }
        }
    }
}
