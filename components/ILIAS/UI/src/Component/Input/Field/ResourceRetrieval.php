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
 */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Symbol\Glyph\Factory as IconFactory;
use ILIAS\UI\Component\Input\Field\ResourceFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
interface ResourceRetrieval
{
    /**
     * Please note that resource retrievals need to be created by the UI framework
     * for asynchronous requests. Until we have proper auto-wiring, the constructor
     * signature needs to be empty.
     */
    public function __construct();

    /**
     * This method will be called by the resource selector input to generate the
     * hierarchical dataset displayed on the client.
     *
     * Resource containers can be rendered asynchronously, in which case this
     * method will be called with the id this container represents. This could be
     * a composite or actual key. If a parent id is provided, this method is
     * expected to load child-resources of the container, which can again contain
     * asynchronous collections. A request like this will be triggered when a
     * resource container is opened on the client.
     *
     * @return \Generator<Resource>
     */
    public function getResources(
        ResourceFactory $resource_factory,
        IconFactory $icon_factory,
        ?string $parent_id = null,
    ): \Generator;
}
