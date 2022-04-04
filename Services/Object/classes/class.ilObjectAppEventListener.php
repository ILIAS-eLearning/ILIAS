<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjectAppEventListener implements ilAppEventListener
{
    /**
     * @inheritdoc
     */
    public static function handleEvent(string $component, string $event, array $parameter) : void
    {
        global $DIC;

        if ('Services/Object' === $component && 'beforeDeletion' === $event) {
            /** @var \ilObjectCustomIconFactory  $customIconFactory */
            $customIconFactory = $DIC['object.customicons.factory'];
            $customIcon = $customIconFactory->getByObjId(
                $parameter['object']->getId(),
                $parameter['object']->getType()
            );
            $customIcon->delete();
        }
    }
}
