<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/classes/states/renderer/class.ilAbstractBuddySystemRelationStateButtonRenderer.php';
/**
 * Class ilBuddySystemSubjectDependentRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemSubjectDependentRelationStateButtonRenderer extends ilAbstractBuddySystemRelationStateButtonRenderer
{
    /**
     * @return string
     */
    public function getTemplateVariablePrefix()
    {
        if ($this->relation->isOwnedByRequest()) {
            return 'REQUESTER_';
        } else {
            return 'REQUESTEE_';
        }
    }

    /**
     *
     */
    protected function render()
    {
        if ($this->relation->isOwnedByRequest()) {
            $this->tpl->setCurrentBlock('requester_container');
        } else {
            $this->tpl->setCurrentBlock('requestee_container');
        }
        parent::render();
        $this->tpl->parseCurrentBlock();
    }
}
