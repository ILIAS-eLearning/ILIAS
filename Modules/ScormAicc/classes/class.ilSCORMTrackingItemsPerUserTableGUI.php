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
class ilSCORMTrackingItemsPerUserTableGUI extends ilTable2GUI
{
    private int $obj_id;
    private int $user_id = 0;

    public function __construct(int $a_obj_id, ?object $a_parent_obj, string $a_parent_cmd)
    {
        $this->obj_id = $a_obj_id;

        $this->setId('sco_trs_usr_' . $this->obj_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    /**
     * Set current user id
     */
    public function setUserId(int $a_usr_id) : void
    {
        $this->user_id = $a_usr_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * Parse table content
     */
    public function parse() : void
    {
        $this->initTable();

        $user_data = $this->getParentObject()->object->getTrackingDataAgg($this->getUserId());
        $data = array();
        foreach ($user_data as $row) {
            $data[] = $row;
        }
        $this->setData($data);
    }


    /**
     * Fill row template
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);

        $ilCtrl->setParameter($this->getParentObject(), 'user_id', $this->getUserId());
        $ilCtrl->setParameter($this->getParentObject(), 'obj_id', $a_set['sco_id']);
        $this->tpl->setVariable('LINK_SCO', $ilCtrl->getLinkTarget($this->getParentObject(), 'showTrackingItemPerUser'));

        $this->tpl->setVariable('VAL_STATUS', $a_set['status']);
        $this->tpl->setVariable('VAL_TIME', $a_set['time']);
        $this->tpl->setVariable('VAL_SCORE', $a_set['score']);
    }

    protected function initTable() : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();


        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.scorm_track_item.html', 'Modules/ScormAicc');
        $this->setTitle(ilObjUser::_lookupFullname($this->getUserId()));

        $this->addColumn($this->lng->txt('title'), 'title', '35%');
        $this->addColumn($this->lng->txt('cont_status'), 'status', '25%');
        $this->addColumn($this->lng->txt('cont_time'), 'time', '20%');
        $this->addColumn($this->lng->txt('cont_score'), 'score', '20%');
    }
}
