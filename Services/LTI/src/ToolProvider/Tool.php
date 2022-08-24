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

use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;
use ILIAS\LTI\ToolProvider\MediaType;
use ILIAS\LTI\ToolProvider\Profile;
use ILIAS\LTI\ToolProvider\Content\Item;
use ILIAS\LTI\ToolProvider\Jwt\Jwt;
use ILIAS\LTI\ToolProvider\Http\HTTPMessage;
use ILIAS\LTIOAuth;
use ILIAS\LTI\ToolProvider\ApiHook\ApiHook;
use ILIAS\LTI\ToolProvider\Util;

/**
 * Class to represent an LTI Tool
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Tool
{
    use System;
    use ApiHook;

    /**
     * Default connection error message.
     */
    public const CONNECTION_ERROR_MESSAGE = 'Sorry, there was an error connecting you to the application.';

    /**
     * Use ID value only.
     */
    public const ID_SCOPE_ID_ONLY = 0;

    /**
     * Prefix an ID with the consumer key.
     */
    public const ID_SCOPE_GLOBAL = 1;

    /**
     * Prefix the ID with the consumer key and context ID.
     */
    public const ID_SCOPE_CONTEXT = 2;

    /**
     * Prefix the ID with the consumer key and resource ID.
     */
    public const ID_SCOPE_RESOURCE = 3;

    /**
     * Character used to separate each element of an ID.
     */
    public const ID_SCOPE_SEPARATOR = ':';

    /**
     * List of supported incoming message types.
     */
    public static array $MESSAGE_TYPES = array(
        'basic-lti-launch-request',
        'ConfigureLaunchRequest',
        'DashboardRequest',
        'ContentItemSelectionRequest',
        'ContentItemUpdateRequest',
        'ToolProxyRegistrationRequest',
        'LtiStartProctoring',
        'LtiEndAssessment'
    );

    /**
     * Names of LTI parameters to be retained in the consumer settings property.
     */
    private static array $LTI_CONSUMER_SETTING_NAMES = array('custom_tc_profile_url', 'custom_system_setting_url', 'custom_oauth2_access_token_url');

    /**
     * Names of LTI parameters to be retained in the context settings property.
     */
    private static array $LTI_CONTEXT_SETTING_NAMES = array('custom_context_setting_url',
                                                            'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
                                                            'custom_context_memberships_url', 'custom_context_memberships_v2_url',
                                                            'custom_context_group_sets_url', 'custom_context_groups_url',
                                                            'custom_lineitems_url', 'custom_ags_scopes'
    );

    /**
     * Names of LTI parameters to be retained in the resource link settings property.
     */
    private static array $LTI_RESOURCE_LINK_SETTING_NAMES = array('lis_result_sourcedid', 'lis_outcome_service_url',
                                                                  'ext_ims_lis_basic_outcome_url', 'ext_ims_lis_resultvalue_sourcedids', 'ext_outcome_data_values_accepted',
                                                                  'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
                                                                  'ext_ims_lti_tool_setting', 'ext_ims_lti_tool_setting_id', 'ext_ims_lti_tool_setting_url',
                                                                  'custom_link_setting_url', 'custom_link_memberships_url',
                                                                  'custom_lineitems_url', 'custom_lineitem_url', 'custom_ags_scopes',
                                                                  'custom_ap_acs_url'
    );

    /**
     * Names of LTI parameters to be retained even when not passed.
     */
    private static array $LTI_RETAIN_SETTING_NAMES = array('custom_lineitem_url');

    /**
     * Names of LTI custom parameter substitution variables (or capabilities) and their associated default message parameter names.
     */
    private static array $CUSTOM_SUBSTITUTION_VARIABLES = array('User.id' => 'user_id',
                                                                'User.image' => 'user_image',
                                                                'User.username' => 'username',
                                                                'User.scope.mentor' => 'role_scope_mentor',
                                                                'Membership.role' => 'roles',
                                                                'Person.sourcedId' => 'lis_person_sourcedid',
                                                                'Person.name.full' => 'lis_person_name_full',
                                                                'Person.name.family' => 'lis_person_name_family',
                                                                'Person.name.given' => 'lis_person_name_given',
                                                                'Person.email.primary' => 'lis_person_contact_email_primary',
                                                                'Context.id' => 'context_id',
                                                                'Context.type' => 'context_type',
                                                                'Context.title' => 'context_title',
                                                                'Context.label' => 'context_label',
                                                                'CourseOffering.sourcedId' => 'lis_course_offering_sourcedid',
                                                                'CourseSection.sourcedId' => 'lis_course_section_sourcedid',
                                                                'CourseSection.label' => 'context_label',
                                                                'CourseSection.title' => 'context_title',
                                                                'ResourceLink.id' => 'resource_link_id',
                                                                'ResourceLink.title' => 'resource_link_title',
                                                                'ResourceLink.description' => 'resource_link_description',
                                                                'Result.sourcedId' => 'lis_result_sourcedid',
                                                                'BasicOutcome.url' => 'lis_outcome_service_url',
                                                                'ToolConsumerProfile.url' => 'custom_tc_profile_url',
                                                                'ToolProxy.url' => 'tool_proxy_url',
                                                                'ToolProxy.custom.url' => 'custom_system_setting_url',
                                                                'ToolProxyBinding.custom.url' => 'custom_context_setting_url',
                                                                'LtiLink.custom.url' => 'custom_link_setting_url',
                                                                'LineItems.url' => 'custom_lineitems_url',
                                                                'LineItem.url' => 'custom_lineitem_url',
                                                                'ToolProxyBinding.memberships.url' => 'custom_context_memberships_url',
                                                                'ToolProxyBinding.nrps.url' => 'custom_context_memberships_v2_url',
                                                                'LtiLink.memberships.url' => 'custom_link_memberships_url',
                                                                'LtiLink.acs.url' => 'custom_ap_acs_url'
    );

