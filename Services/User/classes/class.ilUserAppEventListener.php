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

            /** @var ilObjUser $user */
            $user = $a_parameter['event']->getUser();

            $defaultAuth = AUTH_LOCAL;
            if ($DIC['ilSetting']->get('auth_mode')) {
                $defaultAuth = $DIC['ilSetting']->get('auth_mode');
            }
            $isLdapUser = (
                $user->getAuthMode() == AUTH_LDAP ||
                ($user->getAuthMode() === 'default' && $defaultAuth == AUTH_LDAP)
            );

            if ($isLdapUser) {
                $mail = new ilTermsOfServiceWithdrawnMimeMail();
                $mail->setAdditionalInformation(['user' => $user]);
                $mail->setRecipients([$DIC->settings()->get('admin_mail')]);
                $mail->send();
            } elseif ((bool) $DIC->settings()->get('tos_withdrawal_usr_deletion', false)) {
                $user->delete();
            }
        }
    }
}
