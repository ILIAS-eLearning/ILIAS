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

use ceLTIc\LTI\OAuth\OAuthConsumer;
use ceLTIc\LTI\OAuth\OAuthRequest;
use ceLTIc\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;

/**
 * Builds the parameters of an LTI 1.1 launch and signs them with OAuth1, using the credentials of the
 * provider or the ones set on the object.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final class ilLTI1p1ConsumerLaunchParameterBuilder
{
    /**
     * OAuth1 consumer key: per object when the provider allows customizing it, global otherwise.
     */
    public static function resolveLaunchKey(ilLTITool $tool, string $custom_launch_key): string
    {
        if ($tool->isKeyCustomizable()) {
            return $custom_launch_key;
        }

        return $tool->getKey();
    }

    public static function resolveLaunchSecret(ilLTITool $tool, string $custom_launch_secret): string
    {
        if ($tool->isKeyCustomizable()) {
            return $custom_launch_secret;
        }

        return $tool->getSecret();
    }

    /**
     * @throws ilWACException
     * @throws Exception
     */
    public static function build(
        ilLTITool $tool,
        int $ref_id,
        int $obj_id,
        string $title,
        string $description,
        string $launch_method,
        string $launch_key,
        string $launch_secret,
        array $custom_params_array,
        ilCmiXapiUser $cmix_user,
        string $token,
        string $context_type,
        string $context_id,
        string $context_title,
        ?string $return_url = ''
    ): array {
        global $DIC;
        /* @var ILIAS\DI\Container $DIC */
        $DIC->user()->setExternalAccount($cmix_user->getUsrIdent());

        $roles = $DIC->access()->checkAccess('write', '', $ref_id) ? "Instructor" : "Learner";
        if ($tool->getAlwaysLearner()) {
            $roles = "Learner";
        }

        $resource_link_id = $ref_id;
        if ($tool->getUseToolId()) {
            $resource_link_id = 'p' . $tool->getId();
        }

        $usr_image = '';
        if ($tool->getIncludeUserPicture()) {
            $usr_image = ilObjLTITool::getIliasHttpPath() . "/" . $DIC->user()->getPersonalPicturePath();
        }

        $document_target = "window";
        if ($launch_method == ilObjLTITool::LAUNCH_METHOD_EMBEDDED) {
            $document_target = "iframe";
        }

        $name_given = '-';
        $name_family = '-';
        $name_full = '-';
        switch ($tool->getPrivacyName()) {
            case ilLTITool::PRIVACY_NAME_FIRSTNAME:
                $name_given = $DIC->user()->getFirstname();
                $name_full = $DIC->user()->getFirstname();
                break;
            case ilLTITool::PRIVACY_NAME_LASTNAME:
                $usr_name = $DIC->user()->getUTitle() ? $DIC->user()->getUTitle() . ' ' : '';
                $usr_name .= $DIC->user()->getLastname();
                $name_family = $usr_name;
                $name_full = $usr_name;
                break;
            case ilLTITool::PRIVACY_NAME_FULLNAME:
                $name_given = $DIC->user()->getFirstname();
                $name_family = $DIC->user()->getLastname();
                $name_full = $DIC->user()->getFullname();
                break;
        }

        $user_id_lti = ilCmiXapiUser::getIdentAsId($tool->getPrivacyIdent(), $DIC->user());

        $email_primary = $cmix_user->getUsrIdent();
        if ($tool->getPrivacyIdent() == ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
            $user_id_lti = strstr($email_primary, '@' . ilCmiXapiUser::getIliasUuid(), true);
        }

        ilLTI1p1ConsumerResult::getByKeys($obj_id, $DIC->user()->getId(), true);

        $tool_custom_params = ilObjLTITool::getToolCustomParamsArray($tool);
        $merged_params = array_merge($tool_custom_params, $custom_params_array);

        $tool_consumer_instance_guid = CLIENT_ID . ".";
        $parse_ilias_url = parse_url(ilObjLTITool::getIliasHttpPath());
        if (array_key_exists("path", $parse_ilias_url)) {
            $tool_consumer_instance_guid .= implode(".", array_reverse(explode("/", $parse_ilias_url["path"])));
        }
        $tool_consumer_instance_guid .= $parse_ilias_url["host"];

        $launch_vars = [
            "lti_message_type" => "basic-lti-launch-request",
            "lti_version" => "LTI-1p0",
            "resource_link_id" => $resource_link_id,
            "resource_link_title" => $title,
            "resource_link_description" => $description,
            "user_id" => $user_id_lti,
            "user_image" => $usr_image,
            "roles" => $roles,
            "lis_person_name_given" => $name_given,
            "lis_person_name_family" => $name_family,
            "lis_person_name_full" => $name_full,
            "lis_person_contact_email_primary" => $email_primary,
            "context_id" => $context_id,
            "context_title" => $context_title,
            "context_label" => $context_type . " " . $context_id,
            "launch_presentation_locale" => $DIC->language()->getLangKey(),
            "launch_presentation_document_target" => $document_target,
            "launch_presentation_return_url" => $return_url,
            "tool_consumer_instance_guid" => $tool_consumer_instance_guid,
            "tool_consumer_instance_name" => $DIC->settings()->get("short_inst_name") ? $DIC->settings()->get(
                "short_inst_name"
            ) : CLIENT_ID,
            "tool_consumer_instance_description" => ilObjSystemFolder::_getHeaderTitle(),
            "tool_consumer_instance_url" => ilLink::_getLink(ROOT_FOLDER_ID, "root"),
            "tool_consumer_instance_contact_email" => $DIC->settings()->get("admin_email"),
            "launch_presentation_css_url" => "",
            "tool_consumer_info_product_family_code" => "ilias",
            "tool_consumer_info_version" => ILIAS_VERSION,
            "lis_result_sourcedid" => $token,
            "lis_outcome_service_url" => ilObjLTITool::getIliasHttpPath() . "/ltiresult.php?client_id=" . CLIENT_ID
        ];

        $oauth_params = [
            "url" => $tool->getUrl(),
            "key" => $launch_key,
            "secret" => $launch_secret,
            "callback" => "about:blank",
            "http_method" => "POST",
            "sign_method" => "HMAC_SHA1",
            "token" => null,
            "data" => ($launch_vars + $merged_params)
        ];

        return self::signOAuth($oauth_params);
    }

    /**
     * Signs the launch data with OAuth 1.
     *
     * @param array $a_params sign_method, key, secret, token, callback, http_method, url and data
     * @return array
     * @throws Exception
     */
    public static function signOAuth(array $a_params): array
    {
        $method = match ($a_params['sign_method']) {
            "HMAC_SHA1" => new OAuthSignatureMethod_HMAC_SHA1(),
            default => throw new Exception("Unknown signature method: " . $a_params['sign_method']),
        };

        $consumer = new OAuthConsumer($a_params["key"], $a_params["secret"], $a_params["callback"]);
        $request = OAuthRequest::from_consumer_and_token($consumer, $a_params["token"], $a_params["http_method"], $a_params["url"], $a_params["data"]);
        $request->sign_request($method, $consumer, $a_params["token"]);

        return $request->get_parameters();
    }
}
