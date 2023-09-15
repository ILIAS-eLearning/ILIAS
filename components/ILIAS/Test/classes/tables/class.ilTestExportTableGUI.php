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
    protected function formatActionsList(string $type, string $filename): string
    {
        $list = new ilAdvancedSelectionListGUI();
        $list->setListTitle($this->lng->txt('actions'));
        $this->ctrl->setParameter($this->getParentObject(), 'file', $filename);
        $list->addItem($this->lng->txt('download'), '', $this->ctrl->getLinkTarget($this->getParentObject(), 'download'));
        $this->ctrl->setParameter($this->getParentObject(), 'file', '');
        return $list->getHTML();
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
