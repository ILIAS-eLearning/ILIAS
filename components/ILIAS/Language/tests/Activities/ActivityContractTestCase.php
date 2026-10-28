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

use ILIAS\Language\Tests\Activities\RealFieldsUiFactory;
use ILIAS\Component\Activities\Activity;
use ILIAS\Component\Activities\ActivityType;
use ilLanguageBaseTestCase;
use ReflectionMethod;

/**
 * Contract tests shared by every Activity test case; subclasses only supply createDefaultActivity().
 */
abstract class ActivityContractTestCase extends ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;

    abstract protected function createDefaultActivity(): Activity;

    protected function expectedActivityType(): ActivityType
    {
        return ActivityType::Command;
    }

    public function testGetNameIsTheFullyQualifiedClassName(): void
    {
        $activity = $this->createDefaultActivity();

        $this->assertSame($activity::class, (string) $activity->getName());
    }

    public function testGetTypeIsCommand(): void
    {
        $this->assertSame($this->expectedActivityType(), $this->createDefaultActivity()->getType());
    }

    public function testGetDescriptionReturnsANonEmptyMarkdownDocument(): void
    {
        $this->assertNotSame('', $this->createDefaultActivity()->getDescription()->getRawRepresentation());
    }

    /** An extra parameter, even optional, is a variance fatal error at class-load time. */
    public function testGetInputDescriptionSignatureExactlyMatchesTheActivityInterfaceSignature(): void
    {
        $activity = $this->createDefaultActivity();

        $interface_method = new ReflectionMethod(Activity::class, 'getInputDescription');
        $own_method = new ReflectionMethod($activity::class, 'getInputDescription');

        $this->assertSame(
            $interface_method->getNumberOfParameters(),
            $own_method->getNumberOfParameters(),
            'getInputDescription() must declare exactly as many parameters as Activity::getInputDescription() - ' .
            'no extra (even optional) parameter, or a subclass overriding it with the plain interface ' .
            'signature triggers a PHP fatal error at class-load time.'
        );
        $this->assertSame(
            (string) $interface_method->getReturnType(),
            (string) $own_method->getReturnType()
        );

        foreach ($interface_method->getParameters() as $i => $interface_parameter) {
            $own_parameter = $own_method->getParameters()[$i];
            $this->assertSame((string) $interface_parameter->getType(), (string) $own_parameter->getType());
            $this->assertSame($interface_parameter->allowsNull(), $own_parameter->allowsNull());
        }
    }
}
