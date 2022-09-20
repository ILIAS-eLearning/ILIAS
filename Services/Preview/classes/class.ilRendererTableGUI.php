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

/**
 * Displays an overview of all loaded preview renderers.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @ingroup ServicesPreview
 */
class ilRendererTableGUI extends ilTable2GUI
{
    public function __construct(ilObjFileAccessSettingsGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        // general properties
        $this->setRowTemplate("tpl.renderer_row.html", "Services/Preview");
        $this->setLimit(9999);
        $this->setEnableHeader(true);
        $this->disable("footer");
        $this->setExternalSorting(true);
        $this->setEnableTitle(true);
        $this->setTitle($this->lng->txt("loaded_preview_renderers"));

        $this->addColumn($this->lng->txt("name"));
        $this->addColumn($this->lng->txt("type"));
        $this->addColumn($this->lng->txt("renderer_supported_repo_types"));
        $this->addColumn($this->lng->txt("renderer_supported_file_types"));
    }

    /**
     * Standard Version of Fill Row. Most likely to
     * be overwritten by derived class.
     */
    protected function fillRow(array $a_set): void
    {
        $name = $a_set['name'] ?? '-';
        $type = $this->lng->txt("renderer_type_" . (($a_set['s_plugin'] ?? false) ? "plugin" : "builtin"));

        $repo_types = [];
        foreach ($a_set['supported_repo_types'] as $repo_type) {
            $repo_types[] = $this->lng->txt($repo_type);
        }

        // supports files?
        $file_types = "";
        if (isset($a_set['object']) && $a_set['object'] instanceof ilFilePreviewRenderer) {
            $file_types = implode(", ", $a_set['supported_file_formats'] ?? []);
        }

        // fill template
        $this->tpl->setVariable("TXT_NAME", $name);
        $this->tpl->setVariable("TXT_TYPE", $type);
        $this->tpl->setVariable("TXT_REPO_TYPES", implode(", ", $repo_types));
        $this->tpl->setVariable("TXT_FILE_TYPES", $file_types);
    }
}
