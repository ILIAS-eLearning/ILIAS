<?php

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTI\Profile\Item;
use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;
//use ILIAS\LTI\ToolProvider\MediaType;
use ILIAS\LTI\Profile;
use ILIAS\LTI\HTTPMessage;
use ILIAS\LTIOAuth;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
#require_once dirname(__DIR__, 4) . "/Modules/LTIConsumer/lib/OAuth.php";
#require_once dirname(__DIR__, 2) . "/src/OAuth/OAuth.php";
class ToolProvider
{

/**
 * Default connection error message.
 */
    const CONNECTION_ERROR_MESSAGE = 'Sorry, there was an error connecting you to the application.';

    /**
     * LTI version 1 for messages.
     */
    const LTI_VERSION1 = 'LTI-1p0';
    /**
     * LTI version 2 for messages.
     */
    const LTI_VERSION2 = 'LTI-2p0';
    /**
     * Use ID value only.
     */
    const ID_SCOPE_ID_ONLY = 0;
    /**
     * Prefix an ID with the consumer key.
     */
    const ID_SCOPE_GLOBAL = 1;
    /**
     * Prefix the ID with the consumer key and context ID.
     */
    const ID_SCOPE_CONTEXT = 2;
    /**
     * Prefix the ID with the consumer key and resource ID.
     */
    const ID_SCOPE_RESOURCE = 3;
    /**
     * Character used to separate each element of an ID.
     */
    const ID_SCOPE_SEPARATOR = ':';