//    /**
//     * Tool consumer object.
//     *
//     * @deprecated Use Tool::$platform instead
//     * @see platform
//     *
//     * @var ToolConsumer|null $consumer
//     */
//    public ?ToolConsumer $consumer = null;

    /**
     * Platform object.
     *
     * @var \ilLTIPlatform $platform // UK Check change from Platform|null (to \ILIAS\LTI\ToolProvider\Platform) to \ilLTIPlatform
     */
    public \ilLTIPlatform $platform;

    /**
     * Return URL provided by platform.
     *
     * @var string|null $returnUrl
     */
    public ?string $returnUrl = null;

    /**
     * UserResult object.
     *
     * @var UserResult|null $userResult
     */
    public ?UserResult $userResult = null;

    /**
     * Resource link object.
     *
     * @var ResourceLink|null $resourceLink
     */
    public ?ResourceLink $resourceLink = null;

    /**
     * Context object.
     *
     * @var Context|null $context
     */
    public ?Context $context = null;

    /**
     * Default email domain.
     *
     * @var string $defaultEmail
     */
    public string $defaultEmail = '';

    /**
     * Scope to use for user IDs.
     *
     * @var int $idScope
     */
    public int $idScope = self::ID_SCOPE_ID_ONLY;

    /**
     * Whether shared resource link arrangements are permitted.
     *
     * @var bool $allowSharing
     */
    public bool $allowSharing = false;

    /**
     * Message for last request processed
     *
     * @var string $message
     */
    public ?string $message = null;

    /**
     * Base URL for tool service
     *
     * @var string|null $baseUrl
     */
    public ?string $baseUrl = null;

    /**
     * Vendor details
     *
     * @var Item|null $vendor
     */
    public ?Item $vendor = null;

    /**
     * Product details
     *
     * @var Item|null $product
     */
    public ?Item $product = null;

    /**
     * Services required by Tool
     *
     * @var array|null $requiredServices
     */
    public ?array $requiredServices = null;

    /**
     * Optional services used by Tool
     *
     * @var array|null $optionalServices
     */
    public ?array $optionalServices = null;

    /**
     * Resource handlers for Tool
     *
     * @var array|null $resourceHandlers
     */
    public ?array $resourceHandlers = null;

    /**
     * Message URL for Tool
     *
     * @var string|null $messageUrl
     */
    public ?string $messageUrl = null;

    /**
     * Initiate Login request URL for Tool
     *
     * @var string|null $initiateLoginUrl
     */
    public ?string $initiateLoginUrl = null;

    /**
     * Redirection URIs for Tool
     *
     * @var array|null $redirectionUris
     */
    public ?array $redirectionUris = null;

    /**
     * Default tool for use with service requests
     *
     * @var Tool|null $defaultTool
     */
    public static ?Tool $defaultTool = null;

    /**
     * Use GET method for authentication request messages when true
     *
     * @var bool $authenticateUsingGet
     */
    public static bool $authenticateUsingGet = false;

    /**
     * URL to redirect user to on successful completion of the request.
     *
     * @var string|null $redirectUrl
     */
    protected ?string $redirectUrl = null;

    /**
     * Media types accepted by the platform.
     *
     * @var array|null $mediaTypes
     */
    protected ?array $mediaTypes = null;

    /**
     * Content item types accepted by the platform.
     *
     * @var array|null $contentTypes
     */
    protected ?array $contentTypes = null;

    /**
     * File types accepted by the platform.
     *
     * @var array|null $fileTypes
     */
    protected ?array $fileTypes = null;

    /**
     * Document targets accepted by the platform.
     *
     * @var array|null $documentTargets
     */
    protected ?array $documentTargets = null;

    /**
     * Default HTML to be displayed on a successful completion of the request.
     *
     * @var string|null $output
     */
    protected ?string $output = null;

    /**
     * HTML to be displayed on an unsuccessful completion of the request and no return URL is available.
     *
     * @var string|null $errorOutput
     */
    protected ?string $errorOutput = null;

    /**
     * LTI parameter constraints for auto validation checks.
     *
     * @var array|null $constraints
     */
    private ?array $constraints = null;

    /**
     * Class constructor
     * @param DataConnector|null $dataConnector Object containing a database connection object
     */
    public function __construct(DataConnector $dataConnector = null)
    {
//        $this->consumer = &$this->platform; //UK: deprecated
        $this->initialize();
        if (empty($dataConnector)) {
            $dataConnector = DataConnector::getDataConnector();
        }
        $this->dataConnector = $dataConnector;
    }

    /**
     * Initialise the tool.
     */
    public function initialize()
    {
        $this->id = null;
        $this->key = null;
        $this->name = null;
        $this->secret = null;
        $this->messageUrl = null;
        $this->initiateLoginUrl = null;
        $this->redirectionUris = null;
        $this->rsaKey = null;
        $this->signatureMethod = 'HMAC-SHA1';
        $this->encryptionMethod = null;
        $this->ltiVersion = null;
        $this->settings = array();
        $this->enabled = false;
        $this->enableFrom = null;
        $this->enableUntil = null;
        $this->lastAccess = null;
        $this->created = null;
        $this->updated = null;
        $this->constraints = array();
//        $this->vendor = new Profile\Item(); //Changed UK
        $this->vendor = new \ILIAS\LTI\ToolProvider\Content\Item(null);
//        $this->product = new Profile\Item();
        $this->product = new \ILIAS\LTI\ToolProvider\Content\Item(null);
        $this->requiredServices = array();
        $this->optionalServices = array();
        $this->resourceHandlers = array();
    }

    /**
     * Save the tool to the database.
     *
     * @return bool    True if the object was successfully saved
     */
    public function save(): bool
    {
        return $this->dataConnector->saveTool($this);
    }

    /**
     * Delete the tool from the database.
     *
     * @return bool    True if the object was successfully deleted
     */
    public function delete(): bool
    {
        return $this->dataConnector->deleteTool($this);
    }

    /**
     * Get the message parameters
     *
     * @return array The message parameter array
     */
    public function getMessageParameters(): array
    {
        if (is_null($this->messageParameters)) {
            $this->parseMessage();
            // Set debug mode
            if (Util::$logLevel < Util::LOGLEVEL_DEBUG) {
                $this->debugMode = (isset($this->messageParameters['custom_debug']) &&
                    (strtolower($this->messageParameters['custom_debug']) === 'true'));
                if ($this->debugMode) {
                    Util::$logLevel = Util::LOGLEVEL_DEBUG;
                }
            }
            // Set return URL if available
            if (!empty($this->messageParameters['lti_message_type']) &&
                (($this->messageParameters['lti_message_type'] === 'ContentItemSelectionRequest') || ($this->messageParameters['lti_message_type'] === 'ContentItemUpdateRequest')) &&
                !empty($this->messageParameters['content_item_return_url'])) {
                $this->returnUrl = $this->messageParameters['content_item_return_url'];
            }
            if (empty($this->returnUrl) && !empty($this->messageParameters['launch_presentation_return_url'])) {
                $this->returnUrl = $this->messageParameters['launch_presentation_return_url'];
            }
        }

        return $this->messageParameters;
    }

    /**
     * Process an incoming request
     * @param bool $strictMode True if full compliance with the LTI specification is required (optional, default is false)
     */
    public function handleRequest(bool $strictMode = false)
    {
        $parameters = Util::getRequestParameters();
        if ($this->debugMode) {
            Util::$logLevel = Util::LOGLEVEL_DEBUG;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {  // Ignore HEAD requests
            Util::logRequest(true);
        } elseif (isset($parameters['iss']) && (strlen($parameters['iss']) > 0)) {  // Initiate login request
            Util::logRequest();
            if (!isset($parameters['login_hint']) || (strlen($parameters['login_hint']) <= 0)) {
                $this->ok = false;
                $this->reason = 'Missing login_hint parameter';
            } elseif (!isset($parameters['target_link_uri']) || (strlen($parameters['target_link_uri']) <= 0)) {
                $this->ok = false;
                $this->reason = 'Missing target_link_uri parameter';
            } else {
                $this->ok = $this->sendAuthenticationRequest($parameters);
            }
        } elseif (isset($parameters['openid_configuration']) && (strlen($parameters['openid_configuration']) > 0)) {  // Dynamic registration request
            Util::logRequest();
            $this->onRegistration();
        } else {  // LTI message
            $this->getMessageParameters();
            Util::logRequest();
            if ($this->ok && $this->authenticate($strictMode)) {
                if (empty($this->output)) {
                    $this->doCallback();
                    if ($this->ok && ($this->messageParameters['lti_message_type'] === 'ToolProxyRegistrationRequest')) {
                        $this->platform->save();
                    }
                }
            }
        }
        if (!$this->ok) {
            $errorMessage = "Request failed with reason: '{$this->reason}'";
            if (!empty($this->details)) {
                $errorMessage .= PHP_EOL . 'Debug information:';
                foreach ($this->details as $detail) {
                    $errorMessage .= PHP_EOL . "  {$detail}";
                }
            }
            Util::logError($errorMessage);
        }
        $this->result();
    }

    /**
     * Add a parameter constraint to be checked on launch
     * @param string     $name         Name of parameter to be checked
     * @param bool       $required     True if parameter is required (optional, default is true)
     * @param int|null   $maxLength    Maximum permitted length of parameter value (optional, default is null)
     * @param array|null $messageTypes Array of message types to which the constraint applies (optional, default is all)
     */
    public function setParameterConstraint(string $name, bool $required = true, int $maxLength = null, array $messageTypes = null)
    {
        $name = trim($name);
        if (!empty($name)) {
            $this->constraints[$name] = array('required' => $required, 'max_length' => $maxLength, 'messages' => $messageTypes);
        }
    }

//    /**
//     * Get an array of defined tool consumers
//     *
//     * @deprecated Use getPlatforms() instead
//     * @see Tool::getPlatforms()
//     *
//     * @return array Array of ToolConsumer objects
//     */
//    public function getConsumers() : array
//    {
//        Util::logDebug(
//            'Method ceLTIc\LTI\Tool::getConsumers() has been deprecated; please use ceLTIc\LTI\Tool::getPlatforms() instead.',
//            true
//        );
//        return $this->getPlatforms();
//    }

    /**
     * Get an array of defined platforms
     *
     * @return array Array of Platform objects
     */
    public function getPlatforms(): array
    {
        return $this->dataConnector->getPlatforms();
    }

    /**
     * Find an offered service based on a media type and HTTP action(s)
     * @param string $format  Media type required
     * @param array  $methods Array of HTTP actions required
     * @return object|bool The service object
     */
    public function findService(string $format, array $methods)
    {
        $found = false;
        $services = $this->platform->profile->service_offered;
        if (is_array($services)) {
            $n = -1;
            foreach ($services as $service) {
                $n++;
                if (!is_array($service->format) || !in_array($format, $service->format)) {
                    continue;
                }
                $missing = array();
                foreach ($methods as $method) {
                    if (!is_array($service->action) || !in_array($method, $service->action)) {
                        $missing[] = $method;
                    }
                }
                $methods = $missing;
                if (count($methods) <= 0) {
                    $found = $service;
                    break;
                }
            }
        }

        return $found;
    }

    //not necessary because LTI V 2
//    /**
//     * Send the tool proxy to the platform
//     *
//     * @return bool    True if the tool proxy was accepted
//     */
//    public function doToolProxyService() : bool
//    {
//        // Create tool proxy
//        $toolProxyService = $this->findService('application/vnd.ims.lti.v2.toolproxy+json', array('POST'));
//        $secret = Util::getRandomString(12);
//        $toolProxy = new MediaType\ToolProxy($this, $toolProxyService, $secret);
//        $http = $this->platform->doServiceRequest($toolProxyService, 'POST', 'application/vnd.ims.lti.v2.toolproxy+json',
//            json_encode($toolProxy));
//        $ok = $http->ok && ($http->status === 201) && !empty($http->responseJson->tool_proxy_guid);
//        if ($ok) {
//            $this->platform->setKey($http->responseJson->tool_proxy_guid);
//            $this->platform->secret = $toolProxy->security_contract->shared_secret;
//            $this->platform->toolProxy = $toolProxy; //UK: changed from json_encode($toolProxy);
//            $this->platform->save();
//        }
//
//        return $ok;
//    }

//    /**
//     * Generate a web page containing an auto-submitted form of parameters.
//     * @param string $url    URL to which the form should be submitted
//     * @param array  $params Array of form parameters
//     * @param string $target Name of target (optional)
//     * @return string
//     *@deprecated Use Util::sendForm() instead
//     * @see Util::sendForm()
//     */
//    public static function sendForm(string $url, array $params, string $target = '') : string
//    {
//        Util::logDebug('Method ceLTIc\LTI\Tool::sendForm() has been deprecated; please use ceLTIc\LTI\Util::sendForm() instead.',
//            true);
//        Util::sendForm($url, $params, $target);
//    }

    ###
    ###    PROTECTED METHODS
    ###

    /**
     * Process a valid launch request
     */
    protected function onLaunch()
    {
        $this->reason = 'No onLaunch method found for tool';
        $this->onError();
    }

    /**
     * Process a valid configure request
     */
    protected function onConfigure()
    {
        $this->reason = 'No onConfigure method found for tool';
        $this->onError();
    }

    /**
     * Process a valid dashboard request
     */
    protected function onDashboard()
    {
        $this->reason = 'No onDashboard method found for tool';
        $this->onError();
    }

    /**
     * Process a valid content-item request
     */
    protected function onContentItem()
    {
        $this->reason = 'No onContentItem method found for tool';
        $this->onError();
    }

    /**
     * Process a valid content-item update request
     */
    protected function onContentItemUpdate()
    {
        $this->reason = 'No onContentItemUpdate method found for tool';
        $this->onError();
    }

    /**
     * Process a valid tool proxy registration request
     */
    protected function onRegister()
    {
        $this->reason = 'No onRegister method found for tool';
        $this->onError();
    }

    /**
     * Process a dynamic registration request
     */
    protected function onRegistration()
    {
        $platformConfig = $this->getPlatformConfiguration();
        if ($this->ok) {
            $toolConfig = $this->getConfiguration($platformConfig);
            $registrationConfig = $this->sendRegistration($platformConfig, $toolConfig);
            if ($this->ok) {
                $this->getPlatformToRegister($platformConfig, $registrationConfig);
            }
        }
        $this->getRegistrationResponsePage($toolConfig);
        $this->ok = true;
    }

    /**
     * Process a valid start proctoring request
     */
    protected function onLtiStartProctoring()
    {
        $this->reason = 'No onLtiStartProctoring method found for tool';
        $this->onError();
    }

    /**
     * Process a valid end assessment request
     */
    protected function onLtiEndAssessment()
    {
        $this->reason = 'No onLtiEndAssessment method found for tool';
        $this->onError();
    }

    /**
     * Process a login initiation request
     * @param array $requestParameters Request parameters
     * @param array $authParameters    Authentication request parameters
     */

    protected function onInitiateLogin(array $requestParameters, array &$authParameters)
    {
    }

    /**
     * Process a response to an invalid request
     */
    protected function onError()
    {
        $this->ok = false;
    }

    /**
     * Fetch a platform's configuration data
     *
     * @return array|null  Platform configuration data
     */
    protected function getPlatformConfiguration(): ?array
    {
        if ($this->ok) {
            $parameters = Util::getRequestParameters();
            $this->ok = !empty($parameters['openid_configuration']);
            if ($this->ok) {
                $http = new HttpMessage($parameters['openid_configuration']);
                $this->ok = $http->send();
                if ($this->ok) {
                    $platformConfig = json_decode($http->response, true);
                    $this->ok = !empty($platformConfig);
                }
                if (!$this->ok) {
                    $this->reason = 'Unable to access platform configuration details.';
                }
            } else {
                $this->reason = 'Invalid registration request: missing openid_configuration parameter.';
            }
            if ($this->ok) {
                $this->ok = !empty($platformConfig['registration_endpoint']) && !empty($platformConfig['jwks_uri']) && !empty($platformConfig['authorization_endpoint']) &&
                    !empty($platformConfig['token_endpoint']) && !empty($platformConfig['https://purl.imsglobal.org/spec/lti-platform-configuration']) &&
                    !empty($platformConfig['claims_supported']) && !empty($platformConfig['scopes_supported']) &&
                    !empty($platformConfig['id_token_signing_alg_values_supported']) &&
                    !empty($platformConfig['https://purl.imsglobal.org/spec/lti-platform-configuration']['product_family_code']) &&
                    !empty($platformConfig['https://purl.imsglobal.org/spec/lti-platform-configuration']['version']) &&
                    !empty($platformConfig['https://purl.imsglobal.org/spec/lti-platform-configuration']['messages_supported']);
                if (!$this->ok) {
                    $this->reason = 'Invalid platform configuration details.';
                }
            }
            if ($this->ok) {
                Jwt::setJwtClient(); //added - check
                $jwtClient = Jwt::getJwtClient();
                $algorithms = \array_intersect(
                    $jwtClient::getSupportedAlgorithms(),
                    $platformConfig['id_token_signing_alg_values_supported']
                );
                $this->ok = !empty($algorithms);
                if ($this->ok) {
                    rsort($platformConfig['id_token_signing_alg_values_supported']);
                } else {
                    $this->reason = 'None of the signature algorithms offered by the platform is supported.';
                }
            }
        }
        if (!$this->ok) {
            $platformConfig = null;
        }

        return $platformConfig;
    }

    /**
     * Prepare the tool's configuration data
     * @param array $platformConfig Platform configuration data
     * @return array  Tool configuration data
     */
    protected function getConfiguration(array $platformConfig): array
    {
        $claimsMapping = array(
            'User.id' => 'sub',
            'Person.name.full' => 'name',
            'Person.name.given' => 'given_name',
            'Person.name.family' => 'family_name',
            'Person.email.primary' => 'email'
        );
        $toolName = (!empty($this->product->name)) ? $this->product->name : 'Unnamed tool';
        $toolDescription = (!empty($this->product->description)) ? $this->product->description : '';
//        $oauthRequest = OAuth\OAuthRequest::from_request();
        $oauthRequest = LTIOAuth\OAuthRequest::from_request();
        $toolUrl = $oauthRequest->get_normalized_http_url();
        $pos = strpos($toolUrl, '//');
        $domain = substr($toolUrl, $pos + 2);
        $domain = substr($domain, 0, strpos($domain, '/'));
        $claimsSupported = $platformConfig['claims_supported'];
        $messagesSupported = $platformConfig['https://purl.imsglobal.org/spec/lti-platform-configuration']['messages_supported'];
        $scopesSupported = $platformConfig['scopes_supported'];
        $iconUrl = null;
        $messages = array();
        $claims = array('iss');
        $variables = array();
        $constants = array();
        $redirectUris = array();
        foreach ($this->resourceHandlers as $resourceHandler) {
            if (empty($iconUrl)) {
                $iconUrl = $resourceHandler->icon;
            }
            foreach (array_merge($resourceHandler->optionalMessages, $resourceHandler->requiredMessages) as $message) {
                $type = $message->type;
                if (array_key_exists($type, Util::MESSAGE_TYPE_MAPPING)) {
                    $type = Util::MESSAGE_TYPE_MAPPING[$type];
                }
                $capabilities = array();
                if ($type === 'LtiResourceLinkRequest') {
                    $toolUrl = "{$this->baseUrl}{$message->path}";
                    $redirectUris[] = $toolUrl;
                    $capabilities = $message->capabilities;
                    $variables = array_merge($variables, $message->variables);
                    $constants = array_merge($constants, $message->constants);
                } elseif (in_array($type, $messagesSupported)) {
                    $redirectUris[] = "{$this->baseUrl}{$message->path}";
                    $capabilities = $message->capabilities;
                    $variables = array_merge($message->variables, $variables);
                    $constants = array_merge($message->constants, $constants);
                    $messages[] = array(
                        'type' => $type,
                        'target_link_uri' => "{$this->baseUrl}{$message->path}",
                        'label' => $toolName
                    );
                }
                foreach ($capabilities as $capability) {
                    if (array_key_exists($capability, $claimsMapping) && in_array($claimsMapping[$capability], $claimsSupported)) {
                        $claims[] = $claimsMapping[$capability];
                    }
                }
            }
        }
        if (empty($redirectUris)) {
            $redirectUris = array($toolUrl);
        } else {
            $redirectUris = array_unique($redirectUris);
        }
        if (!empty($claims)) {
            $claims = array_unique($claims);
        }
        $custom = array();
        foreach ($constants as $name => $value) {
            $custom[$name] = $value;
        }
        foreach ($variables as $name => $value) {
            $custom[$name] = '$' . $value;
        }
        $toolConfig = array();
        $toolConfig['application_type'] = 'web';
        $toolConfig['client_name'] = $toolName;
        $toolConfig['response_types'] = array('id_token');
        $toolConfig['grant_types'] = array('implicit', 'client_credentials');
        $toolConfig['initiate_login_uri'] = $toolUrl;
        $toolConfig['redirect_uris'] = $redirectUris;
        $toolConfig['jwks_uri'] = $this->jku;
        $toolConfig['token_endpoint_auth_method'] = 'private_key_jwt';
        $toolConfig['https://purl.imsglobal.org/spec/lti-tool-configuration'] = array(
            'domain' => $domain,
            'target_link_uri' => $toolUrl,
            'custom_parameters' => $custom,
            'claims' => $claims,
            'messages' => $messages,
            'description' => $toolDescription
        );
        $toolConfig['scope'] = implode(' ', array_intersect($this->requiredScopes, $scopesSupported));
        if (!empty($iconUrl)) {
            $toolConfig['logo_uri'] = "{$this->baseUrl}{$iconUrl}";
        }

        return $toolConfig;
    }

    /**
     * Send the tool registration to the platform
     * @param array $platformConfig Platform configuration data
     * @param array $toolConfig     Tool configuration data
     * @return array  Registration data
     */
    protected function sendRegistration(array $platformConfig, array $toolConfig): ?array
    {
        if ($this->ok) {
            $parameters = Util::getRequestParameters();
            $this->ok = !empty($parameters['registration_token']);
            if ($this->ok) {
                $body = json_encode($toolConfig);
                $headers = "Content-type: application/json\n" .
                    "Authorization: Bearer {$parameters['registration_token']}";
                $http = new HttpMessage($platformConfig['registration_endpoint'], 'POST', $body, $headers);
                $this->ok = $http->send();
                if ($this->ok) {
                    $registrationConfig = json_decode($http->response, true);
                    $this->ok = !empty($registrationConfig);
                }
                if (!$this->ok) {
                    $this->reason = 'Unable to register with platform.';
                }
            } else {
                $this->reason = 'Invalid registration request: missing registration_token parameter.';
            }
        }
        if (!$this->ok) {
            $registrationConfig = null;
        }

        return $registrationConfig;
    }

    /**
     * Initialise the platform to be registered
     * @param array $platformConfig     Platform configuration data
     * @param array $registrationConfig Registration data
     * @param bool  $doSave             True if the platform should be saved (optional, default is true)
     * @return Platform  Platform object
     */
    protected function getPlatformToRegister(array $platformConfig, array $registrationConfig, bool $doSave = true): ?Platform
    {
        $domain = $platformConfig['issuer'];
        $pos = strpos($domain, '//');
        if ($pos !== false) {
            $domain = substr($domain, $pos + 2);
            $pos = strpos($domain, '/');
            if ($pos !== false) {
                $domain = substr($domain, 0, $pos);
            }
        }
        $this->platform = new Platform($this->dataConnector);
        $this->platform->name = $domain;
        $this->platform->ltiVersion = Util::LTI_VERSION1P3;
        $this->platform->signatureMethod = reset($platformConfig['id_token_signing_alg_values_supported']);
        $this->platform->platformId = $platformConfig['issuer'];
        $this->platform->clientId = $registrationConfig['client_id'];
        $this->platform->deploymentId = $registrationConfig['https://purl.imsglobal.org/spec/lti-tool-configuration']['deployment_id'];
        $this->platform->authenticationUrl = $platformConfig['authorization_endpoint'];
        $this->platform->accessTokenUrl = $platformConfig['token_endpoint'];
        $this->platform->jku = $platformConfig['jwks_uri'];
        if ($doSave) {
            $this->ok = $this->platform->save();
            if (!$this->ok) {
                $this->reason = 'Sorry, an error occurred when saving the platform details.';
            }
        }

        return $this->platform;
    }

    /**
     * Prepare the page to complete a registration request
     * @param array $toolConfig Tool configuration data
     */
    protected function getRegistrationResponsePage(array $toolConfig)
    {
        $enabled = '';
        if (!empty($this->platform)) {
            $now = time();
            if (!$this->platform->enabled) {
                $enabled = ', but it will need to be enabled by the tool provider before it can be used';
            } elseif (!empty($this->platform->enableFrom) && ($this->platform->enableFrom > $now)) {
                $enabled = ', but you will only have access from ' . date('j F Y H:i T', $this->platform->enableFrom);
                if (!empty($this->platform->enableUntil)) {
                    $enabled .= ' until ' . date('j F Y H:i T', $this->platform->enableUntil);
                }
            } elseif (!empty($this->platform->enableUntil)) {
                if ($this->platform->enableUntil > $now) {
                    $enabled = ', but you will only have access until ' . date('j F Y H:i T', $this->platform->enableUntil);
                } else {
                    $enabled = ', but your access was set to end at ' . date('j F Y H:i T', $this->platform->enableUntil);
                }
            }
        }
        $html = <<< EOD
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>LTI Tool registration</title>
    <style>
      h1 {
        font-soze: 110%;
        font-weight: bold;
      }
      .success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
        border: 1px solid;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
      }
      .error {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
        border: 1px solid;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
      }
      .centre {
        text-align: center;
      }
      button {
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
      }
    </style>
    <script language="javascript" type="text/javascript">
      function doClose(el) {
        (window.opener || window.parent).postMessage({subject:'org.imsglobal.lti.close'}, '*');
        return true;
      }
    </script>
  </head>
  <body>
    <h1>{$toolConfig['client_name']} registration</h1>

EOD;
        if ($this->ok) {
            $html .= <<< EOD
    <p class="success">
      The tool registration was successful{$enabled}.
    </p>
    <p class="centre">
      <button type="button" onclick="return doClose();">Close</button>
    </p>

EOD;
        } else {
            $html .= <<< EOD
    <p class="error">
      Sorry, the registration was not successful: {$this->reason}
    </p>

EOD;
        }
        $html .= <<< EOD
  </body>
</html>
EOD;
        $this->output = $html;
    }

    /**
     * Load the tool from the database by its consumer key.
     * @param string|null        $key           Consumer key
     * @param DataConnector|null $dataConnector A data connector object
     * @param bool               $autoEnable    true if the tool is to be enabled automatically (optional, default is false)
     * @return Tool           The tool object
     */
    public static function fromConsumerKey(string $key = null, DataConnector $dataConnector = null, bool $autoEnable = false): Tool
    {
        $tool = new static($dataConnector);
        $tool->key = $key;
        if (!empty($dataConnector)) {
            $ok = $dataConnector->loadTool($tool);
            if ($ok && $autoEnable) {
                $tool->enabled = true;
            }
        }

        return $tool;
    }

    /**
     * Load the tool from the database by its initiate login URL.
     * @param string             $initiateLoginUrl The initiate login URL
     * @param DataConnector|null $dataConnector    A data connector object
     * @param bool               $autoEnable       True if the tool is to be enabled automatically (optional, default is false)
     * @return Tool           The tool object
     */
    public static function fromInitiateLoginUrl(string $initiateLoginUrl, DataConnector $dataConnector = null, bool $autoEnable = false): Tool
    {
        $tool = new static($dataConnector);
        $tool->initiateLoginUrl = $initiateLoginUrl;
        if ($dataConnector->loadTool($tool)) {
            if ($autoEnable) {
                $tool->enabled = true;
            }
        }

        return $tool;
    }

    /**
     * Load the tool from the database by its record ID.
     * @param string        $id            The tool record ID
     * @param DataConnector $dataConnector A data connector object
     * @return Tool           The tool object
     */
    public static function fromRecordId(string $id, DataConnector $dataConnector): Tool
    {
        $tool = new static($dataConnector);
        $tool->setRecordId($id);
        $dataConnector->loadTool($tool);

        return $tool;
    }

    ###
    ###    PRIVATE METHODS
    ###

    /**
     * Perform the result of an action.
     *
     * This function may redirect the user to another URL rather than returning a value.
     *
     * returns string Output to be displayed (redirection, or display HTML or message) //UK: erased @return
     */
    private function result(): void
    {
        if (!$this->ok) {
            $this->message = self::CONNECTION_ERROR_MESSAGE;
            $this->onError();
        }
        if (!$this->ok) {
            // If not valid, return an error message to the platform if a return URL is provided
            if (!empty($this->returnUrl)) {
                $errorUrl = $this->returnUrl;
                if (!is_null($this->platform) && isset($this->messageParameters['lti_message_type']) &&
                    (($this->messageParameters['lti_message_type'] === 'ContentItemSelectionRequest') ||
                        ($this->messageParameters['lti_message_type'] === 'ContentItemUpdateRequest'))) {
                    $formParams = array();
                    if ($this->debugMode && !is_null($this->reason)) {
                        $formParams['lti_errormsg'] = "Debug error: {$this->reason}";
                    } else {
                        $formParams['lti_errormsg'] = $this->message;
                        if (!is_null($this->reason)) {
                            $formParams['lti_errorlog'] = "Debug error: {$this->reason}";
                        }
                    }
                    if (isset($this->messageParameters['data'])) {
                        $formParams['data'] = $this->messageParameters['data'];
                    }
                    $this->version = (isset($this->messageParameters['lti_version'])) ? $this->messageParameters['lti_version'] : Util::LTI_VERSION1;
                    $page = $this->sendMessage($errorUrl, 'ContentItemSelection', $formParams);
                    echo $page;
                } else {
                    if (strpos($errorUrl, '?') === false) {
                        $errorUrl .= '?';
                    } else {
                        $errorUrl .= '&';
                    }
                    if ($this->debugMode && !is_null($this->reason)) {
                        $errorUrl .= 'lti_errormsg=' . urlencode("Debug error: $this->reason");
                    } else {
                        $errorUrl .= 'lti_errormsg=' . urlencode($this->message);
                        if (!is_null($this->reason)) {
                            $errorUrl .= '&lti_errorlog=' . urlencode("Debug error: $this->reason");
                        }
                    }
                    header("Location: {$errorUrl}");
                }
                exit;
            } else {
                if (!is_null($this->errorOutput)) {
                    echo $this->errorOutput;
                } elseif ($this->debugMode && !empty($this->reason)) {
                    echo "Debug error: {$this->reason}";
                } else {
                    echo "Error: {$this->message}";
                }
                exit;
            }
        } elseif (!is_null($this->redirectUrl)) {
            header("Location: {$this->redirectUrl}");
            exit;
        } elseif (!is_null($this->output)) {
            echo $this->output;
            exit;
        }
    }

    /**
     * Check the authenticity of the LTI message.
     * The platform, resource link and user objects will be initialised if the request is valid.
     * @param bool    $strictMode True if full compliance with the LTI specification is required
     * @return bool    True if the request has been successfully validated.
     */
    private function authenticate(bool $strictMode): bool
    {
        $doSavePlatform = false;
        $this->ok = $this->checkMessage();
        if ($this->ok && $strictMode && !empty($this->jwt) && !empty($this->jwt->hasJwt())) {
            if (!empty($this->jwt->getClaim('https://purl.imsglobal.org/spec/lti/claim/context', '')) &&
                empty($this->messageParameters['context_id'])) {
                $this->ok = false;
                $this->reason = 'Missing id property in https://purl.imsglobal.org/spec/lti/claim/context claim';
            } elseif (!empty($this->jwt->getClaim('https://purl.imsglobal.org/spec/lti/claim/tool_platform', '')) &&
                empty($this->messageParameters['tool_consumer_instance_guid'])) {
                $this->ok = false;
                $this->reason = 'Missing guid property in https://purl.imsglobal.org/spec/lti/claim/tool_platform claim';
            }
        }
        if ($this->ok) {
            if ($this->messageParameters['lti_message_type'] === 'basic-lti-launch-request') {
                $this->ok = isset($this->messageParameters['resource_link_id']) && (strlen(trim($this->messageParameters['resource_link_id'])) > 0);
                if (!$this->ok) {
                    $this->reason = 'Missing resource link ID.';
                }
                if ($this->ok && ($this->messageParameters['lti_version'] === Util::LTI_VERSION1P3)) {
                    $this->ok = isset($this->messageParameters['roles']);
                    if (!$this->ok) {
                        $this->reason = 'Missing roles parameter.';
                    }
                }
            } elseif (($this->messageParameters['lti_message_type'] === 'ContentItemSelectionRequest') ||
                ($this->messageParameters['lti_message_type'] === 'ContentItemUpdateRequest')) {
                $isUpdate = ($this->messageParameters['lti_message_type'] === 'ContentItemUpdateRequest');
                $mediaTypes = array();
                $contentTypes = array();
                $fileTypes = array();
                if (isset($this->messageParameters['accept_media_types']) && (strlen(trim($this->messageParameters['accept_media_types'])) > 0)) {
                    $mediaTypes = array_filter(
                        explode(',', str_replace(' ', '', $this->messageParameters['accept_media_types'])),
                        'strlen'
                    );
                }
                $this->ok = (count($mediaTypes) > 0) || ($this->messageParameters['lti_version'] === Util::LTI_VERSION1P3);
                if (!$this->ok) {
                    $this->reason = 'No content types specified.';
                } elseif ($isUpdate) {
                    if ($this->messageParameters['lti_version'] !== Util::LTI_VERSION1P3) {
                        if (!$this->checkValue(
                            $this->messageParameters['accept_media_types'],
                            array(Item::LTI_LINK_MEDIA_TYPE, Item::LTI_ASSIGNMENT_MEDIA_TYPE),
                            'Invalid value in accept_media_types parameter: \'%s\'.',
                            $strictMode,
                            true
                        )) {
                            $this->ok = false;
                        }
                    } elseif (!$this->checkValue(
                        $this->messageParameters['accept_types'],
                        array(Item::TYPE_LTI_LINK, Item::TYPE_LTI_ASSIGNMENT),
                        'Invalid value in accept_types parameter: \'%s\'.',
                        $strictMode,
                        true
                    )) {
                        $this->ok = false;
                    }
                }
                if ($this->ok) {
                    $mediaTypes = array_unique($mediaTypes);
                    foreach ($mediaTypes as $mediaType) {
                        if (strpos($mediaType, 'application/vnd.ims.lti.') !== 0) {
                            $fileTypes[] = $mediaType;
                        }
                        if (($mediaType === 'text/html') || ($mediaType === '*/*')) {
                            $contentTypes[] = Item::TYPE_LINK;
                            $contentTypes[] = Item::TYPE_HTML;
                        } elseif ((strpos($mediaType, 'image/') === 0) || ($mediaType === '*/*')) {
                            $contentTypes[] = Item::TYPE_IMAGE;
                        } elseif ($mediaType === Item::LTI_LINK_MEDIA_TYPE) {
                            $contentTypes[] = Item::TYPE_LTI_LINK;
                        } elseif ($mediaType === Item::LTI_ASSIGNMENT_MEDIA_TYPE) {
                            $contentTypes[] = Item::TYPE_LTI_ASSIGNMENT;
                        }
                    }
                    if (!empty($fileTypes)) {
                        $contentTypes[] = Item::TYPE_FILE;
                    }
                    $contentTypes = array_unique($contentTypes);
                }
                if ($this->ok) {
                    if (isset($this->messageParameters['accept_presentation_document_targets']) &&
                        (strlen(trim($this->messageParameters['accept_presentation_document_targets'])) > 0)) {
                        $documentTargets = array_filter(explode(
                            ',',
                            str_replace(' ', '', $this->messageParameters['accept_presentation_document_targets'])
                        ), 'strlen');
                        $documentTargets = array_unique($documentTargets);
                        $this->ok = count($documentTargets) > 0;
                        if (!$this->ok) {
                            $this->reason = 'Missing or empty accept_presentation_document_targets parameter.';
                        } else {
                            if (empty($this->jwt) || !$this->jwt->hasJwt()) {
                                $permittedTargets = array('embed', 'frame', 'iframe', 'window', 'popup', 'overlay', 'none');
                            } else {  // JWT
                                $permittedTargets = array('embed', 'iframe', 'window');
                            }
                            foreach ($documentTargets as $documentTarget) {
                                if (!$this->checkValue(
                                    $documentTarget,
                                    $permittedTargets,
                                    'Invalid value in accept_presentation_document_targets parameter: \'%s\'.',
                                    $strictMode,
                                    true
                                )) {
                                    $this->ok = false;
                                    break;
                                }
                            }
                            if ($this->ok) {
                                $this->documentTargets = $documentTargets;
                            }
                        }
                    } else {
                        $this->ok = false;
                        $this->reason = 'No accept_presentation_document_targets parameter found.';
                    }
                }
                if ($this->ok) {
                    $this->ok = !empty($this->messageParameters['content_item_return_url']);
                    if (!$this->ok) {
                        $this->reason = 'Missing content_item_return_url parameter.';
                    } else {
                        $this->mediaTypes = $mediaTypes;
                        $this->contentTypes = $contentTypes;
                        $this->fileTypes = $fileTypes;
                    }
                }
            } elseif ($this->messageParameters['lti_message_type'] === 'ToolProxyRegistrationRequest') {
                $this->ok = ((isset($this->messageParameters['reg_key']) && (strlen(trim($this->messageParameters['reg_key'])) > 0)) && (isset($this->messageParameters['reg_password']) && (strlen(trim($this->messageParameters['reg_password'])) >
                            0)) && (isset($this->messageParameters['tc_profile_url']) && (strlen(trim($this->messageParameters['tc_profile_url'])) >
                            0)) && (isset($this->messageParameters['launch_presentation_return_url']) && (strlen(trim($this->messageParameters['launch_presentation_return_url'])) > 0)));
                if ($this->debugMode && !$this->ok) {
                    $this->reason = 'Missing message parameters.';
                }
            } elseif ($this->messageParameters['lti_message_type'] === 'LtiStartProctoring') {
                $this->ok = isset($this->messageParameters['resource_link_id']) && (strlen(trim($this->messageParameters['resource_link_id'])) > 0);
                if (!$this->ok) {
                    $this->reason = 'Missing resource link ID.';
                } else {
                    $this->ok = isset($this->messageParameters['custom_ap_attempt_number']) && (strlen(trim($this->messageParameters['custom_ap_attempt_number'])) > 0) &&
                        is_numeric($this->messageParameters['custom_ap_attempt_number']);
                    if (!$this->ok) {
                        $this->reason = 'Missing or invalid value for attempt number.';
                    }
                }
                if ($this->ok) {
                    $this->ok = isset($this->messageParameters['user_id']) && (strlen(trim($this->messageParameters['user_id'])) > 0);
                    if (!$this->ok) {
                        $this->reason = 'Missing user ID.';
                    }
                }
            }
        }
        $now = time();
        // Check consumer key
        if ($this->ok && ($this->messageParameters['lti_message_type'] !== 'ToolProxyRegistrationRequest')) {
            $this->ok = isset($this->messageParameters['oauth_consumer_key']);
            if (!$this->ok) {
                $this->reason = 'Missing consumer key.';
            }
            if ($this->ok) {
                $this->ok = !is_null($this->platform->created);
                if (!$this->ok) {
                    if (empty($this->jwt) || !$this->jwt->hasJwt()) {
                        $this->reason = "Consumer key not recognised: {$this->messageParameters['oauth_consumer_key']}";
                    } else {
                        $this->reason = "Platform not recognised (Platform ID | Client ID | Deployment ID): {$this->messageParameters['platform_id']} | {$this->messageParameters['oauth_consumer_key']} | {$this->messageParameters['deployment_id']}";
                    }
                }
            }
            if ($this->ok) {
                if ($this->messageParameters['oauth_signature_method'] !== $this->platform->signatureMethod) {
                    $this->platform->signatureMethod = $this->messageParameters['oauth_signature_method'];
                    $doSavePlatform = true;
                }
                $today = date('Y-m-d', $now);
                if (is_null($this->platform->lastAccess)) {
                    $doSavePlatform = true;
                } else {
                    $last = date('Y-m-d', $this->platform->lastAccess);
                    $doSavePlatform = $doSavePlatform || ($last !== $today);
                }
                $this->platform->lastAccess = $now;
                $this->ok = $this->verifySignature();
            }
            if ($this->ok) {
                if ($this->platform->protected) {
                    if (!is_null($this->platform->consumerGuid)) {
                        $this->ok = empty($this->messageParameters['tool_consumer_instance_guid']) || ($this->platform->consumerGuid === $this->messageParameters['tool_consumer_instance_guid']);
                        if (!$this->ok) {
                            $this->reason = 'Request is from an invalid platform.';
                        }
                    } else {
                        $this->ok = isset($this->messageParameters['tool_consumer_instance_guid']);
                        if (!$this->ok) {
                            $this->reason = 'A platform GUID must be included in the launch request.';
                        }
                    }
                }
                if ($this->ok) {
                    $this->ok = $this->platform->enabled;
                    if (!$this->ok) {
                        $this->reason = 'Platform has not been enabled by the tool.';
                    }
                }
                if ($this->ok) {
                    $this->ok = is_null($this->platform->enableFrom) || ($this->platform->enableFrom <= $now);
                    if ($this->ok) {
                        $this->ok = is_null($this->platform->enableUntil) || ($this->platform->enableUntil > $now);
                        if (!$this->ok) {
                            $this->reason = 'Platform access has expired.';
                        }
                    } else {
                        $this->reason = 'Platform access is not yet available.';
                    }
                }
            }
            // Validate other message parameter values
            if ($this->ok) {
                if (($this->messageParameters['lti_message_type'] === 'ContentItemSelectionRequest') ||
                    ($this->messageParameters['lti_message_type'] === 'ContentItemUpdateRequest')) {
                    $isUpdate = ($this->messageParameters['lti_message_type'] === 'ContentItemUpdateRequest');
                    if (isset($this->messageParameters['accept_unsigned'])) {
                        $this->ok = $this->checkValue(
                            $this->messageParameters['accept_unsigned'],
                            array('true', 'false'),
                            'Invalid value for accept_unsigned parameter: \'%s\'.',
                            $strictMode
                        );
                    }
                    if ($this->ok && isset($this->messageParameters['accept_multiple'])) {
                        if (!$isUpdate) {
                            $this->ok = $this->checkValue(
                                $this->messageParameters['accept_multiple'],
                                array('true', 'false'),
                                'Invalid value for accept_multiple parameter: \'%s\'.',
                                $strictMode
                            );
                        } else {
                            $this->ok = $this->checkValue(
                                $this->messageParameters['accept_multiple'],
                                array('false'),
                                'Invalid value for accept_multiple parameter: \'%s\'.',
                                $strictMode
                            );
                        }
                    }
                    if ($this->ok && isset($this->messageParameters['accept_copy_advice'])) {
                        if (!$isUpdate) {
                            $this->ok = $this->checkValue(
                                $this->messageParameters['accept_copy_advice'],
                                array('true', 'false'),
                                'Invalid value for accept_copy_advice parameter: \'%s\'.',
                                $strictMode
                            );
                        } else {
                            $this->ok = $this->checkValue(
                                $this->messageParameters['accept_copy_advice'],
                                array('false'),
                                'Invalid value for accept_copy_advice parameter: \'%s\'.',
                                $strictMode
                            );
                        }
                    }
                    if ($this->ok && isset($this->messageParameters['auto_create'])) {
                        $this->ok = $this->checkValue(
                            $this->messageParameters['auto_create'],
                            array('true', 'false'),
                            'Invalid value for auto_create parameter: \'%s\'.',
                            $strictMode
                        );
                    }
                    if ($this->ok && isset($this->messageParameters['can_confirm'])) {
                        $this->ok = $this->checkValue(
                            $this->messageParameters['can_confirm'],
                            array('true', 'false'),
                            'Invalid value for can_confirm parameter: \'%s\'.',
                            $strictMode
                        );
                    }
                }
                if ($this->ok && isset($this->messageParameters['launch_presentation_document_target'])) {
                    $this->ok = $this->checkValue(
                        $this->messageParameters['launch_presentation_document_target'],
                        array('embed', 'frame', 'iframe', 'window', 'popup', 'overlay'),
                        'Invalid value for launch_presentation_document_target parameter: \'%s\'.',
                        $strictMode,
                        true
                    );
                    if ($this->ok && ($this->messageParameters['lti_message_type'] === 'LtiStartProctoring') &&
                        ($this->messageParameters['launch_presentation_document_target'] !== 'window')) {
                        $this->ok = !isset($this->messageParameters['launch_presentation_height']) &&
                            !isset($this->messageParameters['launch_presentation_width']);
                        if (!$this->ok) {
                            $this->reason = 'Height and width parameters must only be included for the window document target.';
                        }
                    }
                }
            }
        }

        if ($this->ok && ($this->messageParameters['lti_message_type'] === 'ToolProxyRegistrationRequest')) {
            $this->ok = $this->messageParameters['lti_version'] === Util::LTI_VERSION2;
            if (!$this->ok) {
                $this->reason = 'Invalid lti_version parameter.';
            }
            if ($this->ok) {
                $url = $this->messageParameters['tc_profile_url'];
                if (strpos($url, '?') === false) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }
                $url .= 'lti_version=' . Util::LTI_VERSION2;
                $http = new HttpMessage($url, 'GET', null, 'Accept: application/vnd.ims.lti.v2.toolconsumerprofile+json');
                $this->ok = $http->send();
                if (!$this->ok) {
                    $this->reason = 'Platform profile not accessible.';
                } else {
                    $tcProfile = json_decode($http->response);
                    $this->ok = !is_null($tcProfile);
                    if (!$this->ok) {
                        $this->reason = 'Invalid JSON in platform profile.';
                    }
                }
            }
            // Check for required capabilities
            if ($this->ok) {
                $this->platform = Platform::fromConsumerKey($this->messageParameters['reg_key'], $this->dataConnector);
                $this->platform->profile = $tcProfile;
                $capabilities = $this->platform->profile->capability_offered;
                $missing = array();
                foreach ($this->resourceHandlers as $resourceHandler) {
                    foreach ($resourceHandler->requiredMessages as $message) {
                        if (!in_array($message->type, $capabilities)) {
                            $missing[$message->type] = true;
                        }
                    }
                }
                foreach ($this->constraints as $name => $constraint) {
                    if ($constraint['required']) {
                        if (empty(array_intersect(
                            $capabilities,
                            array_keys(array_intersect(self::$CUSTOM_SUBSTITUTION_VARIABLES, array($name)))
                        ))) {
                            $missing[$name] = true;
                        }
                    }
                }
                if (!empty($missing)) {
                    ksort($missing);
                    $this->reason = 'Required capability not offered - \'' . implode('\', \'', array_keys($missing)) . '\'';
                    $this->ok = false;
                }
            }
            // Check for required services
            if ($this->ok) {
                foreach ($this->requiredServices as $service) {
                    foreach ($service->formats as $format) {
                        if (!$this->findService($format, $service->actions)) {
                            if ($this->ok) {
                                $this->reason = 'Required service(s) not offered - ';
                                $this->ok = false;
                            } else {
                                $this->reason .= ', ';
                            }
                            $this->reason .= "'{$format}' [" . implode(', ', $service->actions) . ']';
                        }
                    }
                }
            }
            if ($this->ok) {
                if ($this->messageParameters['lti_message_type'] === 'ToolProxyRegistrationRequest') {
                    $this->platform->profile = $tcProfile;
                    $this->platform->secret = $this->messageParameters['reg_password'];
                    $this->platform->ltiVersion = $this->messageParameters['lti_version'];
                    $this->platform->name = $tcProfile->product_instance->service_owner->service_owner_name->default_value;
                    $this->platform->consumerName = $this->platform->name;
                    $this->platform->consumerVersion = "{$tcProfile->product_instance->product_info->product_family->code}-{$tcProfile->product_instance->product_info->product_version}";
                    $this->platform->consumerGuid = $tcProfile->product_instance->guid;
                    $this->platform->enabled = true;
                    $this->platform->protected = true;
                    $doSavePlatform = true;
                }
            }
        } elseif ($this->ok && !empty($this->messageParameters['custom_tc_profile_url']) && empty($this->platform->profile)) {
            $url = $this->messageParameters['custom_tc_profile_url'];
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= 'lti_version=' . $this->messageParameters['lti_version'];
            $http = new HttpMessage($url, 'GET', null, 'Accept: application/vnd.ims.lti.v2.toolconsumerprofile+json');
            if ($http->send()) {
                $tcProfile = json_decode($http->response);
                if (!is_null($tcProfile)) {
                    $this->platform->profile = $tcProfile;
                    $doSavePlatform = true;
                }
            }
        }

        if ($this->ok) {

// Check if a relaunch is being requested
            if (isset($this->messageParameters['relaunch_url'])) {
                if (empty($this->messageParameters['platform_state'])) {
                    $this->ok = false;
                    $this->reason = 'Missing or empty platform_state parameter.';
                } else {
                    $this->sendRelaunchRequest();
                }
            } else {

// Validate message parameter constraints
                $invalidParameters = array();
                foreach ($this->constraints as $name => $constraint) {
                    if (empty($constraint['messages']) || in_array(
                        $this->messageParameters['lti_message_type'],
                        $constraint['messages']
                    )) {
                        $ok = true;
                        if ($constraint['required']) {
                            if (!isset($this->messageParameters[$name]) || (strlen(trim($this->messageParameters[$name])) <= 0)) {
                                $invalidParameters[] = "{$name} (missing)";
                                $ok = false;
                            }
                        }
                        if ($ok && !is_null($constraint['max_length']) && isset($this->messageParameters[$name])) {
                            if (strlen(trim($this->messageParameters[$name])) > $constraint['max_length']) {
                                $invalidParameters[] = "{$name} (too long)";
                            }
                        }
                    }
                }
                if (count($invalidParameters) > 0) {
                    $this->ok = false;
                    if (empty($this->reason)) {
                        $this->reason = 'Invalid parameter(s): ' . implode(', ', $invalidParameters) . '.';
                    }
                }

                if ($this->ok) {

// Set the request context
                    $contextId = '';
                    //UK: Check if necessary
                    if ($this->hasConfiguredApiHook(self::$CONTEXT_ID_HOOK, $this->platform->getFamilyCode(), $this)) {
                        $className = $this->getApiHook(self::$CONTEXT_ID_HOOK, $this->platform->getFamilyCode());
                        $tpHook = new $className($this);
                        $contextId = $tpHook->getContextId();
                    }
                    if (empty($contextId) && isset($this->messageParameters['context_id'])) {
                        $contextId = trim($this->messageParameters['context_id']);
                    }
                    if (!empty($contextId)) {
                        $this->context = Context::fromPlatform($this->platform, $contextId);
                        $title = '';
                        if (isset($this->messageParameters['context_title'])) {
                            $title = trim($this->messageParameters['context_title']);
                        }
                        if (empty($title)) {
                            $title = "Course {$this->context->getId()}";
                        }
                        $this->context->title = $title;
                        if (isset($this->messageParameters['context_type'])) {
                            $this->context->type = trim($this->messageParameters['context_type']);
                            if (strpos($this->context->type, 'http://purl.imsglobal.org/vocab/lis/v2/course#') === 0) {
                                $this->context->type = substr($this->context->type, 46);
                            }
                        }
                    }

                    // Set the request resource link
                    if (isset($this->messageParameters['resource_link_id'])) {
                        $contentItemId = '';
                        if (isset($this->messageParameters['custom_content_item_id'])) {
                            $contentItemId = $this->messageParameters['custom_content_item_id'];
                        }
                        if (empty($this->context)) {
                            $this->resourceLink = ResourceLink::fromPlatform(
                                $this->platform,
                                trim($this->messageParameters['resource_link_id']),
                                $contentItemId
                            );
                        } else {
                            $this->resourceLink = ResourceLink::fromContext(
                                $this->context,
                                trim($this->messageParameters['resource_link_id']),
                                $contentItemId
                            );
                        }
                        $title = '';
                        if (isset($this->messageParameters['resource_link_title'])) {
                            $title = trim($this->messageParameters['resource_link_title']);
                        }
                        if (empty($title)) {
                            $title = "Resource {$this->resourceLink->getId()}";
                        }
                        $this->resourceLink->title = $title;
                    }
                    // Delete any existing custom parameters
                    foreach ($this->platform->getSettings() as $name => $value) {
                        if ((strpos($name, 'custom_') === 0) && (!in_array($name, self::$LTI_RETAIN_SETTING_NAMES))) {
                            $this->platform->setSetting($name);
                            $doSavePlatform = true;
                        }
                    }
                    if (!empty($this->context)) {
                        foreach ($this->context->getSettings() as $name => $value) {
                            if ((strpos($name, 'custom_') === 0) && (!in_array($name, self::$LTI_RETAIN_SETTING_NAMES))) {
                                $this->context->setSetting($name);
                            }
                        }
                    }
                    if (!empty($this->resourceLink)) {
                        foreach ($this->resourceLink->getSettings() as $name => $value) {
                            if ((strpos($name, 'custom_') === 0) && (!in_array($name, self::$LTI_RETAIN_SETTING_NAMES))) {
                                $this->resourceLink->setSetting($name);
                            }
                        }
                    }
                    // Save LTI parameters
                    foreach (self::$LTI_CONSUMER_SETTING_NAMES as $name) {
                        if (isset($this->messageParameters[$name])) {
                            $this->platform->setSetting($name, $this->messageParameters[$name]);
                        } elseif (!in_array($name, self::$LTI_RETAIN_SETTING_NAMES)) {
                            $this->platform->setSetting($name);
                        }
                    }
                    if (!empty($this->context)) {
                        foreach (self::$LTI_CONTEXT_SETTING_NAMES as $name) {
                            if (isset($this->messageParameters[$name])) {
                                $this->context->setSetting($name, $this->messageParameters[$name]);
                            } elseif (!in_array($name, self::$LTI_RETAIN_SETTING_NAMES)) {
                                $this->context->setSetting($name);
                            }
                        }
                    }
                    if (!empty($this->resourceLink)) {
                        foreach (self::$LTI_RESOURCE_LINK_SETTING_NAMES as $name) {
                            if (isset($this->messageParameters[$name])) {
                                $this->resourceLink->setSetting($name, $this->messageParameters[$name]);
                            } elseif (!in_array($name, self::$LTI_RETAIN_SETTING_NAMES)) {
                                $this->resourceLink->setSetting($name);
                            }
                        }
                    }
                    // Save other custom parameters at all levels
                    foreach ($this->messageParameters as $name => $value) {
                        if ((strpos($name, 'custom_') === 0) && !in_array(
                            $name,
                            array_merge(
                                self::$LTI_CONSUMER_SETTING_NAMES,
                                self::$LTI_CONTEXT_SETTING_NAMES,
                                self::$LTI_RESOURCE_LINK_SETTING_NAMES
                            )
                        )) {
                            $this->platform->setSetting($name, $value);
                            if (!empty($this->context)) {
                                $this->context->setSetting($name, $value);
                            }
                            if (!empty($this->resourceLink)) {
                                $this->resourceLink->setSetting($name, $value);
                            }
                        }
                    }

                    // Set the user instance
                    $userId = '';
                    if ($this->hasConfiguredApiHook(self::$USER_ID_HOOK, $this->platform->getFamilyCode(), $this)) {
                        $className = $this->getApiHook(self::$USER_ID_HOOK, $this->platform->getFamilyCode());
                        $tpHook = new $className($this);
                        $userId = $tpHook->getUserId();
                    }
                    if (empty($userId) && isset($this->messageParameters['user_id'])) {
                        $userId = trim($this->messageParameters['user_id']);
                    }

                    $this->userResult = UserResult::fromResourceLink($this->resourceLink, $userId);

                    // Set the user name
                    $firstname = (isset($this->messageParameters['lis_person_name_given'])) ? $this->messageParameters['lis_person_name_given'] : '';
                    $lastname = (isset($this->messageParameters['lis_person_name_family'])) ? $this->messageParameters['lis_person_name_family'] : '';
                    $fullname = (isset($this->messageParameters['lis_person_name_full'])) ? $this->messageParameters['lis_person_name_full'] : '';
                    $this->userResult->setNames($firstname, $lastname, $fullname);

                    // Set the sourcedId
                    if (isset($this->messageParameters['lis_person_sourcedid'])) {
                        $this->userResult->sourcedId = $this->messageParameters['lis_person_sourcedid'];
                    }

                    // Set the username
                    if (isset($this->messageParameters['ext_username'])) {
                        $this->userResult->username = $this->messageParameters['ext_username'];
                    } elseif (isset($this->messageParameters['ext_user_username'])) {
                        $this->userResult->username = $this->messageParameters['ext_user_username'];
                    } elseif (isset($this->messageParameters['custom_username'])) {
                        $this->userResult->username = $this->messageParameters['custom_username'];
                    } elseif (isset($this->messageParameters['custom_user_username'])) {
                        $this->userResult->username = $this->messageParameters['custom_user_username'];
                    }

                    // Set the user email
                    $email = (isset($this->messageParameters['lis_person_contact_email_primary'])) ? $this->messageParameters['lis_person_contact_email_primary'] : '';
                    $this->userResult->setEmail($email, $this->defaultEmail);

                    // Set the user image URI
                    if (isset($this->messageParameters['user_image'])) {
                        $this->userResult->image = $this->messageParameters['user_image'];
                    }

                    // Set the user roles
                    if (isset($this->messageParameters['roles'])) {
                        $this->userResult->roles = self::parseRoles(
                            $this->messageParameters['roles'],
                            $this->messageParameters['lti_version']
                        );
                    }

                    // Initialise the platform and check for changes
                    $this->platform->defaultEmail = $this->defaultEmail;
                    if ($this->platform->ltiVersion !== $this->messageParameters['lti_version']) {
                        $this->platform->ltiVersion = $this->messageParameters['lti_version'];
                        $doSavePlatform = true;
                    }
                    if (isset($this->messageParameters['deployment_id'])) {
                        $this->platform->deploymentId = $this->messageParameters['deployment_id'];
                    }
                    if (isset($this->messageParameters['tool_consumer_instance_name'])) {
                        if ($this->platform->consumerName !== $this->messageParameters['tool_consumer_instance_name']) {
                            $this->platform->consumerName = $this->messageParameters['tool_consumer_instance_name'];
                            $doSavePlatform = true;
                        }
                    }
                    if (isset($this->messageParameters['tool_consumer_info_product_family_code'])) {
                        $version = $this->messageParameters['tool_consumer_info_product_family_code'];
                        if (isset($this->messageParameters['tool_consumer_info_version'])) {
                            $version .= "-{$this->messageParameters['tool_consumer_info_version']}";
                        }
                        // do not delete any existing consumer version if none is passed
                        if ($this->platform->consumerVersion !== $version) {
                            $this->platform->consumerVersion = $version;
                            $doSavePlatform = true;
                        }
                    } elseif (isset($this->messageParameters['ext_lms']) && ($this->platform->consumerName !== $this->messageParameters['ext_lms'])) {
                        $this->platform->consumerVersion = $this->messageParameters['ext_lms'];
                        $doSavePlatform = true;
                    }
                    if (isset($this->messageParameters['tool_consumer_instance_guid'])) {
                        if (is_null($this->platform->consumerGuid)) {
                            $this->platform->consumerGuid = $this->messageParameters['tool_consumer_instance_guid'];
                            $doSavePlatform = true;
                        } elseif (!$this->platform->protected && ($this->platform->consumerGuid !== $this->messageParameters['tool_consumer_instance_guid'])) {
                            $this->platform->consumerGuid = $this->messageParameters['tool_consumer_instance_guid'];
                            $doSavePlatform = true;
                        }
                    }
                    if (isset($this->messageParameters['launch_presentation_css_url'])) {
                        if ($this->platform->cssPath !== $this->messageParameters['launch_presentation_css_url']) {
                            $this->platform->cssPath = $this->messageParameters['launch_presentation_css_url'];
                            $doSavePlatform = true;
                        }
                    } elseif (isset($this->messageParameters['ext_launch_presentation_css_url']) && ($this->platform->cssPath !== $this->messageParameters['ext_launch_presentation_css_url'])) {
                        $this->platform->cssPath = $this->messageParameters['ext_launch_presentation_css_url'];
                        $doSavePlatform = true;
                    } elseif (!empty($this->platform->cssPath)) {
                        $this->platform->cssPath = null;
                        $doSavePlatform = true;
                    }
                }

                // Persist changes to platform
                if ($doSavePlatform) {
                    $this->platform->save();
                }

                if ($this->ok) {

// Persist changes to cpntext
                    if (isset($this->context)) {
                        $this->context->save();
                    }

                    if (isset($this->resourceLink)) {
                        // Persist changes to resource link
                        $this->resourceLink->save();

                        // Persist changes to user instnce
                        $this->userResult->setResourceLinkId($this->resourceLink->getRecordId());
                        if (isset($this->messageParameters['lis_result_sourcedid'])) {
                            if ($this->userResult->ltiResultSourcedId !== $this->messageParameters['lis_result_sourcedid']) {
                                $this->userResult->ltiResultSourcedId = $this->messageParameters['lis_result_sourcedid'];
                                $this->userResult->save();
                            }
                        } elseif ($this->userResult->isLearner()) {  // Ensure all learners are recorded in case Assignment and Grade services are used
                            $this->userResult->ltiResultSourcedId = '';
                            $this->userResult->save();
                        }

                        // Check if a share arrangement is in place for this resource link
                        $this->ok = $this->checkForShare();
                    }
                }
            }
        }
        return $this->ok;
    }

    /**
     * Check if a share arrangement is in place.
     *
     * @return bool    True if no error is reported
     */
    private function checkForShare(): bool
    {
        $ok = true;
        $doSaveResourceLink = true;

        $id = $this->resourceLink->primaryResourceLinkId;

        $shareRequest = isset($this->messageParameters['custom_share_key']) && !empty($this->messageParameters['custom_share_key']);
        if ($shareRequest) {
            if (!$this->allowSharing) {
                $ok = false;
                $this->reason = 'Your sharing request has been refused because sharing is not being permitted.';
            } else {
                // Check if this is a new share key
                $shareKey = new ResourceLinkShareKey($this->resourceLink, $this->messageParameters['custom_share_key']);
                if (!is_null($shareKey->resourceLinkId)) {
                    // Update resource link with sharing primary resource link details
                    $id = $shareKey->resourceLinkId;
                    $ok = ($id !== $this->resourceLink->getRecordId());
                    if ($ok) {
                        $this->resourceLink->primaryResourceLinkId = $id;
                        $this->resourceLink->shareApproved = $shareKey->autoApprove;
                        $ok = $this->resourceLink->save();
                        if ($ok) {
                            $doSaveResourceLink = false;
                            $this->userResult->getResourceLink()->primaryResourceLinkId = $id;
                            $this->userResult->getResourceLink()->shareApproved = $shareKey->autoApprove;
                            $this->userResult->getResourceLink()->updated = time();
                            // Remove share key
                            $shareKey->delete();
                        } else {
                            $this->reason = 'An error occurred initialising your share arrangement.';
                        }
                    } else {
                        $this->reason = 'It is not possible to share your resource link with yourself.';
                    }
                }
                if ($ok) {
                    $ok = !is_null($id);
                    if (!$ok) {
                        $this->reason = 'You have requested to share a resource link but none is available.';
                    } else {
                        $ok = (!is_null($this->userResult->getResourceLink()->shareApproved) && $this->userResult->getResourceLink()->shareApproved);
                        if (!$ok) {
                            $this->reason = 'Your share request is waiting to be approved.';
                        }
                    }
                }
            }
        } else {
            // Check no share is in place
            $ok = is_null($id);
            if (!$ok) {
                $this->reason = 'You have not requested to share a resource link but an arrangement is currently in place.';
            }
        }

        // Look up primary resource link
        if ($ok && !is_null($id)) {
            $resourceLink = ResourceLink::fromRecordId($id, $this->dataConnector);
            $ok = !is_null($resourceLink->created);
            if ($ok) {
                if ($doSaveResourceLink) {
                    $this->resourceLink->save();
                }
                $this->resourceLink = $resourceLink;
            } else {
                $this->reason = 'Unable to load resource link being shared.';
            }
        }

        return $ok;
    }

    /**
     * Generate a form to perform an authentication request.
     * @param array $parameters Request parameters
     * @return bool True if form was generated
     */
    private function sendAuthenticationRequest(array $parameters): bool
    {
        $clientId = null;
        if (isset($parameters['client_id'])) {
            $clientId = $parameters['client_id'];
        }
        $deploymentId = null;
        if (isset($parameters['lti_deployment_id'])) {
            $deploymentId = $parameters['lti_deployment_id'];
        }
        $currentLogLevel = Util::$logLevel;
        $this->platform = \ilLTIPlatform::fromPlatformId($parameters['iss'], $clientId, $deploymentId, $this->dataConnector);
        if ($this->platform->debugMode && ($currentLogLevel < Util::LOGLEVEL_INFO)) {
            $this->debugMode = true;
            Util::logRequest();
        }
        $ok = !is_null($this->platform) && !empty($this->platform->authenticationUrl);
        if (!$ok) {
            $this->reason = 'Platform not found or no platform authentication request URL.';
        } else {
            do {
                $nonce = new PlatformNonce($this->platform, Util::getRandomString());
                $ok = !$nonce->load();
            } while (!$ok);
            $nonce->expires = time() + 10;  // Expire after 10 seconds
            $ok = $nonce->save();
            if ($ok) {
//                $oauthRequest = OAuth\OAuthRequest::from_request();
                $oauthRequest = LTIOAuth\OAuthRequest::from_request();
                $redirectUri = $oauthRequest->get_normalized_http_url();
                if (!empty($_SERVER['QUERY_STRING'])) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $redirectUri .= "?{$_SERVER['QUERY_STRING']}";
                    } else {  // Remove all parameters added by platform from query string
                        $queryString = '';
                        $params = explode('&', $_SERVER['QUERY_STRING']);
                        $ignore = false;  // Only include those query parameters which come before any of the standard OpenID Connect ones
                        foreach ($params as $param) {
                            $parts = explode('=', $param, 2);
                            if (in_array(
                                $parts[0],
                                array('iss', 'target_link_uri', 'login_hint', 'lti_message_hint', 'client_id', 'lti_deployment_id')
                            )) {
                                $ignore = true;
                            } elseif (!$ignore) {
                                if ((count($parts) <= 1) || empty($parts[1])) {  // Drop equals sign for empty parameters to workaround Canvas bug
                                    $queryString .= "&{$parts[0]}";
                                } else {
                                    $queryString .= "&{$parts[0]}={$parts[1]}";
                                }
                            }
                        }
                        if (!empty($queryString)) {
                            $queryString = substr($queryString, 1);
                            $redirectUri .= "?{$queryString}";
                        }
                    }
                }
                $params = array(
                    'client_id' => $this->platform->clientId,
                    'login_hint' => $parameters['login_hint'],
                    'nonce' => Util::getRandomString(32),
                    'prompt' => 'none',
                    'redirect_uri' => $redirectUri,
                    'response_mode' => 'form_post',
                    'response_type' => 'id_token',
                    'scope' => 'openid',
                    'state' => $nonce->getValue()
                );
                if (isset($parameters['lti_message_hint'])) {
                    $params['lti_message_hint'] = $parameters['lti_message_hint'];
                }
                $this->onInitiateLogin($parameters, $params);
                if (!Tool::$authenticateUsingGet) {
                    $this->output = Util::sendForm($this->platform->authenticationUrl, $params);
                } else {
                    Util::redirect($this->platform->authenticationUrl, $params);
                }
            } else {
                $this->reason = 'Unable to generate a state value.';
            }
        }

        return $ok;
    }

    /**
     * Generate a form to perform a relaunch request.
     */
    private function sendRelaunchRequest()
    {
        do {
            $nonce = new PlatformNonce($this->platform, Util::getRandomString());
            $ok = !$nonce->load();
        } while (!$ok);
        $ok = $nonce->save();
        if ($ok) {
            $params = array(
                'tool_state' => $nonce->getValue(),
                'platform_state' => $this->messageParameters['platform_state']
            );
            $params = $this->platform->addSignature($this->messageParameters['relaunch_url'], $params);
            $this->output = Util::sendForm($this->messageParameters['relaunch_url'], $params);
        } else {
            $this->reason = 'Unable to generate a state value.';
        }
    }

    /**
     * Validate a parameter value from an array of permitted values.
     * @param mixed  $value         Value to be checked
     * @param array  $values        Array of permitted values
     * @param string $reason        Reason to generate when the value is not permitted
     * @param bool   $strictMode    True if full compliance with the LTI specification is required
     * @param bool   $ignoreInvalid True if invalid values are to be ignored (optional default is false)
     * @return bool    True if value is valid
     */
    private function checkValue(&$value, array $values, string $reason, bool $strictMode, bool $ignoreInvalid = false): bool
    {
        $lookupValue = $value;
        if (!$strictMode) {
            $lookupValue = strtolower($value);
        }
        $ok = in_array($lookupValue, $values);
        if (!$ok && !$strictMode && $ignoreInvalid) {
            Util::logInfo(sprintf($reason, $value) . " [Error ignored]");
            $ok = true;
        } elseif (!$ok && !empty($reason)) {
            $this->reason = sprintf($reason, $value);
        } elseif ($lookupValue !== $value) {
            Util::logInfo(sprintf($reason, $value) . " [Changed to '{$lookupValue}']");
            $value = $lookupValue;
        }

        return $ok;
    }
}
