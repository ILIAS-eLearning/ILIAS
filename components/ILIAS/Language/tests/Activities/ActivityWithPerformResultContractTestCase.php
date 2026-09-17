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
 * Extends ActivityContractTestCase with the getOutputDescription()-vs-
 * perform() cross-check shared by InstallLanguageTest/UpdateLanguageTest/
 * UninstallLanguageTest/RemoveLocalLanguageChangesTest: a real
 * DescriptionFactory (not a mock) is used so a field swap/typo in
 * getOutputDescription() would actually be caught. This additionally
 * cross-checks the declared field names against the keys an actual
 * perform() call returns, so a field renamed in one place but not the other
 * would fail here too.
 */
abstract class ActivityWithPerformResultContractTestCase extends ActivityContractTestCase
{
    /**
     * @return array valid raw parameters createDefaultActivity()'s activity
     *         can perform() without throwing.
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
