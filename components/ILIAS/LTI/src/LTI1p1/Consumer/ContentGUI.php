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

namespace ILIAS\LTI\LTI1p1\Consumer;

final class ContentGUI
{
    public static function renderStartButton(
        \ilObjLTIConsumer $object,
        \ilLTIConsumerContentGUI $guiObject,
        \ILIAS\DI\Container $dic,
        \ilLanguage $lng
    ): string {
        $logger = \ilLoggerFactory::getLogger('lti');
        $logger->info('LTI1p1 renderStartButton: ref_id=' . $object->getRefId() . ' obj_id=' . $object->getId());

        if (
            $object->getOfflineStatus() ||
            $object->isLaunchMethodEmbedded() ||
            $object->getProvider()->getAvailability() == \ilLTIConsumeProvider::AVAILABILITY_NONE
        ) {
            $logger->info('LTI1p1 renderStartButton: skipped (offline, embedded or provider unavailable)');
            return "";
        }

        $cmixUser = new \ilCmiXapiUser(
            $object->getId(),
            $dic->user()->getId(),
            $object->getProvider()->getPrivacyIdent()
        );
        $user_ident = $cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = \ilCmiXapiUser::getIdent($object->getProvider()->getPrivacyIdent(), $dic->user());
            $cmixUser->setUsrIdent($user_ident);
            $cmixUser->save();
            $logger->info('LTI1p1 renderStartButton: created new cmix user identity for usr_id=' . $dic->user()->getId());
        }

        $ilLTIConsumerLaunch = new \ilLTIConsumerLaunch($object->getRefId());
        $context = $ilLTIConsumerLaunch->getContext();
        $contextType = $ilLTIConsumerLaunch::getLTIContextType($context["type"]);
        $contextId = (string) $context["id"];
        $contextTitle = $context["title"];

        $token = \ilCmiXapiAuthToken::fillToken(
            $dic->user()->getId(),
            $object->getRefId(),
            $object->getId()
        );

        $returnUrl = !$object->isLaunchMethodOwnWin() ? '' : str_replace(
            '&amp;',
            '&',
            \ilObjLTIConsumer::getIliasHttpPath() . "/" . $dic->ctrl()->getLinkTarget($guiObject, "", "", false)
        );

        $logger->info('LTI1p1 renderStartButton: building launch parameters for context_type=' . $contextType . ' context_id=' . $contextId);

        $launchParameters = $object->buildLaunchParameters(
            $cmixUser,
            $token,
            $contextType,
            $contextId,
            $contextTitle,
            $returnUrl
        );

        $logger->info('LTI1p1 renderStartButton: launch parameters built (' . count($launchParameters) . ' fields), rendering form');

        $target = $object->getLaunchMethod() == "newWin" ? "_blank" : "_self";
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $lng->txt("show_content") . '" />';
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $object->getProvider()->getProviderUrl() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        foreach ($launchParameters as $field => $value) {
            $output .= sprintf(
                '<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars((string) $field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            ) . "\n";
        }
        $output .= $button;
        $output .= '</form>';
        $output .= '<span id ="lti_launched" style="display:none">' . $lng->txt("launched") . '</span>';
        $output .= '<script type="text/javascript">
        function ltilaunch() {
            document.lti_launch_form.submit();
            document.getElementById("lti_launch_form").style.display = "none";
            document.getElementById("lti_launched").style.display = "inline";
        }</script>';
        return $output;
    }

    public static function resolveLaunchParameters(
        \ilObjLTIConsumer $object,
        \ilCmiXapiUser $cmixUser,
        \ILIAS\DI\Container $dic
    ): array {
        $logger = \ilLoggerFactory::getLogger('lti');
        $logger->info('LTI1p1 resolveLaunchParameters: ref_id=' . $object->getRefId() . ' obj_id=' . $object->getId());

        $ilLTIConsumerLaunch = new \ilLTIConsumerLaunch($object->getRefId());
        $launchContext = $ilLTIConsumerLaunch->getContext();

        $launchContextType = $ilLTIConsumerLaunch::getLTIContextType($launchContext["type"]);
        $launchContextId = (string) $launchContext["id"];
        $launchContextTitle = $launchContext["title"];

        $token = \ilCmiXapiAuthToken::fillToken(
            $dic->user()->getId(),
            $object->getRefId(),
            $object->getId()
        );

        $params = $object->buildLaunchParameters(
            $cmixUser,
            $token,
            $launchContextType,
            $launchContextId,
            $launchContextTitle
        );

        $logger->info('LTI1p1 resolveLaunchParameters: resolved ' . count($params) . ' launch parameters');

        return $params;
    }
}
