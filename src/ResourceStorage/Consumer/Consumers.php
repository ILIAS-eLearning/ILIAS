<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class Consumers
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\ResourceStorage\Consumer
 */
class Consumers
{

    private \ILIAS\ResourceStorage\Consumer\ConsumerFactory $consumer_factory;
    private \ILIAS\ResourceStorage\Resource\ResourceBuilder $resource_builder;

    /**
     * Consumers constructor.
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
