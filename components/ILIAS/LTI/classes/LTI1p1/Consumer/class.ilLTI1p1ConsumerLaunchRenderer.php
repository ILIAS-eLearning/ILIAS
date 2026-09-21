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

/**
 * Renders the three ways of launching an LTI 1.1 object: the launch page, the embedded iframe and the
 * start button that submits the launch form.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTI1p1ConsumerLaunchRenderer
{
    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    public static function renderLaunch(
        ilObjLTIConsumer $object,
        ilLTIConsumerContentGUI $gui_object,
        ILIAS\DI\Container $dic,
        ilLanguage $lng
    ): void {
        $logger = $dic->logger()->forComponent('lti');
        $logger->info('LTI1p1 renderLaunch: ref_id=' . $object->getRefId() . ' launch_method=' . $object->getLaunchMethod());

        if ($object->isLaunchMethodEmbedded()) {
            $tpl = new ilTemplate('tpl.lti1p1_content.html', true, true, 'components/ILIAS/LTI');
            $tpl->setVariable("EMBEDDED_IFRAME_SRC", $dic->ctrl()->getLinkTarget(
                $gui_object,
                ilLTIConsumerContentGUI::CMD_SHOW_EMBEDDED
            ));
            $dic->ui()->mainTemplate()->setContent($tpl->get());
        } else {
            $dic->toolbar()->addText(self::renderStartButton($object, $gui_object, $dic, $lng));
        }
    }

    /**
     * Sends the auto-submitting launch page for the iframe and ends the request.
     * @throws ilTemplateException
     */
    public static function renderEmbeddedLaunch(
        ilObjLTIConsumer $object,
        ilCmiXapiUser $cmix_user,
        ILIAS\DI\Container $dic
    ): never {
        $logger = $dic->logger()->forComponent('lti');
        $logger->info('LTI1p1 renderEmbeddedLaunch: ref_id=' . $object->getRefId() . ' obj_id=' . $object->getId());

        $tpl = new ilTemplate('tpl.lti1p1_embedded.html', true, true, 'components/ILIAS/LTI');
        $tpl->setVariable('LANG', $dic->language()->getLangKey());
        $tpl->setVariable('TITLE', htmlspecialchars($object->getTitle(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        foreach (self::resolveLaunchParameters($object, $cmix_user, $dic) as $field => $value) {
            $tpl->setCurrentBlock('launch_parameter');
            $tpl->setVariable('LAUNCH_PARAMETER', htmlspecialchars((string) $field, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $tpl->setVariable('LAUNCH_PARAM_VALUE', htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $tpl->parseCurrentBlock();
        }

        $v = DEVMODE ? '?vers=' . time() : '?vers=' . ILIAS_VERSION_NUMERIC;
        $tpl->setVariable("DELOS_CSS_HREF", 'assets/css/delos.css' . $v);
        $tpl->setVariable("JQUERY_SRC", 'assets/js/jquery.js' . $v);

        $tpl->setVariable("LOADER_ICON_SRC", ilUtil::getImagePath("media/loader.svg"));
        $tpl->setVariable('LAUNCH_URL', $object->getProvider()->getProviderUrl());

        echo $tpl->get();
        exit; //TODO: no exit
    }

    /**
     * @throws ilCtrlException
     */
    public static function renderStartButton(
        ilObjLTIConsumer $object,
        ilLTIConsumerContentGUI $gui_object,
        ILIAS\DI\Container $dic,
        ilLanguage $lng
    ): string {
        $logger = $dic->logger()->forComponent('lti');
        $logger->info('LTI1p1 renderStartButton: ref_id=' . $object->getRefId() . ' obj_id=' . $object->getId());

        if (
            $object->getOfflineStatus() ||
            $object->isLaunchMethodEmbedded() ||
            $object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE
        ) {
            $logger->info('LTI1p1 renderStartButton: skipped (offline, embedded or provider unavailable)');
            return "";
        }

        $cmix_user = new ilCmiXapiUser(
            $object->getId(),
            $dic->user()->getId(),
            $object->getProvider()->getPrivacyIdent()
        );
        $user_ident = $cmix_user->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($object->getProvider()->getPrivacyIdent(), $dic->user());
            $cmix_user->setUsrIdent($user_ident);
            $cmix_user->save();
            $logger->info('LTI1p1 renderStartButton: created new cmix user identity for usr_id=' . $dic->user()->getId());
        }

        $return_url = !$object->isLaunchMethodOwnWin() ? '' : str_replace(
            '&amp;',
            '&',
            ilObjLTIConsumer::getIliasHttpPath() . "/" . $dic->ctrl()->getLinkTarget($gui_object, "", "")
        );

        $launch_parameters = self::resolveLaunchParameters($object, $cmix_user, $dic, $return_url);

        $logger->info('LTI1p1 renderStartButton: launch parameters built (' . count($launch_parameters) . ' fields), rendering form');

        $target = $object->getLaunchMethod() == "newWin" ? "_blank" : "_self";
        $button = '<input class="btn btn-default ilPre" type="button" onClick="ltilaunch()" value = "' . $lng->txt("show_content") . '" />';
        $output = '<form id="lti_launch_form" name="lti_launch_form" action="' . $object->getProvider()->getProviderUrl() . '" method="post" target="' . $target . '" encType="application/x-www-form-urlencoded">';
        foreach ($launch_parameters as $field => $value) {
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
        ilObjLTIConsumer $object,
        ilCmiXapiUser $cmix_user,
        ILIAS\DI\Container $dic,
        string $return_url = ''
    ): array {
        $logger = $dic->logger()->forComponent('lti');
        $logger->info('LTI1p1 resolveLaunchParameters: ref_id=' . $object->getRefId() . ' obj_id=' . $object->getId());

        $lti_consumer_launch = new ilLTI1p1ConsumerLaunchContext($object->getRefId());
        $launch_context = $lti_consumer_launch->getContext();

        $launch_context_type = $lti_consumer_launch::getLTIContextType($launch_context["type"]);
        $launch_context_id = (string) $launch_context["id"];
        $launch_context_title = $launch_context["title"];

        $token = ilCmiXapiAuthToken::fillToken(
            $dic->user()->getId(),
            $object->getRefId(),
            $object->getId()
        );

        $params = $object->buildLaunchParameters(
            $cmix_user,
            $token,
            $launch_context_type,
            $launch_context_id,
            $launch_context_title,
            $return_url
        );

        $logger->info('LTI1p1 resolveLaunchParameters: resolved ' . count($params) . ' launch parameters');

        return $params;
    }
}
