<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Notes\Export;

use ilFileUtils;
use ILIAS\Notes\Note;

/**
 * This exports the whole
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class NotesHtmlExport
{
    protected static $export_key_set = false;
    protected \ilLanguage $lng;
    protected int $user_id;
    protected int $type;
    /**
     * @var int[]
     */
    protected array $author_ids;
    protected string $export_dir;
    protected string $sub_dir;
    protected string $target_dir;
    protected \ILIAS\GlobalScreen\Services $global_screen;
    protected \ILIAS\Services\Export\HTML\Util $export_util;

    public function __construct(
        int $type,
        int $user_id,
        array $author_ids
    ) {
        global $DIC;

        $this->type = $type;
        $this->author_ids = $author_ids;
        $this->user_id = $user_id;
        $this->lng = $DIC->language();

        \ilExport::_createExportDirectory($user_id, "html_notes", "usr");
        $exp_dir = \ilExport::_getExportDirectory($user_id, "html_notes", "usr");
        $sub_dir = "user_notes";

        $this->export_dir = $exp_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $exp_dir . "/" . $sub_dir;

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util($exp_dir, $sub_dir);
        if (!self::$export_key_set) {
            self::$export_key_set = true;
            $this->global_screen->tool()->context()->current()->addAdditionalData(
                \ilHTMLExportViewLayoutProvider::HTML_EXPORT_RENDERING,
                true
            );
        }
    }

    protected function initDirectories() : void
    {
        // initialize temporary target directory
        ilFileUtils::delDir($this->target_dir);
        ilFileUtils::makeDir($this->target_dir);
    }

    /**
     * Export HTML
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportHTML($page_content) : void
    {
        $this->initDirectories();
        $this->export_util->exportSystemStyle();

        $this->exportPage($page_content);

        // export comments user images
        $this->exportUserImages();
        $zip = $this->zipPackage();
        \ilFileDelivery::deliverFileLegacy($zip, "user_notes.zip");
    }

    protected function exportUserImages() : void
    {
        $user_export = new \ILIAS\Notes\Export\UserImageExporter();
        $user_export->exportUserImages($this->target_dir, $this->author_ids);
    }

    public function zipPackage() : string
    {
        $zip_file = \ilExport::_getExportDirectory($this->user_id, "html_notes", "usr") .
            "/user_notes.zip";
        ilFileUtils::zip($this->target_dir, $zip_file);
        ilFileUtils::delDir($this->target_dir);
        return $zip_file;
    }

    /**
     * Export page
     * @throws \ILIAS\UI\NotImplementedException
     * @throws \ilTemplateException
     */
    public function exportPage(
        string $content
    ) : void {
        $tpl = $this->getInitialisedTemplate();

        $tpl->setTitle(($this->type === Note::PRIVATE)
            ? $this->lng->txt("notes")
            : $this->lng->txt("notes_comments"));

        $this->writeExportFile("index.html", $tpl, $content);
    }

    /**
     * Get initialised template
     */
    protected function getInitialisedTemplate() : \ilGlobalPageTemplate
    {
        global $DIC;

        $this->global_screen->layout()->meta()->reset();

        $location_stylesheet = \ilUtil::getStyleSheetLocation();
        $this->global_screen->layout()->meta()->addCss($location_stylesheet);

        $tabs = $DIC->tabs();
        $tabs->clearTargets();
        $tabs->clearSubTabs();

        $toolbar = $DIC->toolbar();
        $toolbar->setItems([]);

        return new \ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());
    }


    /**
     * Write HTML to file
     */
    protected function writeExportFile(
        string $a_file,
        \ilGlobalPageTemplate $a_tpl,
        string $a_content
    ) : string {
        $file = $this->target_dir . "/" . $a_file;
        if (is_file($file)) {
            return "";
        }
        $a_tpl->setContent($a_content);
        $content = $a_tpl->printToString();

        // open file
        file_put_contents($file, $content);
        return $file;
    }
}
