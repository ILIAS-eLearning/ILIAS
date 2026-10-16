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

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Util\Archive\Archives;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\Data\URI;
use ILIAS\FileDelivery\Delivery\StreamDelivery;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class ContainerURIConsumer implements ContainerConsumer
{
    use GetRevisionTrait;

    private Archives $archives;
    protected ?int $revision_number = null;
    private bool $isolated = true;

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(
        private SrcBuilder $src_builder,
        private StorableResource $resource,
        private StreamAccess $stream_access,
        private string $start_file,
        private float $valid_for_at_least_minutes = 60.0
    ) {
        global $DIC;
        $this->archives = $DIC->archives();
    }

    /**
     * Delivers the container from the ILIAS domain even when User Content Isolation
     * is active, for content that must run on the same origin as ILIAS (e.g. SCORM
     * packages, which call the API of the player synchronously). Such content does
     * not profit from the isolation.
     *
     * @deprecated We do not want content outside of the User Content Isolation; this only exists
     *             until the remaining consumers (e.g. SCORM) run on the content domain and
     *             will be removed with ILIAS 13. Calls are reported by the code rule
     *             `ilias.deliveryWithoutIsolation` and must be allowed explicitly, see
     *             scripts/PHPStan/README.md.
     */
    public function withoutIsolation(): self
    {
        $clone = clone $this;
        $clone->isolated = false;
        return $clone;
    }

    public function getURI(): ?URI
    {
        $filename = basename($this->start_file);
        if ($filename === '') {
            $filename = null;
        }

        $startfile = urlencode($this->start_file); // e.g. for files with " " (spaces)
        $startfile = str_replace(urlencode("/"), "/", $startfile); // we must keep directory sepparators

        $revision = $this->stream_access->populateRevision($this->getRevision());
        $uri_string = (
            $this->isolated
            ? $this->src_builder->getRevisionURL($revision, true, $this->valid_for_at_least_minutes, $filename)
            : $this->src_builder->getRevisionURLWithoutIsolation($revision, $this->valid_for_at_least_minutes, $filename)
        ) . StreamDelivery::SUBREQUEST_SEPARATOR . $startfile;

        try {
            return new URI($uri_string);
        } catch (\Throwable) {
            return null;
        }
    }
}
