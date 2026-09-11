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
use ILIAS\LegalDocuments\Condition\Definition\UserCountryDefinition;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Privacy\Purpose\Purposes;
use ilObjUser;

class UserCountry implements Condition
{
    public function __construct(
        private readonly CriterionContent $criterion,
        private readonly UserCountryDefinition $definition,
        private readonly UIFactory $create,
        private readonly Purposes $purposes
    ) {
    }

    public function asComponent(): Component
    {
        return $this->create->legacy()->content(sprintf(
            '<div><b>%s</b><br/>%s</div>',
            $this->definition->translatedType(),
            $this->definition->translatedCountry($this->criterion->arguments()['country'] ?? '')
        ));
    }

    public function eval(ilObjUser $user): bool
    {
        $country = $user->getProfileData()->getPostalAddress()?->resolve(
            $this->purposes->technicalProcessing('legal_documents_country_condition')
        )->country ?? '';
        return strtoupper($country) === strtoupper($this->criterion->arguments()['country']);
    }

    public function definition(): ConditionDefinition
    {
        return $this->definition;
    }

    public function knownToNeverMatchWith(Condition $other): bool
    {
        return $other instanceof (self::class);
    }
}
