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

use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class BaseConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
abstract class BaseConsumer implements DeliveryConsumer
{
    use GetRevisionTrait;

    protected ?int $revision_number = null;
    protected string $file_name = '';

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(
        protected StorableResource $resource,
        protected StreamAccess $stream_access,
        protected FileNamePolicy $file_name_policy
    ) {
        $this->file_name = $this->resource->getCurrentRevision()->getInformation()->getTitle();
    }

    abstract public function run(): void;

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number): DeliveryConsumer
    {
        $this->revision_number = $revision_number;
        return $this;
    }

    public function overrideFileName(string $file_name): DeliveryConsumer
    {
        $this->file_name = $file_name;
        return $this;
    }
}