    /**
     * Permitted LTI versions for messages.
     */
    private static array $LTI_VERSIONS = array(self::LTI_VERSION1, self::LTI_VERSION2);
    /**
     * List of supported message types and associated class methods.
     */
    private static array $MESSAGE_TYPES = array('basic-lti-launch-request' => 'onLaunch',
                                          'ContentItemSelectionRequest' => 'onContentItem',
                                          'ToolProxyRegistrationRequest' => 'register');
    /**
         * List of supported message types and associated class methods
         */
    private static array $METHOD_NAMES = array('basic-lti-launch-request' => 'onLaunch',
                                         'ContentItemSelectionRequest' => 'onContentItem',
                                         'ToolProxyRegistrationRequest' => 'onRegister');
    /**
         * Names of LTI parameters to be retained in the consumer settings property.
         */
    private static array $LTI_CONSUMER_SETTING_NAMES = array('custom_tc_profile_url', 'custom_system_setting_url');
    /**
         * Names of LTI parameters to be retained in the context settings property.
         */
    private static array $LTI_CONTEXT_SETTING_NAMES = array('custom_context_setting_url',
                                                      'custom_lineitems_url', 'custom_results_url',
                                                      'custom_context_memberships_url');
    /**
         * Names of LTI parameters to be retained in the resource link settings property.
         */
    private static array $LTI_RESOURCE_LINK_SETTING_NAMES = array('lis_result_sourcedid', 'lis_outcome_service_url',
                                                            'ext_ims_lis_basic_outcome_url', 'ext_ims_lis_resultvalue_sourcedids',
                                                            'ext_ims_lis_memberships_id', 'ext_ims_lis_memberships_url',
                                                            'ext_ims_lti_tool_setting', 'ext_ims_lti_tool_setting_id', 'ext_ims_lti_tool_setting_url',
                                                            'custom_link_setting_url',
                                                            'custom_lineitem_url', 'custom_result_url');
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
                                                          'Results.url' => 'custom_results_url',
                                                          'Result.url' => 'custom_result_url',
                                                          'ToolProxyBinding.memberships.url' => 'custom_context_memberships_url');


    /**
     * True if the last request was successful.
     *
     * @var boolean $ok
     */
    public bool $ok = true;
    /**
     * Tool Consumer object.
     *
     * @var ToolConsumer $consumer
     */
    public ?ToolConsumer $consumer = null;
    /**
     * Return URL provided by tool consumer.
     *
     * @var string $returnUrl
     */
    public ?string $returnUrl = null;
    /**
     * User object.
     *
     * @var User $user
     */
    public ?User $user = null;
    /**
     * Resource link object.
     *
     * @var ResourceLink $resourceLink
     */
    public ?ResourceLink $resourceLink = null;
    /**
     * Context object.
     *
     * @var Context $context
     */
    public ?Context $context = null;
    /**
     * Data connector object.
     *
     * @var DataConnector $dataConnector
     */
    public ?DataConnector $dataConnector = null;
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
     * @var boolean $allowSharing
     */
    public bool $allowSharing = false;
    /**
     * Message for last request processed
     *
     * @var string $message
     */
    public string $message = self::CONNECTION_ERROR_MESSAGE;
    /**
     * Error message for last request processed.
     *
     * @var string $reason
     */
    public ?string $reason = null;
    /**
     * Details for error message relating to last request processed.
     *
     * @var array $details
     */
    public array $details = array();
    /**
     * Base URL for tool provider service
     *
     * @var string $baseUrl
     */
    public ?string $baseUrl = null;
    /**
     * Vendor details
     *
     * @var Item $vendor
     */
    public ?Item $vendor = null;
    /**
     * Product details
     *
     * @var Item $product
     */
    public ?Item $product = null;
    /**
     * Services required by Tool Provider
     *
     * @var array $requiredServices
     */
    public ?array $requiredServices = null;
    /**
     * Optional services used by Tool Provider
     *
     * @var array $optionalServices
     */
    public ?array $optionalServices = null;
    /**
     * Resource handlers for Tool Provider
     *
     * @var array $resourceHandlers
     */
    public ?array $resourceHandlers = null;

    /**
     * URL to redirect user to on successful completion of the request.
     *
     * @var string $redirectUrl
     */
    protected ?string $redirectUrl = null;
    /**
     * URL to redirect user to on successful completion of the request.
     *
     * @var string|string[]|null $mediaTypes
     */
    // TODO PHP8 Review: Union Types are not supported by PHP 7.4!
    protected $mediaTypes = null;
    /**
     * URL to redirect user to on successful completion of the request.
     *
     * @var string|string[]|null $documentTargets
     */
    // TODO PHP8 Review: Union Types are not supported by PHP 7.4!
    protected $documentTargets = null;
    /**
     * HTML to be displayed on a successful completion of the request.
     *
     * @var string $output
     */
    protected ?string $output = null;
    /**
     * HTML to be displayed on an unsuccessful completion of the request and no return URL is available.
     *
     * @var string $errorOutput
     */
    protected ?string $errorOutput = null;
    /**
     * Whether debug messages explaining the cause of errors are to be returned to the tool consumer.
     *
     * @var boolean $debugMode
     */
    protected bool $debugMode = false;

    /**
         * Callback functions for handling requests.
         */
    private ?array $callbackHandler = null;
    /**
     * LTI parameter constraints for auto validation checks.
     *
     * @var array $constraints
     */
    protected ?array $constraints = null;

    /**
     * Class constructor
     * @param DataConnector $dataConnector Object containing a database connection object
     */
    public function __construct(DataConnector $dataConnector)
    {
        global $DIC;
        $this->constraints = array();
        $this->dataConnector = $dataConnector;
        $this->ok = !is_null($this->dataConnector);

        // Set debug mode
        $this->debugMode = $DIC->http()->wrapper()->post()->has('custom_debug') && (strtolower($DIC->http()->wrapper()->post()->retrieve('custom_debug', $DIC->refinery()->kindlyTo()->string())) === 'true');

        // Set return URL if available
        if ($DIC->http()->wrapper()->post()->has('launch_presentation_return_url')) {
            $this->returnUrl = $DIC->http()->wrapper()->post()->retrieve('launch_presentation_return_url', $DIC->refinery()->kindlyTo()->string());
        } elseif ($DIC->http()->wrapper()->post()->has('content_item_return_url')) {
            $this->returnUrl = $DIC->http()->wrapper()->post()->retrieve('content_item_return_url', $DIC->refinery()->kindlyTo()->string());
        }
        $this->vendor = new Profile\Item();
        $this->product = new Profile\Item();
        $this->requiredServices = array();
        $this->optionalServices = array();
        $this->resourceHandlers = array();
    }


    /**
     * Add a parameter constraint to be checked on launch
     * @param string     $name         Name of parameter to be checked
     * @param boolean    $required     True if parameter is required (optional, default is true)
     * @param int|null   $maxLength    Maximum permitted length of parameter value (optional, default is null)
     * @param array|null $messageTypes Array of message types to which the constraint applies (optional, default is all)
     */
    public function setParameterConstraint(string $name, bool $required = true, int $maxLength = null, ?array $messageTypes = null) : void
    {
        $name = trim($name);
        if (strlen($name) > 0) {
            $this->constraints[$name] = array('required' => $required, 'max_length' => $maxLength, 'messages' => $messageTypes);
        }
    }

    /**
     * Get an array of defined tool consumers
     *
     * @return array Array of ToolConsumer objects
     */
    public function getConsumers() : array
    {
        return $this->dataConnector->getToolConsumers();
    }

    /**
     * Find an offered service based on a media type and HTTP action(s)
     *
     * @param string $format  Media type required
     * @param array  $methods Array of HTTP actions required
     *
     * @return object The service object
     */
    public function findService(string $format, array $methods) : object // TODO PHP8 Review: Check/Resolve Type-Mismatch, this can return an `object` or bool
    {
        $found = false;
        $services = $this->consumer->profile->service_offered; // TODO PHP8 Review: Undefined Property
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

//    /**
//     * Send the tool proxy to the Tool Consumer
//     *
//     * @return boolean True if the tool proxy was accepted
//     */
//    public function doToolProxyService() : bool
//    {
//
    //// Create tool proxy
//        $toolProxyService = $this->findService('application/vnd.ims.lti.v2.toolproxy+json', array('POST'));
//        $secret = DataConnector::getRandomString(12);
//        $toolProxy = new MediaType\ToolProxy($this, $toolProxyService, $secret);
//        $http = $this->consumer->doServiceRequest($toolProxyService, 'POST', 'application/vnd.ims.lti.v2.toolproxy+json', json_encode($toolProxy));
//        $ok = $http->ok && ($http->status == 201) && isset($http->responseJson->tool_proxy_guid) && (strlen($http->responseJson->tool_proxy_guid) > 0);
//        if ($ok) {
//            $this->consumer->setKey($http->responseJson->tool_proxy_guid);
//            $this->consumer->secret = $toolProxy->security_contract->shared_secret;
//            $this->consumer->toolProxy = json_encode($toolProxy);
//            $this->consumer->save();
//        }
//
//        return $ok;
//    }

    /**
     * Get an array of fully qualified user roles
     *
     * @param mixed $roles  Comma-separated list of roles or array of roles
     *
     * @return array Array of roles
     */
    public static function parseRoles(mixed $roles) : array // TODO PHP8 Review: Type `mixed` is not supported!
    {
        if (!is_array($roles)) {
            $roles = explode(',', $roles);
        }
        $parsedRoles = array();
        foreach ($roles as $role) {
            $role = trim($role);
            if (!empty($role)) {
                if (substr($role, 0, 4) !== 'urn:') {
                    $role = 'urn:lti:role:ims/lis/' . $role;
                }
                $parsedRoles[] = $role;
            }
        }

        return $parsedRoles;
    }

    /**
         * Generate a web page containing an auto-submitted form of parameters.
         *
         * @param string $url URL to which the form should be submitted
         * @param array $params Array of form parameters
         * @param string $target Name of target (optional)
         */
    public static function sendForm(string $url, array $params, string $target = '') : string
    {
        // TODO PHP8 Review: Please avoid inline HTML
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
<form action="{$url}" method="post" target="" encType="application/x-www-form-urlencoded">

EOD;

        foreach ($params as $key => $value) {
            $key = htmlentities($key, ENT_COMPAT | ENT_HTML401, 'UTF-8');
            $value = htmlentities($value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
            $page .= <<< EOD
    <input type="hidden" name="{$key}" value="{$value}" />

EOD;
        }

        $page .= <<< EOD
</form>
</body>
</html>
EOD;

        return $page;
    }

    ###
    ###    PROTECTED METHODS
    ###

    /**
     * Process a valid launch request
     *
     * @return boolean True if no error
     */
    protected function onLaunch() : void
    {
        $this->onError();
    }

    /**
     * Process a valid content-item request
     *
     * @return boolean True if no error
     */
    protected function onContentItem() : void
    {
        $this->onError();
    }

    /**
     * Process a valid tool proxy registration request
     *
     * @return boolean True if no error
     */
    protected function onRegister() : void
    {
        $this->onError();
    }

    /**
     * Process a response to an invalid request
     *
     * @return boolean True if no further error processing required
     */
    protected function onError() : void
    {
        $this->doCallback('onError');
    }

    ###
    ###    PRIVATE METHODS
    ###

    /**
     * Call any callback function for the requested action.
     *
     * This function may set the redirect_url and output properties.
     *
     * @return boolean True if no error reported
     */
    // TODO PHP8 Review: Missing Parameter Type Declaration
    private function doCallback($method = null) : void
    {
        global $DIC;
        $callback = $method;
        if (is_null($callback)) {
            $callback = self::$METHOD_NAMES[$DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string())];
        }
        if (method_exists($this, $callback)) {
            $result = $this->$callback();
        } elseif (is_null($method) && $this->ok) {
            $this->ok = false;
            $this->reason = "Message type not supported: {$DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string())}";
        }
        if ($this->ok && ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) == 'ToolProxyRegistrationRequest')) {
            $this->consumer->save();
        }
    }
}
