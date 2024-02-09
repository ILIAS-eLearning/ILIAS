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

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAdvancedMetaData
 */
class ilMDCopyrightTableGUI extends ilTable2GUI
{
    protected bool $has_write;

    public function __construct(ilObjMDSettingsGUI $a_parent_obj, string $a_parent_cmd = '', bool $a_has_write = false)
    {
        $this->has_write = $a_has_write;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        if ($this->has_write) {
            $this->addColumn('', 'f', '1');
            $this->addColumn($this->lng->txt("position"), "order");
            $this->addCommandButton("saveCopyrightPosition", $this->lng->txt("meta_save_order"));
        }
        $this->addColumn($this->lng->txt('title'), 'title', "30%");
        $this->addColumn($this->lng->txt('md_used'), 'used', "5%");
        $this->addColumn($this->lng->txt('md_copyright_preview'), 'preview', "50%");
        $this->addColumn($this->lng->txt('meta_copyright_status'), 'status', "10%");

        if ($this->has_write) {
            $this->addColumn('', 'edit', "10%");
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_copyright_row.html", "Services/MetaData");
        $this->setDefaultOrderField("order");
        $this->setDefaultOrderDirection("asc");
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->has_write) {
            if ($a_set['default']) {
                $this->tpl->setVariable('DISABLED', "disabled");
            }
            $this->tpl->setVariable('VAL_ID', $a_set['id']);

            // order
            $this->tpl->setCurrentBlock('order_by_position');
            if ($a_set['default']) {
                $this->tpl->setVariable('ORDER_DISABLED', 'disabled="disabled"');
            }
            $this->tpl->setVariable('ORDER_ID', $a_set['id']);
            $this->tpl->setVariable('ORDER_VALUE', $a_set['position']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if ($a_set['description'] !== '') {
            $this->tpl->setVariable('VAL_DESCRIPTION', $a_set['description']);
        }
        $this->tpl->setVariable('VAL_USAGE', $a_set['used']);
        $this->tpl->setVariable('VAL_PREVIEW', $a_set['preview']);

        $status = [];
        if ($a_set['status']) {
            $status[] = $this->lng->txt('meta_copyright_outdated');
        } else {
            $status[] = $this->lng->txt('meta_copyright_in_use');
        }
        if ($a_set['default']) {
            $status[] = $this->lng->txt('md_copyright_default');
        }

        $this->tpl->setVariable('VAL_STATUS', implode(', ', $status));

        if ($this->has_write) {
            $this->ctrl->setParameter($this->getParentObject(), 'entry_id', $a_set['id']);
            $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'editEntry'));
            $this->ctrl->clearParameters($this->getParentObject());

            $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));

            if ((int) $a_set['used'] > 0) {
                $this->tpl->setCurrentBlock("link_usage");

                // direct redirection to ...UsageGUI
                $this->ctrl->setParameterByClass(
                    'ilMDCopyrightUsageGUI',
                    'entry_id',
                    $a_set['id']
                );
                $this->tpl->setVariable(
                    'USAGE_LINK',
                    $this->ctrl->getLinkTargetByClass(
                        'ilMDCopyrightUsageGUI',
                        ''
                    )
                );
                $this->tpl->setVariable('TXT_USAGE', $this->lng->txt('meta_copyright_show_usages'));
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    public function parseSelections(): void
    {
        // These entries are ordered by 1. is_default, 2. position
        $entries = ilMDCopyrightSelectionEntry::_getEntries();

        $position = -10;
        $entry_arr = [];
        foreach ($entries as $entry) {
            $tmp_arr['id'] = $entry->getEntryId();
            $tmp_arr['title'] = $entry->getTitle();
            $tmp_arr['description'] = $entry->getDescription();
            $tmp_arr['used'] = $entry->getUsage();
            $tmp_arr['preview'] = $entry->getCopyright();
            $tmp_arr['default'] = $entry->getIsDefault();
            $tmp_arr['status'] = $entry->getOutdated();
            $tmp_arr['position'] = ($position += 10);

            $entry_arr[] = $tmp_arr;
        }

        $this->setData($entry_arr);
    }
}
