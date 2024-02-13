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

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Resource\StorableContainerResource;
use ILIAS\Filesystem\Util\Archive\Unzip;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class ContainerURIConsumer implements ContainerConsumer
{
    use GetRevisionTrait;

    private \ILIAS\Filesystem\Util\Archive\Archives $archives;
    protected ?int $revision_number = null;
    private StorableResource $resource;
    private StreamAccess $stream_access;

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(
        private SrcBuilder $src_builder,
        StorableContainerResource $resource,
        StreamAccess $stream_access,
        private string $start_file,
        private float $valid_for_at_least_minutes = 60.0
    )
    {
        global $DIC;
        $this->resource = $resource;
        $this->archives = $DIC->archives();
        $this->stream_access = $stream_access;
    }

    public function getURI(): URI
    {
        $uri_string = $this->src_builder->getRevisionURL(
                $this->stream_access->populateRevision($this->getRevision()),
                true, 60,
                $this->valid_for_at_least_minutes
            ) . '/' . $this->start_file;

        return new URI($uri_string);
    }
}
