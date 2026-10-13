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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Tests\Activities\ActivityContractTestCase;
use ILIAS\Data\Description\Factory as DescriptionFactory;

/**
 * Cross-checks the declared output field names against the keys perform() returns, using a real
 * DescriptionFactory so a field swap or typo is caught.
 */
abstract class ActivityWithPerformResultContractTestCase extends ActivityContractTestCase
{
    /**
     * @return array raw parameters that createDefaultActivity()'s activity can perform() without throwing
     */
    abstract protected function validPerformParameters(): array;

    public function testOutputDescriptionFieldNamesMatchTheActualPerformResultKeys(): void
    {
        $activity = $this->createDefaultActivity();
        $result = $activity->perform($this->validPerformParameters());

        $description = $activity->getOutputDescription(new DescriptionFactory());
        $declared_field_names = [];
        foreach ($description->getFields() as $field) {
            $declared_field_names[] = $field->getName();
        }

        $this->assertSame($declared_field_names, array_keys($result));
    }
}
