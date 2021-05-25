<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/**
 * Class Consumers
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\ResourceStorage\Consumer
 */
class Consumers
{

    /**
     * @var ConsumerFactory
     */
    private $consumer_factory;
    /**
     * @var ResourceBuilder
     */
    private $resource_builder;

    /**
     * Consumers constructor.
     * @param ConsumerFactory $cf
     * @param ResourceBuilder $r
     */
    public function __construct(
        ConsumerFactory $cf,
        ResourceBuilder $r
    ) {
        $this->consumer_factory = $cf;
        $this->resource_builder = $r;
    }

    public function download(ResourceIdentification $identification) : DownloadConsumer
    {
        return $this->consumer_factory->download($this->resource_builder->get($identification));
    }

    public function inline(ResourceIdentification $identification) : InlineConsumer
    {
        return $this->consumer_factory->inline($this->resource_builder->get($identification));
    }

    public function stream(ResourceIdentification $identification) : FileStreamConsumer
    {
        return $this->consumer_factory->fileStream($this->resource_builder->get($identification));
    }

    public function src(ResourceIdentification $identification) : SrcConsumer
    {
        return $this->consumer_factory->src($this->resource_builder->get($identification));
    }
}
