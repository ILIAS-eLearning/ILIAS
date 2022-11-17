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

use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;

/**
 * Class Consumers
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\ResourceStorage\Consumer
 */
class Consumers
{
    private ConsumerFactory $consumer_factory;
    private ResourceBuilder $resource_builder;
    private CollectionBuilder $collection_builder;
    private ?SrcBuilder $src_builder = null;

    /**
     * Consumers constructor.
     */
    public function __construct(
        ConsumerFactory $consumer_factory,
        ResourceBuilder $resource_builder,
        CollectionBuilder $collection_builder,
        ?SrcBuilder $src_builder = null
    ) {
        $this->consumer_factory = $consumer_factory;
        $this->resource_builder = $resource_builder;
        $this->collection_builder = $collection_builder;
        $this->src_builder = $src_builder ?? new InlineSrcBuilder();
    }

    public function download(ResourceIdentification $identification): DownloadConsumer
    {
        return $this->consumer_factory->download($this->resource_builder->get($identification));
    }

    public function inline(ResourceIdentification $identification): InlineConsumer
    {
        return $this->consumer_factory->inline($this->resource_builder->get($identification));
    }

    public function stream(ResourceIdentification $identification): FileStreamConsumer
    {
        return $this->consumer_factory->fileStream($this->resource_builder->get($identification));
    }

    public function src(ResourceIdentification $identification): SrcConsumer
    {
        return $this->consumer_factory->src($this->resource_builder->get($identification), $this->src_builder);
    }

    public function downloadCollection(
        ResourceCollectionIdentification $identification,
        ?string $zip_filename = null
    ): DownloadMultipleConsumer {
        return $this->downloadResources(
            iterator_to_array($this->collection_builder->getResourceIds($identification)),
            $zip_filename
        );
    }

    public function downloadResources(
        array $identifications,
        ?string $zip_filename = null
    ): DownloadMultipleConsumer {
        $resources = [];
        foreach ($identifications as $rid) {
            if (!$rid instanceof ResourceIdentification) {
                throw new \InvalidArgumentException('Expected ResourceIdentification');
            }
            $resources[] = $this->resource_builder->get($rid);
        }

        return $this->consumer_factory->downloadMultiple(
            $resources,
            $zip_filename
        );
    }
}
