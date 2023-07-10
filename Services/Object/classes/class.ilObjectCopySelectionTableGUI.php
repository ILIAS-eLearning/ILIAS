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
 * Selection of sub items
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopySelectionTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    protected ilObjectDefinition $obj_definition;
    protected ilTree $tree;
    protected ilAccessHandler $access;

    protected string $type;

    public function __construct(?object $parent_class, string $parent_cmd, string $type, string $back_cmd)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();

        parent::__construct($parent_class, $parent_cmd);
        $this->type = $type;

        $this->setTitle($this->lng->txt($this->type . '_wizard_page'));
        $this->addColumn($this->lng->txt('title'), '', '55%');
        $this->addColumn($this->lng->txt('copy'), '', '15%');
        $this->addColumn($this->lng->txt('link'), '', '15%');
        $this->addColumn($this->lng->txt('omit'), '', '15%');
        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.obj_copy_selection_row.html", "Services/Object");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setLimit(999999);

        $this->setFormName('cmd');

        $this->addCommandButton('copyContainerToTargets', $this->lng->txt('obj_' . $this->type . '_duplicate'));
        if ($back_cmd == "") {
            $this->addCommandButton("cancel", $this->lng->txt('cancel'));
        } else {
            $this->addCommandButton($back_cmd, $this->lng->txt('btn_back'));
        }
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function parseSource(int $source): void
    {
        $first = true;
        foreach ($this->tree->getSubTree($root = $this->tree->getNodeData($source)) as $node) {
            if ($node['type'] == 'rolf') {
                continue;
            }
            if (!$this->access->checkAccess('visible', '', (int) $node['child'])) {
                continue;
            }

            $r = [];
            $r['last'] = false;
            $r['source'] = $first;
            $r['ref_id'] = $node['child'];
            $r['depth'] = $node['depth'] - $root['depth'];
            $r['type'] = $node['type'];
            $r['title'] = $node['title'];
            $r['copy'] = $this->obj_definition->allowCopy($node['type']);
            $r['perm_copy'] = $this->access->checkAccess('copy', '', (int) $node['child']);
            $r['link'] = $this->obj_definition->allowLink($node['type']);
            $r['perm_link'] = true;

            if (!trim($r['title']) && $r['type'] == 'sess') {
                // use session date as title if no object title
                $app_info = ilSessionAppointment::_lookupAppointment((int) $node["obj_id"]);
                $r['title'] = ilSessionAppointment::_appointmentToString((int) $app_info['start'], (int) $app_info['end'], (bool) $app_info['fullday']);
            }

            $rows[] = $r;
            $first = false;
        }

        $rows[] = ['last' => true];
        $this->setData($rows);
    }

    protected function fillRow(array $set): void
    {
        if ($set['last']) {
            $this->tpl->setCurrentBlock('footer_copy');
            $this->tpl->setVariable('TXT_COPY_ALL', $this->lng->txt('copy_all'));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_link');
            $this->tpl->setVariable('TXT_LINK_ALL', $this->lng->txt('link_all'));
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_omit');
            $this->tpl->setVariable('TXT_OMIT_ALL', $this->lng->txt('omit_all'));
            $this->tpl->parseCurrentBlock();
            return;
        }

        for ($i = 0; $i < $set['depth']; $i++) {
            $this->tpl->touchBlock('padding');
            $this->tpl->touchBlock('end_padding');
        }
        $this->tpl->setVariable('TREE_IMG', ilObject::_getIcon(ilObject::_lookupObjId((int) $set['ref_id']), "tiny", $set['type']));
        $this->tpl->setVariable('TREE_ALT_IMG', $this->lng->txt('obj_' . $set['type']));
        $this->tpl->setVariable('TREE_TITLE', $set['title']);

        if ($set['source']) {
            return;
        }

        // Copy
        if ($set['perm_copy'] and $set['copy']) {
            $this->tpl->setCurrentBlock('radio_copy');
            $this->tpl->setVariable('TXT_COPY', $this->lng->txt('copy'));
            $this->tpl->setVariable('NAME_COPY', 'cp_options[' . $set['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_COPY', ilCopyWizardOptions::COPY_WIZARD_COPY);
            $this->tpl->setVariable('ID_COPY', $set['depth'] . '_' . $set['type'] . '_' . $set['ref_id'] . '_copy');
            $this->tpl->setVariable('COPY_CHECKED', 'checked="checked"');
            $this->tpl->parseCurrentBlock();
        } elseif ($set['copy']) {
            $this->tpl->setCurrentBlock('missing_copy_perm');
            $this->tpl->setVariable('TXT_MISSING_COPY_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Link
        if ($set['perm_link'] and $set['link']) {
            $this->tpl->setCurrentBlock('radio_link');
            $this->tpl->setVariable('TXT_LINK', $this->lng->txt('link'));
            $this->tpl->setVariable('NAME_LINK', 'cp_options[' . $set['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_LINK', ilCopyWizardOptions::COPY_WIZARD_LINK);
            $this->tpl->setVariable('ID_LINK', $set['depth'] . '_' . $set['type'] . '_' . $set['ref_id'] . '_link');
            if (!$set['copy'] or !$set['perm_copy']) {
                $this->tpl->setVariable('LINK_CHECKED', 'checked="checked"');
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($set['link']) {
            $this->tpl->setCurrentBlock('missing_link_perm');
            $this->tpl->setVariable('TXT_MISSING_LINK_PERM', $this->lng->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Omit
        $this->tpl->setCurrentBlock('omit_radio');
        $this->tpl->setVariable('TXT_OMIT', $this->lng->txt('omit'));
        $this->tpl->setVariable('NAME_OMIT', 'cp_options[' . $set['ref_id'] . '][type]');
        $this->tpl->setVariable('VALUE_OMIT', ilCopyWizardOptions::COPY_WIZARD_OMIT);
        $this->tpl->setVariable('ID_OMIT', $set['depth'] . '_' . $set['type'] . '_' . $set['ref_id'] . '_omit');
        if ((!$set['copy'] or !$set['perm_copy']) and (!$set['link'])) {
            $this->tpl->setVariable('OMIT_CHECKED', 'checked="checked"');
        }

        $this->tpl->parseCurrentBlock();
    }
}
