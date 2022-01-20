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
class ilSCORMTrackingItemPerUserTableGUI extends ilTable2GUI
{
    private int $obj_id = 0;
    private int $user_id = 0;
    private $sco = null;

    /**
     * Constructor
     */
    public function __construct($a_obj_id, ?object $a_parent_obj, string $a_parent_cmd)
    {
        $this->obj_id = $a_obj_id;

        $this->setId('sco_tr_usr_' . $this->obj_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /**
     * Get Obj id
     */
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

    /**
     * Get user id
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * Set sco id
     */
    public function setScoId(int $a_sco_id) : void
    {
        $this->sco = new ilSCORMItem($a_sco_id);
    }

    /**
     * Get SCORM item
     * @return ilSCORMItem $sco
     */
    public function getSco() : \ilSCORMItem
    {
        return $this->sco;
    }

    /**
     * Parse table content
     */
    public function parse() : void
    {
        $this->initTable();

        $sco_data = $this->getParentObject()->object->getTrackingDataPerUser(
            $this->getSco()->getId(),
            $this->getUserId()
        );

        $data = array();
        foreach ($sco_data as $row) {
            $data[] = $row;
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
        $ilCtrl = $DIC['ilCtrl'];

        $this->tpl->setVariable('VAR', $a_set['lvalue']);
        $this->tpl->setVariable('VAL', $a_set['rvalue']);
    }

    /**
     * Init table
     */
    protected function initTable() : void
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];


        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate('tpl.scorm_track_item_per_user.html', 'Modules/ScormAicc');
        $this->setTitle(
            $this->getSco()->getTitle() . ' - ' .
            ilObjUser::_lookupFullname($this->getUserId())
        );

        $this->addColumn($this->lng->txt('cont_lvalue'), 'lvalue', '50%');
        $this->addColumn($this->lng->txt('cont_rvalue'), 'rvalue', '50%');
    }
}
