<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsPerScoTableGUI extends ilTable2GUI
{
    private int $obj_id = 0;

    /**
     * Constructor
     */
    public function __construct($a_obj_id, ?object $a_parent_obj, string $a_parent_cmd)
    {
        $this->obj_id = $a_obj_id;

        $this->setId('sco_trs_usr_' . $this->obj_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /**
     * Get Obj id
     * @return int
     */
    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Parse table content
     */
    public function parse() : void
    {
        $this->initTable();

        $scos = $this->getParentObject()->object->getTrackedItems();

        $data = array();
        foreach ($scos as $row) {
            $tmp = array();
            $tmp['title'] = $row->getTitle();
            $tmp['id'] = $row->getId();

            $data[] = $tmp;
        }
        $this->setData($data);
    }


    /**
     * Fill row template
     * @param array $a_set
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        $this->tpl->setVariable('TXT_ITEM_TITLE', $a_set['title']);
        $ilCtrl->setParameter($this->getParentObject(), 'obj_id', $a_set['id']);
        $this->tpl->setVariable('LINK_ITEM', $ilCtrl->getLinkTarget($this->getParentObject(), 'showTrackingItemSco'));
    }

    /**
     * Init table
     */
    protected function initTable() : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();


        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.scorm_track_items_sco.html', 'Modules/ScormAicc');
        $this->setTitle($this->lng->txt('cont_tracking_items'));

        $this->addColumn($this->lng->txt('title'), 'title', '100%');
    }
}
