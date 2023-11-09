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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Class ilQuestionPoolExportTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup components\ILIASTest
 */
class ilQuestionPoolExportTableGUI extends ilExportTableGUI
{
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    public function __construct($parent_obj, $parent_cmd, $exp_obj)
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        parent::__construct($parent_obj, $parent_cmd, $exp_obj);
    }

    /**
     * @param string $type
     * @param string $filename
     */
    protected function formatActionsList($type, $filename): string
    {
        $this->ctrl->setParameter($this->getParentObject(), 'file', $type . ':' . $filename);
        $action = $this->ui_factory->link()->standard(
            $this->lng->txt('download'),
            $this->ctrl->getLinkTarget($this->getParentObject(), 'download')
        );
        $this->ctrl->setParameter($this->getParentObject(), 'file', '');
        $dropdown = $this->ui_factory->dropdown()->standard($action)->withLabel($this->lng->txt('actions'));
        return $this->ui_renderer->render($dropdown);

    }

    /**
     * @inheritdoc
     */
    public function numericOrdering(string $a_field): bool
    {
        if (in_array($a_field, array('size', 'date'))) {
            return true;
        }

        return false;
    }

    protected function initMultiCommands(): void
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }

    public function getExportFiles(): array
    {
        $obj_type = $this->obj->getType();
        $types = [];
        foreach ($this->parent_obj->getFormats() as $f) {
            $types[] = $f['key'];
            $this->formats[$f['key']] = $f['txt'];
        }
        $file = [];

        foreach ($types as $type) {
            $dir = ilExport::_getExportDirectory(
                $this->obj->getId(),
                $type,
                $obj_type
            );

            // quit if import dir not available
            if (!is_dir($dir) || !is_writable($dir)) {
                continue;
            }

            // open directory
            $h_dir = dir($dir);

            // get files and save the in the array
            while ($entry = $h_dir->read()) {
                if ($entry !== "."
                    && $entry !== ".."
                    && (substr($entry, -4) === ".zip" || substr($entry, -5) === ".xlsx")
                    && (preg_match("/^[0-9]{10}_{2}[0-9]+_{2}(" . $obj_type . "_)*[0-9]+\.zip\$/", $entry)
                        || preg_match("/^[0-9]{10}_{2}[0-9]+_{2}(" . $obj_type . "_)*[0-9]+\.xlsx\$/", $entry))) {
                    $ts = substr($entry, 0, strpos($entry, "__"));
                    $file[$entry . $type] = [
                        "type" => (string) $type,
                        "file" => (string) $entry,
                        "size" => (int) filesize($dir . "/" . $entry),
                        "timestamp" => (int) $ts
                    ];
                }
            }

            // close import directory
            $h_dir->close();
        }

        // sort files
        ksort($file);
        reset($file);
        return $file;
    }
}
