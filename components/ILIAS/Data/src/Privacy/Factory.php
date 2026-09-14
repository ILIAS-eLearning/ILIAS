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

use ILIAS\Data\Privacy\Logger\PrivacyLogger;
use ILIAS\Data\Privacy\Source\Source;
use ILIAS\Data\Privacy\Types\PostalAddress;
use ILIAS\Data\Privacy\Types\PostalAddressValue;

/**
 * Creates privacy data types with the configured audit logger bound to
 * every instance.
 *
 * Obtain this via {@see Services::factory()} — never construct privacy
 * types directly in production code, otherwise their resolves bypass the
 * audit trail.
 */
final readonly class Factory
{
    public function __construct(
        private PrivacyLogger $logger,
    ) {
    }

    public function postalAddress(PostalAddressValue $value, Source $source): PostalAddress
    {
        return new PostalAddress($value, $source, $this->logger);
    }
}
