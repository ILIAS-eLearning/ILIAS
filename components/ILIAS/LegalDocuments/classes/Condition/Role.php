<?php

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

declare(strict_types=1);

namespace ILIAS\LegalDocuments\Condition;

use ILIAS\LegalDocuments\Condition;
use ILIAS\LegalDocuments\ConditionDefinition;
use ILIAS\LegalDocuments\Condition\Definition\RoleDefinition;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\UI\Component\Component;
use ilObjUser;
use ilRbacReview;
use ILIAS\UI\Factory as UIFactory;

class Role implements Condition
{
    public function __construct(
        private readonly CriterionContent $criterion,
        private readonly RoleDefinition $definition,
        private readonly UIFactory $create,
        private readonly ilRbacReview $review
    ) {
    }

    public function asComponent(): Component
    {
        return $this->create->legacy(sprintf(
            '<div><b>%s</b><br/>%s</div>',
            $this->definition->translatedType(),
            $this->definition->translatedRole((int) $this->criterion->arguments()['role_id'])
        ));
    }

    public function eval(ilObjUser $user): bool
    {
        return $this->review->isAssigned($user->getId(), (int) $this->criterion->arguments()['role_id']);
    }

    public function definition(): ConditionDefinition
    {
        return $this->definition;
    }

    public function knownToNeverMatchWith(Condition $other): bool
    {
        return false;
    }
}
