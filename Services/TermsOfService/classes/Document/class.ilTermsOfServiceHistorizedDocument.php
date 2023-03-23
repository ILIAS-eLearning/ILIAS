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

/**
 * Class ilTermsOfServiceHistorizedDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHistorizedDocument implements ilTermsOfServiceSignableDocument
{
    public function __construct(private readonly ilTermsOfServiceAcceptanceEntity $entity, private readonly ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria)
    {
    }

    public function content(): string
    {
        return $this->entity->getTitle();
    }

    public function title(): string
    {
        return $this->entity->getTitle();
    }

    public function id(): int
    {
        return $this->entity->getDocumentId();
    }

    public function criteria(): array
    {
        return array_map(static function (array $criterion): ilTermsOfServiceHistorizedCriterion {
            return new ilTermsOfServiceHistorizedCriterion(
                $criterion['id'],
                $criterion['value']
            );
        }, $this->criteria->getArrayCopy());
    }
}
