<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBuddySystemSubjectDependentRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemSubjectDependentRelationStateButtonRenderer extends ilAbstractBuddySystemRelationStateButtonRenderer
{
    /**
     * @inheritDoc
     */
    public function getTemplateVariablePrefix() : string
    {
        if ($this->relation->isOwnedByActor()) {
            return 'REQUESTER_';
        }

        return 'REQUESTEE_';
    }

    /**
     * @inheritDoc
     */
    protected function render() : void
    {
        if ($this->relation->isOwnedByActor()) {
            $this->tpl->setCurrentBlock('requester_container');
        } else {
            $this->tpl->setCurrentBlock('requestee_container');
        }
        parent::render();
        $this->tpl->parseCurrentBlock();
    }
}