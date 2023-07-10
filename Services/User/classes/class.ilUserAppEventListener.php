<?php

declare(strict_types=1);

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
    public static function handleEvent(string $component, string $event, array $parameter): void
    {
        global $DIC;

        $db = $DIC['ilDB'];
        $tree = $DIC['tree'];
        $rbac_review = $DIC['rbacreview'];
        $settings = $DIC['ilSetting'];
        $user = $DIC['ilUser'];
        $user_starting_point_repository = new ilUserStartingPointRepository(
            $user,
            $db,
            $tree,
            $rbac_review,
            $settings
        );

        if ('Services/Object' === $component && 'beforeDeletion' === $event) {
            if (isset($parameter['object']) && $parameter['object'] instanceof ilObjRole) {
                $user_starting_point_repository->onRoleDeleted($parameter['object']);
            }
        }

        if ('Services/TermsOfService' === $component && ilTermsOfServiceEventWithdrawn::class === $event) {
            $user = $parameter['event']->getUser();

            $defaultAuth = ilAuthUtils::AUTH_LOCAL;
            if ($settings->get('auth_mode')) {
                $defaultAuth = $settings->get('auth_mode');
            }
            $isLdapUser = (
                $user->getAuthMode() == ilAuthUtils::AUTH_LDAP ||
                ($user->getAuthMode() === 'default' && $defaultAuth == ilAuthUtils::AUTH_LDAP)
            );

            if ($isLdapUser) {
                $mail = new ilTermsOfServiceWithdrawnMimeMail();
                $mail->setAdditionalInformation(['user' => $user]);
                $mail->setRecipients([$settings->get('admin_mail')]);
                $mail->send();
            } elseif ($settings->get('tos_withdrawal_usr_deletion', "0")) {
                $user->delete();
            }
        }
    }
}
