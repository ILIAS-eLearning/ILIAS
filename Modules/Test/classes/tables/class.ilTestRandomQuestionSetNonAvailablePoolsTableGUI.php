<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test(QuestionPool)
 */
class ilTestRandomQuestionSetNonAvailablePoolsTableGUI extends ilTable2GUI
{
    const IDENTIFIER = 'NonAvailPoolsTbl';
    
    /**
     * @var ilCtrl
     */
    protected $ctrl = null;
    
    /**
     * @var ilLanguage
     */
    protected $lng;
    
    public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
    {
        parent::__construct($parentGUI, $parentCMD);
        
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }
    
    private function setTableIdentifiers()
    {
        $this->setId(self::IDENTIFIER);
        $this->setPrefix(self::IDENTIFIER);
        $this->setFormName(self::IDENTIFIER);
    }
    
    public function build()
    {
        $this->setTableIdentifiers();
        
        $this->setTitle($this->lng->txt('tst_non_avail_pools_table'));
        
        $this->setRowTemplate('tpl.il_tst_non_avail_pools_row.html', 'Modules/Test');
        
        $this->enable('header');
        $this->disable('sort');
        
        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);
        
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
        
        $this->addColumns();
    }
    
    protected function addColumns()
    {
        $this->addColumn($this->lng->txt('title'), '', '30%');
        $this->addColumn($this->lng->txt('path'), '', '30%');
        $this->addColumn($this->lng->txt('status'), '', '40%');
        $this->addColumn($this->lng->txt('actions'), '', '');
    }
    
    public function init(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
    {
        $rows = array();
        
        $pools = $sourcePoolDefinitionList->getNonAvailablePools();
        
        foreach ($pools as $nonAvailablePool) {
            /** @var ilTestRandomQuestionSetNonAvailablePool $nonAvailablePool */
            
            $set = array();
            
            $set['id'] = $nonAvailablePool->getId();
            $set['title'] = $nonAvailablePool->getTitle();
            $set['path'] = $nonAvailablePool->getPath();
            $set['status'] = $nonAvailablePool->getUnavailabilityStatus();
            
            $rows[] = $set;
        }
        
        $this->setData($rows);
    }
    
    protected function getDerivePoolLink($poolId)
    {
        $this->ctrl->setParameter($this->parent_obj, 'derive_pool_id', $poolId);
        
        $link = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilTestRandomQuestionSetConfigGUI::CMD_SELECT_DERIVATION_TARGET
        );
        
        return $link;
    }
    
    public function fillRow($set)
    {
        if ($set['status'] == ilTestRandomQuestionSetNonAvailablePool::UNAVAILABILITY_STATUS_LOST) {
            $link = $this->getDerivePoolLink($set['id']);
            $this->tpl->setCurrentBlock('single_action');
            $this->tpl->setVariable('ACTION_HREF', $link);
            $this->tpl->setVariable('ACTION_TEXT', $this->lng->txt('tst_derive_new_pool'));
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable('TITLE', $set['title']);
        $this->tpl->setVariable('PATH', $set['path']);
        $this->tpl->setVariable('STATUS', $this->getStatusText($set['status']));
    }
    
    protected function getStatusText($status)
    {
        return $this->lng->txt('tst_non_avail_pool_msg_status_' . $status);
    }
}
