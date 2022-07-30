<?php declare(strict_types=1);

//use ILIAS\LTI\ToolProvider;
#use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;
#use ilLTIDataConnector;
use ILIAS\LTI\ToolProvider\MediaType;
use ILIAS\LTI\ToolProvider\Profile;
use ILIAS\LTI\ToolProvider\Content\Item;
use ILIAS\LTI\ToolProvider\Jwt\Jwt;
use ILIAS\LTI\ToolProvider\Http\HTTPMessage;
use ILIAS\LTIOAuth;
use ILIAS\LTI\ToolProvider\ApiHook\ApiHook;
use ILIAS\LTI\ToolProvider\Util;
#use ILIAS\LTI\ToolProvider\OAuthDataStore;
//added
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;
#use ILIAS\LTI\ToolProvider\User;
use ILIAS\LTI\ToolProvider\ResourceLinkShareKey;

#use ILIAS\LTI\Profile\Item;

#use ILIAS\LTI\Tool\MediaType;
#use ILIAS\LTI\Profile;

#use ILIAS\LTI\OAuth;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * LTI provider for LTI launch
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */
class ilLTITool extends ILIAS\LTI\ToolProvider\Tool
{
//    /**
//     * Permitted LTI versions for messages.
//     * @var string[]
//     */
//    private static array $LTI_VERSIONS = array(self::LTI_VERSION1, self::LTI_VERSION2);
//
//    /**
//     * List of supported message types and associated class methods.
//     * @var array<string, string>
//     */
//    private static array $MESSAGE_TYPES = array('basic-lti-launch-request' => 'onLaunch',
//                                                'ContentItemSelectionRequest' => 'onContentItem',
//                                                'ToolProxyRegistrationRequest' => 'register'
//    );
//
//    /**
//     * List of supported message types and associated class methods
//     */
//    private static array $METHOD_NAMES = array('basic-lti-launch-request' => 'onLaunch',
//                                               'ContentItemSelectionRequest' => 'onContentItem',
//                                               'ToolProxyRegistrationRequest' => 'onRegister'
//    );

    /**
     * Names of LTI parameters to be retained in the consumer settings property.
     */
    private static array $LTI_CONSUMER_SETTING_NAMES = array('custom_tc_profile_url',
                                                             'custom_system_setting_url',
                                                             'custom_oauth2_access_token_url'
    );

    /**
     * Names of LTI parameters to be retained in the context settings property.
     */
    private static array $LTI_CONTEXT_SETTING_NAMES = array('custom_context_setting_url',
                                                            'ext_ims_lis_memberships_id',
                                                            'ext_ims_lis_memberships_url',
                                                            'custom_context_memberships_url',
                                                            'custom_context_memberships_v2_url',
                                                            'custom_context_group_sets_url',
                                                            'custom_context_groups_url',
                                                            'custom_lineitems_url',
                                                            'custom_ags_scopes'
    );

    /**
     * Names of LTI parameters to be retained in the resource link settings property.
     */
    private static array $LTI_RESOURCE_LINK_SETTING_NAMES = array('lis_result_sourcedid',
                                                                  'lis_outcome_service_url',
                                                                  'ext_ims_lis_basic_outcome_url',
                                                                  'ext_ims_lis_resultvalue_sourcedids',
                                                                  'ext_outcome_data_values_accepted',
                                                                  'ext_ims_lis_memberships_id',
                                                                  'ext_ims_lis_memberships_url',
                                                                  'ext_ims_lti_tool_setting',
                                                                  'ext_ims_lti_tool_setting_id',
                                                                  'ext_ims_lti_tool_setting_url',
                                                                  'custom_link_setting_url',
                                                                  'custom_link_memberships_url',
                                                                  'custom_lineitems_url',
                                                                  'custom_lineitem_url',
                                                                  'custom_ags_scopes',
                                                                  'custom_ap_acs_url'
    );

//    /**
//     * Names of LTI custom parameter substitution variables (or capabilities) and their associated default message parameter names.
//     */
//    private static array $CUSTOM_SUBSTITUTION_VARIABLES = array('User.id' => 'user_id',
//                                                                'User.image' => 'user_image',
//                                                                'User.username' => 'username',
//                                                                'User.scope.mentor' => 'role_scope_mentor',
//                                                                'Membership.role' => 'roles',
//                                                                'Person.sourcedId' => 'lis_person_sourcedid',
//                                                                'Person.name.full' => 'lis_person_name_full',
//                                                                'Person.name.family' => 'lis_person_name_family',
//                                                                'Person.name.given' => 'lis_person_name_given',
//                                                                'Person.email.primary' => 'lis_person_contact_email_primary',
//                                                                'Context.id' => 'context_id',
//                                                                'Context.type' => 'context_type',
//                                                                'Context.title' => 'context_title',
//                                                                'Context.label' => 'context_label',
//                                                                'CourseOffering.sourcedId' => 'lis_course_offering_sourcedid',
//                                                                'CourseSection.sourcedId' => 'lis_course_section_sourcedid',
//                                                                'CourseSection.label' => 'context_label',
//                                                                'CourseSection.title' => 'context_title',
//                                                                'ResourceLink.id' => 'resource_link_id',
//                                                                'ResourceLink.title' => 'resource_link_title',
//                                                                'ResourceLink.description' => 'resource_link_description',
//                                                                'Result.sourcedId' => 'lis_result_sourcedid',
//                                                                'BasicOutcome.url' => 'lis_outcome_service_url',
//                                                                'ToolConsumerProfile.url' => 'custom_tc_profile_url',
//                                                                'ToolProxy.url' => 'tool_proxy_url',
//                                                                'ToolProxy.custom.url' => 'custom_system_setting_url',
//                                                                'ToolProxyBinding.custom.url' => 'custom_context_setting_url',
//                                                                'LtiLink.custom.url' => 'custom_link_setting_url',
//                                                                'LineItems.url' => 'custom_lineitems_url',
//                                                                'LineItem.url' => 'custom_lineitem_url',
//                                                                'Results.url' => 'custom_results_url',
//                                                                'Result.url' => 'custom_result_url',
//                                                                'ToolProxyBinding.memberships.url' => 'custom_context_memberships_url'
//    );
    /**
     * LTI parameter constraints for auto validation checks.
     * @var array|null $constraints
     */
    private ?array $constraints = null;

