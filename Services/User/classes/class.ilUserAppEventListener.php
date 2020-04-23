<?php declare(strict_types=1);

/**
 * Class ilUserAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAppEventListener implements ilAppEventListener
{
    /**
     * @inheritDoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        if ('Services/Object' === $a_component && 'beforeDeletion' === $a_event) {
            if (isset($a_parameter['object']) && $a_parameter['object'] instanceof ilObjRole) {
                \ilStartingPoint::onRoleDeleted($a_parameter['object']);
            }
        }
    }
}
