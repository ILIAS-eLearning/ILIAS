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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDashObjectsTableGUI extends ilTable2GUI
{
    public function __construct(
        object $parent_obj,
        string $parent_cmd,
        int $sub_id
    ) {
        global $DIC;

        $this->id = 'dash_obj_' . $sub_id;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        parent::__construct($parent_obj, $parent_cmd);

        $this->setTitle($this->lng->txt(''));

        $this->addColumn('', '', '', true);

        $this->setEnableNumInfo(false);
        $this->setEnableHeader(false);

        $this->setRowTemplate('tpl.dash_obj_row.html', 'Services/Dashboard');

        $this->setLimit(9999);
    }

    protected function fillRow(array $set): void
    {
        $this->tpl->setVariable('ID', $set['ref_id']);
        $this->tpl->setVariable('ICON', ilObject::_getIcon((int) $set['obj_id']));
        $this->tpl->setVariable('TITLE', $set['title']);
    }
}