    /**
     * @var \ilLogger
     */
    protected ?\ilLogger $logger = null;

    /**
     * @var bool
     */
    public bool $debugMode = true;

    private \ILIAS\DI\Container $dic;

    private \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $postwrapper;

    private \ILIAS\Refinery\KindlyTo\Group $kindlyTo;

    /**
     * ilLTITool constructor.
     * @param ilLTIDataConnector $dataConnector
     */
    public function __construct(ilLTIDataConnector $dataConnector)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->postwrapper = $DIC->http()->wrapper()->post();
        $this->kindlyTo = $DIC->refinery()->kindlyTo();
        $this->logger = ilLoggerFactory::getLogger('ltis');
//        $this->initialize();
        if (empty($dataConnector)) {
            $dataConnector = ilLTIDataConnector::getDataConnector();
        }
        $this->dataConnector = $dataConnector;
//        parent::__construct($dataConnector);
        $this->setParameterConstraint('resource_link_id', true, 50, array('basic-lti-launch-request'));
        $this->setParameterConstraint('user_id', true, 50, array('basic-lti-launch-request'));
        $this->setParameterConstraint('roles', true, null, array('basic-lti-launch-request'));
    }

    /**
     * Check if a share arrangement is in place.
     * @return boolean True if no error is reported
     */
    private function checkForShare() : bool
    {
        $ok = true;
        $doSaveResourceLink = true;

        $id = $this->resourceLink->primaryResourceLinkId;

        $id = $this->resourceLink->primaryResourceLinkId;
        $shareRequest = $this->postwrapper->has('custom_share_key') && !empty($this->postwrapper->retrieve(
            'custom_share_key',
            $this->kindlyTo->string()
        ));
        if ($shareRequest) {
            if (!$this->allowSharing) {
                $ok = false;
                $this->reason = 'Your sharing request has been refused because sharing is not being permitted.';
            } else {
                // Check if this is a new share key
                $shareKey = new ResourceLinkShareKey(
                    $this->resourceLink,
                    $this->postwrapper->retrieve('custom_share_key', $this->kindlyTo->string())
                );
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

    ###
    ###    PROTECTED METHODS
    ###

    /**
     * Process a valid launch request
     */
    protected function onLaunch() : void
    {
        // save/update current user
        if ($this->user instanceof User) {
            $this->user->save();
        }

        if ($this->context instanceof Context) {
            $this->context->save();
        }

        if ($this->resourceLink instanceof ResourceLink) {
            $this->resourceLink->save();
        }
    }

//    /**
//     * Process a valid content-item request
//     * @return boolean True if no error
//     */
//    protected function onContentItem() : void
//    {
//        $this->onError();
//    }

//    /**
//     * Process a valid tool proxy registration request
//     * @return boolean True if no error
//     */
//    protected function onRegister() : void
//    {
//        $this->onError();
//    }

//    /**
//     * Process an incoming request
//     */
//    public function handleRequest() : void
//    {
//        if ($this->ok) {
//            if ($this->authenticate()) {
//                $this->doCallback();
//            }
//        }
//        // if return url is given, this redirects in case of errors
//        $this->result();
    ////        return $this->ok;
//    }

    ###
    ###    PRIVATE METHODS
    ###
    // ToDo erase
//    /**
//     * Check the authenticity of the LTI launch request.
//     * The consumer, resource link and user objects will be initialised if the request is valid.
//     * @return boolean True if the request has been successfully validated.
//     */
//    private function authenticate() : bool
//    {
//
//        global $DIC;
//        // Get the consumer
//        $doSaveConsumer = false;
//        // Check all required launch parameters
//        $this->ok = $this->postwrapper->has('lti_message_type') && array_key_exists(
//            $this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()),
//            self::$MESSAGE_TYPES
//        );
//        if (!$this->ok) {
//            $this->reason = 'Invalid or missing lti_message_type parameter.';
//        }
//        if ($this->ok) {
//            $this->ok = $this->postwrapper->has('lti_version') && in_array($this->postwrapper->retrieve('lti_version', $this->kindlyTo->string()), self::$LTI_VERSIONS);
//            if (!$this->ok) {
//                $this->reason = 'Invalid or missing lti_version parameter.';
//            }
//        }
//        if ($this->ok) {
//            if ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) === 'basic-lti-launch-request') {
//                $this->ok = $this->postwrapper->has('resource_link_id')
//                    && (strlen(trim($this->postwrapper->retrieve('resource_link_id', $this->kindlyTo->string()))) > 0);
//                if (!$this->ok) {
//                    $this->reason = 'Missing resource link ID.';
//                }
//            } elseif ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) === 'ContentItemSelectionRequest') {
//                if ($this->postwrapper->has('accept_media_types') && (strlen(trim(
//                    $this->postwrapper->retrieve('accept_media_types', $this->kindlyTo->string())
//                )) > 0)) {
//                    $mediaTypes = array_filter(
//                        explode(',', str_replace(
//                            ' ',
//                            '',
//                            $this->postwrapper->retrieve('accept_media_types', $this->kindlyTo->string())
//                        )),
//                        'strlen'
//                    );
//                    $mediaTypes = array_unique($mediaTypes);
//                    $this->ok = count($mediaTypes) > 0;
//                    if (!$this->ok) {
//                        $this->reason = 'No accept_media_types found.';
//                    } else {
//                        $this->mediaTypes = $mediaTypes;
//                    }
//                } else {
//                    $this->ok = false;
//                }
//                if ($this->ok && $this->postwrapper->has('accept_presentation_document_targets')
//                    && (strlen(trim($this->postwrapper->retrieve('accept_presentation_document_targets', $this->kindlyTo->string()))) > 0)) {
//                    $documentTargets = array_filter(explode(
//                        ',',
//                        str_replace(' ', '', $this->postwrapper->retrieve('accept_presentation_document_targets', $this->kindlyTo->string()))
//                    ), 'strlen');
//                    $documentTargets = array_unique($documentTargets);
//                    $this->ok = count($documentTargets) > 0;
//                    if (!$this->ok) {
//                        $this->reason = 'Missing or empty accept_presentation_document_targets parameter.';
//                    } else {
//                        foreach ($documentTargets as $documentTarget) {
//                            $this->ok = $this->checkValue(
//                                $documentTarget,
//                                array('embed', 'frame', 'iframe', 'window', 'popup', 'overlay', 'none'),
//                                'Invalid value in accept_presentation_document_targets parameter: %s.'
//                            );
//                            if (!$this->ok) {
//                                break;
//                            }
//                        }
//                        if ($this->ok) {
//                            $this->documentTargets = $documentTargets;
//                        }
//                    }
//                } else {
//                    $this->ok = false;
//                }
//                if ($this->ok) {
//                    $this->ok = $this->postwrapper->has('content_item_return_url') && (strlen(trim($this->postwrapper->retrieve('content_item_return_url', $this->kindlyTo->string()))) > 0);
//                    if (!$this->ok) {
//                        $this->reason = 'Missing content_item_return_url parameter.';
//                    }
//                }
//            } elseif ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) == 'ToolProxyRegistrationRequest') {
//                $this->ok = (($this->postwrapper->has('reg_key') && (strlen(trim($this->postwrapper->retrieve('reg_key', $this->kindlyTo->string()))) > 0)) &&
//                    ($this->postwrapper->has('reg_password') && (strlen(trim($this->postwrapper->retrieve('reg_password', $this->kindlyTo->string()))) > 0)) &&
//                    ($this->postwrapper->has('tc_profile_url') && (strlen(trim($this->postwrapper->retrieve('tc_profile_url', $this->kindlyTo->string()))) > 0)) &&
//                    ($this->postwrapper->has('launch_presentation_return_url') && (strlen(trim($this->postwrapper->retrieve('launch_presentation_return_url', $this->kindlyTo->string()))) > 0)));
//                if ($this->debugMode && !$this->ok) {
//                    $this->reason = 'Missing message parameters.';
//                }
//            }
//        }
//        $now = time();
//
//        $this->logger->debug('Checking consumer key...');
//
//        // Check consumer key
//        if ($this->ok && ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) != 'ToolProxyRegistrationRequest')) {
//            $this->ok = $this->postwrapper->has('oauth_consumer_key');
//            if (!$this->ok) {
//                $this->reason = 'Missing consumer key.';
//            }
//            if ($this->ok) {
//                $this->consumer = new ilLTIPlatform($this->postwrapper->retrieve('oauth_consumer_key', $this->kindlyTo->string()), $this->dataConnector);
//                $this->ok = !is_null($this->consumer->created);
//                if (!$this->ok) {
//                    $this->reason = 'Invalid consumer key.';
//                }
//            }
//            if ($this->ok) {
//                $today = date('Y-m-d', $now);
//                if (is_null($this->consumer->lastAccess)) {
//                    $doSaveConsumer = true;
//                } else {
//                    $last = date('Y-m-d', $this->consumer->lastAccess);
//                    $doSaveConsumer = $doSaveConsumer || ($last !== $today);
//                }
//                $this->consumer->lastAccess = $now;
//                try {
//                    $store = new \ILIAS\LTI\ToolProvider\OAuthDataStore($this);
//                    $server = new \ILIAS\LTIOAuth\OAuthServer($store);
//                    $method = new \ILIAS\LTIOAuth\OAuthSignatureMethod_HMAC_SHA1();
//                    $server->add_signature_method($method);
//                    $request = \ILIAS\LTIOAuth\OAuthRequest::from_request();
    ////                    $res = $server->verify_request($request);
//                } catch (\Exception $e) {
//                    $this->ok = false;
//                    if (empty($this->reason)) {
    ////                        if ($this->debugMode) {
    ////                            $consumer = new \ILIAS\LTIOAuth\OAuthConsumer($this->consumer->getKey(), $this->consumer->secret);
    ////                            $signature = $request->build_signature($method, $consumer, false);
    ////                            $this->reason = $e->getMessage();
    ////                            if (empty($this->reason)) {
    ////                                $this->reason = 'OAuth exception';
    ////                            }
    ////                            $this->details[] = 'Timestamp: ' . time();
    ////                            $this->details[] = "Signature: {$signature}";
    ////                            $this->details[] = "Base string: {$request->base_string}]";
    ////                        } else {
//                        $this->reason = 'OAuth signature check failed - perhaps an incorrect secret or timestamp.';
    ////                        }
//                    }
//                }
//            }
//            // $this->ok = true; //ACHTUNG Problem Signature bei M.
//            if ($this->ok) {
//                $today = date('Y-m-d', $now);
//                if (is_null($this->consumer->lastAccess)) {
//                    $doSaveConsumer = true;
//                } else {
//                    $last = date('Y-m-d', $this->consumer->lastAccess);
//                    $doSaveConsumer = $doSaveConsumer || ($last !== $today);
//                }
//                $this->consumer->lastAccess = $now;
//                if ($this->consumer->protected) {
//                    if (!is_null($this->consumer->consumerGuid)) {
//                        $this->ok = empty($this->postwrapper->retrieve('tool_consumer_instance_guid', $this->kindlyTo->string())) ||
//                            ($this->consumer->consumerGuid === $this->postwrapper->retrieve('tool_consumer_instance_guid', $this->kindlyTo->string()));
//                        if (!$this->ok) {
//                            $this->reason = 'Request is from an invalid tool consumer.';
//                        }
//                    } else {
//                        $this->ok = $this->postwrapper->has('tool_consumer_instance_guid');
//                        if (!$this->ok) {
//                            $this->reason = 'A tool consumer GUID must be included in the launch request.';
//                        }
//                    }
//                }
//                if ($this->ok) {
//                    $this->ok = $this->consumer->enabled;
//                    if (!$this->ok) {
//                        $this->reason = 'Tool consumer has not been enabled by the tool provider.';
//                    }
//                }
//                if ($this->ok) {
//                    $this->ok = is_null($this->consumer->enableFrom) || ($this->consumer->enableFrom <= $now);
//                    if ($this->ok) {
//                        $this->ok = is_null($this->consumer->enableUntil) || ($this->consumer->enableUntil > $now);
//                        if (!$this->ok) {
//                            $this->reason = 'Tool consumer access has expired.';
//                        }
//                    } else {
//                        $this->reason = 'Tool consumer access is not yet available.';
//                    }
//                }
//            }
//            // Validate other message parameter values
//            if ($this->ok) {
//                if ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) === 'ContentItemSelectionRequest') {
//                    if ($this->postwrapper->has('accept_unsigned')) {
//                        $this->ok = $this->checkValue(
//                            $this->postwrapper->retrieve('accept_unsigned', $this->kindlyTo->string()),
//                            array('true', 'false'),
//                            'Invalid value for accept_unsigned parameter: %s.'
//                        );
//                    }
//                    if ($this->ok && $this->postwrapper->has('accept_multiple')) {
//                        $this->ok = $this->checkValue(
//                            $this->postwrapper->retrieve('accept_multiple', $this->kindlyTo->string()),
//                            array('true', 'false'),
//                            'Invalid value for accept_multiple parameter: %s.'
//                        );
//                    }
//                    if ($this->ok && $this->postwrapper->has('accept_copy_advice')) {
//                        $this->ok = $this->checkValue(
//                            $this->postwrapper->retrieve('accept_copy_advice', $this->kindlyTo->string()),
//                            array('true', 'false'),
//                            'Invalid value for accept_copy_advice parameter: %s.'
//                        );
//                    }
//                    if ($this->ok && $this->postwrapper->has('auto_create')) {
//                        $this->ok = $this->checkValue(
//                            $this->postwrapper->retrieve('auto_create', $this->kindlyTo->string()),
//                            array('true', 'false'),
//                            'Invalid value for auto_create parameter: %s.'
//                        );
//                    }
//                    if ($this->ok && $this->postwrapper->has('can_confirm')) {
//                        $this->ok = $this->checkValue(
//                            $this->postwrapper->retrieve('can_confirm', $this->kindlyTo->string()),
//                            array('true', 'false'),
//                            'Invalid value for can_confirm parameter: %s.'
//                        );
//                    }
//                } elseif ($this->postwrapper->has('launch_presentation_document_target')) {
//                    $this->ok = $this->checkValue(
//                        $this->postwrapper->retrieve('launch_presentation_document_target', $this->kindlyTo->string()),
//                        array('embed', 'frame', 'iframe', 'window', 'popup', 'overlay'),
//                        'Invalid value for launch_presentation_document_target parameter: %s.'
//                    );
//                }
//            }
//        }
//
//        if ($this->ok && ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) === 'ToolProxyRegistrationRequest')) {
//            $this->ok = $this->postwrapper->retrieve('lti_version', $this->kindlyTo->string()) == ILIAS\LTI\ToolProvider\Util::LTI_VERSION2;
//            if (!$this->ok) {
//                $this->reason = 'Invalid lti_version parameter';
//            }
//            if ($this->ok) {
//                $http = new HTTPMessage(
//                    $this->postwrapper->retrieve('tc_profile_url', $this->kindlyTo->string()),
//                    'GET',
//                    null,
//                    'Accept: application/vnd.ims.lti.v2.toolconsumerprofile+json'
//                );
//                $this->ok = $http->send();
//                if (!$this->ok) {
//                    $this->reason = 'Tool consumer profile not accessible.';
//                } else {
//                    $tcProfile = json_decode((string) $http->response);
//                    $this->ok = !is_null($tcProfile);
//                    if (!$this->ok) {
//                        $this->reason = 'Invalid JSON in tool consumer profile.';
//                    }
//                }
//            }
//            // Check for required capabilities
//            if ($this->ok) {
//                // $this->consumer = new Platform($_POST['reg_key'], $this->dataConnector);
//                $this->consumer = new ilLTIPlatform($this->postwrapper->retrieve('oauth_consumer_key', $this->kindlyTo->string()), $this->dataConnector);
//                // TODO PHP8 Review: Variable $tcProfile is probably undefined
//                $this->consumer->profile = $tcProfile; // TODO PHP8 Review: Undefined Property
//                $capabilities = $this->consumer->profile->capability_offered;
//                $missing = array();
//                foreach ($this->resourceHandlers as $resourceHandler) {
//                    foreach ($resourceHandler->requiredMessages as $message) {
//                        if (!in_array($message->type, $capabilities)) {
//                            $missing[$message->type] = true;
//                        }
//                    }
//                }
//                foreach ($this->constraints as $name => $constraint) {
//                    if ($constraint['required']) {
//                        if (!in_array($name, $capabilities) && !in_array($name, array_flip($capabilities))) {
//                            $missing[$name] = true;
//                        }
//                    }
//                }
//                if (!empty($missing)) {
//                    ksort($missing);
//                    $this->reason = 'Required capability not offered - \'' . implode(
//                        '\', \'',
//                        array_keys($missing)
//                    ) . '\'';
//                    $this->ok = false;
//                }
//            }
//            // Check for required services
//            if ($this->ok) {
//                foreach ($this->requiredServices as $service) {
//                    foreach ($service->formats as $format) {
//                        if (!$this->findService($format, $service->actions)) {
//                            if ($this->ok) {
//                                $this->reason = 'Required service(s) not offered - ';
//                                $this->ok = false;
//                            } else {
//                                $this->reason .= ', ';
//                            }
//                            $this->reason .= "'{$format}' [" . implode(', ', $service->actions) . ']';
//                        }
//                    }
//                }
//            }
//            if ($this->ok) {
//                if ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) === 'ToolProxyRegistrationRequest') {
//                    // TODO PHP8 Review: Variable $tcProfile is probably undefined
//                    $this->consumer->profile = $tcProfile; // TODO PHP8 Review: Undefined Property
//                    $this->consumer->secret = $this->postwrapper->retrieve('reg_password', $this->kindlyTo->string());
//                    $this->consumer->ltiVersion = $this->postwrapper->retrieve('lti_version', $this->kindlyTo->string());
//                    $this->consumer->name = $tcProfile->product_instance->service_owner->service_owner_name->default_value;
//                    $this->consumer->consumerName = $this->consumer->name;
//                    $this->consumer->consumerVersion = "{$tcProfile->product_instance->product_info->product_family->code}-{$tcProfile->product_instance->product_info->product_version}";
//                    $this->consumer->consumerGuid = $tcProfile->product_instance->guid;
//                    $this->consumer->enabled = true;
//                    $this->consumer->protected = true;
//                    $doSaveConsumer = true;
//                }
//            }
//        } elseif ($this->ok &&
//            $this->postwrapper->has('custom_tc_profile_url') &&
//            $this->postwrapper->retrieve('custom_tc_profile_url', $this->kindlyTo->string()) != "" &&
//            empty($this->consumer->profile)) {
//            $http = new HTTPMessage(
//                $this->postwrapper->retrieve('custom_tc_profile_url', $this->kindlyTo->string()),
//                'GET',
//                null,
//                'Accept: application/vnd.ims.lti.v2.toolconsumerprofile+json'
//            );
//            if ($http->send()) {
//                $tcProfile = json_decode((string) $http->response);
//                if (!is_null($tcProfile)) {
//                    $this->consumer->profile = $tcProfile; // TODO PHP8 Review: Undefined Property
//                    $doSaveConsumer = true;
//                }
//            }
//        }
//
//        $this->logger->debug('Still ok: ' . ($this->ok ? '1' : '0'));
//        if (!$this->ok) {
//            $this->logger->debug('Reason: ' . $this->reason);
//        }
//
//        if ($this->ok) {
//
    //// Set the request context
//            if ($this->postwrapper->has('context_id')) {
//                $this->context = Context::fromConsumer($this->consumer, trim($this->postwrapper->retrieve('context_id', $this->kindlyTo->string())));
//                $title = '';
//                if ($this->postwrapper->has('context_title')) {
//                    $title = trim($this->postwrapper->retrieve('context_title', $this->kindlyTo->string()));
//                }
//                if (empty($title)) {
//                    $title = "Course {$this->context->getId()}";
//                }
//                $this->context->title = $title;
//            }
//
//            // Set the request resource link
//            if ($this->postwrapper->has('resource_link_id')) {
//                $contentItemId = '';
//                if ($this->postwrapper->has('custom_content_item_id')) {
//                    $contentItemId = $this->postwrapper->retrieve('custom_content_item_id', $this->kindlyTo->string());
//                }
//                $this->resourceLink = ResourceLink::fromConsumer(
//                    $this->consumer,
//                    trim($this->postwrapper->retrieve('resource_link_id', $this->kindlyTo->string())),
//                    $contentItemId
//                );
//                if (!empty($this->context)) {
//                    $this->resourceLink->setContextId($this->context->getRecordId());
//                }
//                $title = '';
//                if ($this->postwrapper->has('resource_link_title')) {
//                    $title = trim($this->postwrapper->retrieve('resource_link_title', $this->kindlyTo->string()));
//                }
//                if (empty($title)) {
//                    $title = "Resource {$this->resourceLink->getId()}";
//                }
//                $this->resourceLink->title = $title;
//                // Delete any existing custom parameters
//                foreach ($this->consumer->getSettings() as $name => $value) {
//                    if (strpos($name, 'custom_') === 0) {
//                        $this->consumer->setSetting($name);
//                        $doSaveConsumer = true;
//                    }
//                }
//                if (!empty($this->context)) {
//                    foreach ($this->context->getSettings() as $name => $value) {
//                        if (strpos($name, 'custom_') === 0) {
//                            $this->context->setSetting($name);
//                        }
//                    }
//                }
//                foreach ($this->resourceLink->getSettings() as $name => $value) {
//                    if (strpos($name, 'custom_') === 0) {
//                        $this->resourceLink->setSetting($name);
//                    }
//                }
//                // Save LTI parameters
//                foreach (self::$LTI_CONSUMER_SETTING_NAMES as $name) {
//                    if ($this->postwrapper->has($name)) {
//                        $this->consumer->setSetting($name, $this->postwrapper->retrieve($name, $this->kindlyTo->string()));
//                    } else {
//                        $this->consumer->setSetting($name);
//                    }
//                }
//                if (!empty($this->context)) {
//                    foreach (self::$LTI_CONTEXT_SETTING_NAMES as $name) {
//                        if ($this->postwrapper->has($name)) {
//                            $this->context->setSetting($name, $this->postwrapper->retrieve($name, $this->kindlyTo->string()));
//                        } else {
//                            $this->context->setSetting($name);
//                        }
//                    }
//                }
//                foreach (self::$LTI_RESOURCE_LINK_SETTING_NAMES as $name) {
//                    if ($this->postwrapper->has($name)) {
//                        $this->resourceLink->setSetting($name, $this->postwrapper->retrieve($name, $this->kindlyTo->string()));
//                    } else {
//                        $this->resourceLink->setSetting($name);
//                    }
//                }
//                // Save other custom parameters
//
//                // TODO PHP8 Review: Remove/Replace SuperGlobals
//                foreach ($_POST as $name => $value) {
//                    if ((strpos($name, 'custom_') === 0) &&
//                        !in_array(
//                            $name,
//                            array_merge(
//                                self::$LTI_CONSUMER_SETTING_NAMES,
//                                self::$LTI_CONTEXT_SETTING_NAMES,
//                                self::$LTI_RESOURCE_LINK_SETTING_NAMES
//                            )
//                        )) {
//                        $this->resourceLink->setSetting($name, $value);
//                    }
//                }
//            }
//
//            // Set the user instance
//            $userId = '';
//            if ($this->postwrapper->has('user_id')) {
//                $userId = trim($this->postwrapper->retrieve('user_id', $this->kindlyTo->string()));
//            }
//
//            $this->user = User::fromResourceLink($this->resourceLink, $userId);
//
//            // Set the user name
//            $firstname = ($this->postwrapper->has('lis_person_name_given')) ? $this->postwrapper->retrieve('lis_person_name_given', $this->kindlyTo->string()) : '';
//            $lastname = ($this->postwrapper->has('lis_person_name_family')) ? $this->postwrapper->retrieve('lis_person_name_family', $this->kindlyTo->string()) : '';
//            $fullname = ($this->postwrapper->has('lis_person_name_full')) ? $this->postwrapper->retrieve('lis_person_name_full', $this->kindlyTo->string()) : '';
//            $this->user->setNames($firstname, $lastname, $fullname);
//
//            // Set the user email
//            $email = ($this->postwrapper->has('lis_person_contact_email_primary')) ? $this->postwrapper->retrieve('lis_person_contact_email_primary', $this->kindlyTo->string()) : '';
//            $this->user->setEmail($email, $this->defaultEmail);
//
//            // Set the user image URI
//            if ($this->postwrapper->has('user_image')) {
//                $this->user->image = $this->postwrapper->retrieve('user_image', $this->kindlyTo->string());
//            }
//
//            // Set the user roles
//            if ($this->postwrapper->has('roles')) {
//                $this->user->roles = self::parseRoles($this->user->roles = self::parseRoles($this->postwrapper->retrieve('roles', $this->kindlyTo->listOf($this->kindlyTo->string()))));
//            }
//
//            // Initialise the consumer and check for changes
//            $this->consumer->defaultEmail = $this->defaultEmail;
//            if ($this->consumer->ltiVersion !== $this->postwrapper->retrieve('lti_version', $this->kindlyTo->string())) {
//                $this->consumer->ltiVersion = $this->postwrapper->retrieve('lti_version', $this->kindlyTo->string());
//                $doSaveConsumer = true;
//            }
//            if ($this->postwrapper->has('tool_consumer_instance_name')) {
//                if ($this->consumer->consumerName !== $this->postwrapper->retrieve('tool_consumer_instance_name', $this->kindlyTo->string())) {
//                    $this->consumer->consumerName = $this->postwrapper->retrieve('tool_consumer_instance_name', $this->kindlyTo->string());
//                    $doSaveConsumer = true;
//                }
//            }
//            if ($this->postwrapper->has('tool_consumer_info_product_family_code')) {
//                $version = $this->postwrapper->retrieve('tool_consumer_info_product_family_code', $this->kindlyTo->string());
//                if ($this->postwrapper->has('tool_consumer_info_version')) {
//                    $version .= "-{$this->postwrapper->retrieve('tool_consumer_info_version', $this->kindlyTo->string())}";
//                }
//                // do not delete any existing consumer version if none is passed
//                if ($this->consumer->consumerVersion !== $version) {
//                    $this->consumer->consumerVersion = $version;
//                    $doSaveConsumer = true;
//                }
//            } elseif ($this->postwrapper->has('ext_lms') && ($this->consumer->consumerName !== $this->postwrapper->retrieve('ext_lms', $this->kindlyTo->string()))) {
//                $this->consumer->consumerVersion = $this->postwrapper->retrieve('ext_lms', $this->kindlyTo->string());
//                $doSaveConsumer = true;
//            }
//            if ($this->postwrapper->has('tool_consumer_instance_guid')) {
//                if (is_null($this->consumer->consumerGuid)) {
//                    $this->consumer->consumerGuid = $this->postwrapper->retrieve('tool_consumer_instance_guid', $this->kindlyTo->string());
//                    $doSaveConsumer = true;
//                } elseif (!$this->consumer->protected) {
//                    $doSaveConsumer = ($this->consumer->consumerGuid !== $this->postwrapper->retrieve('tool_consumer_instance_guid', $this->kindlyTo->string()));
//                    if ($doSaveConsumer) {
//                        $this->consumer->consumerGuid = $this->postwrapper->retrieve('tool_consumer_instance_guid', $this->kindlyTo->string());
//                    }
//                }
//            }
//            if ($this->postwrapper->has('launch_presentation_css_url')) {
//                if ($this->consumer->cssPath !== $this->postwrapper->retrieve('launch_presentation_css_url', $this->kindlyTo->string())) {
//                    $this->consumer->cssPath = $this->postwrapper->retrieve('launch_presentation_css_url', $this->kindlyTo->string());
//                    $doSaveConsumer = true;
//                }
//            } elseif ($this->postwrapper->has('ext_launch_presentation_css_url') &&
//                ($this->consumer->cssPath !== $this->postwrapper->retrieve('ext_launch_presentation_css_url', $this->kindlyTo->string()))) {
//                $this->consumer->cssPath = $this->postwrapper->retrieve('ext_launch_presentation_css_url', $this->kindlyTo->string());
//                $doSaveConsumer = true;
//            } elseif (!empty($this->consumer->cssPath)) {
//                $this->consumer->cssPath = null;
//                $doSaveConsumer = true;
//            }
//        }
//
//        // Persist changes to consumer
//        if ($doSaveConsumer) {
//            $this->consumer->save();
//        }
//        if ($this->ok && isset($this->context)) {
//            $this->context->save();//ACHTUNG EVTL. TODO
//        }
//
    ////        $this->logger->dump(get_class($this->context));
//
//        if ($this->ok && isset($this->resourceLink)) {
//
    //// Check if a share arrangement is in place for this resource link
    ////            $this->ok = $this->checkForShare();//ACHTUNG EVTL. TODO
//            // Persist changes to resource link
//            $this->resourceLink->save();
//
//            // Save the user instance
//            if ($this->postwrapper->has('lis_result_sourcedid')) {
//                if ($this->user->ltiResultSourcedId !== $this->postwrapper->retrieve('lis_result_sourcedid', $this->kindlyTo->string())
//                ) {
//                    $this->user->ltiResultSourcedId = $this->postwrapper->retrieve('lis_result_sourcedid', $this->kindlyTo->string());
//                    $this->user->save();
//                }
//            } elseif (!empty($this->user->ltiResultSourcedId)) {
//                $this->user->ltiResultSourcedId = '';
//                $this->user->save();
//            }
//        }
    ////        die ($this->reason.'---'.$this->ok);//ACHTUNG WEG!
//        return $this->ok;
//    }

    /**
     * Validate a parameter value from an array of permitted values.
     * @param mixed  $value         Value to be checked
     * @param array  $values        Array of permitted values
     * @param string $reason        Reason to generate when the value is not permitted
     * @param bool   $strictMode    True if full compliance with the LTI specification is required
     * @param bool   $ignoreInvalid True if invalid values are to be ignored (optional default is false)
     * @return bool    True if value is valid
     */
    private function checkValue($value, array $values, string $reason, bool $strictMode = false, bool $ignoreInvalid = false) : bool
    {
        $lookupValue = $value;
        if (!$strictMode) {
            $lookupValue = strtolower($value);
        }
        $ok = in_array($lookupValue, $values);
        if (!$ok && !$strictMode && $ignoreInvalid) {
//            Util::logInfo(sprintf($reason, $value) . " [Error ignored]");
            $ok = true;
        } elseif (!$ok && !empty($reason)) {
            $this->reason = sprintf($reason, $value);
        } elseif ($lookupValue !== $value) {
//            Util::logInfo(sprintf($reason, $value) . " [Changed to '{$lookupValue}']");
            $value = $lookupValue;
        }

        return $ok;
    }

//    /**
//     * Call any callback function for the requested action.
//     * This function may set the redirect_url and output properties.
//     * @param string|null $method
//     * @return void True if no error reported
//     */
//    private function doCallback(?string $method = null) : void
//    {
//        // TODO PHP8 Review: Move Global Access to Constructor
//        global $DIC;
//        $callback = $method;
//        if (is_null($callback)) {
//            $callback = self::$METHOD_NAMES[$this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string())
    //];
//        }
//        if (method_exists($this, $callback)) {
//            $result = $this->$callback(); // ACHTUNG HIER PROBLEM UK
//        } elseif (is_null($method) && $this->ok) {
//            $this->ok = false;
//            $this->reason = "Message type not supported: {$this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string())
    //}";
//        }
//        if ($this->ok && ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string())
    // == 'ToolProxyRegistrationRequest')) {
//            $this->consumer->save();
//        }
//    }

    /**
     * Perform the result of an action.
     * This function may redirect the user to another URL rather than returning a value.
     * string Output to be displayed (redirection, or display HTML or message)
     */
    private function result() : void
    {
        $ok = false;
        if (!$this->ok) {
            $this->onError();
        }
        if (!$ok) {
            if (!$this->ok) {
                // If not valid, return an error message to the tool consumer if a return URL is provided
                if (!empty($this->returnUrl)) {
                    $errorUrl = $this->returnUrl;
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
                    if (!is_null($this->platform) && $this->postwrapper->has('lti_message_type') && ($this->postwrapper->retrieve('lti_message_type', $this->kindlyTo->string()) === 'ContentItemSelectionRequest')) {
                        $formParams = array();
                        if ($this->postwrapper->has('data')) {
                            $formParams['data'] = $this->postwrapper->retrieve('data', $this->kindlyTo->string());
                        }
                        $version = ($this->postwrapper->has('lti_version')) ? $this->postwrapper->retrieve('lti_version', $this->kindlyTo->string()) : ILIAS\LTI\ToolProvider\Util::LTI_VERSION1;
                        $formParams = $this->platform->signParameters(
                            $errorUrl,
                            'ContentItemSelection',
                            $version,
                            $formParams
                        );
                        $page = ILIAS\LTI\ToolProvider\Util::sendForm($errorUrl, $formParams); //Check UK
                        echo $page;
                    } else {
                        header("Location: {$errorUrl}");
                    }
                    exit; //ACHTUNG HIER EVTL. PROBLEM
                } else {
                    if (!is_null($this->errorOutput)) {
                        echo $this->errorOutput;
                    } elseif ($this->debugMode && !empty($this->reason)) {
                        echo "Debug error: {$this->reason}";
                    } else {
                        echo "Error: {$this->message}";
                    }
                }
            } elseif (!is_null($this->redirectUrl)) {
                header("Location: {$this->redirectUrl}");
                exit;
            } elseif (!is_null($this->output)) {
                echo $this->output;
            }
        }
    }

//    /**
//     * Process a response to an invalid request
//     * boolean True if no further error processing required
//     */
//    protected function onError() : void
//    {
//        // only return error status
////        return $this->ok;
//
//        $this->doCallback('onError');
//        // return parent::onError(); //Stefan M.
//    }
}
