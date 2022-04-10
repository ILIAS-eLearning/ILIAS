<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHistorizedDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHistorizedDocument implements ilTermsOfServiceSignableDocument
{
    private ilTermsOfServiceAcceptanceEntity $entity;
    private ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria;

    public function __construct(
        ilTermsOfServiceAcceptanceEntity $entity,
        ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria
    ) {
        $this->entity = $entity;
        $this->criteria = $criteria;
    }

    public function content() : string
    {
        return $this->entity->getTitle();
    }

    public function title() : string
    {
        return $this->entity->getTitle();
    }

    public function id() : int
    {
        return $this->entity->getDocumentId();
    }

    public function criteria() : array
    {
        $criteria = [];
        foreach ($this->criteria as $criterion) {
            $criteria[] = new ilTermsOfServiceHistorizedCriterion(
                $criterion['id'],
                $criterion['value']
            );
        }

        return $criteria;
    }
}
