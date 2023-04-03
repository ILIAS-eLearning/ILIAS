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
namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MetaBarItemFactory
 * This factory provides you all available types for MainMenu GlobalScreen Items.
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarItemFactory
{
    /**
     * @param IdentificationInterface $identification
     * @return TopParentItem
     */
    public function topParentItem(IdentificationInterface $identification) : TopParentItem
    {
        return new TopParentItem($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return TopLegacyItem
     */
    public function topLegacyItem(IdentificationInterface $identification) : TopLegacyItem
    {
        return new TopLegacyItem($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return LinkItem
     */
    public function linkItem(IdentificationInterface $identification) : LinkItem
    {
        return new LinkItem($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return TopLinkItem
     */
    public function topLinkItem(IdentificationInterface $identification) : TopLinkItem
    {
        return new TopLinkItem($identification);
    }

    /**
     * @param IdentificationInterface $identification
     * @return NotificationCenter
     */
    public function notificationCenter(IdentificationInterface $identification) : NotificationCenter
    {
        static $created;
        if ($created === true) {
            // I currently disabled this since we have unresolved problems in https://mantis.ilias.de/view.php?id=26374
            // throw new \LogicException("only one NotificationCenter can exist");
        }
        $created = true;

        return new NotificationCenter($identification);
    }
}
