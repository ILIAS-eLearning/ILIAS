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

/**
 * Class ilUserAppEventListener
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilUserAppEventListener implements ilAppEventListener
{
    /**
     * @param array<string,mixed>  $a_parameter
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
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

            $defaultAuth = ilAuthUtils::AUTH_LOCAL;
            if ($DIC['ilSetting']->get('auth_mode')) {
                $defaultAuth = $DIC['ilSetting']->get('auth_mode');
            }
            $isLdapUser = (
                $user->getAuthMode() == ilAuthUtils::AUTH_LDAP ||
                ($user->getAuthMode() === 'default' && $defaultAuth == ilAuthUtils::AUTH_LDAP)
            );

            if ($isLdapUser) {
                $mail = new ilTermsOfServiceWithdrawnMimeMail();
                $mail->setAdditionalInformation(['user' => $user]);
                $mail->setRecipients([$DIC->settings()->get('admin_mail')]);
                $mail->send();
            } elseif ($DIC->settings()->get('tos_withdrawal_usr_deletion', "0")) {
                $user->delete();
            }
        }
    }
}
