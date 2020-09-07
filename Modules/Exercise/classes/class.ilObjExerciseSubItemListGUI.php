<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Object/classes/class.ilSubItemListGUI.php';
include_once './Modules/Exercise/classes/class.ilExAssignment.php';

/**
* Represents search sub item lists
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseSubItemListGUI extends ilSubItemListGUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;


    /**
     * Constructor
     */
    public function __construct($a_cmd_class)
    {
        global $DIC;
        parent::__construct($a_cmd_class);

        $this->access = $DIC->access();
    }

    /**
     * Check if read access to assignments is granted
     * @param int assignment id
     * @return
     */
    protected function isAssignmentVisible($a_ass_id)
    {
        $ilAccess = $this->access;
        
        if ($ilAccess->checkAccess('write', '', $this->getRefId())) {
            return true;
        }
        
        return ilExAssignment::lookupAssignmentOnline($a_ass_id);
    }


    /**
     * @see ilSubItemListGUI::getHTML()
     */
    public function getHTML()
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule('exc');
        
        $valid = false;
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (!$this->isAssignmentVisible($sub_item)) {
                continue;
            }
            $valid = true;

            if (is_object($this->getHighlighter()) and strlen($this->getHighlighter()->getContent($this->getObjId(), $sub_item))) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('exc_assignment'));
            $this->tpl->setVariable('SEPERATOR', ':');
            
            #$link_data = ilLinkResourceItems::lookupItem($this->getObjId(),$sub_item);
            #$link_data = ilParameterAppender::_append($link_data);

            #$this->getItemListGUI()->setChildId($sub_item);
            $this->tpl->setVariable('LINK', 'ilias.php?baseClass=ilExerciseHandlerGUI&cmd=showOverview&ref_id=' . $this->getRefId() . '&ass_id=' . $sub_item);
            $this->tpl->setVariable('TITLE', ilExAssignment::lookupTitle($sub_item));

            if (count($this->getSubItemIds(true)) > 1) {
                $this->parseRelevance($sub_item);
            }
            
            $this->tpl->parseCurrentBlock();
        }
        
        $this->showDetailsLink();
        
        return $valid ? $this->tpl->get() : '';
    }
}
