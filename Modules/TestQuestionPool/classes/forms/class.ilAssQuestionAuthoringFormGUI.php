<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package        Modules/Test(QuestionPool)
 */
class ilAssQuestionAuthoringFormGUI extends ilPropertyFormGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng = null;
    
    /**
     * ilAssQuestionAuthoringFormGUI constructor.
     */
    public function __construct()
    {
        global $DIC; /* @var ILIAS\DI\Container $DIC */
        $this->lng = $DIC['lng'];
        
        parent::__construct();
    }
    
    /**
     * @param assQuestion $questionOBJ
     */
    public function addGenericAssessmentQuestionCommandButtons(assQuestion $questionOBJ)
    {
        //if( !$this->object->getSelfAssessmentEditingMode() && !$_GET["calling_test"] )
        //	$this->addCommandButton("saveEdit", $this->lng->txt("save_edit"));
        
        if (!$questionOBJ->getSelfAssessmentEditingMode()) {
            $this->addCommandButton("saveReturn", $this->lng->txt("save_return"));
        }
        
        $this->addCommandButton("save", $this->lng->txt("save"));
    }
    
    /**
     * @param ilFormPropertyGUI $replacingItem
     * @return bool
     */
    public function replaceFormItemByPostVar(ilFormPropertyGUI $replacingItem)
    {
        $itemWasReplaced = false;
        
        $preparedItems = array();
        
        foreach ($this->getItems() as $dodgingItem) {
            /* @var ilFormPropertyGUI $dodgingItem */
            
            if ($dodgingItem->getPostVar() == $replacingItem->getPostVar()) {
                $preparedItems[] = $replacingItem;
                $itemWasReplaced = true;
                continue;
            }
            
            $preparedItems[] = $dodgingItem;
        }
        
        $this->setItems($preparedItems);
        
        return $itemWasReplaced;
    }
}
