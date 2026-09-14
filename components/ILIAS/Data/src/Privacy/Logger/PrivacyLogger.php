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

namespace ILIAS\Data\Privacy\Logger;

use ILIAS\Data\Privacy\PrivacyDataType;
use ILIAS\Data\Privacy\Purpose\Purpose;

/**
 * Receives every resolve() call on a privacy data type — the GDPR audit
 * trail.
 *
 * Backends are contributed by components via
 * `$contribute[PrivacyLogger::class]` in their component bootstrap; the
 * Data component collects all contributions into a {@see CompositeLogger}.
 *
 * Implementations MUST only record metadata (type, source, purpose,
 * timestamp, acting user) — never the raw value.
 */
interface PrivacyLogger
{
    public function log(PrivacyDataType $data, Purpose $purpose): void;
}
