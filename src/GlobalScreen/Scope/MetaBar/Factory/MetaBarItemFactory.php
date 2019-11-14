<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/**
 * Class MetaBarItemFactory
 *
 * This factory provides you all available types for MainMenu GlobalScreen Items.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MetaBarItemFactory
{

    /**
     * @param IdentificationInterface $identification
     *
     * @return TopParentItem
     */
    public function topParentItem(IdentificationInterface $identification) : TopParentItem
    {
        return new TopParentItem($identification);
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return TopLegacyItem
     */
    public function topLegacyItem(IdentificationInterface $identification) : TopLegacyItem
    {
        return new TopLegacyItem($identification);
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return LinkItem
     */
    public function linkItem(IdentificationInterface $identification) : LinkItem
    {
        return new LinkItem($identification);
    }


    /**
     * @param IdentificationInterface $identification
     *
     * @return TopLinkItem
     */
    public function topLinkItem(IdentificationInterface $identification) : TopLinkItem
    {
        return new TopLinkItem($identification);
    }


    /**
     * @param IdentificationInterface $identification
     *
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
