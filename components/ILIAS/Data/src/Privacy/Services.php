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

use ILIAS\Data\Privacy\Purpose\Purposes;
use ILIAS\Data\Privacy\Source\Sources;

/**
 * Entry point to the privacy data type infrastructure.
 *
 * Modern components receive this via `$use[Services::class]` in their
 * component bootstrap; legacy code reaches it through
 * `$DIC[\ILIAS\Data\Privacy\Services::class]`.
 */
interface Services
{
    /**
     * Creates privacy data types with the audit logger bound.
     */
    public function factory(): Factory;

    /**
     * Factory for the available sources, including the catalogues of
     * known ILIAS personal data columns.
     */
    public function sources(): Sources;

    /**
     * Factory for the available purposes.
     */
    public function purposes(): Purposes;
}
