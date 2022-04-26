<?php declare(strict_types=1);

use ILIAS\LTI\ToolProvider;
use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;
use ILIAS\LTI\HTTPMessage;
use ILIAS\LTI\ToolProvider\OAuthDataStore;
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;
use ILIAS\LTI\ToolProvider\User;
use ILIAS\LTI\ToolProvider\ResourceLinkShareKey;
#use ILIAS\LTI\Profile\Item;

#use ILIAS\LTI\ToolProvider\MediaType;
use ILIAS\LTI\Profile;

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
class ilLTIToolProvider extends ToolProvider\ToolProvider
{
    /**
     * Permitted LTI versions for messages.
     * @var string[]
     */
    private static array $LTI_VERSIONS = array(self::LTI_VERSION1, self::LTI_VERSION2);

    /**
     * List of supported message types and associated class methods.
     * @var array<string, string>
     */
    private static array $MESSAGE_TYPES = array('basic-lti-launch-request' => 'onLaunch',
                                                'ContentItemSelectionRequest' => 'onContentItem',
                                                'ToolProxyRegistrationRequest' => 'register'
    );

    /**
     * List of supported message types and associated class methods
     */
    private static array $METHOD_NAMES = array('basic-lti-launch-request' => 'onLaunch',
                                               'ContentItemSelectionRequest' => 'onContentItem',
                                               'ToolProxyRegistrationRequest' => 'onRegister'
    );

    /**
     * Names of LTI parameters to be retained in the consumer settings property.
     */
    private static array $LTI_CONSUMER_SETTING_NAMES = array('custom_tc_profile_url', 'custom_system_setting_url');

    /**
     * Names of LTI parameters to be retained in the context settings property.
     */
    private static array $LTI_CONTEXT_SETTING_NAMES = array('custom_context_setting_url',
                                                            'custom_lineitems_url',
                                                            'custom_results_url',
                                                            'custom_context_memberships_url'
    );

