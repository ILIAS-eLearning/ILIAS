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
use ILIAS\LTI\ToolProvider\Service;
use ILIAS\LTI\ToolProvider\Http\HTTPMessage;
use ILIAS\LTI\ToolProvider\ApiHook\ApiHook;
use DOMDocument;
use DOMElement;

/**
 * Class to represent a platform resource link
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class ResourceLink
{
    use ApiHook;

    /**
     * Read action.
     */
    public const EXT_READ = 1;

    /**
     * Write (create/update) action.
     */
    public const EXT_WRITE = 2;

    /**
     * Delete action.
     */
    public const EXT_DELETE = 3;

    /**
     * Create action.
     */
    public const EXT_CREATE = 4;

    /**
     * Update action.
     */
    public const EXT_UPDATE = 5;

    /**
     * Decimal outcome type.
     */
    public const EXT_TYPE_DECIMAL = 'decimal';

    /**
     * Percentage outcome type.
     */
    public const EXT_TYPE_PERCENTAGE = 'percentage';

    /**
     * Ratio outcome type.
     */
    public const EXT_TYPE_RATIO = 'ratio';

    /**
     * Letter (A-F) outcome type.
     */
    public const EXT_TYPE_LETTER_AF = 'letteraf';

    /**
     * Letter (A-F) with optional +/- outcome type.
     */
    public const EXT_TYPE_LETTER_AF_PLUS = 'letterafplus';

    /**
     * Pass/fail outcome type.
     */
    public const EXT_TYPE_PASS_FAIL = 'passfail';

    /**
     * Free text outcome type.
     */
    public const EXT_TYPE_TEXT = 'freetext';

    /**
     * Context title.
     *
     * @var string|null $title
     */
    public ?string $title = null;

    /**
     * Resource link ID as supplied in the last connection request.
     *
     * @var string|null $ltiResourceLinkId
     */
    public ?string $ltiResourceLinkId = null;

    /**
     * User group sets (null if the platform does not support the groups enhancement)
     *
     * A group set is represented by an associative array with the following elements:
     *   - title
     *   - groups (array of group IDs)
     *   - num_members
     *   - num_staff
     *   - num_learners
     * The array key value is the group set ID.
     *
     * @var array|null $groupSets
     */
    public ?array $groupSets = null;

    /**
     * User groups (null if the platform does not support the groups enhancement)
     *
     * A group is represented by an associative array with the following elements:
     *   - title
     *   - set (ID of group set, array of IDs if the group belongs to more than one set, omitted if the group is not part of a set)
     * The array key value is the group ID.
     *
     * @var array|null $groups
     */
    public ?array $groups = null;

    /**
     * HttpMessage object for last service request.
     *
     * @var HttpMessage|null $lastServiceRequest
     */
    public ?HTTPMessage $lastServiceRequest = null;

    /**
     * Request for last service request.
     *
     * @var string|null $extRequest
     */
    public ?string $extRequest = null;

    /**
     * Request headers for last service request.
     *
     * @var array|null|string $extRequestHeaders //UK: added string
     */
    public $extRequestHeaders = null;

    /**
     * Response from last service request.
     *
     * @var string|null $extResponse
     */
    public ?string $extResponse = null;

    /**
     * Response header from last service request.
     *
     * @var array|null|string $extResponseHeaders //UK: added string
     */
    public $extResponseHeaders = null;

    /**
     * Primary key value for resource link being shared (if any).
     *
     * @var string|null $primaryResourceLinkId
     */
    public ?string $primaryResourceLinkId = null;

    /**
     * Whether the sharing request has been approved by the primary resource link.
     *
     * @var bool|null $shareApproved
     */
    public ?bool $shareApproved = null;

    /**
     * Timestamp for when the object was created.
     *
     * @var int|null $created
     */
    public ?int $created = null;

    /**
     * Timestamp for when the object was last updated.
     *
     * @var int|null $updated
     */
    public ?int $updated = null;

    /**
     * Record ID for this resource link.
     *
     * @var int|null $id
     */
    private ?int $id = null;

    /**
     * Platform for this resource link.
     *
     * @var Platform|null $platform
     */
    private ?Platform $platform = null;

    /**
     * Platform ID for this resource link.
     *
     * @var int|null $platformId
     */
    private ?int $platformId = null;

    /**
     * Context for this resource link.
     *
     * @var Context|null $context
     */
    private ?Context $context = null;

    /**
     * Context ID for this resource link.
     *
     * @var int|null $contextId
     */
    private ?int $contextId = null;

    /**
     * Setting values (LTI parameters, custom parameters and local parameters).
     *
     * @var array|null $settings
     */
    private ?array $settings = null;

    /**
     * Whether the settings value have changed since last saved.
     *
     * @var bool $settingsChanged
     */
    private bool $settingsChanged = false;

    /**
     * XML document for the last extension service request.
     *
     * @var DOMDocument|null $extDoc //UK: added DOMDocument; erased: string
     */
    private ?DOMDocument $extDoc = null;

    /**
     * XML node array for the last extension service request.
     *
     * @var array|null $extNodes //UK: added array|null; erased object|null
     */
    private ?array $extNodes = null;

    /**
     * Data connector object or string.
     *
     * @var DataConnector|null $dataConnector
     */
    private ?DataConnector $dataConnector = null;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialise the resource link.
     */
    public function initialize()
    {
        $this->title = '';
        $this->settings = array();
        $this->groupSets = null;
        $this->groups = null;
        $this->primaryResourceLinkId = null;
        $this->shareApproved = null;
        $this->created = null;
        $this->updated = null;
    }

    /**
     * Initialise the resource link.
     *
     * Synonym for initialize().
     */
    public function initialise()
    {
        $this->initialize();
    }

    /**
     * Save the resource link to the database.
     *
     * @return bool    True if the resource link was successfully saved.
     */
    public function save(): bool
    {
        $ok = $this->getDataConnector()->saveResourceLink($this);
        if ($ok) {
            $this->settingsChanged = false;
        }

        return $ok;
    }

    /**
     * Delete the resource link from the database.
     *
     * @return bool    True if the resource link was successfully deleted.
     */
    public function delete(): bool
    {
        return $this->getDataConnector()->deleteResourceLink($this);
    }

//    /**
//     * Get tool consumer.
//     *
//     * @deprecated Use getPlatform() instead
//     * @see Context::getPlatform()
//     *
//     * @return ToolConsumer Tool consumer object for this resource link.
//     */
//    public function getConsumer()
//    {
//        Util::logDebug('Method ceLTIc\LTI\ResourceLink::getConsumer() has been deprecated; please use ceLTIc\LTI\ResourceLink::getPlatform() instead.',
//            true);
//        return $this->getPlatform();
//    }

//    /**
//     * Get tool consumer ID.
//     *
//     * @deprecated Use getPlatformId() instead
//     * @see Context::getPlatformId()
//     *
//     * @return int|null Tool Consumer ID for this resource link.
//     */
//    public function getConsumerId()
//    {
//        Util::logDebug('Method ceLTIc\LTI\ResourceLink::getConsumerId() has been deprecated; please use ceLTIc\LTI\ResourceLink::getPlatformId() instead.',
//            true);
//        return $this->getPlatformId();
//    }

