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
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\RendererInterface;

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilMDCopyrightTableGUI extends ilTable2GUI
{
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ilDBInterface $db;
    protected RendererInterface $renderer;
    protected RepositoryInterface $repository;

    protected bool $has_write;

    /**
     * Rendering the modals into the table leads to a mess
     * in the css, since the table is also a form.
     * @var Signal[]
     */
    protected array $edit_modal_signals = [];

    public function __construct(
        RepositoryInterface $repository,
        RendererInterface $renderer,
        ilMDCopyrightConfigurationGUI $parent_obj,
        string $parent_cmd = '',
        bool $has_write = false
    ) {
        global $DIC;

        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->db = $DIC->database();
        $this->repository = $repository;
        $this->renderer = $renderer;

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
        $this->addColumn($this->lng->txt('meta_copyright_status'), 'status', "10%");

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
        $entries = $this->repository->getAllEntries();

        $position = -10;
        $entry_arr = [];
        foreach ($entries as $entry) {
            $tmp_arr['id'] = $entry->id();
            $tmp_arr['title'] = $entry->title();
            $tmp_arr['description'] = $entry->description();
            $tmp_arr['used'] = $this->countUsagesForEntry($entry->id());
            $tmp_arr['preview'] = $this->ui_renderer->render(
                $this->renderer->toUIComponents($entry->copyrightData())
            );
            $tmp_arr['default'] = $entry->isDefault();
            $tmp_arr['status'] = $entry->isOutdated();
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

    private function countUsagesForEntry(int $entry_id): int
    {
        $query = "SELECT count(meta_rights_id) used FROM il_meta_rights " .
            "WHERE description = " . $this->db->quote(
                'il_copyright_entry__' . IL_INST_ID . '__' . $entry_id,
                'text'
            );

        $res = $this->db->query($query);
        $row = $this->db->fetchObject($res);
        return (int) $row->used;
    }
}
