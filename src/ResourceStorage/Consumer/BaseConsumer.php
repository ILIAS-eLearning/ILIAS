<?php declare(strict_types=1);

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
 *********************************************************************/
 
namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;

/**
 * Class BaseConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
abstract class BaseConsumer implements DeliveryConsumer
{
    use GetRevisionTrait;

    protected \ILIAS\ResourceStorage\StorageHandler\StorageHandler $storage_handler;
    protected \ILIAS\ResourceStorage\Resource\StorableResource $resource;
    protected ?int $revision_number = null;
    protected \ILIAS\ResourceStorage\Policy\FileNamePolicy $file_name_policy;
    protected string $file_name = '';

    /**
     * DownloadConsumer constructor.
     */
    public function __construct(
        StorableResource $resource,
        StorageHandler $storage_handler,
        FileNamePolicy $file_name_policy
    ) {
        $this->resource = $resource;
        $this->storage_handler = $storage_handler;
        $this->file_name_policy = $file_name_policy;
        $this->file_name = $resource->getCurrentRevision()->getInformation()->getTitle();
    }

    abstract public function run() : void;

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number) : DeliveryConsumer
    {
        $this->revision_number = $revision_number;
        return $this;
    }

    public function overrideFileName(string $file_name) : DeliveryConsumer
    {
        $this->file_name = $file_name;
        return $this;
    }
}