    /**
     * Names of LTI parameters to be retained in the resource link settings property.
     */
    private static array $LTI_RESOURCE_LINK_SETTING_NAMES = array('lis_result_sourcedid',
                                                                  'lis_outcome_service_url',
                                                                  'ext_ims_lis_basic_outcome_url',
                                                                  'ext_ims_lis_resultvalue_sourcedids',
                                                                  'ext_ims_lis_memberships_id',
                                                                  'ext_ims_lis_memberships_url',
                                                                  'ext_ims_lti_tool_setting',
                                                                  'ext_ims_lti_tool_setting_id',
                                                                  'ext_ims_lti_tool_setting_url',
                                                                  'custom_link_setting_url',
                                                                  'custom_lineitem_url',
                                                                  'custom_result_url'
    );

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
                                                                'ToolProxyBinding.memberships.url' => 'custom_context_memberships_url'
    );

    /**
     * @var \ilLogger
     */
    protected ?\ilLogger $logger = null;

    /**
     * @var bool
     */
    public bool $debugMode = true;

    /**
     * ilLTIToolProvider constructor.
     * @param DataConnector $dataConnector
     */
    public function __construct(DataConnector $dataConnector)
    {
        $this->logger = ilLoggerFactory::getLogger('ltis');
        parent::__construct($dataConnector);
    }

    /**
     * Check if a share arrangement is in place.
     * @return boolean True if no error is reported
     */
    private function checkForShare() : bool
    {
        global $DIC;
        // TODO PHP8 Review: Move Global Access to Constructor
        $ok = true;
        $doSaveResourceLink = true;

        $id = $this->resourceLink->primaryResourceLinkId;
        $shareRequest = $DIC->http()->wrapper()->post()->has('custom_share_key') && !empty($DIC->http()->wrapper()->post()->retrieve('custom_share_key', $DIC->refinery()->kindlyTo()->string()));
        if ($shareRequest) {
            if (!$this->allowSharing) {
                $ok = false;
                $this->reason = 'Your sharing request has been refused because sharing is not being permitted.';
            } else {
                // Check if this is a new share key
                $shareKey = new ResourceLinkShareKey($this->resourceLink, $DIC->http()->wrapper()->post()->retrieve('custom_share_key', $DIC->refinery()->kindlyTo()->string()));
                if (!is_null($shareKey->resourceLinkId)) {
                    // Update resource link with sharing primary resource link details
                    $key = $shareKey->resourceLinkId;
                    $ok = ($id !== $this->resourceLink->getRecordId());
                    if ($ok) {
                        $this->resourceLink->primaryResourceLinkId = $id;
                        $this->resourceLink->shareApproved = $shareKey->autoApprove;
                        $ok = $this->resourceLink->save();
                        if ($ok) {
                            $doSaveResourceLink = false;
                            $this->user->getResourceLink()->primaryResourceLinkId = $id;
                            $this->user->getResourceLink()->shareApproved = $shareKey->autoApprove;
                            $this->user->getResourceLink()->updated = time();
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
                    $ok = !is_null($key); // TODO PHP8 Review: Variable $key is probably undefined
                    if (!$ok) {
                        $this->reason = 'You have requested to share a resource link but none is available.';
                    } else {
                        $ok = (!is_null($this->user->getResourceLink()->shareApproved) && $this->user->getResourceLink()->shareApproved);
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
            // $consumer = new ToolConsumer($key, $this->dataConnector);
            $consumer = new ilLTIToolConsumer($DIC->http()->wrapper()->post()->retrieve('oauth_consumer_key', $DIC->refinery()->kindlyTo()->string()), $this->dataConnector);
            $ok = !is_null($consumer->created);
            if ($ok) {
                $resourceLink = ResourceLink::fromConsumer($consumer, $id);
                $ok = !is_null($resourceLink->created);
            }
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

    /**
     * Process a valid content-item request
     * @return boolean True if no error
     */
    protected function onContentItem() : void
    {
        $this->onError();
    }

    /**
     * Process a valid tool proxy registration request
     * @return boolean True if no error
     */
    protected function onRegister() : void
    {
        $this->onError();
    }

    /**
     * Process an incoming request
     */
    public function handleRequest() : void
    {
        if ($this->ok) {
            if ($this->authenticate()) {
                $this->doCallback();
            }
        }
        // if return url is given, this redirects in case of errors
        $this->result();
//        return $this->ok;
    }

    ###
    ###    PRIVATE METHODS
    ###

    /**
     * Check the authenticity of the LTI launch request.
     * The consumer, resource link and user objects will be initialised if the request is valid.
     * @return boolean True if the request has been successfully validated.
     */
    private function authenticate() : bool
    {
        // TODO PHP8 Review: Move Global Access to Constructor
        global $DIC;
        // Get the consumer
        $doSaveConsumer = false;
        // Check all required launch parameters
        $this->ok = $DIC->http()->wrapper()->post()->has('lti_message_type') && array_key_exists(
            $DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()),
            self::$MESSAGE_TYPES
        );
        if (!$this->ok) {
            $this->reason = 'Invalid or missing lti_message_type parameter.';
        }
        if ($this->ok) {
            $this->ok = $DIC->http()->wrapper()->post()->has('lti_version') && in_array($DIC->http()->wrapper()->post()->retrieve('lti_version', $DIC->refinery()->kindlyTo()->string()), self::$LTI_VERSIONS);
            if (!$this->ok) {
                $this->reason = 'Invalid or missing lti_version parameter.';
            }
        }
        if ($this->ok) {
            if ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) === 'basic-lti-launch-request') {
                $this->ok = $DIC->http()->wrapper()->post()->has('resource_link_id')
                    && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('resource_link_id', $DIC->refinery()->kindlyTo()->string()))) > 0);
                if (!$this->ok) {
                    $this->reason = 'Missing resource link ID.';
                }
            } elseif ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) === 'ContentItemSelectionRequest') {
                if ($DIC->http()->wrapper()->post()->has('accept_media_types') && (strlen(trim(
                    $DIC->http()->wrapper()->post()->retrieve('accept_media_types', $DIC->refinery()->kindlyTo()->string())
                )) > 0)) {
                    $mediaTypes = array_filter(
                        explode(',', str_replace(
                            ' ',
                            '',
                            $DIC->http()->wrapper()->post()->retrieve('accept_media_types', $DIC->refinery()->kindlyTo()->string())
                        )),
                        'strlen'
                    );
                    $mediaTypes = array_unique($mediaTypes);
                    $this->ok = count($mediaTypes) > 0;
                    if (!$this->ok) {
                        $this->reason = 'No accept_media_types found.';
                    } else {
                        $this->mediaTypes = $mediaTypes;
                    }
                } else {
                    $this->ok = false;
                }
                if ($this->ok && $DIC->http()->wrapper()->post()->has('accept_presentation_document_targets')
                    && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('accept_presentation_document_targets', $DIC->refinery()->kindlyTo()->string()))) > 0)) {
                    $documentTargets = array_filter(explode(
                        ',',
                        str_replace(' ', '', $DIC->http()->wrapper()->post()->retrieve('accept_presentation_document_targets', $DIC->refinery()->kindlyTo()->string()))
                    ), 'strlen');
                    $documentTargets = array_unique($documentTargets);
                    $this->ok = count($documentTargets) > 0;
                    if (!$this->ok) {
                        $this->reason = 'Missing or empty accept_presentation_document_targets parameter.';
                    } else {
                        foreach ($documentTargets as $documentTarget) {
                            $this->ok = $this->checkValue(
                                $documentTarget,
                                array('embed', 'frame', 'iframe', 'window', 'popup', 'overlay', 'none'),
                                'Invalid value in accept_presentation_document_targets parameter: %s.'
                            );
                            if (!$this->ok) {
                                break;
                            }
                        }
                        if ($this->ok) {
                            $this->documentTargets = $documentTargets;
                        }
                    }
                } else {
                    $this->ok = false;
                }
                if ($this->ok) {
                    $this->ok = $DIC->http()->wrapper()->post()->has('content_item_return_url') && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('content_item_return_url', $DIC->refinery()->kindlyTo()->string()))) > 0);
                    if (!$this->ok) {
                        $this->reason = 'Missing content_item_return_url parameter.';
                    }
                }
            } elseif ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) == 'ToolProxyRegistrationRequest') {
                $this->ok = (($DIC->http()->wrapper()->post()->has('reg_key') && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('reg_key', $DIC->refinery()->kindlyTo()->string()))) > 0)) &&
                    ($DIC->http()->wrapper()->post()->has('reg_password') && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('reg_password', $DIC->refinery()->kindlyTo()->string()))) > 0)) &&
                    ($DIC->http()->wrapper()->post()->has('tc_profile_url') && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('tc_profile_url', $DIC->refinery()->kindlyTo()->string()))) > 0)) &&
                    ($DIC->http()->wrapper()->post()->has('launch_presentation_return_url') && (strlen(trim($DIC->http()->wrapper()->post()->retrieve('launch_presentation_return_url', $DIC->refinery()->kindlyTo()->string()))) > 0)));
                if ($this->debugMode && !$this->ok) {
                    $this->reason = 'Missing message parameters.';
                }
            }
        }
        $now = time();

        $this->logger->debug('Checking consumer key...');

        // Check consumer key
        if ($this->ok && ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) != 'ToolProxyRegistrationRequest')) {
            $this->ok = $DIC->http()->wrapper()->post()->has('oauth_consumer_key');
            if (!$this->ok) {
                $this->reason = 'Missing consumer key.';
            }
            if ($this->ok) {
                $this->consumer = new ilLTIToolConsumer($DIC->http()->wrapper()->post()->retrieve('oauth_consumer_key', $DIC->refinery()->kindlyTo()->string()), $this->dataConnector);
                $this->ok = !is_null($this->consumer->created);
                if (!$this->ok) {
                    $this->reason = 'Invalid consumer key.';
                }
            }
            if ($this->ok) {
                $today = date('Y-m-d', $now);
                if (is_null($this->consumer->lastAccess)) {
                    $doSaveConsumer = true;
                } else {
                    $last = date('Y-m-d', $this->consumer->lastAccess);
                    $doSaveConsumer = $doSaveConsumer || ($last !== $today);
                }
                $this->consumer->last_access = $now; // TODO PHP8 Review: Undefined Property
                try {
                    $store = new OAuthDataStore($this);
                    $server = new \ILIAS\LTIOAuth\OAuthServer($store);
                    $method = new \ILIAS\LTIOAuth\OAuthSignatureMethod_HMAC_SHA1();
                    $server->add_signature_method($method);
                    $request = \ILIAS\LTIOAuth\OAuthRequest::from_request();
//                    $res = $server->verify_request($request);
                } catch (\Exception $e) {
                    $this->ok = false;
                    if (empty($this->reason)) {
//                        if ($this->debugMode) {
//                            $consumer = new \ILIAS\LTIOAuth\OAuthConsumer($this->consumer->getKey(), $this->consumer->secret);
//                            $signature = $request->build_signature($method, $consumer, false);
//                            $this->reason = $e->getMessage();
//                            if (empty($this->reason)) {
//                                $this->reason = 'OAuth exception';
//                            }
//                            $this->details[] = 'Timestamp: ' . time();
//                            $this->details[] = "Signature: {$signature}";
//                            $this->details[] = "Base string: {$request->base_string}]";
//                        } else {
                        $this->reason = 'OAuth signature check failed - perhaps an incorrect secret or timestamp.';
//                        }
                    }
                }
            }
            // $this->ok = true; //ACHTUNG Problem Signature bei M.
            if ($this->ok) {
                $today = date('Y-m-d', $now);
                if (is_null($this->consumer->lastAccess)) {
                    $doSaveConsumer = true;
                } else {
                    $last = date('Y-m-d', $this->consumer->lastAccess);
                    $doSaveConsumer = $doSaveConsumer || ($last !== $today);
                }
                $this->consumer->last_access = $now; // TODO PHP8 Review: Undefined Property
                if ($this->consumer->protected) {
                    if (!is_null($this->consumer->consumerGuid)) {
                        $this->ok = empty($DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_guid', $DIC->refinery()->kindlyTo()->string())) ||
                            ($this->consumer->consumerGuid === $DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_guid', $DIC->refinery()->kindlyTo()->string()));
                        if (!$this->ok) {
                            $this->reason = 'Request is from an invalid tool consumer.';
                        }
                    } else {
                        $this->ok = $DIC->http()->wrapper()->post()->has('tool_consumer_instance_guid');
                        if (!$this->ok) {
                            $this->reason = 'A tool consumer GUID must be included in the launch request.';
                        }
                    }
                }
                if ($this->ok) {
                    $this->ok = $this->consumer->enabled;
                    if (!$this->ok) {
                        $this->reason = 'Tool consumer has not been enabled by the tool provider.';
                    }
                }
                if ($this->ok) {
                    $this->ok = is_null($this->consumer->enableFrom) || ($this->consumer->enableFrom <= $now);
                    if ($this->ok) {
                        $this->ok = is_null($this->consumer->enableUntil) || ($this->consumer->enableUntil > $now);
                        if (!$this->ok) {
                            $this->reason = 'Tool consumer access has expired.';
                        }
                    } else {
                        $this->reason = 'Tool consumer access is not yet available.';
                    }
                }
            }
            // Validate other message parameter values
            if ($this->ok) {
                if ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) === 'ContentItemSelectionRequest') {
                    if ($DIC->http()->wrapper()->post()->has('accept_unsigned')) {
                        $this->ok = $this->checkValue(
                            $DIC->http()->wrapper()->post()->retrieve('accept_unsigned', $DIC->refinery()->kindlyTo()->string()),
                            array('true', 'false'),
                            'Invalid value for accept_unsigned parameter: %s.'
                        );
                    }
                    if ($this->ok && $DIC->http()->wrapper()->post()->has('accept_multiple')) {
                        $this->ok = $this->checkValue(
                            $DIC->http()->wrapper()->post()->retrieve('accept_multiple', $DIC->refinery()->kindlyTo()->string()),
                            array('true', 'false'),
                            'Invalid value for accept_multiple parameter: %s.'
                        );
                    }
                    if ($this->ok && $DIC->http()->wrapper()->post()->has('accept_copy_advice')) {
                        $this->ok = $this->checkValue(
                            $DIC->http()->wrapper()->post()->retrieve('accept_copy_advice', $DIC->refinery()->kindlyTo()->string()),
                            array('true', 'false'),
                            'Invalid value for accept_copy_advice parameter: %s.'
                        );
                    }
                    if ($this->ok && $DIC->http()->wrapper()->post()->has('auto_create')) {
                        $this->ok = $this->checkValue(
                            $DIC->http()->wrapper()->post()->retrieve('auto_create', $DIC->refinery()->kindlyTo()->string()),
                            array('true', 'false'),
                            'Invalid value for auto_create parameter: %s.'
                        );
                    }
                    if ($this->ok && $DIC->http()->wrapper()->post()->has('can_confirm')) {
                        $this->ok = $this->checkValue(
                            $DIC->http()->wrapper()->post()->retrieve('can_confirm', $DIC->refinery()->kindlyTo()->string()),
                            array('true', 'false'),
                            'Invalid value for can_confirm parameter: %s.'
                        );
                    }
                } elseif ($DIC->http()->wrapper()->post()->has('launch_presentation_document_target')) {
                    $this->ok = $this->checkValue(
                        $DIC->http()->wrapper()->post()->retrieve('launch_presentation_document_target', $DIC->refinery()->kindlyTo()->string()),
                        array('embed', 'frame', 'iframe', 'window', 'popup', 'overlay'),
                        'Invalid value for launch_presentation_document_target parameter: %s.'
                    );
                }
            }
        }

        if ($this->ok && ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) === 'ToolProxyRegistrationRequest')) {
            $this->ok = $DIC->http()->wrapper()->post()->retrieve('lti_version', $DIC->refinery()->kindlyTo()->string()) == self::LTI_VERSION2;
            if (!$this->ok) {
                $this->reason = 'Invalid lti_version parameter';
            }
            if ($this->ok) {
                $http = new HTTPMessage(
                    $DIC->http()->wrapper()->post()->retrieve('tc_profile_url', $DIC->refinery()->kindlyTo()->string()),
                    'GET',
                    null,
                    'Accept: application/vnd.ims.lti.v2.toolconsumerprofile+json'
                );
                $this->ok = $http->send();
                if (!$this->ok) {
                    $this->reason = 'Tool consumer profile not accessible.';
                } else {
                    $tcProfile = json_decode((string) $http->response);
                    $this->ok = !is_null($tcProfile);
                    if (!$this->ok) {
                        $this->reason = 'Invalid JSON in tool consumer profile.';
                    }
                }
            }
            // Check for required capabilities
            if ($this->ok) {
                // $this->consumer = new ToolConsumer($_POST['reg_key'], $this->dataConnector);
                $this->consumer = new ilLTIToolConsumer($DIC->http()->wrapper()->post()->retrieve('oauth_consumer_key', $DIC->refinery()->kindlyTo()->string()), $this->dataConnector);
                // TODO PHP8 Review: Variable $tcProfile is probably undefined
                $this->consumer->profile = $tcProfile; // TODO PHP8 Review: Undefined Property
                $capabilities = $this->consumer->profile->capability_offered;
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
                        if (!in_array($name, $capabilities) && !in_array($name, array_flip($capabilities))) {
                            $missing[$name] = true;
                        }
                    }
                }
                if (!empty($missing)) {
                    ksort($missing);
                    $this->reason = 'Required capability not offered - \'' . implode(
                        '\', \'',
                        array_keys($missing)
                    ) . '\'';
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
                if ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) === 'ToolProxyRegistrationRequest') {
                    // TODO PHP8 Review: Variable $tcProfile is probably undefined
                    $this->consumer->profile = $tcProfile; // TODO PHP8 Review: Undefined Property
                    $this->consumer->secret = $DIC->http()->wrapper()->post()->retrieve('reg_password', $DIC->refinery()->kindlyTo()->string());
                    $this->consumer->ltiVersion = $DIC->http()->wrapper()->post()->retrieve('lti_version', $DIC->refinery()->kindlyTo()->string());
                    $this->consumer->name = $tcProfile->product_instance->service_owner->service_owner_name->default_value;
                    $this->consumer->consumerName = $this->consumer->name;
                    $this->consumer->consumerVersion = "{$tcProfile->product_instance->product_info->product_family->code}-{$tcProfile->product_instance->product_info->product_version}";
                    $this->consumer->consumerGuid = $tcProfile->product_instance->guid;
                    $this->consumer->enabled = true;
                    $this->consumer->protected = true;
                    $doSaveConsumer = true;
                }
            }
        } elseif ($this->ok &&
            $DIC->http()->wrapper()->post()->has('custom_tc_profile_url') &&
            $DIC->http()->wrapper()->post()->retrieve('custom_tc_profile_url', $DIC->refinery()->kindlyTo()->string()) != "" &&
            empty($this->consumer->profile)) {
            $http = new HTTPMessage(
                $DIC->http()->wrapper()->post()->retrieve('custom_tc_profile_url', $DIC->refinery()->kindlyTo()->string()),
                'GET',
                null,
                'Accept: application/vnd.ims.lti.v2.toolconsumerprofile+json'
            );
            if ($http->send()) {
                $tcProfile = json_decode((string) $http->response);
                if (!is_null($tcProfile)) {
                    $this->consumer->profile = $tcProfile; // TODO PHP8 Review: Undefined Property
                    $doSaveConsumer = true;
                }
            }
        }

        $this->logger->debug('Still ok: ' . ($this->ok ? '1' : '0'));
        if (!$this->ok) {
            $this->logger->debug('Reason: ' . $this->reason);
        }

        if ($this->ok) {

// Set the request context
            if ($DIC->http()->wrapper()->post()->has('context_id')) {
                $this->context = Context::fromConsumer($this->consumer, trim($DIC->http()->wrapper()->post()->retrieve('context_id', $DIC->refinery()->kindlyTo()->string())));
                $title = '';
                if ($DIC->http()->wrapper()->post()->has('context_title')) {
                    $title = trim($DIC->http()->wrapper()->post()->retrieve('context_title', $DIC->refinery()->kindlyTo()->string()));
                }
                if (empty($title)) {
                    $title = "Course {$this->context->getId()}";
                }
                $this->context->title = $title;
            }

            // Set the request resource link
            if ($DIC->http()->wrapper()->post()->has('resource_link_id')) {
                $contentItemId = '';
                if ($DIC->http()->wrapper()->post()->has('custom_content_item_id')) {
                    $contentItemId = $DIC->http()->wrapper()->post()->retrieve('custom_content_item_id', $DIC->refinery()->kindlyTo()->string());
                }
                $this->resourceLink = ResourceLink::fromConsumer(
                    $this->consumer,
                    trim($DIC->http()->wrapper()->post()->retrieve('resource_link_id', $DIC->refinery()->kindlyTo()->string())),
                    $contentItemId
                );
                if (!empty($this->context)) {
                    $this->resourceLink->setContextId($this->context->getRecordId());
                }
                $title = '';
                if ($DIC->http()->wrapper()->post()->has('resource_link_title')) {
                    $title = trim($DIC->http()->wrapper()->post()->retrieve('resource_link_title', $DIC->refinery()->kindlyTo()->string()));
                }
                if (empty($title)) {
                    $title = "Resource {$this->resourceLink->getId()}";
                }
                $this->resourceLink->title = $title;
                // Delete any existing custom parameters
                foreach ($this->consumer->getSettings() as $name => $value) {
                    if (strpos($name, 'custom_') === 0) {
                        $this->consumer->setSetting($name);
                        $doSaveConsumer = true;
                    }
                }
                if (!empty($this->context)) {
                    foreach ($this->context->getSettings() as $name => $value) {
                        if (strpos($name, 'custom_') === 0) {
                            $this->context->setSetting($name);
                        }
                    }
                }
                foreach ($this->resourceLink->getSettings() as $name => $value) {
                    if (strpos($name, 'custom_') === 0) {
                        $this->resourceLink->setSetting($name);
                    }
                }
                // Save LTI parameters
                foreach (self::$LTI_CONSUMER_SETTING_NAMES as $name) {
                    if ($DIC->http()->wrapper()->post()->has($name)) {
                        $this->consumer->setSetting($name, $DIC->http()->wrapper()->post()->retrieve($name, $DIC->refinery()->kindlyTo()->string()));
                    } else {
                        $this->consumer->setSetting($name);
                    }
                }
                if (!empty($this->context)) {
                    foreach (self::$LTI_CONTEXT_SETTING_NAMES as $name) {
                        if ($DIC->http()->wrapper()->post()->has($name)) {
                            $this->context->setSetting($name, $DIC->http()->wrapper()->post()->retrieve($name, $DIC->refinery()->kindlyTo()->string()));
                        } else {
                            $this->context->setSetting($name);
                        }
                    }
                }
                foreach (self::$LTI_RESOURCE_LINK_SETTING_NAMES as $name) {
                    if ($DIC->http()->wrapper()->post()->has($name)) {
                        $this->resourceLink->setSetting($name, $DIC->http()->wrapper()->post()->retrieve($name, $DIC->refinery()->kindlyTo()->string()));
                    } else {
                        $this->resourceLink->setSetting($name);
                    }
                }
                // Save other custom parameters
    
                // TODO PHP8 Review: Remove/Replace SuperGlobals
                foreach ($_POST as $name => $value) {
                    if ((strpos($name, 'custom_') === 0) &&
                        !in_array(
                            $name,
                            array_merge(
                                self::$LTI_CONSUMER_SETTING_NAMES,
                                self::$LTI_CONTEXT_SETTING_NAMES,
                                self::$LTI_RESOURCE_LINK_SETTING_NAMES
                            )
                        )) {
                        $this->resourceLink->setSetting($name, $value);
                    }
                }
            }

            // Set the user instance
            $userId = '';
            if ($DIC->http()->wrapper()->post()->has('user_id')) {
                $userId = trim($DIC->http()->wrapper()->post()->retrieve('user_id', $DIC->refinery()->kindlyTo()->string()));
            }

            $this->user = User::fromResourceLink($this->resourceLink, $userId);

            // Set the user name
            $firstname = ($DIC->http()->wrapper()->post()->has('lis_person_name_given')) ? $DIC->http()->wrapper()->post()->retrieve('lis_person_name_given', $DIC->refinery()->kindlyTo()->string()) : '';
            $lastname = ($DIC->http()->wrapper()->post()->has('lis_person_name_family')) ? $DIC->http()->wrapper()->post()->retrieve('lis_person_name_family', $DIC->refinery()->kindlyTo()->string()) : '';
            $fullname = ($DIC->http()->wrapper()->post()->has('lis_person_name_full')) ? $DIC->http()->wrapper()->post()->retrieve('lis_person_name_full', $DIC->refinery()->kindlyTo()->string()) : '';
            $this->user->setNames($firstname, $lastname, $fullname);

            // Set the user email
            $email = ($DIC->http()->wrapper()->post()->has('lis_person_contact_email_primary')) ? $DIC->http()->wrapper()->post()->retrieve('lis_person_contact_email_primary', $DIC->refinery()->kindlyTo()->string()) : '';
            $this->user->setEmail($email, $this->defaultEmail);

            // Set the user image URI
            if ($DIC->http()->wrapper()->post()->has('user_image')) {
                $this->user->image = $DIC->http()->wrapper()->post()->retrieve('user_image', $DIC->refinery()->kindlyTo()->string());
            }

            // Set the user roles
            if ($DIC->http()->wrapper()->post()->has('roles')) {
                $this->user->roles = self::parseRoles($this->user->roles = self::parseRoles($DIC->http()->wrapper()->post()->retrieve('roles', $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->string()))));
            }

            // Initialise the consumer and check for changes
            $this->consumer->defaultEmail = $this->defaultEmail;
            if ($this->consumer->ltiVersion !== $DIC->http()->wrapper()->post()->retrieve('lti_version', $DIC->refinery()->kindlyTo()->string())) {
                $this->consumer->ltiVersion = $DIC->http()->wrapper()->post()->retrieve('lti_version', $DIC->refinery()->kindlyTo()->string());
                $doSaveConsumer = true;
            }
            if ($DIC->http()->wrapper()->post()->has('tool_consumer_instance_name')) {
                if ($this->consumer->consumerName !== $DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_name', $DIC->refinery()->kindlyTo()->string())) {
                    $this->consumer->consumerName = $DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_name', $DIC->refinery()->kindlyTo()->string());
                    $doSaveConsumer = true;
                }
            }
            if ($DIC->http()->wrapper()->post()->has('tool_consumer_info_product_family_code')) {
                $version = $DIC->http()->wrapper()->post()->retrieve('tool_consumer_info_product_family_code', $DIC->refinery()->kindlyTo()->string());
                if ($DIC->http()->wrapper()->post()->has('tool_consumer_info_version')) {
                    $version .= "-{$DIC->http()->wrapper()->post()->retrieve('tool_consumer_info_version', $DIC->refinery()->kindlyTo()->string())}";
                }
                // do not delete any existing consumer version if none is passed
                if ($this->consumer->consumerVersion !== $version) {
                    $this->consumer->consumerVersion = $version;
                    $doSaveConsumer = true;
                }
            } elseif ($DIC->http()->wrapper()->post()->has('ext_lms') && ($this->consumer->consumerName !== $DIC->http()->wrapper()->post()->retrieve('ext_lms', $DIC->refinery()->kindlyTo()->string()))) {
                $this->consumer->consumerVersion = $DIC->http()->wrapper()->post()->retrieve('ext_lms', $DIC->refinery()->kindlyTo()->string());
                $doSaveConsumer = true;
            }
            if ($DIC->http()->wrapper()->post()->has('tool_consumer_instance_guid')) {
                if (is_null($this->consumer->consumerGuid)) {
                    $this->consumer->consumerGuid = $DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_guid', $DIC->refinery()->kindlyTo()->string());
                    $doSaveConsumer = true;
                } elseif (!$this->consumer->protected) {
                    $doSaveConsumer = ($this->consumer->consumerGuid !== $DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_guid', $DIC->refinery()->kindlyTo()->string()));
                    if ($doSaveConsumer) {
                        $this->consumer->consumerGuid = $DIC->http()->wrapper()->post()->retrieve('tool_consumer_instance_guid', $DIC->refinery()->kindlyTo()->string());
                    }
                }
            }
            if ($DIC->http()->wrapper()->post()->has('launch_presentation_css_url')) {
                if ($this->consumer->cssPath !== $DIC->http()->wrapper()->post()->retrieve('launch_presentation_css_url', $DIC->refinery()->kindlyTo()->string())) {
                    $this->consumer->cssPath = $DIC->http()->wrapper()->post()->retrieve('launch_presentation_css_url', $DIC->refinery()->kindlyTo()->string());
                    $doSaveConsumer = true;
                }
            } elseif ($DIC->http()->wrapper()->post()->has('ext_launch_presentation_css_url') &&
                ($this->consumer->cssPath !== $DIC->http()->wrapper()->post()->retrieve('ext_launch_presentation_css_url', $DIC->refinery()->kindlyTo()->string()))) {
                $this->consumer->cssPath = $DIC->http()->wrapper()->post()->retrieve('ext_launch_presentation_css_url', $DIC->refinery()->kindlyTo()->string());
                $doSaveConsumer = true;
            } elseif (!empty($this->consumer->cssPath)) {
                $this->consumer->cssPath = null;
                $doSaveConsumer = true;
            }
        }

        // Persist changes to consumer
        if ($doSaveConsumer) {
            $this->consumer->save();
        }
        if ($this->ok && isset($this->context)) {
            $this->context->save();//ACHTUNG EVTL. TODO
        }

