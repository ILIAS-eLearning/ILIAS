<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author	Björn Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup	ModulesTest
*/

class ilTestManScoringParticipantsTableGUI extends ilTable2GUI
{
    const PARENT_DEFAULT_CMD		= 'showManScoringParticipantsTable';
    const PARENT_APPLY_FILTER_CMD	= 'applyManScoringParticipantsFilter';
    const PARENT_RESET_FILTER_CMD	= 'resetManScoringParticipantsFilter';
    
    const PARENT_EDIT_SCORING_CMD	= 'showManScoringParticipantScreen';
    
    /**
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	ilObjectGUI	$parentObj
     */
    public function __construct($parentObj)
    {
        $this->setPrefix('manScorePartTable');
        $this->setId('manScorePartTable');

        parent::__construct($parentObj, self::PARENT_DEFAULT_CMD);
        
        $this->setFilterCommand(self::PARENT_APPLY_FILTER_CMD);
        $this->setResetCommand(self::PARENT_RESET_FILTER_CMD);

        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $this->setFormName('manScorePartTable');
        $this->setStyle('table', 'fullwidth');

        $this->enable('header');

        $this->setFormAction($ilCtrl->getFormAction($parentObj, self::PARENT_DEFAULT_CMD));

        $this->setRowTemplate("tpl.il_as_tst_man_scoring_participant_tblrow.html", "Modules/Test");

        $this->initColumns();
        $this->initOrdering();

        $this->initFilter();
    }
    
    private function initColumns()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        if ($this->parent_obj->object->getAnonymity()) {
            $this->addColumn($lng->txt("name"), 'lastname', '100%');
        } else {
            $this->addColumn($lng->txt("lastname"), 'lastname', '');
            $this->addColumn($lng->txt("firstname"), 'firstname', '');
            $this->addColumn($lng->txt("login"), 'login', '');
        }
        
        $this->addColumn('', '', '1%');
    }
    
    private function initOrdering()
    {
        $this->enable('sort');

        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
    }
    
    public function initFilter()
    {
        global $DIC;
        $lng = $DIC['lng'];
        
        $this->setDisableFilterHiding(true);
        
        include_once 'Services/Form/classes/class.ilSelectInputGUI.php';
        $participantStatus = new ilSelectInputGUI($lng->txt('tst_participant_status'), 'participant_status');

        $statusOptions                                                = array();
        $statusOptions[ilTestScoringGUI::PART_FILTER_ALL_USERS]       = $lng->txt("all_users");
        $statusOptions[ilTestScoringGUI::PART_FILTER_MANSCORING_NONE] = $lng->txt("manscoring_none");
        $statusOptions[ilTestScoringGUI::PART_FILTER_MANSCORING_DONE] = $lng->txt("manscoring_done");
        $statusOptions[ilTestScoringGUI::PART_FILTER_ACTIVE_ONLY]     = $lng->txt("usr_active_only");
        $statusOptions[ilTestScoringGUI::PART_FILTER_INACTIVE_ONLY]   = $lng->txt("usr_inactive_only");
        //$statusOptions[ ilTestScoringGUI::PART_FILTER_MANSCORING_PENDING ]	= $lng->txt("manscoring_pending");

        $participantStatus->setOptions($statusOptions);

        $this->addFilterItem($participantStatus);

        $participantStatus->readFromSession();
        
        if (!$participantStatus->getValue()) {
            $participantStatus->setValue(ilTestScoringGUI::PART_FILTER_MANSCORING_NONE);
        }
    }

    /**
     * @global	ilCtrl		$ilCtrl
     * @global	ilLanguage	$lng
     * @param	array		$row
     */
    public function fillRow($row)
    {
        //vd($row);
        
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $ilCtrl->setParameter($this->parent_obj, 'active_id', $row['active_id']);
    
        if (!$this->parent_obj->object->getAnonymity()) {
            $this->tpl->setCurrentBlock('personal');
            $this->tpl->setVariable("PARTICIPANT_FIRSTNAME", $row['firstname']);
            $this->tpl->setVariable("PARTICIPANT_LOGIN", $row['login']);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("PARTICIPANT_LASTNAME", $row['lastname']);

        $this->tpl->setVariable("HREF_SCORE_PARTICIPANT", $ilCtrl->getLinkTarget($this->parent_obj, self::PARENT_EDIT_SCORING_CMD));
        $this->tpl->setVariable("TXT_SCORE_PARTICIPANT", $lng->txt('tst_edit_scoring'));
    }
    
    public function getInternalyOrderedDataValues()
    {
        $this->determineOffsetAndOrder();
        
        return ilUtil::sortArray(
            $this->getData(),
            $this->getOrderField(),
            $this->getOrderDirection(),
            $this->numericOrdering($this->getOrderField())
        );
    }
}
