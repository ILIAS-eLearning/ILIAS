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
 * List booking schedules (for booking pool)
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBookingSchedulesTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjectDataCache $obj_data_cache;
    protected int $ref_id;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $ilCtrl = $DIC->ctrl();
        $ilObjDataCache = $DIC["ilObjDataCache"];

        $this->ref_id = $a_ref_id;
        $this->setId("bksd");

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("book_is_used"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.booking_schedule_row.html", "Modules/BookingManager");
    
        $this->getItems($ilObjDataCache->lookupObjId($this->ref_id));
    }

    /**
     * Build summary item rows for given object and filter(s)
     * @param int $a_pool_id (aka parent obj id)
     */
    public function getItems(int $a_pool_id) : void
    {
        $data = ilBookingSchedule::getList($a_pool_id);
        
        $this->setMaxCount(count($data));
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);

        if ($a_set["is_used"]) {
            $this->tpl->setVariable("TXT_IS_USED", $lng->txt("yes"));
        } else {
            $this->tpl->setVariable("TXT_IS_USED", $lng->txt("no"));
        }

        $ilCtrl->setParameter($this->parent_obj, 'schedule_id', $a_set['booking_schedule_id']);

        $alist = new ilAdvancedSelectionListGUI();
        $alist->setId($a_set['booking_schedule_id']);
        $alist->setListTitle($lng->txt("actions"));
    
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTarget($this->parent_obj, 'edit')); // #12306
            
            if (!$a_set["is_used"]) {
                $alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTarget($this->parent_obj, 'confirmDelete'));
            }
        }

        $this->tpl->setVariable("LAYER", $alist->getHTML());
    }
}
