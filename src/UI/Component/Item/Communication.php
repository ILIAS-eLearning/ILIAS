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
 *
 *********************************************************************/

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;

/**
 * Interface Notification
 * @package ILIAS\UI\Component\Item
 */
interface Communication extends Item
{
    /**
     * Set icon as lead
     */
    public function withLeadIcon(Icon $icon) : Communication;

    /**
     * Set avatar as lead
     */
    public function withLeadAvatar(Avatar $avatar) : Communication;

    /**
     * Get lead Icon or Avatar
     * @return null|Icon|Avatar
     */
    public function getLead();
}
