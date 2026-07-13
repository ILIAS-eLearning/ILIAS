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

namespace ILIAS\components\Export\HTML;

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
    public function __construct(
        string $export_dir,
        string $sub_dir,
        protected ?ExportCollector $export_collector = null
    ) {
        global $DIC;

        $this->export_dir = $export_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $export_dir . "/" . $sub_dir;

        $this->co_page_html_export = new \ilCOPageHTMLExport($this->target_dir, null, 0, $export_collector);
        $this->global_screen = $DIC->globalScreen();
    }

    /**
     * Export system style
     */
    public function exportSystemStyle(
        array $standard_icons = []
    ): void {
        // legacy export
        if (is_null($this->export_collector)) {
            $sys_style_html_export = new \ilSystemStyleHTMLExport($this->target_dir);
            $sys_style_html_export->export();
        } else {

            $asset_dirs = ["css", "fonts", "images/logo", "images/standard"];
            foreach ($asset_dirs as $asset_dir) {

                $asset_dir = "./assets/" . $asset_dir;

                // export system style sheet
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($asset_dir, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $item) {
                    if (!$item->isDir()) {
                        if ($asset_dir === "./assets/images/standard" &&
                            !in_array($iterator->getSubPathname(), $standard_icons)) {
                            continue;
                        }
                        $this->export_collector->addFile(
                            $item->getPathname(),
                            $asset_dir . DIRECTORY_SEPARATOR . $iterator->getSubPathname()
                        );
                    }
                }

                /*
                // export (icon) images
                foreach (
                    [
                        ['file' => 'media/enlarge.svg', 'exp_file_name' => ''],
                        ['file' => 'browser/blank.png', 'exp_file_name' => '/browser/plus.png'],
                        ['file' => 'browser/blank.png', 'exp_file_name' => '/browser/minus.png'],
                        ['file' => 'browser/blank.png', 'exp_file_name' => '/browser/blank.png'],
                        ['file' => 'media/spacer.png', 'exp_file_name' => ''],
                        ['file' => 'standard/icon_st.svg', 'exp_file_name' => ''],
                        ['file' => 'standard/icon_pg.svg', 'exp_file_name' => ''],
                        ['file' => 'standard/icon_lm.svg', 'exp_file_name' => ''],
                    ] as $im) {
                    $from = $to = $im['file'];
                    if ($im['exp_file_name'] != '') {
                        $to = $im['exp_file_name'];
                    }
                    $this->export_collector->addFile(
                        \ilUtil::getImagePath($from, '', 'filesystem'),
                        $img_dir . '/' . $to
                    );
                }*/
            }
        }
    }

    /**
     * Export content style
     */
    public function exportCOPageFiles(int $style_sheet_id = 0, string $obj_type = ""): void
    {
        // init co page html exporter
        $this->co_page_html_export->setContentStyleId($style_sheet_id);
        if (is_null($this->export_collector)) {
            $this->co_page_html_export->createDirectories();
        }
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
            // skip dummy "assets/content_style/style.css"
            if (str_contains($item->getContent(), "assets/content_style")) {
                continue;
            }
            $this->exportResourceFile($target_dir, $item->getContent());
        }
        $js = $global_screen->layout()->meta()->getJs();
        foreach ($js->getItemsInOrderOfDelivery() as $item) {
            $this->exportResourceFile($target_dir, $item->getContent());
        }
    }

    public function resetGlobalScreen(): void
    {
        // we reset to get rid of collected onload code
        // however we accumulate js and css files, since the
        // ui framework renderer do not register their resources each time
        // the renderer is being called.
        $css = $this->global_screen->layout()->meta()->getCss();
        $js = $this->global_screen->layout()->meta()->getJs();
        $this->global_screen->layout()->meta()->reset();
        foreach ($css->getItemsInOrderOfDelivery() as $item) {
            $this->global_screen->layout()->meta()->addCss($item->getContent());
        }
        foreach ($js->getItemsInOrderOfDelivery() as $item) {
            $this->global_screen->layout()->meta()->addJs($item->getContent());
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
            if (is_null($this->export_collector)) {
                ilFileUtils::makeDirParents($target_dir . "/" . $dir);
                if (!is_file($target_dir . "/" . $file)) {
                    copy($file, $target_dir . "/" . $file);
                }
            } else {
                $this->export_collector->addFile($file, $file);
            }
        }
    }
}
