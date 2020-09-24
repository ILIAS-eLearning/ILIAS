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

        if ('Services/TermsOfService' === $a_component && ilTermsOfServiceEventWithdrawn::class === $a_event) {
            global $DIC;

            $shouldDeleteAccountOnWithdrawal = $DIC->settings()->get(
                'tos_withdrawal_usr_deletion',
                false
            );

            $user = new ilObjUser($a_parameter['event']->getUsrId());
            if($shouldDeleteAccountOnWithdrawal == 1 &&
                ($user->getAuthMode() == AUTH_LOCAL || $user->getAuthMode() == 'default')
            ) {
                $user->delete();
            }
        }
    }
}