//    /**
//     * Set tool consumer ID.
//     *
//     * @deprecated Use setPlatformId() instead
//     * @see Context::setPlatformId()
//     *
//     * @param int $consumerId   Tool Consumer ID for this resource link.
//     */
//    public function setConsumerId($consumerId)
//    {
//        Util::logDebug('Method ceLTIc\LTI\ResourceLink::setConsumerId() has been deprecated; please use ceLTIc\LTI\ResourceLink::setPlatformId() instead.',
//            true);
//        $this->setPlatformId($consumerId);
//    }

    /**
     * Get platform.
     *
     * @return Platform  Platform object for this resource link.
     */
    public function getPlatform(): ?Platform
    {
        if (is_null($this->platform)) {
            if (!is_null($this->context) || !is_null($this->contextId)) {
                $this->platform = $this->getContext()->getPlatform();
            } else {
                $this->platform = Platform::fromRecordId($this->platformId, $this->getDataConnector());
            }
        }

        return $this->platform;
    }

    /**
     * Get platform ID.
     *
     * @return int|null  Platform ID for this resource link.
     */
    public function getPlatformId(): ?int
    {
        return $this->platformId;
    }

    /**
     * Set platform ID.
     * @param int|null $platformId Platform ID for this resource link. //UK: added |null
     */
    public function setPlatformId(?int $platformId)
    {
        $this->platform = null;
        $this->platformId = $platformId;
    }

    /**
     * Get context.
     *
     * @return Context|null LTIContext object for this resource link.
     */
    public function getContext(): ?Context
    {
        if (is_null($this->context) && !is_null($this->contextId)) {
            $this->context = Context::fromRecordId($this->contextId, $this->getDataConnector());
        }

        return $this->context;
    }

    /**
     * Get context record ID.
     *
     * @return int|null Context record ID for this resource link.
     */
    public function getContextId(): ?int
    {
        if (is_null($this->contextId) && !is_null($this->context)) {
            $this->contextId = $this->context->getRecordId();
        }

        return $this->contextId;
    }

    /**
     * Set context.
     * @param Context $context Context for this resource link.
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
        $this->contextId = $context->getRecordId();
    }

    /**
     * Set context ID.
     * @param int|null $contextId Context ID for this resource link. //UK: added |null
     */
    public function setContextId(?int $contextId)
    {
        if ($this->contextId !== $contextId) {
            $this->context = null;
            $this->contextId = $contextId;
        }
    }

    /**
     * Get consumer key.
     *
     * @return string Consumer key value for this resource link.
     */
    public function getKey(): string
    {
        return $this->getPlatform()->getKey();
    }

    /**
     * Get resource link ID.
     *
     * @return string ID for this resource link.
     */
    public function getId(): ?string
    {
        return $this->ltiResourceLinkId;
    }

    /**
     * Get resource link record ID.
     *
     * @return int|null Record ID for this resource link.
     */
    public function getRecordId(): ?int
    {
        return $this->id;
    }

    /**
     * Set resource link record ID.
     * @param int $id Record ID for this resource link.
     */
    public function setRecordId(int $id)
    {
        $this->id = $id;
    }

    /**
     * Get the data connector.
     *
     * @return DataConnector|null Data connector object or string
     */
    public function getDataConnector(): ?DataConnector
    {
        if (empty($this->dataConnector)) {
            $this->getPlatform();
            if (!empty($this->platform)) {
                $this->dataConnector = $this->platform->getDataConnector();
            }
        }

        return $this->dataConnector;
    }

    /**
     * Get a setting value.
     * @param string $name    Name of setting
     * @param string $default Value to return if the setting does not exist (optional, default is an empty string)
     * @return string Setting value
     */
    public function getSetting(string $name, string $default = ''): string
    {
        if (array_key_exists($name, $this->settings)) {
            $value = $this->settings[$name];
        } else {
            $value = $default;
        }

        return $value;
    }

    /**
     * Set a setting value.
     * @param string      $name  Name of setting
     * @param string|null $value Value to set, use an empty value to delete a setting (optional, default is null)
     */
    public function setSetting(string $name, string $value = null)
    {
        $old_value = $this->getSetting($name);
        if ($value !== $old_value) {
            if (!empty($value)) {
                $this->settings[$name] = $value;
            } else {
                unset($this->settings[$name]);
            }
            $this->settingsChanged = true;
        }
    }

    /**
     * Get an array of all setting values.
     *
     * @return array Associative array of setting values
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /**
     * Set an array of all setting values.
     * @param array $settings Associative array of setting values
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Save setting values.
     *
     * @return bool    True if the settings were successfully saved
     */
    public function saveSettings(): bool
    {
        if ($this->settingsChanged) {
            $ok = $this->save();
        } else {
            $ok = true;
        }

        return $ok;
    }

    /**
     * Check if an Outcomes service is available.
     *
     * @return bool    True if this resource link supports an Outcomes service
     */
    public function hasOutcomesService(): bool
    {
        $has = !empty($this->getSetting('ext_ims_lis_basic_outcome_url')) || !empty($this->getSetting('lis_outcome_service_url'));
        if (!$has && !empty($this->getSetting('custom_lineitem_url')) && !empty($this->getSetting('custom_ags_scopes'))) {
            $scopes = explode(',', $this->getSetting('custom_ags_scopes'));
            $has = in_array(Service\Score::$SCOPE, $scopes) && in_array(Service\Result::$SCOPE, $scopes);
        }
        if (!$has) {
            $has = self::hasConfiguredApiHook(self::$OUTCOMES_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this);
        }
        return $has;
    }

    /**
     * Check if a Memberships service is available.
     *
     * @return bool    True if this resource link supports a Memberships service
     */
    public function hasMembershipsService(): bool
    {
        $has = false;
        if (!empty($this->getContextId())) {
            $has = !empty($this->getContext()->getSetting('custom_context_memberships_url')) || !empty($this->getContext()->getSetting('custom_context_memberships_v2_url'));
        }
        if (!$has) {
            $has = !empty($this->getSetting('custom_link_memberships_url'));
        }
        if (!$has) {
            $has = !empty($this->getSetting('ext_ims_lis_memberships_url'));
        }
        if (!$has) {
            $has = self::hasConfiguredApiHook(self::$MEMBERSHIPS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this);
        }

        return $has;
    }

    /**
     * Check if the Setting extension service is available.
     *
     * @return bool    True if this resource link supports the Setting extension service
     */
    public function hasSettingService(): bool
    {
        $url = $this->getSetting('ext_ims_lti_tool_setting_url');

        return !empty($url);
    }

    /**
     * Check if the Line Item service is available.
     *
     * @return bool    True if this resource link supports the Line Item service
     */
    public function hasLineItemService(): bool
    {
        $has = false;
        if (!empty($this->getSetting('custom_ags_scopes'))) {
            $scopes = explode(',', $this->getSetting('custom_ags_scopes'));
            if (in_array(Service\LineItem::$SCOPE, $scopes) || in_array(Service\LineItem::$SCOPE_READONLY, $scopes)) {
                $has = !empty($this->getSetting('custom_lineitems_url'));
            }
        }

        return $has;
    }

    /**
     * Check if the Score service is available.
     *
     * @return bool    True if this resource link supports the Score service
     */
    public function hasScoreService(): bool
    {
        $has = false;
        if (!empty($this->getSetting('custom_ags_scopes'))) {
            $scopes = explode(',', $this->getSetting('custom_ags_scopes'));
            if (in_array(Service\Score::$SCOPE, $scopes)) {
                $has = !empty($this->getSetting('custom_lineitem_url'));
            }
        }

        return $has;
    }

    /**
     * Check if the Result service is available.
     *
     * @return bool    True if this resource link supports the Result service
     */
    public function hasResultService(): bool
    {
        $has = false;
        if (!empty($this->getSetting('custom_ags_scopes'))) {
            $scopes = explode(',', $this->getSetting('custom_ags_scopes'));
            if (in_array(Service\Result::$SCOPE, $scopes)) {
                $has = !empty($this->getSetting('custom_lineitem_url'));
            }
        }

        return $has;
    }

    /**
     * Check if the Assessment Control service is available.
     *
     * @return bool    True if this resource link supports the Assessment Control service
     */
    public function hasAssessmentControlService(): bool
    {
        $url = $this->getSetting('custom_ap_acs_url');

        return !empty($url);
    }

    /**
     * Perform an Outcomes service request.
     * @param int        $action     The action type constant
     * @param Outcome    $ltiOutcome Outcome object
     * @param UserResult $userResult UserResult object
     * @return bool    True if the request was successfully processed
     */
    public function doOutcomesService(int $action, Outcome $ltiOutcome, UserResult $userResult): bool
    {
        $ok = false;
        $this->extResponse = '';
        // Lookup service details from the source resource link appropriate to the user (in case the destination is being shared)
        $sourceResourceLink = $userResult->getResourceLink();
        $sourcedId = $userResult->ltiResultSourcedId;

        // Use LTI 1.1 service in preference to extension service if it is available
        $urlAGS = $sourceResourceLink->getSetting('custom_lineitem_url');
        $urlLTI11 = $sourceResourceLink->getSetting('lis_outcome_service_url');
        $urlExt = $sourceResourceLink->getSetting('ext_ims_lis_basic_outcome_url');

        if (!empty($urlAGS)) {
            if (($action === self::EXT_READ) && ($ltiOutcome->type === self::EXT_TYPE_DECIMAL) && $sourceResourceLink->hasResultService()) {
                $ltiOutcome = $this->doResultService($userResult, $urlAGS);
                $ok = !empty($ltiOutcome);
            } elseif ((($action === self::EXT_WRITE) && $this->checkValueType($ltiOutcome, array(self::EXT_TYPE_DECIMAL)) && $sourceResourceLink->hasScoreService()) ||
                ($action === self::EXT_DELETE)) {
                if ($action === self::EXT_DELETE) {
                    $ltiOutcome->setValue(null);
                    $ltiOutcome->activityProgress = 'Initialized';
                    $ltiOutcome->gradingProgress = 'NotReady';
                }
                $ok = $this->doScoreService($ltiOutcome, $userResult, $urlAGS);
            }
        }
        if (!$ok && is_null($ltiOutcome->getValue())) {
            $ltiOutcome->setValue('');
        }
        if (!$ok && !empty($urlLTI11)) {
            $do = '';
            $outcome = $ltiOutcome->getValue();
            if (($action === self::EXT_READ) && ($ltiOutcome->type === self::EXT_TYPE_DECIMAL)) {
                $do = 'readResult';
            } elseif (($action === self::EXT_WRITE) && $this->checkValueType($ltiOutcome, array(self::EXT_TYPE_DECIMAL))) {
                $do = 'replaceResult';
                if (($ltiOutcome->getPointsPossible() <> 1) && ($ltiOutcome->getPointsPossible() > 0)) {
                    $outcome = $outcome / $ltiOutcome->getPointsPossible();
                }
            } elseif ($action === self::EXT_DELETE) {
                $do = 'deleteResult';
            }
            if (!empty($do)) {
                $xml = '';
                if ($action === self::EXT_WRITE) {
                    $comment = (empty($ltiOutcome->comment)) ? '' : trim($ltiOutcome->comment);
                    if (!empty($comment) && !empty($sourceResourceLink->getSetting('ext_outcome_data_values_accepted'))) {
                        $resultDataTypes = explode(',', $sourceResourceLink->getSetting('ext_outcome_data_values_accepted'));
                        $resultDataType = '';
                        if (count($resultDataTypes) === 1) {
                            $resultDataType = $resultDataTypes[0];
                        } elseif (count($resultDataTypes) > 1) {
                            $isUrl = (strpos($comment, 'http://') === 0) || (strpos($comment, 'https://') === 0);
                            if ($isUrl && in_array('ltiLaunchUrl', $resultDataTypes)) {
                                $resultDataType = 'ltiLaunchUrl';
                            } elseif ($isUrl && in_array('url', $resultDataTypes)) {
                                $resultDataType = 'url';
                            } elseif (in_array('text', $resultDataTypes)) {
                                $resultDataType = 'text';
                            }
                        }
                        if (!empty($resultDataType)) {
                            $xml = <<< EOF

          <resultData>
            <{$resultDataType}>{$comment}</{$resultDataType}>
          </resultData>
EOF;
                        }
                    }
                    $xml = <<< EOF

        <result>
          <resultScore>
            <language>{$ltiOutcome->language}</language>
            <textString>{$outcome}</textString>
          </resultScore>{$xml}
        </result>
EOF;
                }
                $sourcedId = htmlentities($sourcedId);
                $xml = <<<EOF
      <resultRecord>
        <sourcedGUID>
          <sourcedId>{$sourcedId}</sourcedId>
        </sourcedGUID>{$xml}
      </resultRecord>
EOF;
                if ($this->doLTI11Service($do, $urlLTI11, $xml)) {
                    switch ($action) {
                        case self::EXT_READ:
                            if (!isset($this->extNodes['imsx_POXBody']["{$do}Response"]['result']['resultScore']['textString'])) {
                                break;
                            } else {
                                $ltiOutcome->setValue($this->extNodes['imsx_POXBody']["{$do}Response"]['result']['resultScore']['textString']);
                            }
                            // no break
                        case self::EXT_WRITE:
                        case self::EXT_DELETE:
                            $ok = true;
                            break;
                    }
                }
            }
        }
        if (!$ok && !empty($urlExt)) {
            $do = '';
            $outcome = $ltiOutcome->getValue();
            if (($action === self::EXT_READ) && ($ltiOutcome->type === self::EXT_TYPE_DECIMAL)) {
                $do = 'basic-lis-readresult';
            } elseif (($action === self::EXT_WRITE) && $this->checkValueType($ltiOutcome, array(self::EXT_TYPE_DECIMAL))) {
                $do = 'basic-lis-updateresult';
                if (($ltiOutcome->getPointsPossible() <> 1) && ($ltiOutcome->getPointsPossible() > 0)) {
                    $outcome = $outcome / $ltiOutcome->getPointsPossible();
                }
            } elseif ($action === self::EXT_DELETE) {
                $do = 'basic-lis-deleteresult';
            }
            if (!empty($do)) {
                $params = array();
                $params['sourcedid'] = $sourcedId;
                $params['result_resultscore_textstring'] = $outcome;
                if (!empty($ltiOutcome->language)) {
                    $params['result_resultscore_language'] = $ltiOutcome->language;
                }
                if (!empty($ltiOutcome->status)) {
                    $params['result_statusofresult'] = $ltiOutcome->status;
                }
                if (!empty($ltiOutcome->date)) {
                    $params['result_date'] = $ltiOutcome->date;
                }
                if (!empty($ltiOutcome->type)) {
                    $params['result_resultvaluesourcedid'] = $ltiOutcome->type;
                }
                if (!empty($ltiOutcome->dataSource)) {
                    $params['result_datasource'] = $ltiOutcome->dataSource;
                }
                if ($this->doService($do, $urlExt, $params, 'https://purl.imsglobal.org/spec/lti-ext/scope/outcomes')) {
                    switch ($action) {
                        case self::EXT_READ:
                            if (isset($this->extNodes['result']['resultscore']['textstring'])) {
                                $ltiOutcome->setValue($this->extNodes['result']['resultscore']['textstring']);
                            }
                            // no break
                        case self::EXT_WRITE:
                        case self::EXT_DELETE:
                            $ok = true;
                            break;
                    }
                }
            }
        }
        if ((!$ok) && $this->hasConfiguredApiHook(self::$OUTCOMES_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this)) {
            $className = $this->getApiHook(self::$OUTCOMES_SERVICE_HOOK, $this->getPlatform()->getFamilyCode());
            $hook = new $className($this);
            $response = $hook->doOutcomesService($action, $ltiOutcome, $userResult);
            if ($response !== false) {
                $ok = true;
                if ($action === self::EXT_READ) {
                    $ltiOutcome->setValue($response);
                }
            }
        }

        return $ok;
    }

