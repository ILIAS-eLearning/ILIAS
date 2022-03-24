<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Represents search sub item lists
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilObjExerciseSubItemListGUI extends ilSubItemListGUI
{
    protected ilAccessHandler $access;

    public function __construct(string $a_cmd_class)
    {
        global $DIC;
        parent::__construct($a_cmd_class);

        $this->access = $DIC->access();
    }

    protected function isAssignmentVisible(
        int $a_ass_id
    ) : bool {
        $ilAccess = $this->access;
        
        if ($ilAccess->checkAccess('write', '', $this->getRefId())) {
            return true;
        }
        
        return ilExAssignment::lookupAssignmentOnline($a_ass_id);
    }

    public function getHTML() : string
    {
        $lng = $this->lng;
        
        $lng->loadLanguageModule('exc');
        
        $valid = false;
        foreach ($this->getSubItemIds(true) as $sub_item) {
            if (!$this->isAssignmentVisible($sub_item)) {
                continue;
            }
            $valid = true;

            if (is_object($this->getHighlighter()) && strlen($this->getHighlighter()->getContent($this->getObjId(), $sub_item))) {
                $this->tpl->setCurrentBlock('sea_fragment');
                $this->tpl->setVariable('TXT_FRAGMENT', $this->getHighlighter()->getContent($this->getObjId(), $sub_item));
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock('subitem');
            $this->tpl->setVariable('SUBITEM_TYPE', $lng->txt('exc_assignment'));
            $this->tpl->setVariable('SEPERATOR', ':');
            
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
