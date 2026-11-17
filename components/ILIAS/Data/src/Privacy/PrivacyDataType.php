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

namespace ILIAS\Data\Privacy;

use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirement;
use ILIAS\Data\Privacy\Source\Source;

/**
 * A personal data value that can only be resolved with an explicit requirement.
 *
 * Instances carry the origin of the value (a {@see Source}) and hand out the
 * raw value exclusively through {@see resolve()}, stating a {@see TechnicalRequirement}.
 * Every resolve call site is statically collectable via the PHPStan
 * rule in `scripts/PHPStan/Rules/Privacy`, which is the basis
 * of the generated documentation. Resolving records nothing at runtime.
 *
 * @template T
 */
interface PrivacyDataType extends \Stringable
{
    /**
     * Returns the raw value. Requires a technical requirement.
     *
     * @return T
     */
    public function resolve(TechnicalRequirement $requirement): mixed;

    /**
     * Describes where this value originates from.
     */
    public function getSource(): Source;

    /**
     * How the generated privacy documentation names this kind of data, a
     * noun phrase with its article, e.g. "the postal address".
     */
    public static function label(): string;

    /**
     * For logs and debugging. MUST never expose the raw value,
     * e.g. "ILIAS\Data\Privacy\Types\PostalAddress(***) from usr_data.(street,city,zipcode,country)".
     */
    public function __toString(): string;
}
