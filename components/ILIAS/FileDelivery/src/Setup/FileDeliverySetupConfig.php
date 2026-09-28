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

namespace ILIAS\FileDelivery\Setup;

use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\Setup\Config;

/**
 * Setup configuration for the FileDelivery component.
 *
 * Currently used to enable the IRSS User Content Isolation feature
 * (delivery of user content via a dedicated content domain) at install
 * or update time. The values are written to a static PHP artefact and
 * read at runtime without DB access.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final readonly class FileDeliverySetupConfig implements Config
{
    private bool $isolation_activated;
    private ?string $isolation_content_domain;

    public function __construct(
        bool $isolation_activated = false,
        ?string $isolation_content_domain = null,
    ) {
        // normalize to a bare origin using the same rules the runtime enforces
        $content = IsolationConfig::normalizeOrigin($isolation_content_domain);

        // The ILIAS domain is no longer configured here: it is derived from the
        // installed http_path at build time (see IsolationObjective). Only the
        // content domain is admin-provided and therefore validated here.
        if ($isolation_activated && $content === null) {
            throw new \InvalidArgumentException(
                'IRSS User Content Isolation requires a valid content domain '
                . '(a bare host like "content.example.org" or a "scheme://host[:port]" '
                . 'origin, no userinfo/path/query). Given: '
                . var_export($isolation_content_domain, true)
            );
        }

        $this->isolation_activated = $isolation_activated;
        $this->isolation_content_domain = $content;
    }

    public function isIsolationActivated(): bool
    {
        return $this->isolation_activated;
    }

    public function getIsolationContentDomain(): ?string
    {
        return $this->isolation_content_domain;
    }
}
