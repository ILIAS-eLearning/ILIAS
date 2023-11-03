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

namespace ILIAS\LTI\ToolProvider\Content;

/**
 * Class to represent an LTI assignment content-item object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class LtiAssignmentItem extends LtiLinkItem
{
    /**
     * Class constructor.
     *
     * @param Placement[]|Placement $placementAdvices  Array of Placement objects (or single placement object) for item (optional)
     * @param string $id   URL of content-item (optional)
     */
    public function __construct($placementAdvices = null, ?string $id = null)
    {
        Item::__construct(Item::TYPE_LTI_ASSIGNMENT, $placementAdvices, $id);
        $this->setMediaType(Item::LTI_ASSIGNMENT_MEDIA_TYPE);
    }
}
