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

use ILIAS\Data\Privacy\Purpose\Purpose;
use ILIAS\Data\Privacy\Source\Source;

/**
 * A personal data value that can only be resolved with an explicit purpose.
 *
 * Instances carry the origin of the value (a {@see Source}) and hand out the
 * raw value exclusively through {@see resolve()}, stating a {@see Purpose}.
 * Every resolve call is reported to the wired {@see Logger\PrivacyLogger},
 * forming the GDPR audit trail, and is statically collectable via the
 * PHPStan extension in `components/ILIAS/Data/PHPStan/Privacy`.
 *
 * @template T
 */
interface PrivacyDataType extends \Stringable
{
    /**
     * Returns the raw value. Requires a purpose and is logged.
     *
     * @return T
     */
    public function resolve(Purpose $purpose): mixed;

    /**
     * Describes where this value originates from.
     */
    public function getSource(): Source;

    /**
     * For logs and debugging. MUST never expose the raw value,
     * e.g. "ILIAS\Data\Privacy\Types\PostalAddress(***) from usr_data.(street,city,zipcode,country)".
     */
    public function __toString(): string;
}
