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
 * Class ilCmiXapiLrsTypesTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */

declare(strict_types=1);

class ilCmiXapiLrsTypesTableGUI extends ilTable2GUI
{
    public const TABLE_ID = 'cmix_lrs_types_table';

    private \ILIAS\DI\Container $dic;

    public function __construct(ilObjCmiXapiAdministrationGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $this->dic = $DIC;

        $this->setId(self::TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.cmix_lrs_types_table_row.html', 'components/ILIAS/CmiXapi');

        $this->setTitle($DIC->language()->txt('tbl_lrs_types_header'));
        //$this->setDescription($DIC->language()->txt('tbl_lrs_types_header_info'));

        $this->initColumns();
    }

    protected function initColumns(): void
    {
        $this->addColumn($this->dic->language()->txt('tbl_lrs_type_title'), 'title');
        $this->addColumn($this->dic->language()->txt('tbl_lrs_type_availability'), 'availability');
        $this->addColumn($this->dic->language()->txt('tbl_lrs_type_usages'), 'usages');
        $this->addColumn('', '', '8%');
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('LRS_TYPE_TITLE', $a_set['title']);
        $this->tpl->setVariable('LRS_TYPE_AVAILABILITY', $this->getAvailabilityLabel((string) $a_set['availability']));
        $this->tpl->setVariable('LRS_TYPE_USAGES', $a_set['usages'] ? $a_set['usages'] : '');
        $this->tpl->setVariable('ACTIONS', $this->getActionsList($a_set));
    }

    protected function getAvailabilityLabel(string $availability): string
    {
        return $this->dic->language()->txt('conf_availability_' . $availability);
    }

    /**
     * @throws ilCtrlException
     */
    protected function getActionsList(array $data): string
    {
        //todo: implement delete is usages=0
        $this->ctrl->setParameter($this->parent_obj, 'lrs_type_id', $data['lrs_type_id']);

        $link = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilObjCmiXapiAdministrationGUI::CMD_SHOW_LRS_TYPE_FORM
        );

        $this->ctrl->setParameter($this->parent_obj, 'lrs_type_id', '');

        $actions[] = $this->dic->ui()->factory()->link()->standard($this->lng->txt('edit'), $link);
        $dropdown = $this->dic->ui()->factory()->dropdown()->standard($actions)->withLabel($this->lng->txt('actions'));

        return $this->dic->ui()->renderer()->render($dropdown);
    }
}
