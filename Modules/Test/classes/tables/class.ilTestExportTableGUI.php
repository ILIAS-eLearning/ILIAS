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
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 *
 * @ingroup ModulesTest
 */
class ilTestExportTableGUI extends ilExportTableGUI
{
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    public function __construct(object $a_parent_obj, string $a_parent_cmd, ilObject $a_exp_obj)
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);
    }

    protected function formatActionsList(string $type, string $filename): string
    {
        $this->ctrl->setParameter($this->getParentObject(), 'file', $filename);
        $actions[] = $this->ui_factory->link()->standard($this->lng->txt('download'), $this->ctrl->getLinkTarget($this->getParentObject(), 'download'));
        $this->ctrl->setParameter($this->getParentObject(), 'file', '');
        $dropdown = $this->ui_factory->dropdown()->standard($actions);
        return $this->ui_renderer->render($dropdown);
    }

    protected function initMultiCommands(): void
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }

    /**
     * Overwrite method because data is passed from outside
     */
    public function getExportFiles(): array
    {
        return array();
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->lng->txt(''), '', '1', true);
        $this->addColumn($this->lng->txt('type'), 'type');
        $this->addColumn($this->lng->txt('file'), 'file');
        $this->addColumn($this->lng->txt('size'), 'size');
        $this->addColumn($this->lng->txt('date'), 'timestamp');
    }

    public function numericOrdering(string $a_field): bool
    {
        if (in_array($a_field, array('size', 'date'))) {
            return true;
        }

        return false;
    }

    protected function getRowId(array $row): string
    {
        return $row['file'];
    }

    public function resetFormats(): void
    {
        $this->formats = [];
    }

    public function addFormat(string $format)
    {
        $this->formats[$format] = $format;
    }
}
