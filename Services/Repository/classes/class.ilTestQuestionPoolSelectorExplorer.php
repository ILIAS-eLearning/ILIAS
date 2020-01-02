<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php';
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilTestQuestionPoolSelectorExplorer extends ilRepositorySelectorExplorerGUI
{
    protected $availableQuestionPools = array();
    
    public function __construct($targetGUI, $roundtripCMD, $selectCMD)
    {
        parent::__construct($targetGUI, $roundtripCMD, $targetGUI, $selectCMD);
        
        $this->setTypeWhiteList(array('grp', 'cat', 'crs', 'fold', 'qpl'));
        $this->setClickableTypes(array('qpl'));
        $this->setSelectMode('', false);
        $this->selection_par = 'quest_pool_ref';
    }
    
    public function getAvailableQuestionPools()
    {
        return $this->availableQuestionPools;
    }
    
    public function setAvailableQuestionPools($availableQuestionPools)
    {
        $this->availableQuestionPools = $availableQuestionPools;
    }
    
    public function isAvailableQuestionPool($qplRefId)
    {
        /* @var ilObjectDataCache $objCache */
        $objCache = isset($GLOBALS['DIC']) ? $GLOBALS['DIC']['ilObjDataCache'] : $GLOBALS['ilObjDataCache'];

        $qplObjId = $objCache->lookupObjId($qplRefId);
        return in_array($qplObjId, $this->getAvailableQuestionPools());
    }
    
    public function isNodeClickable($a_node)
    {
        if ($a_node['type'] != 'qpl') {
            return parent::isNodeClickable($a_node);
        }
        
        return $this->isAvailableQuestionPool($a_node['child']);
    }
    
    public function isNodeVisible($a_node)
    {
        if ($a_node['type'] != 'qpl') {
            return parent::isNodeVisible($a_node);
        }
        
        return $this->isAvailableQuestionPool($a_node['child']);
    }
}
