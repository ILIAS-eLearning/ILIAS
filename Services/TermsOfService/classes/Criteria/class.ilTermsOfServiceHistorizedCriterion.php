<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHistorizedCriterion
 * @author Michael Jansen <mjansen@datababay.de>
 */
class ilTermsOfServiceHistorizedCriterion implements ilTermsOfServiceEvaluableCriterion
{
    private string $id;
    private array $config;

    public function __construct(string $id, array $config)
    {
        $this->id = $id;
        $this->config = $config;
    }

    public function getCriterionValue() : ilTermsOfServiceCriterionConfig
    {
        return new ilTermsOfServiceCriterionConfig($this->config);
    }

    public function getCriterionId() : string
    {
        return $this->id;
    }
}
