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

use ceLTIc\LTI\OAuth\OAuthConsumer;
use ceLTIc\LTI\OAuth\OAuthRequest;
use ceLTIc\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;

final class LaunchParameterBuilder
{
    /**
     * OAuth1 consumer key: per object when the provider allows customizing it, global otherwise.
     */
    public static function resolveLaunchKey(\ilLTIConsumeProvider $provider, string $customLaunchKey): string
    {
        if ($provider->isProviderKeyCustomizable()) {
            return $customLaunchKey;
        }

        return $provider->getProviderKey();
    }

    public static function resolveLaunchSecret(\ilLTIConsumeProvider $provider, string $customLaunchSecret): string
    {
        if ($provider->isProviderKeyCustomizable()) {
            return $customLaunchSecret;
        }

        return $provider->getProviderSecret();
    }

    /**
     * @throws \ilWACException
     * @return array<string, string>
     */
    public static function build(
        \ilLTIConsumeProvider $provider,
        int $refId,
        int $objId,
        string $title,
        string $description,
        string $launchMethod,
        string $launchKey,
        string $launchSecret,
        array $customParamsArray,
        \ilCmiXapiUser $cmixUser,
        string $token,
        string $contextType,
        string $contextId,
        string $contextTitle,
        ?string $returnUrl = ''
    ): array {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        $DIC->user()->setExternalAccount($cmixUser->getUsrIdent());

        $roles = $DIC->access()->checkAccess('write', '', $refId) ? "Instructor" : "Learner";
        if ($provider->getAlwaysLearner() == true) {
            $roles = "Learner";
        }

        $resource_link_id = $refId;
        if ($provider->getUseProviderId() == true) {
            $resource_link_id = 'p' . $provider->getId();
        }

        $usrImage = '';
        if ($provider->getIncludeUserPicture()) {
            $usrImage = \ilObjLTIConsumer::getIliasHttpPath() . "/" . $DIC->user()->getPersonalPicturePath("small");
        }

        $documentTarget = "window";
        if ($launchMethod == \ilObjLTIConsumer::LAUNCH_METHOD_EMBEDDED) {
            $documentTarget = "iframe";
        }

        $nameGiven = '-';
        $nameFamily = '-';
        $nameFull = '-';
        switch ($provider->getPrivacyName()) {
            case \ilLTIConsumeProvider::PRIVACY_NAME_FIRSTNAME:
                $nameGiven = $DIC->user()->getFirstname();
                $nameFull = $DIC->user()->getFirstname();
                break;
            case \ilLTIConsumeProvider::PRIVACY_NAME_LASTNAME:
                $usrName = $DIC->user()->getUTitle() ? $DIC->user()->getUTitle() . ' ' : '';
                $usrName .= $DIC->user()->getLastname();
                $nameFamily = $usrName;
                $nameFull = $usrName;
                break;
            case \ilLTIConsumeProvider::PRIVACY_NAME_FULLNAME:
                $nameGiven = $DIC->user()->getFirstname();
                $nameFamily = $DIC->user()->getLastname();
                $nameFull = $DIC->user()->getFullname();
                break;
        }

        $userIdLTI = \ilCmiXapiUser::getIdentAsId($provider->getPrivacyIdent(), $DIC->user());

        $emailPrimary = $cmixUser->getUsrIdent();
        if ($provider->getPrivacyIdent() == \ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
            $userIdLTI = strstr($emailPrimary, '@' . \ilCmiXapiUser::getIliasUuid(), true);
        }

        \ilLTIConsumerResult::getByKeys($objId, $DIC->user()->getId(), true);

        $provider_custom_params = \ilObjLTIConsumer::getProviderCustomParamsArray($provider);
        $merged_params = array_merge($provider_custom_params, $customParamsArray);

        $toolConsumerInstanceGuid = CLIENT_ID . ".";
        $parseIliasUrl = parse_url(\ilObjLTIConsumer::getIliasHttpPath());
        if (array_key_exists("path", $parseIliasUrl)) {
            $toolConsumerInstanceGuid .= implode(".", array_reverse(explode("/", $parseIliasUrl["path"])));
        }
        $toolConsumerInstanceGuid .= $parseIliasUrl["host"];

        $launch_vars = [
            "lti_message_type" => "basic-lti-launch-request",
            "lti_version" => "LTI-1p0",
            "resource_link_id" => $resource_link_id,
            "resource_link_title" => $title,
            "resource_link_description" => $description,
            "user_id" => $userIdLTI,
            "user_image" => $usrImage,
            "roles" => $roles,
            "lis_person_name_given" => $nameGiven,
            "lis_person_name_family" => $nameFamily,
            "lis_person_name_full" => $nameFull,
            "lis_person_contact_email_primary" => $emailPrimary,
            "context_id" => $contextId,
            "context_title" => $contextTitle,
            "context_label" => $contextType . " " . $contextId,
            "launch_presentation_locale" => $DIC->language()->getLangKey(),
            "launch_presentation_document_target" => $documentTarget,
            "launch_presentation_return_url" => $returnUrl,
            "tool_consumer_instance_guid" => $toolConsumerInstanceGuid,
            "tool_consumer_instance_name" => $DIC->settings()->get("short_inst_name") ? $DIC->settings()->get(
                "short_inst_name"
            ) : CLIENT_ID,
            "tool_consumer_instance_description" => \ilObjSystemFolder::_getHeaderTitle(),
            "tool_consumer_instance_url" => \ilLink::_getLink(ROOT_FOLDER_ID, "root"),
            "tool_consumer_instance_contact_email" => $DIC->settings()->get("admin_email"),
            "launch_presentation_css_url" => "",
            "tool_consumer_info_product_family_code" => "ilias",
            "tool_consumer_info_version" => ILIAS_VERSION,
            "lis_result_sourcedid" => $token,
            "lis_outcome_service_url" => \ilObjLTIConsumer::getIliasHttpPath() . "/ltiresult.php?client_id=" . CLIENT_ID
        ];

        $OAuthParams = [
            "url" => $provider->getProviderUrl(),
            "key" => $launchKey,
            "secret" => $launchSecret,
            "callback" => "about:blank",
            "http_method" => "POST",
            "sign_method" => "HMAC_SHA1",
            "token" => null,
            "data" => ($launch_vars + $merged_params)
        ];

        return self::signOAuth($OAuthParams);
    }

    /**
     * sign request data with OAuth
     *
     * @param array $a_params (    "method => signature methos
     *                    "key" => consumer key
     *                    "secret" => shared secret
     *                    "token"    => request token
     *                    "url" => request url
     *                    data => array (key => value)
     *                )
     *
     * @return array    signed data
     * @throws \Exception
     */
    public static function signOAuth(array $a_params): array
    {
        switch ($a_params['sign_method']) {
            case "HMAC_SHA1":
                $method = new OAuthSignatureMethod_HMAC_SHA1();
                break;
            default:
                throw new \Exception("Unknown signature method: " . $a_params['sign_method']);
        }

        $consumer = new OAuthConsumer($a_params["key"], $a_params["secret"], $a_params["callback"]);
        $request = OAuthRequest::from_consumer_and_token($consumer, $a_params["token"], $a_params["http_method"], $a_params["url"], $a_params["data"]);
        $request->sign_request($method, $consumer, $a_params["token"]);

        return $request->get_parameters();
    }
}
