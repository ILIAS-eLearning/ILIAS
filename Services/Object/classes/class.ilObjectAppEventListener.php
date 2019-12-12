<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjectAppEventListener implements \ilAppEventListener
{
    /**
     * @inheritdoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        global $DIC;

        if ('Services/Object' === $a_component && 'beforeDeletion' === $a_event) {
            /** @var \ilObjectCustomIconFactory  $customIconFactory */
            $customIconFactory = $DIC['object.customicons.factory'];
            $customIcon        = $customIconFactory->getByObjId($a_parameter['object']->getId(), $a_parameter['object']->getType());
            $customIcon->delete();
        }
    }
}
