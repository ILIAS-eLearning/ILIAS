<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilBuddySystemSubjectDependentRelationStateButtonRenderer
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilBuddySystemSubjectDependentRelationStateButtonRenderer extends ilAbstractBuddySystemRelationStateButtonRenderer
{
    protected function getTemplateVariablePrefix() : string
    {
        if ($this->relation->isOwnedByActor()) {
            return 'REQUESTER_';
        }

        return 'REQUESTEE_';
    }

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