//    /**
//     * Perform a Memberships extension service request.
//     * The userResult table is updated with any user objects with lis_result_sourcedid values.
//     * @param bool    $withGroups True is group information is to be requested as well
//     * @return mixed Array of UserResult objects or False if the request was not successful
//     *@deprecated Use getMemberships() instead
//     * @see ResourceLink::getMemberships()
//     */
//    public function doMembershipsService(bool $withGroups = false)
//    {
//        Util::logDebug('Method ceLTIc\LTI\ResourceLink::doMembershipsService() has been deprecated; please use ceLTIc\LTI\ResourceLink::getMemberships() instead.',
//            true);
//        return $this->getMemberships($withGroups);
//    }

    /**
     * Perform a Setting service request.
     * @param int         $action The action type constant
     * @param string|null $value  The setting value (optional, default is null)
     * @return mixed The setting value for a read action, true if a write or delete action was successful, otherwise false
     */
    public function doSettingService(int $action, string $value = null)
    {
        $response = false;
        $this->extResponse = '';
        switch ($action) {
            case self::EXT_READ:
                $do = 'basic-lti-loadsetting';
                break;
            case self::EXT_WRITE:
                $do = 'basic-lti-savesetting';
                break;
            case self::EXT_DELETE:
                $do = 'basic-lti-deletesetting';
                break;
        }
        if (isset($do)) {
            $url = $this->getSetting('ext_ims_lti_tool_setting_url');
            $params = array();
            $params['id'] = $this->getSetting('ext_ims_lti_tool_setting_id');
            if (is_null($value)) {
                $value = '';
            }
            $params['setting'] = $value;

            if ($this->doService($do, $url, $params, 'https://purl.imsglobal.org/spec/lti-ext/scope/setting')) {
                switch ($action) {
                    case self::EXT_READ:
                        if (isset($this->extNodes['setting']['value'])) {
                            $response = $this->extNodes['setting']['value'];
                            if (is_array($response)) {
                                $response = '';
                            }
                        }
                        break;
                    case self::EXT_WRITE:
                        $this->setSetting('ext_ims_lti_tool_setting', $value);
                        $this->saveSettings();
                        $response = true;
                        break;
                    case self::EXT_DELETE:
                        $response = true;
                        break;
                }
            }
        }

        return $response;
    }

    /**
     * Check if the Tool Settings service is available.
     *
     * @return bool    True if this resource link supports the Tool Settings service
     */
    public function hasToolSettingsService(): bool
    {
        $has = !empty($this->getSetting('custom_link_setting_url'));
        if (!$has) {
            $has = self::hasConfiguredApiHook(self::$TOOL_SETTINGS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this);
        }
        return $has;
    }

    /**
     * Get Tool Settings.
     * @param int  $mode   Mode for request (optional, default is current level only)
     * @param bool $simple True if all the simple media type is to be used (optional, default is true)
     * @return mixed The array of settings if successful, otherwise false
     */
    public function getToolSettings(int $mode = Service\ToolSettings::MODE_CURRENT_LEVEL, bool $simple = true)
    {
        $ok = false;
        $settings = array();
        if (!empty($this->getSetting('custom_link_setting_url'))) {
            $url = $this->getSetting('custom_link_setting_url');
            $service = new Service\ToolSettings($this, $url, $simple);
            $settings = $service->get($mode);
            $this->lastServiceRequest = $service->getHttpMessage();
            $ok = $settings !== false;
        }
        if (!$ok && $this->hasConfiguredApiHook(self::$TOOL_SETTINGS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this)) {
            $className = $this->getApiHook(self::$TOOL_SETTINGS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode());
            $hook = new $className($this);
            $settings = $hook->getToolSettings($mode, $simple);
        }

        return $settings;
    }

    /**
     * Set Tool Settings.
     * @param array $settings An associative array of settings (optional, default is none)
     * @return bool    True if action was successful, otherwise false
     */
    public function setToolSettings(array $settings = array()): bool
    {
        $ok = false;
        if (!empty($this->getSetting('custom_link_setting_url'))) {
            $url = $this->getSetting('custom_link_setting_url');
            $service = new Service\ToolSettings($this, $url);
            $ok = $service->set($settings);
            $this->lastServiceRequest = $service->getHttpMessage();
        }
        if (!$ok && $this->hasConfiguredApiHook(self::$TOOL_SETTINGS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this)) {
            $className = $this->getApiHook(self::$TOOL_SETTINGS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode());
            $hook = new $className($this);
            $ok = $hook->setToolSettings($settings);
        }

        return $ok;
    }

    /**
     * Check if the Membership service is supported.
     *
     * @deprecated Use hasMembershipsService() instead
     * @see ResourceLink::hasMembershipsService()
     *
     * @return bool    True if this resource link supports the Membership service
     */
    public function hasMembershipService(): bool
    {
        Util::logDebug(
            'Method ceLTIc\LTI\ResourceLink::hasMembershipService() has been deprecated; please use ceLTIc\LTI\ResourceLink::hasMembershipsService() instead.',
            true
        );
        return $this->hasMembershipsService();
    }

    /**
     * Get Membership.
     *
     * @deprecated Use getMemberships() instead
     * @see ResourceLink::getMemberships()
     *
     * @return mixed The array of UserResult objects if successful, otherwise false
     */
    public function getMembership()
    {
        Util::logDebug(
            'Method ceLTIc\LTI\ResourceLink::getMembership() has been deprecated; please use ceLTIc\LTI\ResourceLink::getMemberships() instead.',
            true
        );
        return $this->getMemberships();
    }

    /**
     * Get Memberships.
     * @param bool $withGroups True is group information is to be requested as well
     * @return mixed The array of UserResult objects if successful, otherwise false
     */
    public function getMemberships(bool $withGroups = false)
    {
        $ok = false;
        $userResults = array();
        $hasLtiLinkService = !empty($this->getSetting('custom_link_memberships_url'));
        $hasLtiContextService = !empty($this->getContextId()) &&
            (!empty($this->getContext()->getSetting('custom_context_memberships_url')) || !empty($this->getContext()->getSetting('custom_context_memberships_v2_url')));
        $hasGroupsService = !empty($this->getContextId()) && !empty($this->getContext()->getSetting('custom_context_groups_url'));
        $hasExtService = !empty($this->getSetting('ext_ims_lis_memberships_url'));
        $hasApiHook = $this->hasConfiguredApiHook(self::$MEMBERSHIPS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode(), $this);
        if (($hasLtiContextService && (!$withGroups || $hasGroupsService)) || (!$hasExtService && !$hasApiHook)) {
            if (!empty($this->getContextId()) && !empty($this->getContext()->getSetting('custom_context_memberships_v2_url'))) {
                $url = $this->getContext()->getSetting('custom_context_memberships_v2_url');
                $format = Service\Membership::MEDIA_TYPE_MEMBERSHIPS_NRPS;
            } else {
                $url = $this->getContext()->getSetting('custom_context_memberships_url');
                $format = Service\Membership::MEDIA_TYPE_MEMBERSHIPS_V1;
            }
            $service = new Service\Membership($this, $url, $format);
            if (!$withGroups) {
                $userResults = $service->get();
            } else {
                $userResults = $service->getWithGroups();
            }
            $this->lastServiceRequest = $service->getHttpMessage();
            $ok = $userResults !== false;
        } elseif ($hasLtiLinkService) {
            $id = $this->id;
            $this->id = null;
            $url = $this->getSetting('custom_link_memberships_url');
            $format = Service\Membership::MEDIA_TYPE_MEMBERSHIPS_V1;
            $service = new Service\Membership($this, $url, $format);
            if (!$withGroups) {
                $userResults = $service->get();
            } else {
                $userResults = $service->getWithGroups();
            }
            $this->lastServiceRequest = $service->getHttpMessage();
            $this->id = $id;
            $ok = $userResults !== false;
        }
        if (!$ok && $hasExtService) {
            $this->extResponse = '';
            $url = $this->getSetting('ext_ims_lis_memberships_url');
            $params = array();
            $params['id'] = $this->getSetting('ext_ims_lis_memberships_id');
            if ($withGroups) {
                $ok = $this->doService(
                    'basic-lis-readmembershipsforcontextwithgroups',
                    $url,
                    $params,
                    'https://purl.imsglobal.org/spec/lti-ext/scope/memberships'
                );
            }
            if (!$ok) {
                $ok = $this->doService(
                    'basic-lis-readmembershipsforcontext',
                    $url,
                    $params,
                    'https://purl.imsglobal.org/spec/lti-ext/scope/memberships'
                );
            }
            if ($ok) {
                $this->groupSets = array();
                $this->groups = array();
                if (isset($this->extNodes['memberships'])) {
                    $memberships = $this->extNodes['memberships'];
                } elseif (isset($this->extNodes['members'])) {
                    $memberships = $this->extNodes['members'];
                } else {
                    $ok = false;
                }
            }
            if ($ok) {
                if (!isset($memberships['member'])) {
                    $members = array();
                } elseif (!isset($memberships['member'][0])) {
                    $members = array();
                    $members[0] = $memberships['member'];
                } else {
                    $members = $memberships['member'];
                }

                for ($i = 0; $i < count($members); $i++) {
                    $userresult = UserResult::fromResourceLink($this, $members[$i]['user_id']);

                    // Set the user name
                    $firstname = (isset($members[$i]['person_name_given'])) ? $members[$i]['person_name_given'] : '';
                    $lastname = (isset($members[$i]['person_name_family'])) ? $members[$i]['person_name_family'] : '';
                    $fullname = (isset($members[$i]['person_name_full'])) ? $members[$i]['person_name_full'] : '';
                    $userresult->setNames($firstname, $lastname, $fullname);

                    // Set the sourcedId
                    if (isset($members[$i]['person_sourcedid'])) {
                        $userresult->sourcedId = $members[$i]['person_sourcedid'];
                    }

                    // Set the user email
                    $email = (isset($members[$i]['person_contact_email_primary'])) ? $members[$i]['person_contact_email_primary'] : '';
                    $userresult->setEmail($email, $this->getPlatform()->defaultEmail);

                    // Set the user roles
                    if (isset($members[$i]['roles'])) {
                        $userresult->roles = Tool::parseRoles($members[$i]['roles']);
                    }

                    // Set the user groups
                    if (!isset($members[$i]['groups']['group'])) {
                        $groups = array();
                    } elseif (!isset($members[$i]['groups']['group'][0])) {
                        $groups = array();
                        $groups[0] = $members[$i]['groups']['group'];
                    } else {
                        $groups = $members[$i]['groups']['group'];
                    }
                    for ($j = 0; $j < count($groups); $j++) {
                        $group = $groups[$j];
                        if (isset($group['set'])) {
                            $set_id = $group['set']['id'];
                            if (!isset($this->groupSets[$set_id])) {
                                $this->groupSets[$set_id] = array('title' => $group['set']['title'], 'groups' => array(),
                                                                  'num_members' => 0, 'num_staff' => 0, 'num_learners' => 0);
                            }
                            $this->groupSets[$set_id]['num_members']++;
                            if ($userresult->isStaff()) {
                                $this->groupSets[$set_id]['num_staff']++;
                            }
                            if ($userresult->isLearner()) {
                                $this->groupSets[$set_id]['num_learners']++;
                            }
                            if (!in_array($group['id'], $this->groupSets[$set_id]['groups'])) {
                                $this->groupSets[$set_id]['groups'][] = $group['id'];
                            }
                            $this->groups[$group['id']] = array('title' => $group['title'], 'set' => $set_id);
                        } else {
                            $this->groups[$group['id']] = array('title' => $group['title']);
                        }
                        $userresult->groups[] = $group['id'];
                    }
                    if (isset($members[$i]['lis_result_sourcedid'])) {
                        $userresult->ltiResultSourcedId = $members[$i]['lis_result_sourcedid'];
                    }
                    $userResults[] = $userresult;
                }
            } else {
                $userResults = false;
            }
            $ok = $userResults !== false;
        }
        if (!$ok && $hasApiHook) {
            $className = $this->getApiHook(self::$MEMBERSHIPS_SERVICE_HOOK, $this->getPlatform()->getFamilyCode());
            $hook = new $className($this);
            $userResults = $hook->getMemberships($withGroups);
            $ok = $userResults !== false;
        }
        if ($ok) {
            $oldUsers = $this->getUserResultSourcedIDs(true, Tool::ID_SCOPE_RESOURCE);
            foreach ($userResults as $userresult) {
                // If a result sourcedid is provided save the user
                if (!empty($userresult->ltiResultSourcedId)) {
                    $userresult->save();
                }
                // Remove old user (if it exists)
                unset($oldUsers[$userresult->getId(Tool::ID_SCOPE_RESOURCE)]);
            }
            // Delete any old users which were not in the latest list from the platform
            foreach ($oldUsers as $id => $userresult) {
                $userresult->delete();
            }
        }

        return $userResults;
    }

    /**
     * Obtain an array of UserResult objects for users with a result sourcedId.
     * The array may include users from other resource links which are sharing this resource link.
     * It may also be optionally indexed by the user ID of a specified scope.
     * @param bool     $localOnly True if only users from this resource link are to be returned, not users from shared resource links (optional, default is false)
     * @param int|null $idScope   Scope to use for ID values (optional, default is null for platform default)
     * @return UserResult[] Array of UserResult objects
     */
    public function getUserResultSourcedIDs(bool $localOnly = false, int $idScope = null): array
    {
        return $this->getDataConnector()->getUserResultSourcedIDsResourceLink($this, $localOnly, $idScope);
    }

    /**
     * Get an array of ResourceLinkShare objects for each resource link which is sharing this context.
     *
     * @return ResourceLinkShare[] Array of ResourceLinkShare objects
     */
    public function getShares(): array
    {
        return $this->getDataConnector()->getSharesResourceLink($this);
    }

    /**
     * Get line items.
     * @param string|null $resourceId Tool resource ID
     * @param string|null $tag        Tag
     * @param int|null    $limit      Limit of line items to be returned in each request, null for service default
     * @return Service\LineItem[]|bool  Array of LineItem objects or false on error
     */
    public function getLineItems(string $resourceId = null, string $tag = null, int $limit = null)
    {
        $lineItems = false;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        $lineItemService = $this->getLineItemService();
        if (!empty($lineItemService)) {
            $lineItems = $lineItemService->getAll($this->ltiResourceLinkId, $resourceId, $tag, $limit);
            $http = $lineItemService->getHttpMessage();
            $this->extResponse = $http->response;
            $this->extResponseHeaders = $http->responseHeaders;
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $lineItems;
    }

    /**
     * Create a new line item.
     * @param LineItem $lineItem Line item object
     * @return bool  True if successful
     */
    public function createLineItem(LineItem $lineItem): bool
    {
        $ok = false;
        $lineItemService = $this->getLineItemService();
        if (!empty($lineItemService)) {
            $lineItem->ltiResourceLinkId = $this->ltiResourceLinkId;
            $ok = $lineItemService->createLineItem($lineItem);
        }

        return $ok;
    }

    /**
     * Get all outcomes.
     * @param int|null $limit Limit of outcomes to be returned in each request, null for service default
     * @return Outcome[]|bool  Array of Outcome objects or false on error
     */
    public function getOutcomes(int $limit = null)
    {
        $outcomes = false;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        $url = $this->getSetting('custom_lineitem_url');
        if (!empty($url)) {
            $resultService = new Service\Result($this->getPlatform(), $url);
            $outcomes = $resultService->getAll($limit);
            $http = $resultService->getHttpMessage();
            $this->extResponse = $http->response;
            $this->extResponseHeaders = $http->responseHeaders;
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $outcomes;
    }

    /**
     * Perform an Assessment Control action.
     * @param AssessmentControlAction $assessmentControlAction Assessment control object
     * @param User                    $user                    User object
     * @param int                     $attemptNumber           Number of attempt
     * @return string|bool    The response status or false if the request was not successfully processed
     */
    public function doAssessmentControlAction(AssessmentControlAction $assessmentControlAction, User $user, int $attemptNumber)
    {
        $status = false;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        $url = $this->getSetting('custom_ap_acs_url');
        if (!empty($url)) {
            $assessmentControlService = new Service\AssessmentControl($this, $url);
            $status = $assessmentControlService->submitAction($assessmentControlAction, $user, $attemptNumber);
            $http = $assessmentControlService->getHttpMessage();
            $this->extResponse = $http->response;
            $this->extResponseHeaders = $http->responseHeaders;
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $status;
    }

//    /**
//     * Class constructor from consumer.
//     *
//     * @deprecated Use fromPlatform() instead
//     * @see ResourceLink::fromPlatform()
//     *
//     * @param ToolConsumer $consumer            Consumer object
//     * @param string $ltiResourceLinkId         Resource link ID value
//     * @param string $tempId                    Temporary Resource link ID value (optional, default is null)
//     *
//     * @return ResourceLink
//     */
//    public static function fromConsumer($consumer, $ltiResourceLinkId, $tempId = null)
//    {
//        Util::logDebug('Method ceLTIc\LTI\ResourceLink::fromConsumer() has been deprecated; please use ceLTIc\LTI\ResourceLink::fromPlatform() instead.',
//            true);
//        return self::fromPlatform($consumer, $ltiResourceLinkId, $tempId);
//    }

    /**
     * Class constructor from platform.
     * @param Platform    $platform          Platform object
     * @param string      $ltiResourceLinkId Resource link ID value
     * @param string|null $tempId            Temporary Resource link ID value (optional, default is null)
     * @return ResourceLink
     */
    public static function fromPlatform(Platform $platform, string $ltiResourceLinkId, string $tempId = null): ResourceLink
    {
        $resourceLink = new ResourceLink();
        $resourceLink->platform = $platform;
        $resourceLink->dataConnector = $platform->getDataConnector();
        $resourceLink->ltiResourceLinkId = $ltiResourceLinkId;
        if (!empty($ltiResourceLinkId)) {
            $resourceLink->load();
            if (is_null($resourceLink->id) && !empty($tempId)) {
                $resourceLink->ltiResourceLinkId = $tempId;
                $resourceLink->load();
                $resourceLink->ltiResourceLinkId = $ltiResourceLinkId;
            }
        }

        return $resourceLink;
    }

    /**
     * Class constructor from context.
     * @param Context     $context           Context object
     * @param string      $ltiResourceLinkId Resource link ID value
     * @param string|null $tempId            Temporary Resource link ID value (optional, default is null)
     * @return ResourceLink
     */
    public static function fromContext(Context $context, string $ltiResourceLinkId, string $tempId = null): ResourceLink
    {
        $resourceLink = new ResourceLink();
        $resourceLink->setContext($context);
        $resourceLink->dataConnector = $context->getDataConnector();
        $resourceLink->ltiResourceLinkId = $ltiResourceLinkId;
        if (!empty($ltiResourceLinkId)) {
            $resourceLink->load();
            if (is_null($resourceLink->id) && !empty($tempId)) {
                $resourceLink->ltiResourceLinkId = $tempId;
                $resourceLink->load();
                $resourceLink->ltiResourceLinkId = $ltiResourceLinkId;
            }
            $resourceLink->setContext($context);  // Ensure context remains set
        }

        return $resourceLink;
    }

    /**
     * Load the resource link from the database.
     * @param int           $id            Record ID of resource link
     * @param DataConnector $dataConnector Database connection object
     * @return ResourceLink  ResourceLink object
     */
    public static function fromRecordId(int $id, DataConnector $dataConnector): ResourceLink
    {
        $resourceLink = new ResourceLink();
        $resourceLink->dataConnector = $dataConnector;
        $resourceLink->load($id);

        return $resourceLink;
    }

    ###
    ###  PRIVATE METHODS
    ###

    /**
     * Load the resource link from the database.
     * @param int|null $id Record ID of resource link (optional, default is null)
     * @return bool    True if resource link was successfully loaded
     */
    private function load(int $id = null): bool
    {
        $this->initialize();
        $this->id = $id;

        return $this->getDataConnector()->loadResourceLink($this);
    }

    /**
     * Convert data type of value to a supported type if possible.
     * @param Outcome    $ltiOutcome     Outcome object
     * @param array|null $supportedTypes Array of outcome types to be supported (optional, default is null to use supported types reported in the last launch for this resource link)
     * @return bool    True if the type/value are valid and supported
     */
    private function checkValueType(Outcome $ltiOutcome, array $supportedTypes = null): bool
    {
        if (empty($supportedTypes)) {
            $supportedTypes = explode(
                ',',
                str_replace(' ', '', strtolower($this->getSetting('ext_ims_lis_resultvalue_sourcedids', self::EXT_TYPE_DECIMAL)))
            );
        }
        $type = $ltiOutcome->type;
        $value = $ltiOutcome->getValue();
        // Check whether the type is supported or there is no value
        $ok = in_array($type, $supportedTypes) || empty($value);
        if (!$ok) {
            // Convert numeric values to decimal
            if ($type === self::EXT_TYPE_PERCENTAGE) {
                if (substr($value, -1) === '%') {
                    $value = substr($value, 0, -1);
                }
                $ok = is_numeric($value) && ($value >= 0) && ($value <= 100);
                if ($ok) {
                    $ltiOutcome->setValue($value / 100);
                    $ltiOutcome->type = self::EXT_TYPE_DECIMAL;
                }
            } elseif ($type === self::EXT_TYPE_RATIO) {
                $parts = explode('/', $value, 2);
                $ok = (count($parts) === 2) && is_numeric($parts[0]) && is_numeric($parts[1]) && ($parts[0] >= 0) && ($parts[1] > 0);
                if ($ok) {
                    $ltiOutcome->setValue($parts[0] / $parts[1]);
                    $ltiOutcome->type = self::EXT_TYPE_DECIMAL;
                }
            // Convert letter_af to letter_af_plus or text
            } elseif ($type === self::EXT_TYPE_LETTER_AF) {
                if (in_array(self::EXT_TYPE_LETTER_AF_PLUS, $supportedTypes)) {
                    $ok = true;
                    $ltiOutcome->type = self::EXT_TYPE_LETTER_AF_PLUS;
                } elseif (in_array(self::EXT_TYPE_TEXT, $supportedTypes)) {
                    $ok = true;
                    $ltiOutcome->type = self::EXT_TYPE_TEXT;
                }
            // Convert letter_af_plus to letter_af or text
            } elseif ($type === self::EXT_TYPE_LETTER_AF_PLUS) {
                if (in_array(self::EXT_TYPE_LETTER_AF, $supportedTypes) && (strlen($value) === 1)) {
                    $ok = true;
                    $ltiOutcome->type = self::EXT_TYPE_LETTER_AF;
                } elseif (in_array(self::EXT_TYPE_TEXT, $supportedTypes)) {
                    $ok = true;
                    $ltiOutcome->type = self::EXT_TYPE_TEXT;
                }
            // Convert text to decimal
            } elseif ($type === self::EXT_TYPE_TEXT) {
                $ok = is_numeric($value) && ($value >= 0) && ($value <= 1);
                if ($ok) {
                    $ltiOutcome->type = self::EXT_TYPE_DECIMAL;
                } elseif (substr($value, -1) === '%') {
                    $value = substr($value, 0, -1);
                    $ok = is_numeric($value) && ($value >= 0) && ($value <= 100);
                    if ($ok) {
                        if (in_array(self::EXT_TYPE_PERCENTAGE, $supportedTypes)) {
                            $ltiOutcome->type = self::EXT_TYPE_PERCENTAGE;
                        } else {
                            $ltiOutcome->setValue($value / 100);
                            $ltiOutcome->type = self::EXT_TYPE_DECIMAL;
                        }
                    }
                }
            }
        }

        return $ok;
    }

    /**
     * Send an unofficial LTI service request to the platform.
     * @param string $type   Message type value
     * @param string $url    URL to send request to
     * @param array  $params Associative array of parameter values to be passed
     * @param string $scope  Scope for service
     * @return bool    True if the request successfully obtained a response
     */
    private function doService(string $type, string $url, array $params, string $scope): bool
    {
        $ok = false;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        if (!empty($url)) {
            $params['lti_version'] = $this->getPlatform()->ltiVersion;
            $params['lti_message_type'] = $type;
            $retry = false;
            $newToken = false;
            if (!$this->getPlatform()->useOAuth1()) {
                $accessToken = $this->platform->getAccessToken();
                $retry = true;
                if (empty($accessToken)) {
                    $accessToken = new AccessToken($this->platform);
                    $this->platform->setAccessToken($accessToken);
                }
                if (!$accessToken->hasScope($scope) && (empty(Tool::$defaultTool) || !in_array(
                    $scope,
                    Tool::$defaultTool->requiredScopes
                ))) {
                    $accessToken->expires = time();
                    $accessToken->get($scope, true);
                    $this->platform->setAccessToken($accessToken);
                    $newToken = true;
                }
            }
            do {
                // Add message signature
                $signed = $this->getPlatform()->addSignature($url, $params, 'POST', 'application/x-www-form-urlencoded');
                // Connect to platform
                if (is_array($signed)) {
                    $http = new HttpMessage($url, 'POST', $signed);
                } else {
                    $http = new HttpMessage($url, 'POST', $params, $signed);
                }
                if ($http->send()) {
                    // Parse XML response
                    $this->extResponse = $http->response;
                    $this->extResponseHeaders = $http->responseHeaders;
                    try {
                        $this->extDoc = new DOMDocument();
                        $this->extDoc->loadXML($http->response);
                        $this->extNodes = $this->domnodeToArray($this->extDoc->documentElement);
                        if (isset($this->extNodes['statusinfo']['codemajor']) && ($this->extNodes['statusinfo']['codemajor'] === 'Success')) {
                            $ok = true;
                        }
                    } catch (\Exception $e) {
                    }
                }
                $retry = $retry && !$newToken && !$ok;
                if ($retry) {  // Obtain a new access token just for the required scope
                    $accessToken = $this->platform->getAccessToken();
                    $accessToken->expires = time();
                    $accessToken->get($scope, true);
                    $this->platform->setAccessToken($accessToken);
                    $newToken = true;
                }
            } while ($retry);
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $ok;
    }

    /**
     * Send a request to the Result service endpoint.
     * @param UserResult $userResult UserResult object
     * @param string     $url        URL to send request to
     * @return Outcome    Outcome object
     */
    private function doResultService(UserResult $userResult, string $url): ?Outcome
    {
        $outcome = null;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        if (!empty($url)) {
            $resultService = new Service\Result($this->getPlatform(), $url);
            $outcome = $resultService->get($userResult);
            $http = $resultService->getHttpMessage();
            $this->extResponse = $http->response;
            $this->extResponseHeaders = $http->responseHeaders;
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $outcome;
    }

    /**
     * Send a service request to the Score service endpoint.
     * @param Outcome    $ltiOutcome Outcome object
     * @param UserResult $userResult UserResult object
     * @param string     $url        URL to send request to
     * @return bool    True if the request successfully obtained a response
     */
    private function doScoreService(Outcome $ltiOutcome, UserResult $userResult, string $url): bool
    {
        $ok = false;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        if (!empty($url)) {
            $scoreService = new Service\Score($this->getPlatform(), $url);
            $scoreService->submit($ltiOutcome, $userResult);
            $http = $scoreService->getHttpMessage();
            $this->extResponse = $http->response;
            $this->extResponseHeaders = $http->responseHeaders;
            $ok = $http->ok;
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $ok;
    }

    /**
     * Send an LTI 1.1 service request to the platform.
     * @param string $type Message type value
     * @param string $url  URL to send request to
     * @param string $xml  XML of message request
     * @return bool    True if the request successfully obtained a response
     */
    private function doLTI11Service(string $type, string $url, string $xml): bool
    {
        $ok = false;
        $this->extRequest = '';
        $this->extRequestHeaders = '';
        $this->extResponse = '';
        $this->extResponseHeaders = '';
        $this->lastServiceRequest = null;
        if (!empty($url)) {
            $id = uniqid();
            $xmlRequest = <<< EOD
<?xml version = "1.0" encoding = "UTF-8"?>
<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
  <imsx_POXHeader>
    <imsx_POXRequestHeaderInfo>
      <imsx_version>V1.0</imsx_version>
      <imsx_messageIdentifier>{$id}</imsx_messageIdentifier>
    </imsx_POXRequestHeaderInfo>
  </imsx_POXHeader>
  <imsx_POXBody>
    <{$type}Request>
{$xml}
    </{$type}Request>
  </imsx_POXBody>
</imsx_POXEnvelopeRequest>
EOD;
            $scope = 'https://purl.imsglobal.org/spec/lti-bo/scope/basicoutcome';
            $retry = false;
            $newToken = false;
            if (!$this->getPlatform()->useOAuth1()) {
                $accessToken = $this->platform->getAccessToken();
                $retry = true;
                if (empty($accessToken)) {
                    $accessToken = new AccessToken($this->platform);
                    $this->platform->setAccessToken($accessToken);
                }
                if (!$accessToken->hasScope($scope) && (empty(Tool::$defaultTool) || !in_array(
                    $scope,
                    Tool::$defaultTool->requiredScopes
                ))) {
                    $accessToken->expires = time();
                    $accessToken->get($scope, true);
                    $this->platform->setAccessToken($accessToken);
                    $newToken = true;
                }
            }
            do {
                // Add message signature
                $header = $this->getPlatform()->addSignature($url, $xmlRequest, 'POST', 'application/xml');
                // Connect to platform
                $http = new HttpMessage($url, 'POST', $xmlRequest, $header);
                if ($http->send()) {
                    // Parse XML response
                    $this->extResponse = $http->response;
                    $this->extResponseHeaders = $http->responseHeaders;
                    try {
                        $this->extDoc = new DOMDocument();
                        $this->extDoc->loadXML($http->response);
                        $this->extNodes = $this->domnodeToArray($this->extDoc->documentElement);
                        if (isset($this->extNodes['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo']['imsx_codeMajor']) &&
                            ($this->extNodes['imsx_POXHeader']['imsx_POXResponseHeaderInfo']['imsx_statusInfo']['imsx_codeMajor'] === 'success')) {
                            $ok = true;
                        }
                    } catch (\Exception $e) {
                    }
                }
                $retry = $retry && !$newToken && !$ok;
                if ($retry) {  // Obtain a new access token just for the required scope
                    $accessToken = $this->platform->getAccessToken();
                    $accessToken->expires = time();
                    $accessToken->get($scope, true);
                    $this->platform->setAccessToken($accessToken);
                    $newToken = true;
                }
            } while ($retry);
            $this->extRequest = $http->request;
            $this->extRequestHeaders = $http->requestHeaders;
            $this->lastServiceRequest = $http;
        }

        return $ok;
    }

    /**
     * Get the Line Item service object.
     *
     * @return bool|Service\LineItem    Line Item service, or false if not available //erased Service\\LineItem
     */
    private function getLineItemService()
    {
        $url = $this->getSetting('custom_lineitems_url');
        if (!empty($url)) {
            $lineItemService = new Service\LineItem($this->getPlatform(), $url);
        } else {
            $lineItemService = false;
        }

        return $lineItemService;
    }

    /**
     * Convert DOM nodes to array.
     * @param DOMElement $node XML element
     * @return array|string Array of XML document elements
     */
    private function domnodeToArray(DOMElement $node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0; $i < $node->childNodes->length; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnodeToArray($child);
                    if (isset($child->tagName)) {
                        $output[$child->tagName][] = $v;
                    } else {
                        $s = (string) $v;
                        if (!empty($s)) {
                            $output = $s;
                        }
                    }
                }
                if (is_array($output)) {
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attrNode) {
                            $output['@attributes'][$attrNode->name] = (string) $attrNode->value;
                        }
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && (count($v) === 1) && ($t !== '@attributes')) {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }

        return $output;
    }
}
