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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Signal;

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilMDCopyrightTableGUI extends ilTable2GUI
{
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilMDSettingsModalService $modal_service;

    protected bool $has_write;

    /**
     * Rendering the modals into the table leads to a mess
     * in the css, since the table is also a form.
     * @var Signal[]
     */
    protected array $edit_modal_signals = [];

    public function __construct(
        ilMDCopyrightSelectionGUI $parent_obj,
        string $parent_cmd = '',
        bool $has_write = false
    ) {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->modal_service = new ilMDSettingsModalService(
            $this->ui_factory
        );

        $this->has_write = $has_write;

        parent::__construct($parent_obj, $parent_cmd);

        if ($this->has_write) {
            $this->addColumn('', 'f', '1');
            $this->addColumn($this->lng->txt("position"), "order");
            $this->addCommandButton("saveCopyrightPosition", $this->lng->txt("meta_save_order"));
        }
        $this->addColumn($this->lng->txt('title'), 'title', "30%");
        $this->addColumn($this->lng->txt('md_used'), 'used', "5%");
        $this->addColumn($this->lng->txt('md_copyright_preview'), 'preview', "50%");
        $this->addColumn($this->lng->txt('meta_copyright_status'), 'status', "5%");

        if ($this->has_write) {
            $this->addColumn('', 'edit', "10%");
        }

        $this->setFormAction($this->ctrl->getFormAction($parent_obj));
        $this->setRowTemplate("tpl.show_copyright_row.html", "components/ILIAS/MetaData");
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
        if ($a_set['status']) {
            $this->tpl->setVariable('VAL_STATUS', $this->lng->txt('meta_copyright_outdated'));
        } else {
            $this->tpl->setVariable('VAL_STATUS', $this->lng->txt('meta_copyright_in_use'));
        }

        if ($this->has_write) {
            $this->tpl->setVariable('ACTIONS', $this->getActionsForEntry(
                (int) $a_set['id'],
                (int) $a_set['used'] > 0
            ));
        }
    }

    public function setEditModalSignal(int $entry_id, Signal $signal): void
    {
        $this->edit_modal_signals[$entry_id] = $signal;
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

    protected function getActionsForEntry(int $id, bool $with_usages): string
    {
        $buttons = [];

        $buttons[] = $this->ui_factory->button()->shy(
            $this->lng->txt('edit'),
            $this->edit_modal_signals[$id]
        );

        if ($with_usages) {
            $this->ctrl->setParameterByClass(
                ilMDCopyrightUsageGUI::class,
                'entry_id',
                $id
            );
            $usage_link = $this->ctrl->getLinkTargetByClass(
                'ilMDCopyrightUsageGUI',
                ''
            );
            $this->ctrl->clearParametersByClass(ilMDCopyrightUsageGUI::class);
            $buttons[] = $this->ui_factory->button()->shy(
                $this->lng->txt('meta_copyright_show_usages'),
                $usage_link
            );
        }

        $actions = $this->ui_factory
            ->dropdown()
            ->standard($buttons)
            ->withLabel($this->lng->txt('actions'));
        return $this->ui_renderer->render($actions);
    }
}
