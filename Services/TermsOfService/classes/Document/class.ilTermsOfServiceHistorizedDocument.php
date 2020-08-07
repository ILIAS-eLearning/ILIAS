<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHistorizedDocument
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHistorizedDocument implements ilTermsOfServiceSignableDocument
{
    /** @var ilTermsOfServiceAcceptanceEntity */
    private $entity;
    /** @var ilTermsOfServiceAcceptanceHistoryCriteriaBag */
    private $criteria;
    /** @var ilTermsOfServiceCriterionTypeFactoryInterface */
    private $criterionTypeFactory;

    /**
     * ilTermsOfServiceHistorizedDocument constructor.
     * @param ilTermsOfServiceAcceptanceEntity $entity
     * @param ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria
     * @param ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     */
    public function __construct(
        ilTermsOfServiceAcceptanceEntity $entity,
        ilTermsOfServiceAcceptanceHistoryCriteriaBag $criteria,
        ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
    ) {
        $this->entity = $entity;
        $this->criteria = $criteria;
        $this->criterionTypeFactory = $criterionTypeFactory;
    }

    /**
     * @inheritDoc
     */
    public function content() : string
    {
        return $this->entity->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function title() : string
    {
        return $this->entity->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function id() : int
    {
        return $this->entity->getDocumentId();
    }

    /**
     * @inheritDoc
     */
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
