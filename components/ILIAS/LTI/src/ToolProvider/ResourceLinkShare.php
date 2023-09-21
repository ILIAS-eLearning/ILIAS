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

namespace ILIAS\LTI\ToolProvider;

/**
 * Class to represent a platform resource link share
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ResourceLinkShare
{
    /**
     * Consumer name value.
     *
     * @var string|null $consumerName
     */
    public ?string $consumerName = null;

    /**
     * Resource link ID value.
     *
     * @var string|null $resourceLinkId
     */
    public ?string $resourceLinkId = null;

    /**
     * Title of sharing context.
     *
     * @var string|null $title
     */
    public ?string $title = null;

    /**
     * Whether sharing request is to be automatically approved on first use.
     *
     * @var bool|null $approved
     */
    public ?bool $approved = null;

    /**
     * Class constructor.
     */
    public function __construct()
    {
    }
}
