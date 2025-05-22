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

    public function getURI(): ?URI
    {
        $filename = basename($this->start_file);
        if ($filename === '') {
            $filename = null;
        }

        $startfile = urlencode($this->start_file); // e.g. for files with " " (spaces)
        $startfile = str_replace(urlencode("/"), "/", $startfile); // we must keep directory sepparators

        $uri_string = $this->src_builder->getRevisionURL(
            $this->stream_access->populateRevision($this->getRevision()),
            true,
            $this->valid_for_at_least_minutes,
            $filename
        ) . StreamDelivery::SUBREQUEST_SEPARATOR . $startfile;

        try {
            return new URI($uri_string);
        } catch (\Throwable) {
            return null;
        }
    }
}
