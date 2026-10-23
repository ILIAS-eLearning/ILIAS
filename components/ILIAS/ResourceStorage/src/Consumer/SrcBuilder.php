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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Revision\Revision;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface SrcBuilder
{
    /**
     * @param string|null $filename
     * @throw \RuntimeException if signing is not possible or failed, but was requested with $signed = true
     */
    public function getRevisionURL(
        Revision $revision,
        bool $signed = true,
        float $valid_for_at_least_minutes = 60.0,
        ?string $filename = null
    ): string;

    /**
     * Signed URL delivered from the ILIAS domain instead of the content domain of
     * User Content Isolation.
     *
     * @deprecated We do not want content outside of the User Content Isolation; this only exists
     *             until the remaining consumers (e.g. SCORM) run on the content domain and
     *             will be removed with ILIAS 13.
     * @internal Only for {@see \ILIAS\ResourceStorage\Consumer\ContainerURIConsumer::withoutIsolation()},
     *           which is the one way for consumers to deliver without isolation.
     * @throw \RuntimeException if signing is not possible or failed
     */
    public function getRevisionURLWithoutIsolation(
        Revision $revision,
        float $valid_for_at_least_minutes = 60.0,
        ?string $filename = null
    ): string;

    /**
     * @throw \RuntimeException if signing is not possible or failed, but was requested with $signed = true
     */
    public function getFlavourURLs(Flavour $flavour, bool $signed = true): \Generator;
}
