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

namespace ILIAS\Data\Privacy\PHPStan\Type\Fixtures;

use ILIAS\Data\Privacy\PrivacyDataType;
use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirement;
use ILIAS\Data\Privacy\Types\PostalAddress;

use function PHPStan\Testing\assertType;

function concreteTypeUsesNativeGenerics(PostalAddress $address, TechnicalRequirement $requirement): void
{
    assertType('ILIAS\\Data\\Privacy\\Types\\PostalAddressValue', $address->resolve($requirement));
}

/**
 * @param PrivacyDataType<int> $wrapped_int
 */
function genericInterfaceTypeResolvesToTypeArgument(PrivacyDataType $wrapped_int, TechnicalRequirement $requirement): void
{
    assertType('int', $wrapped_int->resolve($requirement));
}
