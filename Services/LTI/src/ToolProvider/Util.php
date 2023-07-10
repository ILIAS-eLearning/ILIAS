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

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTIOAuth;

final class Util
{
    /**
     * LTI version 1 for messages.
     */
    public const LTI_VERSION1 = 'LTI-1p0';

    /**
     * LTI version 1.3 for messages.
     */
    public const LTI_VERSION1P3 = '1.3.0';

    /**
     * LTI version 2 for messages.
     */
    public const LTI_VERSION2 = 'LTI-2p0';

    /**
     * Prefix for standard JWT message claims.
     */
    public const JWT_CLAIM_PREFIX = 'https://purl.imsglobal.org/spec/lti';

    /**
     * Mapping for standard message types.
     */
    public const MESSAGE_TYPE_MAPPING = array(
        'basic-lti-launch-request' => 'LtiResourceLinkRequest',
        'ContentItemSelectionRequest' => 'LtiDeepLinkingRequest',
        'ContentItemSelection' => 'LtiDeepLinkingResponse',
        'ContentItemUpdateRequest' => 'LtiDeepLinkingUpdateRequest'
    );

    /**
     * Mapping for standard message parameters to JWT claim.
     */
    public const JWT_CLAIM_MAPPING = array(
        'accept_types' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'accept_types', 'isArray' => true),
        'accept_copy_advice' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'copyAdvice', 'isBoolean' => true),
        'accept_media_types' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'accept_media_types'),
        'accept_multiple' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'accept_multiple', 'isBoolean' => true),
        'accept_presentation_document_targets' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'accept_presentation_document_targets', 'isArray' => true),
        'accept_unsigned' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'accept_unsigned', 'isBoolean' => true),
        'auto_create' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'auto_create', 'isBoolean' => true),
        'can_confirm' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'can_confirm'),
        'content_item_return_url' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'deep_link_return_url'),
        'content_items' => array('suffix' => 'dl', 'group' => '', 'claim' => 'content_items', 'isObject' => true),
        'data' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'data'),
        'data.LtiDeepLinkingResponse' => array('suffix' => 'dl', 'group' => '', 'claim' => 'data'),
        'text' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'text'),
        'title' => array('suffix' => 'dl', 'group' => 'deep_linking_settings', 'claim' => 'title'),
        'lti_msg' => array('suffix' => 'dl', 'group' => '', 'claim' => 'msg'),
        'lti_errormsg' => array('suffix' => 'dl', 'group' => '', 'claim' => 'errormsg'),
        'lti_log' => array('suffix' => 'dl', 'group' => '', 'claim' => 'log'),
        'lti_errorlog' => array('suffix' => 'dl', 'group' => '', 'claim' => 'errorlog'),
        'context_id' => array('suffix' => '', 'group' => 'context', 'claim' => 'id'),
        'context_label' => array('suffix' => '', 'group' => 'context', 'claim' => 'label'),
        'context_title' => array('suffix' => '', 'group' => 'context', 'claim' => 'title'),
        'context_type' => array('suffix' => '', 'group' => 'context', 'claim' => 'type', 'isArray' => true),
        'lis_course_offering_sourcedid' => array('suffix' => '', 'group' => 'lis', 'claim' => 'course_offering_sourcedid'),
        'lis_course_section_sourcedid' => array('suffix' => '', 'group' => 'lis', 'claim' => 'course_section_sourcedid'),
        'launch_presentation_css_url' => array('suffix' => '', 'group' => 'launch_presentation', 'claim' => 'css_url'),
        'launch_presentation_document_target' => array('suffix' => '', 'group' => 'launch_presentation', 'claim' => 'document_target'),
        'launch_presentation_height' => array('suffix' => '', 'group' => 'launch_presentation', 'claim' => 'height'),
        'launch_presentation_locale' => array('suffix' => '', 'group' => 'launch_presentation', 'claim' => 'locale'),
        'launch_presentation_return_url' => array('suffix' => '', 'group' => 'launch_presentation', 'claim' => 'return_url'),
        'launch_presentation_width' => array('suffix' => '', 'group' => 'launch_presentation', 'claim' => 'width'),
        'lis_person_contact_email_primary' => array('suffix' => '', 'group' => null, 'claim' => 'email'),
        'lis_person_name_family' => array('suffix' => '', 'group' => null, 'claim' => 'family_name'),
        'lis_person_name_full' => array('suffix' => '', 'group' => null, 'claim' => 'name'),
        'lis_person_name_given' => array('suffix' => '', 'group' => null, 'claim' => 'given_name'),
        'lis_person_sourcedid' => array('suffix' => '', 'group' => 'lis', 'claim' => 'person_sourcedid'),
        'user_id' => array('suffix' => '', 'group' => null, 'claim' => 'sub'),
        'user_image' => array('suffix' => '', 'group' => null, 'claim' => 'picture'),
        'roles' => array('suffix' => '', 'group' => '', 'claim' => 'roles', 'isArray' => true),
        'platform_id' => array('suffix' => '', 'group' => null, 'claim' => 'iss'),
        'deployment_id' => array('suffix' => '', 'group' => '', 'claim' => 'deployment_id'),
        'lti_message_type' => array('suffix' => '', 'group' => '', 'claim' => 'message_type'),
        'lti_version' => array('suffix' => '', 'group' => '', 'claim' => 'version'),
        'resource_link_description' => array('suffix' => '', 'group' => 'resource_link', 'claim' => 'description'),
        'resource_link_id' => array('suffix' => '', 'group' => 'resource_link', 'claim' => 'id'),
        'resource_link_title' => array('suffix' => '', 'group' => 'resource_link', 'claim' => 'title'),
        'target_link_uri' => array('suffix' => '', 'group' => '', 'claim' => 'target_link_uri'),
        'tool_consumer_info_product_family_code' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'product_family_code'),
        'tool_consumer_info_version' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'version'),
        'tool_consumer_instance_contact_email' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'contact_email'),
        'tool_consumer_instance_description' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'description'),
        'tool_consumer_instance_guid' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'guid'),
        'tool_consumer_instance_name' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'name'),
        'tool_consumer_instance_url' => array('suffix' => '', 'group' => 'tool_platform', 'claim' => 'url'),
        'tool_state' => array('suffix' => '', 'group' => 'tool', 'claim' => 'state'),
        'custom_context_memberships_v2_url' => array('suffix' => 'nrps', 'group' => 'namesroleservice', 'claim' => 'context_memberships_url'),
        'custom_nrps_versions' => array('suffix' => 'nrps', 'group' => 'namesroleservice', 'claim' => 'service_versions', 'isArray' => true),
        'custom_lineitems_url' => array('suffix' => 'ags', 'group' => 'endpoint', 'claim' => 'lineitems'),
        'custom_lineitem_url' => array('suffix' => 'ags', 'group' => 'endpoint', 'claim' => 'lineitem'),
        'custom_ags_scopes' => array('suffix' => 'ags', 'group' => 'endpoint', 'claim' => 'scope', 'isArray' => true),
        'custom_context_groups_url' => array('suffix' => 'gs', 'group' => 'groupsservice', 'claim' => 'context_groups_url'),
        'custom_context_group_sets_url' => array('suffix' => 'gs', 'group' => 'groupsservice', 'claim' => 'context_group_sets_url'),
        'custom_gs_scopes' => array('suffix' => 'gs', 'group' => 'groupsservice', 'claim' => 'scope', 'isArray' => true),
        'custom_gs_versions' => array('suffix' => 'gs', 'group' => 'groupsservice', 'claim' => 'service_versions', 'isArray' => true),
        'lis_outcome_service_url' => array('suffix' => 'bo', 'group' => 'basicoutcome', 'claim' => 'lis_outcome_service_url'),
        'lis_result_sourcedid' => array('suffix' => 'bo', 'group' => 'basicoutcome', 'claim' => 'lis_result_sourcedid'),
        'custom_ap_attempt_number' => array('suffix' => 'ap', 'group' => '', 'claim' => 'attempt_number', 'isInteger' => true),
        'custom_ap_start_assessment_url' => array('suffix' => 'ap', 'group' => '', 'claim' => 'start_assessment_url'),
        'custom_ap_session_data' => array('suffix' => 'ap', 'group' => '', 'claim' => 'session_data'),
        'custom_ap_acs_actions' => array('suffix' => 'ap', 'group' => 'acs', 'claim' => 'actions', 'isArray' => true),
        'custom_ap_acs_url' => array('suffix' => 'ap', 'group' => 'acs', 'claim' => 'assessment_control_url'),
        'custom_ap_proctoring_settings_data' => array('suffix' => 'ap', 'group' => 'proctoring_settings', 'claim' => 'data'),
        'custom_ap_email_verified' => array('suffix' => '', 'group' => null, 'claim' => 'email_verified', 'isBoolean' => true),
        'custom_ap_verified_user_given_name' => array('suffix' => 'ap', 'group' => 'verified_user', 'claim' => 'given_name'),
        'custom_ap_verified_user_family_name' => array('suffix' => 'ap', 'group' => 'verified_user', 'claim' => 'family_name'),
        'custom_ap_verified_user_full_name' => array('suffix' => 'ap', 'group' => 'verified_user', 'claim' => 'full_name'),
        'custom_ap_verified_user_image' => array('suffix' => 'ap', 'group' => 'verified_user', 'claim' => 'picture'),
        'custom_ap_end_assessment_return' => array('suffix' => 'ap', 'group' => '', 'claim' => 'end_assessment_return', 'isBoolean' => true)
    );

    /**
     * No logging.
     */
    public const LOGLEVEL_NONE = 0;

    /**
     * Log errors only.
     */
    public const LOGLEVEL_ERROR = 1;

    /**
     * Log error and information messages.
     */
    public const LOGLEVEL_INFO = 2;

    /**
     * Log all messages.
     */
    public const LOGLEVEL_DEBUG = 3;

    /**
     * Permitted LTI versions for messages.
     */
    public static array $LTI_VERSIONS = array(self::LTI_VERSION1, self::LTI_VERSION1P3, self::LTI_VERSION2);

    /**
     * List of supported message types and associated class methods.
     */
    public static array $METHOD_NAMES = array(
        'basic-lti-launch-request' => 'onLaunch',
        'ConfigureLaunchRequest' => 'onConfigure',
        'DashboardRequest' => 'onDashboard',
        'ContentItemSelectionRequest' => 'onContentItem',
        'ContentItemSelection' => 'onContentItem',
        'ContentItemUpdateRequest' => 'onContentItemUpdate',
        'ToolProxyRegistrationRequest' => 'onRegister',
        'LtiStartProctoring' => 'onLtiStartProctoring',
        'LtiStartAssessment' => 'onLtiStartAssessment',
        'LtiEndAssessment' => 'onLtiEndAssessment'
    );

    /**
     * GET and POST request parameters
     */
    public static ?array $requestParameters = null;

    /**
     * Current logging level.
     *
     * @var int $logLevel
     */
    public static int $logLevel = self::LOGLEVEL_NONE;

    /**
     * Check whether the request received could be an LTI message.
     *
     * @return bool
     */
    public static function isLtiMessage(): bool
    {
//        $isLti = ($_SERVER['REQUEST_METHOD'] === 'POST') &&
//            (!empty($_POST['lti_message_type']) || !empty($_POST['id_token']) || !empty($_POST['JWT']) ||
//                !empty($_POST['iss']));
//        if (!$isLti) {
//            $isLti = ($_SERVER['REQUEST_METHOD'] === 'GET') && (!empty($_GET['iss']) || !empty($_GET['openid_configuration']));
//        }
        global $DIC;
        $wrapper = $DIC->http()->wrapper()->post();
        $refString = $DIC->refinery()->kindlyTo()->string();
        $isLti = ($wrapper->has('lti_message_type') && $wrapper->retrieve('lti_message_type', $refString) != '') ||
            ($wrapper->has('id_token') && $wrapper->retrieve('id_token', $refString) != '') ||
            ($wrapper->has('JWT') && $wrapper->retrieve('JWT', $refString) != '') ||
            ($wrapper->has('iss') && $wrapper->retrieve('iss', $refString) != '');
        if (!$isLti) {
            $wrapper = $DIC->http()->wrapper()->query();
            $isLti = ($wrapper->has('iss') && $wrapper->retrieve('iss', $refString) != '') ||
                ($wrapper->has('openid_configuration') && $wrapper->retrieve('openid_configuration', $refString) != '');
        }
        return $isLti;
    }

    /**
     * Return GET and POST request parameters (POST parameters take precedence)
     *
     * @return array|null
     */
    public static function getRequestParameters(): ?array
    {
        if (is_null(self::$requestParameters)) {
//            special for ILIAS instead of
//            self::$requestParameters = array_merge($_GET, $_POST);
//            also not possible
//            self::$requestParameters = array_merge(
//                //Argument 1 passed to Symfony\Component\HttpFoundation\Request::createRequestFromFactory() must be of the type array, object given
//                (array) \Symfony\Component\HttpFoundation\Request::createFromGlobals()->query->all(),
//                (array) \Symfony\Component\HttpFoundation\Request::createFromGlobals()->request->all()
//                (array) $_GET, (array) $_POST
//            );
            global $DIC;
            $post = $DIC->http()->wrapper()->post();
            $query = $DIC->http()->wrapper()->query();
            $refinery = $DIC->refinery()->kindlyTo()->string();

            $divAr = ['accept_copy_advice',
                          'accept_media_types',
                          'accept_multiple',
                          'accept_presentation_document_targets',
                          'accept_types',
                          'accept_unsigned',
                          'auto_create',
                          'can_confirm',
                          'client_id',
                          'content_item_return_url',
                          'context_id',
                          'context_title',
                          'context_type',
                          'custom_ap_attempt_number',
                          'custom_content_item_id',
                          'custom_share_key',
                          'custom_tc_profile_url',
                          'custom_user_username',
                          'custom_username',
                          'deployment_id',
                          'ext_launch_presentation_css_url',
                          'ext_lms',
                          'ext_user_username',
                          'ext_username',
                          'iss',
                          'launch_presentation_css_url',
                          'launch_presentation_document_target',
                          'launch_presentation_height',
                          'launch_presentation_return_url',
                          'launch_presentation_width',
                          'lis_person_contact_email_primary',
                          'lis_person_name_family',
                          'lis_person_name_full',
                          'lis_person_name_given',
                          'lis_person_sourcedid',
                          'login_hint',
                          'lti_deployment_id',
                          'lti_message_hint',
                          'lti_message_type',
                          'lti_version',
                          'oauth_consumer_key',
                          'oauth_signature_method',
                          'openid_configuration',
                          'platform_state',
                          'reg_key',
                          'reg_password',
                          'registration_token',
                          'relaunch_url',
                          'resource_link_id',
                          'resource_link_title',
                          'roles',
                          'target_link_uri',
                          'tc_profile_url',
                          'tool_consumer_info_product_family_code',
                          'tool_consumer_info_version',
                          'tool_consumer_instance_guid',
                          'tool_consumer_instance_name',
                          'user_id',
                          'user_image'
            ];
            $LTI_CONSUMER_SETTING_NAMES = ['custom_tc_profile_url', 'custom_system_setting_url', 'custom_oauth2_access_token_url'];
            $LTI_CONTEXT_SETTING_NAMES = ['custom_context_setting_url',
                                               'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
                                               'custom_context_memberships_url', 'custom_context_memberships_v2_url',
                                               'custom_context_group_sets_url', 'custom_context_groups_url',
                                               'custom_lineitems_url', 'custom_ags_scopes'
            ];
            $LTI_RESOURCE_LINK_SETTING_NAMES = ['lis_result_sourcedid', 'lis_outcome_service_url',
                                                     'ext_ims_lis_basic_outcome_url', 'ext_ims_lis_resultvalue_sourcedids', 'ext_outcome_data_values_accepted',
                                                     'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
                                                     'ext_ims_lti_tool_setting', 'ext_ims_lti_tool_setting_id', 'ext_ims_lti_tool_setting_url',
                                                     'custom_link_setting_url', 'custom_link_memberships_url',
                                                     'custom_lineitems_url', 'custom_lineitem_url', 'custom_ags_scopes',
                                                     'custom_ap_acs_url'
            ];

            $requestAr = array_merge($divAr, $LTI_CONSUMER_SETTING_NAMES, $LTI_CONTEXT_SETTING_NAMES, $LTI_RESOURCE_LINK_SETTING_NAMES);

            foreach ($requestAr as $param) {
                if ($query->has($param)) {
                    self::$requestParameters[$param] = $query->retrieve($param, $refinery);
                }
                if ($post->has($param)) {
                    self::$requestParameters[$param] = $post->retrieve($param, $refinery);
                }
            }
        }
        return self::$requestParameters;
    }

    /**
     * Log an error message.
     * @param string $message    Message to be logged
     * @param bool   $showSource True if the name and line number of the current file are to be included
     * @return void
     */
    public static function logError(string $message, bool $showSource = true)
    {
        if (self::$logLevel >= self::LOGLEVEL_ERROR) {
            self::log("[ERROR] {$message}", $showSource);
        }
    }

    /**
     * Log an information message.
     * @param string $message    Message to be logged
     * @param bool   $showSource True if the name and line number of the current file are to be included
     * @return void
     */
    public static function logInfo(string $message, bool $showSource = false)
    {
        if (self::$logLevel >= self::LOGLEVEL_INFO) {
            self::log("[INFO] {$message}", $showSource);
        }
    }

    /**
     * Log a debug message.
     * @param string $message    Message to be logged
     * @param bool   $showSource True if the name and line number of the current file are to be included
     * @return void
     */
    public static function logDebug(string $message, bool $showSource = false)
    {
        if (self::$logLevel >= self::LOGLEVEL_DEBUG) {
            self::log("[DEBUG] {$message}", $showSource);
        }
    }

    /**
     * Log a request received.
     * @param bool $debugLevel True if the request details should be logged at the debug level (optional, default is false for information level)
     * @return void
     */
    public static function logRequest(bool $debugLevel = false)
    {
        if (!$debugLevel) {
            $logLevel = self::LOGLEVEL_INFO;
        } else {
            $logLevel = self::LOGLEVEL_DEBUG;
        }
        if (self::$logLevel >= $logLevel) {
            $message = "{$_SERVER['REQUEST_METHOD']} request received for '{$_SERVER['REQUEST_URI']}'";
//            $body = file_get_contents(OAuth\OAuthRequest::$POST_INPUT);
            $body = file_get_contents(LTIOAuth\OAuthRequest::$POST_INPUT);
            if (!empty($body)) {
//                $params = OAuth\OAuthUtil::parse_parameters($body);
                $params = LTIOAuth\OAuthUtil::parse_parameters($body);
                if (!empty($params)) {
                    $message .= " with body parameters of:\n" . var_export($params, true);
                } else {
                    $message .= " with a body of:\n" . var_export($body, true);
                }
            }
            if (!$debugLevel) {
                self::logInfo($message);
            } else {
                self::logDebug($message);
            }
        }
    }

    /**
     * Log a form submission.
     * @param string $url        URL to which the form should be submitted
     * @param array  $params     Array of form parameters
     * @param string $method     HTTP Method used to submit form (optional, default is POST)
     * @param bool   $debugLevel True if the form details should always be logged (optional, default is false to use current log level)
     * @return void
     */
    public static function logForm(string $url, array $params, string $method = 'POST', bool $debugLevel = false)
    {
        if (!$debugLevel) {
            $logLevel = self::$logLevel;
        } else {
            $logLevel = self::LOGLEVEL_DEBUG;
        }
        if (self::$logLevel >= self::LOGLEVEL_INFO) {
            $message = "Form submitted using {$method} to '{$url}'";
            if (!empty($params)) {
                $message .= " with parameters of:\n" . var_export($params, true);
            } else {
                $message .= " with no parameters";
            }
            if ($logLevel < self::LOGLEVEL_DEBUG) {
                self::logInfo($message);
            } else {
                self::logDebug($message);
            }
        }
    }

    /**
     * Log an error message irrespective of the logging level.
     * @param string $message    Message to be logged
     * @param bool   $showSource True if the name and line number of the current file are to be included
     * @return void
     */
    public static function log(string $message, bool $showSource = false)
    {
        $source = '';
        if ($showSource) {
            $backtraces = debug_backtrace();
            foreach ($backtraces as $backtrace) {
                if (isset($backtrace['file'])) {
                    $source .= PHP_EOL . "  {$backtrace['file']}";
                    if (isset($backtrace['line'])) {
                        $source .= " line {$backtrace['line']}";
                    }
                }
            }
            if (!empty($source)) {
                $source = PHP_EOL . "See: {$source}";
            }
        }
        error_log($message . $source);
    }

    /**
     * Generate a web page containing an auto-submitted form of parameters.
     * @param string $url    URL to which the form should be submitted
     * @param array  $params Array of form parameters
     * @param string $target Name of target (optional)
     * @return string
     */
    public static function sendForm(string $url, array $params, string $target = ''): string
    {
        self::logForm($url, $params, 'POST');
        $page = <<< EOD
<html>
<head>
<title>IMS LTI message</title>
<script type="text/javascript">
//<![CDATA[
function doOnLoad() {
    document.forms[0].submit();
}

window.onload=doOnLoad;
//]]>
</script>
</head>
<body>
<form action="{$url}" method="post" target="{$target}" encType="application/x-www-form-urlencoded">

EOD;
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $key = htmlentities($key, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                if (!is_array($value)) {
                    $value = htmlentities($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                    $page .= <<< EOD
    <input type="hidden" name="{$key}" value="{$value}" />

EOD;
                } else {
                    foreach ($value as $element) {
                        $element = htmlentities($element, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                        $page .= <<< EOD
    <input type="hidden" name="{$key}" value="{$element}" />

EOD;
                    }
                }
            }
        }

        $page .= <<< EOD
</form>
</body>
</html>
EOD;

        return $page;
    }

    /**
     * Redirect to a URL with query parameters.
     * @param string $url    URL to which the form should be submitted
     * @param array  $params Array of form parameters
     * @return string
     */
    public static function redirect(string $url, array $params): string
    {
        if (!empty($params)) {
            if (strpos($url, '?') === false) {
                $url .= '?';
                $sep = '';
            } else {
                $sep = '&';
            }
            foreach ($params as $key => $value) {
                $key = urlencode($key);
                if (!is_array($value)) {
                    $value = urlencode($value);
                    $url .= "{$sep}{$key}={$value}";
                    $sep = '&';
                } else {
                    foreach ($value as $element) {
                        $element = urlencode($element);
                        $url .= "{$sep}{$key}={$element}";
                        $sep = '&';
                    }
                }
            }
        }

        header("Location: {$url}");
        exit;
    }

    /**
     * Generate a random string.
     * The generated string will only comprise letters (upper- and lower-case) and digits.
     * @param int $length Length of string to be generated (optional, default is 8 characters)
     * @return string Random string
     */
    public static function getRandomString(int $length = 8): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

        $value = '';
        $charsLength = strlen($chars) - 1;

        for ($i = 1; $i <= $length; $i++) {
            $value .= $chars[rand(0, $charsLength)];
        }

        return $value;
    }

    /**
     * Strip HTML tags from a string.
     * @param string $html HTML string to be stripped
     * @return string
     */
    public static function stripHtml(string $html): string
    {
        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML401);

        return $html;
    }

    /**
     * Clone an object and any objects it contains.
     * @param object $obj Object to be cloned
     * @return object
     */
    public static function cloneObject(object $obj): object
    {
        $clone = clone $obj;
        $objVars = get_object_vars($clone);
        foreach ($objVars as $attrName => $attrValue) {
            if (is_object($clone->$attrName)) {
                $clone->$attrName = self::cloneObject($clone->$attrName);
            } elseif (is_array($clone->$attrName)) {
                foreach ($clone->$attrName as &$attrArrayValue) {
                    if (is_object($attrArrayValue)) {
                        $attrArrayValue = self::cloneObject($attrArrayValue);
                    }
                    unset($attrArrayValue);
                }
            }
        }

        return $clone;
    }


    public static function logtxt(string $msg)
    {
        file_put_contents("log.txt", $msg . "\n", FILE_APPEND);
    }
}