//        $this->logger->dump(get_class($this->context));

        if ($this->ok && isset($this->resourceLink)) {

// Check if a share arrangement is in place for this resource link
//            $this->ok = $this->checkForShare();//ACHTUNG EVTL. TODO
            // Persist changes to resource link
            $this->resourceLink->save();

            // Save the user instance
            if ($DIC->http()->wrapper()->post()->has('lis_result_sourcedid')) {
                if ($this->user->ltiResultSourcedId !== $DIC->http()->wrapper()->post()->retrieve('lis_result_sourcedid', $DIC->refinery()->kindlyTo()->string())
                ) {
                    $this->user->ltiResultSourcedId = $DIC->http()->wrapper()->post()->retrieve('lis_result_sourcedid', $DIC->refinery()->kindlyTo()->string());
                    $this->user->save();
                }
            } elseif (!empty($this->user->ltiResultSourcedId)) {
                $this->user->ltiResultSourcedId = '';
                $this->user->save();
            }
        }
//        die ($this->reason.'---'.$this->ok);//ACHTUNG WEG!
        return $this->ok;
    }

    /**
     * Validate a parameter value from an array of permitted values.
     * @param string $value
     * @param array  $values
     * @param string $reason
     * @return boolean True if value is valid
     */
    private function checkValue(string $value, array $values, string $reason) : bool
    {
        $ok = in_array($value, $values);
        if (!$ok && !empty($reason)) {
            $this->reason = sprintf($reason, $value);
        }

        return $ok;
    }

    /**
     * Call any callback function for the requested action.
     * This function may set the redirect_url and output properties.
     * @param string|null $method
     * @return void True if no error reported
     */
    private function doCallback(?string $method = null) : void
    {
        // TODO PHP8 Review: Move Global Access to Constructor
        global $DIC;
        $callback = $method;
        if (is_null($callback)) {
            $callback = self::$METHOD_NAMES[$DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string())
];
        }
        if (method_exists($this, $callback)) {
            $result = $this->$callback(); // ACHTUNG HIER PROBLEM UK
        } elseif (is_null($method) && $this->ok) {
            $this->ok = false;
            $this->reason = "Message type not supported: {$DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string())
}";
        }
        if ($this->ok && ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string())
 == 'ToolProxyRegistrationRequest')) {
            $this->consumer->save();
        }
    }

    /**
     * Perform the result of an action.
     * This function may redirect the user to another URL rather than returning a value.
     * string Output to be displayed (redirection, or display HTML or message)
     */
    private function result() : void
    {
        global $DIC; // TODO PHP8 Review: Move Global Access to Constructor
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
                    if (!is_null($this->consumer) && $DIC->http()->wrapper()->post()->has('lti_message_type') && ($DIC->http()->wrapper()->post()->retrieve('lti_message_type', $DIC->refinery()->kindlyTo()->string()) === 'ContentItemSelectionRequest')) {
                        $formParams = array();
                        if ($DIC->http()->wrapper()->post()->has('data')) {
                            $formParams['data'] = $DIC->http()->wrapper()->post()->retrieve('data', $DIC->refinery()->kindlyTo()->string());
                        }
                        $version = ($DIC->http()->wrapper()->post()->has('lti_version')) ? $DIC->http()->wrapper()->post()->retrieve('lti_version', $DIC->refinery()->kindlyTo()->string()) : self::LTI_VERSION1;
                        $formParams = $this->consumer->signParameters(
                            $errorUrl,
                            'ContentItemSelection',
                            $version,
                            $formParams
                        );
                        $page = self::sendForm($errorUrl, $formParams);
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

    /**
     * Process a response to an invalid request
     * boolean True if no further error processing required
     */
    protected function onError() : void
    {
        // only return error status
//        return $this->ok;

        $this->doCallback('onError');
        // return parent::onError(); //Stefan M.
    }
}
