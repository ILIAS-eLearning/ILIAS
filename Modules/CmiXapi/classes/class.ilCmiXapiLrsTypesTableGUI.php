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
 * Class ilCmiXapiLrsTypesTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiLrsTypesTableGUI extends ilTable2GUI
{
    public const TABLE_ID = 'cmix_lrs_types_table';

    public function __construct(ilObjCmiXapiAdministrationGUI $a_parent_obj, string $a_parent_cmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->setId(self::TABLE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormAction($DIC->ctrl()->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate('tpl.cmix_lrs_types_table_row.html', 'Modules/CmiXapi');

        $this->setTitle($DIC->language()->txt('tbl_lrs_types_header'));
        //$this->setDescription($DIC->language()->txt('tbl_lrs_types_header_info'));

        $this->initColumns();
    }

    protected function initColumns(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->addColumn($DIC->language()->txt('tbl_lrs_type_title'), 'title');
        $this->addColumn($DIC->language()->txt('tbl_lrs_type_availability'), 'availability');
        $this->addColumn($DIC->language()->txt('tbl_lrs_type_usages'), 'usages');
        $this->addColumn('', '', '1%');
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('LRS_TYPE_TITLE', $a_set['title']);
        $this->tpl->setVariable('LRS_TYPE_AVAILABILITY', $this->getAvailabilityLabel($a_set['availability']));
        $this->tpl->setVariable('LRS_TYPE_USAGES', $a_set['usages'] ? $a_set['usages'] : '');
        $this->tpl->setVariable('ACTIONS', $this->getActionsList($a_set)->getHTML());
    }

    protected function getAvailabilityLabel(string $availability): string
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        return $DIC->language()->txt('conf_availability_' . $availability);
    }

    /**
     * @throws ilCtrlException
     */
    protected function getActionsList(array $data): \ilAdvancedSelectionListGUI
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->ctrl()->setParameter($this->parent_obj, 'lrs_type_id', $data['lrs_type_id']);

        $link = $DIC->ctrl()->getLinkTarget(
            $this->parent_obj,
            ilObjCmiXapiAdministrationGUI::CMD_SHOW_LRS_TYPE_FORM
        );

        $DIC->ctrl()->setParameter($this->parent_obj, 'lrs_type_id', '');

        $actionList = new ilAdvancedSelectionListGUI();
        $actionList->setListTitle($DIC->language()->txt('actions'));
        $actionList->addItem($DIC->language()->txt('edit'), '', $link);

        return $actionList;
    }
}
