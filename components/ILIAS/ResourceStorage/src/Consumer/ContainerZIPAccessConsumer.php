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
use ILIAS\Filesystem\Util\Archive\ZipDirectoryHandling;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
class ContainerZIPAccessConsumer implements ContainerConsumer
{
    use GetRevisionTrait;

    private \ILIAS\Filesystem\Util\Archive\Archives $archives;
    protected ?int $revision_number = null;
    private StorableResource $resource;
    private StreamAccess $stream_access;

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(StorableContainerResource $resource, StreamAccess $stream_access)
    {
        global $DIC;
        $this->resource = $resource;
        $this->archives = $DIC->archives();
        $this->stream_access = $stream_access;
    }

    public function getZIP(UnzipOptions $unzip_options = null): Unzip
    {
        $revision = $this->getRevision();
        $revision = $this->stream_access->populateRevision($revision);
        $zip_stream = $revision->maybeStreamResolver()?->getStream();

        return $this->archives->unzip($zip_stream, $unzip_options);
    }
}
