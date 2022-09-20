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

/**
 * Object selection for export
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilExportSelectionTableGUI extends ilTable2GUI
{
    protected ILIAS\HTTP\Wrapper\SuperGlobalDropInReplacement $post_data;

    protected ilAccessHandler $access;
    protected ilObjectDefinition $objDefinition;
    protected ilTree $tree;

    public function __construct(object $a_parent_class, string $a_parent_cmd)
    {
        global $DIC;

        //TODO PHP8-Review: please check the usage of $_POST
        /** @var ILIAS\HTTP\Wrapper\SuperGlobalDropInReplacement $_POST */
        $this->post_data = $_POST;

        $this->tree = $DIC->repositoryTree();
        $this->objDefinition = $DIC['objDefinition'];
        $this->access = $DIC->access();

        parent::__construct($a_parent_class, $a_parent_cmd);

        $this->lng->loadLanguageModule('export');
        $this->setTitle($this->lng->txt('export_select_resources'));
        $this->addColumn($this->lng->txt('title'), '');
        $this->addColumn($this->lng->txt('export_last_export'), '');
        $this->addColumn($this->lng->txt('export_last_export_file'), '');
        $this->addColumn($this->lng->txt('export_create_new_file'), '');
        $this->addColumn($this->lng->txt('export_omit_resource'), '');

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.export_item_selection_row.html", "Services/Export");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setLimit(10000);

        $this->setFormName('cmd');

        $this->addCommandButton('saveItemSelection', $this->lng->txt('export_save_selection'));
        $this->addCommandButton($a_parent_cmd, $this->lng->txt('cancel'));
    }

    protected function fillRow(array $a_set): void
    {
        $a_set['copy'] = $a_set['copy'] ?? false;
        $a_set['perm_copy'] = $a_set['perm_copy'] ?? false;
        $a_set['link'] = $a_set['link'] ?? false;
        $a_set['perm_export'] = $a_set['perm_export'] ?? false;

        // set selected radio button
        $selected = '';
        if ((!$a_set['copy'] or !$a_set['perm_copy']) and (!$a_set['link'])) {
            $selected = "OMIT";
        }
        if ($a_set['perm_export'] and $a_set['last_export']) {
            $selected = "EXPORT_E";
        }
        if (is_array($this->post_data["cp_options"])) {
            if (isset($this->post_data["cp_options"][$a_set['ref_id']]["type"])) {
                switch ($this->post_data["cp_options"][$a_set['ref_id']]["type"]) {
                    case "2":
                        $selected = "EXPORT";
                        break;
                    case "1":
                        $selected = "EXPORT_E";
                        break;
                }
            }
        }

        if ($a_set['last']) {
            $this->tpl->setCurrentBlock('footer_export_e');
            $this->tpl->setVariable('TXT_EXPORT_E_ALL', $this->lng->txt('select_all'));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_export');
            $this->tpl->setVariable('TXT_EXPORT_ALL', $this->lng->txt('select_all'));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_omit');
            $this->tpl->setVariable('TXT_OMIT_ALL', $this->lng->txt('select_all'));
            $this->tpl->parseCurrentBlock();
            return;
        }

        for ($i = 0; $i < $a_set['depth']; $i++) {
            $this->tpl->touchBlock('padding');
            $this->tpl->touchBlock('end_padding');
        }
        $this->tpl->setVariable(
            'TREE_IMG',
            ilObject::_getIcon(ilObject::_lookupObjId((int) $a_set['ref_id']), "tiny", $a_set['type'])
        );
        $this->tpl->setVariable('TREE_ALT_IMG', $this->lng->txt('obj_' . $a_set['type']));
        $this->tpl->setVariable('TREE_TITLE', $a_set['title']);

        if ($a_set['last_export']) {
            $this->tpl->setVariable(
                'VAL_LAST_EXPORT',
                ilDatePresentation::formatDate(new ilDateTime($a_set['last_export'], IL_CAL_UNIX))
            );
        } else {
            $this->tpl->setVariable('VAL_LAST_EXPORT', $this->lng->txt('no_file'));
        }

        if ($a_set['source']) {
            return;
        }

        // Export existing
        if ($a_set['perm_export'] and $a_set['last_export']) {
            $this->tpl->setCurrentBlock('radio_export_e');
            $this->tpl->setVariable('TXT_EXPORT_E', $this->lng->txt('export_existing'));
            $this->tpl->setVariable('NAME_EXPORT_E', 'cp_options[' . $a_set['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_EXPORT_E', ilExportOptions::EXPORT_EXISTING);
            $this->tpl->setVariable(
                'ID_EXPORT_E',
                $a_set['depth'] . '_' . $a_set['type'] . '_' . $a_set['ref_id'] . '_export_e'
            );
            $this->tpl->setVariable('EXPORT_E_CHECKED', 'checked="checked"');
            $this->tpl->parseCurrentBlock();
        } elseif (!$a_set['perm_export']) {
            $this->tpl->setCurrentBlock('missing_export_perm');
            $this->tpl->setVariable('TXT_MISSING_EXPORT_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Create new
        if ($a_set['perm_export'] and $a_set['export']) {
            $this->tpl->setCurrentBlock('radio_export');
            $this->tpl->setVariable('TXT_EXPORT', $this->lng->txt('export'));
            $this->tpl->setVariable('NAME_EXPORT', 'cp_options[' . $a_set['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_EXPORT', ilExportOptions::EXPORT_BUILD);
            $this->tpl->setVariable(
                'ID_EXPORT',
                $a_set['depth'] . '_' . $a_set['type'] . '_' . $a_set['ref_id'] . '_export'
            );
            if ($selected == "EXPORT") {
                $this->tpl->setVariable('EXPORT_CHECKED', 'checked="checked"');
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($a_set['export']) {
            $this->tpl->setCurrentBlock('missing_export_perm');
            $this->tpl->setVariable('TXT_MISSING_EXPORT_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Omit
        $this->tpl->setCurrentBlock('omit_radio');
        $this->tpl->setVariable('TXT_OMIT', $this->lng->txt('omit'));
        $this->tpl->setVariable('NAME_OMIT', 'cp_options[' . $a_set['ref_id'] . '][type]');
        $this->tpl->setVariable('VALUE_OMIT', ilExportOptions::EXPORT_OMIT);
        $this->tpl->setVariable('ID_OMIT', $a_set['depth'] . '_' . $a_set['type'] . '_' . $a_set['ref_id'] . '_omit');
        if ($selected == "OMIT") {
            $this->tpl->setVariable($selected . '_CHECKED', 'checked="checked"');
        }
        $this->tpl->parseCurrentBlock();
    }

    public function parseContainer(int $a_source): void
    {
        $first = true;
        $rows = [];
        foreach ($this->tree->getSubTree($root = $this->tree->getNodeData($a_source)) as $node) {
            if ($node['type'] == 'rolf') {
                continue;
            }
            if (!$this->objDefinition->allowExport($node['type'])) {
                #continue;
            }
            if ($node['type'] == "file" &&
                ilObjFileAccess::_isFileHidden($node['title'])) {
                continue;
            }
            $r = array();

            if ($last = ilExportFileInfo::lookupLastExport((int) $node['obj_id'], 'xml')) {
                $r['last_export'] = $last->getCreationDate()->get(IL_CAL_UNIX);
            } else {
                $r['last_export'] = 0;
            }

            $r['last'] = false;
            $r['source'] = $first;
            $r['ref_id'] = $node['child'];
            $r['depth'] = $node['depth'] - $root['depth'];
            $r['type'] = $node['type'];
            $r['title'] = $node['title'];
            $r['export'] = $this->objDefinition->allowExport($node['type']);
            $r['perm_export'] = $this->access->checkAccess('write', '', (int) $node['child']);

            $rows[] = $r;

            $first = false;
        }

        $rows[] = array('last' => true);
        $this->setData($rows);
    }
}
