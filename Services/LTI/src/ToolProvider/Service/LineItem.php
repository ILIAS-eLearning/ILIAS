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

namespace ILIAS\LTI\ToolProvider\Service;

use ILIAS\LTI\ToolProvider;
use ILIAS\LTI\ToolProvider\Platform;

/**
 * Class to implement the Line Item service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class LineItem extends AssignmentGrade
{
    /**
     * Line item media type.
     */
    public const MEDIA_TYPE_LINE_ITEM = 'application/vnd.ims.lis.v2.lineitem+json';

    /**
     * Line item container media type.
     */
    public const MEDIA_TYPE_LINE_ITEMS = 'application/vnd.ims.lis.v2.lineitemcontainer+json';

    /**
     * Access scope.
     */
    public static string $SCOPE = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';

    /**
     * Read-only access scope.
     */
    public static string $SCOPE_READONLY = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly';

    /**
     * Default limit on size of container to be returned from requests.
     */
    public static ?int $defaultLimit = null; //UK added ?int

    /**
     * Limit on size of container to be returned from requests.
     *
     * A limit of null (or zero) will disable paging of requests
     *
     * @var int|null  $limit
     */
    private ?int $limit;

    /**
     * Whether requests should be made one page at a time when a limit is set.
     *
     * When false, all objects will be requested, even if this requires several requests based on the limit set.
     *
     * @var boolean  $pagingMode
     */
    private bool $pagingMode;

    /**
     * Class constructor.
     * @param Platform $platform   Platform object for this service request
     * @param string   $endpoint   Service endpoint
     * @param int|null $limit      Limit of line items to be returned in each request, null for all
     * @param boolean  $pagingMode True if only a single page should be requested when a limit is set
     */
    public function __construct(Platform $platform, string $endpoint, int $limit = null, bool $pagingMode = false)
    {
        parent::__construct($platform, $endpoint);
        $this->limit = $limit;
        $this->pagingMode = $pagingMode;
        $this->scope = self::$SCOPE;
    }

    /**
     * Retrieve all line items.
     * The returned items can be filtered by a resource link ID, a resource ID and/or a tag.  Requests can
     * also be limited to a number of items which may mean that multiple requests will be made to retrieve the
     * full list.
     * @param string|null $ltiResourceLinkId LTI resource link ID (optional)
     * @param string|null $resourceId        Tool resource ID (optional)
     * @param string|null $tag               Tag (optional)
     * @param int|null    $limit             Limit of line items to be returned in each request, null for service default (optional)
     * @return LineItem[]|bool  Array of LineItem objects or false on error
     */
    public function getAll(string $ltiResourceLinkId = null, string $resourceId = null, string $tag = null, int $limit = null)
    {
        $params = array();
        if (!empty($ltiResourceLinkId)) {
            $params['resource_link_id'] = $ltiResourceLinkId;
        }
        if (!empty($resourceId)) {
            $params['resource_id'] = $resourceId;
        }
        if (!empty($tag)) {
            $params['tag'] = $tag;
        }
        if (is_null($limit)) {
            $limit = $this->limit;
        }
        if (is_null($limit)) {
            $limit = self::$defaultLimit;
        }
        if (!empty($limit)) {
            $params['limit'] = $limit;
        }
        $lineItems = array();
        $endpoint = $this->endpoint;
        do {
            $this->scope = self::$SCOPE_READONLY;
            $this->mediaType = self::MEDIA_TYPE_LINE_ITEMS;
            $http = $this->send('GET', $params);
            $this->scope = self::$SCOPE;
            $url = '';
            if ($http->ok) {
                if (!empty($http->responseJson)) {
                    foreach ($http->responseJson as $lineItem) {
                        $lineItems[] = self::toLineItem($this->getPlatform(), $lineItem);
                    }
                }
                if (!$this->pagingMode && $http->hasRelativeLink('next')) {
                    $url = $http->getRelativeLink('next');
                    $this->endpoint = $url;
                    $params = array();
                }
            } else {
                $lineItems = false;
            }
        } while ($url);
        $this->endpoint = $endpoint;

        return $lineItems;
    }

    /**
     * Create a new line item.
     * @param \ILIAS\LTI\ToolProvider\LineItem $lineItem Line item object //UK: changed from LTI\LineItem
     * @return bool  True if successful
     */
    public function createLineItem(ToolProvider\LineItem $lineItem): bool
    {
        $lineItem->endpoint = null;
        $this->mediaType = self::MEDIA_TYPE_LINE_ITEM;
        $http = $this->send('POST', null, self::toJson($lineItem));
        $ok = $http->ok && !empty($http->responseJson);
        if ($ok) {
            $newLineItem = self::toLineItem($this->getPlatform(), $http->responseJson);
            foreach (get_object_vars($newLineItem) as $key => $value) {
                $lineItem->$key = $value;
            }
        }

        return $ok;
    }

    /**
     * Save a line item.
     * @param \ILIAS\LTI\ToolProvider\LineItem $lineItem Line item object //UK: changed from LTI\LineItem
     * @return bool  True if successful
     */
    public function saveLineItem(ToolProvider\LineItem $lineItem): bool
    {
        $this->mediaType = self::MEDIA_TYPE_LINE_ITEM;
        $http = $this->send('PUT', null, self::toJson($lineItem));
        $ok = $http->ok;
        if ($ok && !empty($http->responseJson)) {
            $savedLineItem = self::toLineItem($this->getPlatform(), $http->responseJson);
            foreach (get_object_vars($savedLineItem) as $key => $value) {
                $lineItem->$key = $value;
            }
        }

        return $ok;
    }

    /**
     * Delete a line item.
     * @param ToolProvider\LineItem $lineItem Line item object //UK: changed from LTI\LineItem
     * @return bool  True if successful
     */
    public function deleteLineItem(ToolProvider\LineItem $lineItem): bool
    {
        $this->mediaType = self::MEDIA_TYPE_LINE_ITEM;
        $http = $this->send('DELETE');

        return $http->ok;
    }

    /**
     * Retrieve a line item.
     * @param Platform $platform Platform object for this service request
     * @param string   $endpoint Line item endpoint
     * @return \ILIAS\LTI\ToolProvider\LineItem|bool  LineItem object, or false on error //UK: changed from LTI\\LineItem|bool
     */
    public static function getLineItem(Platform $platform, string $endpoint)
    {
        $service = new self($platform, $endpoint);
        $service->scope = self::$SCOPE_READONLY;
        $service->mediaType = self::MEDIA_TYPE_LINE_ITEM;
        $http = $service->send('GET');
        $service->scope = self::$SCOPE;
        if ($http->ok && !empty($http->responseJson)) {
            $lineItem = self::toLineItem($platform, $http->responseJson);
        } else {
            $lineItem = false;
        }

        return $lineItem;
    }

    ###
    ###  PRIVATE METHODS
    ###

    /**
     * Create a line item from a JSON object.
     * @param Platform $platform Platform object for this service request
     * @param object   $json     JSON object to convert
     * @return null|\ILIAS\LTI\ToolProvider\LineItem  LineItem object, or null on error //UK: changed from LTI\\LineItem|null
     */
    private static function toLineItem(Platform $platform, object $json): ?ToolProvider\LineItem
    {
        if (!empty($json->id) && !empty($json->label) && !empty($json->scoreMaximum)) {
//            $lineItem = new LTI\LineItem($platform, $json->label, $json->scoreMaximum);
            $lineItem = new \ILIAS\LTI\ToolProvider\LineItem($platform, $json->label, $json->scoreMaximum);
            if (!empty($json->id)) {
                $lineItem->endpoint = $json->id;
            }
            if (!empty($json->resourceLinkId)) {
                $lineItem->ltiResourceLinkId = $json->resourceLinkId;
            }
            if (!empty($json->resourceId)) {
                $lineItem->resourceId = $json->resourceId;
            }
            if (!empty($json->tag)) {
                $lineItem->tag = $json->tag;
            }
            if (!empty($json->startDateTime)) {
                $lineItem->submitFrom = strtotime($json->startDateTime);
            }
            if (!empty($json->endDateTime)) {
                $lineItem->submitUntil = strtotime($json->endDateTime);
            }
        } else {
            $lineItem = null;
        }

        return $lineItem;
    }

    /**
     * Create a JSON string from a line item.
     * @param ToolProvider\LineItem $lineItem Line item object //UK: changed from LTI\\LineItem|null
     * @return string    JSON representation of line item
     */
    private static function toJson(ToolProvider\LineItem $lineItem): string
    {
        $json = new \stdClass();
        if (!empty($lineItem->endpoint)) {
            $json->id = $lineItem->endpoint;
        }
        if (!empty($lineItem->label)) {
            $json->label = $lineItem->label;
        }
        if (!empty($lineItem->pointsPossible)) {
            $json->scoreMaximum = $lineItem->pointsPossible;
        }
        if (!empty($lineItem->ltiResourceLinkId)) {
            $json->resourceLinkId = $lineItem->ltiResourceLinkId;
        }
        if (!empty($lineItem->resourceId)) {
            $json->resourceId = $lineItem->resourceId;
        }
        if (!empty($lineItem->tag)) {
            $json->tag = $lineItem->tag;
        }
        if (!empty($lineItem->submitFrom)) {
            $json->startDateTime = date('Y-m-d\TH:i:sP', $lineItem->submitFrom);
        }
        if (!empty($lineItem->submitUntil)) {
            $json->endDateTime = date('Y-m-d\TH:i:sP', $lineItem->submitUntil);
        }

        return json_encode($json);
    }
}
