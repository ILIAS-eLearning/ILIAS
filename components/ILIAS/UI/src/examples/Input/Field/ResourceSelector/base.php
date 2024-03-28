<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\ResourceSelector;

use ILIAS\UI\Component\Input\Field\ResourceRetrieval;
use ILIAS\UI\Component\Input\Field\ResourceFactory;
use ILIAS\UI\Component\Symbol\Glyph\Factory as GlyphFactory;

/**
 * Base example showing how the resource selector is used within a form.
 */
function base(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->resourceSelector(
        getResourceRetrieval(),
        "select single resource",
        "choose from one of the resources using the modal selector.",
    );

    return 'not implemented.';
}

/**
 * Returns a very basic implementation of ResourceRetrieval which will not
 * work in a productive environment.
 */
function getResourceRetrieval(): ResourceRetrieval
{
    return new class () implements ResourceRetrieval {
        public function __construct()
        {
        }
        public function getResources(
            ResourceFactory $resource_factory,
            GlyphFactory $icon_factory,
            ?string $parent_id = null,
        ): \Generator {
            if (null === $parent_id) {
                yield $resource_factory->container(
                    '1',
                    'root',
                    null,
                    $resource_factory->container('2', 'category 1')->withRenderChildrenAsync(true),
                    $resource_factory->resource('3', 'resource 1'),
                );
            } else {
                yield $resource_factory->container("$parent_id.1", "sub category 1")->withRenderChildrenAsync(true);
                yield $resource_factory->resource("$parent_id.2", "sub resource 1");
            }
        }
    };
}
