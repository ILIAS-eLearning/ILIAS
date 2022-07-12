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
