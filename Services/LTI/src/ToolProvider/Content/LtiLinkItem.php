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

namespace ILIAS\LTI\ToolProvider\Content;

/**
 * Class to represent an LTI link content-item object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class LtiLinkItem extends Item
{
    /**
     * Custom parameters for content-item.
     *
     * @var array $custom
     */
    private array $custom = array();

    /**
     * Line-item object for content-item.
     *
     * @var LineItem|null $lineItem
     */
    private ?LineItem $lineItem = null;

    /**
     * Time period for availability.
     *
     * @var string|null $available
     */
    private ?string $available = null;

    /**
     * Time period for submission.
     *
     * @var string|null $submission
     */
    private ?string $submission = null;

    /**
     * Do not allow the item to be updated?
     *
     * @var bool $noUpdate
     */
    private ?bool $noUpdate = null;

    /**
     * Class constructor.
     *
     * @param Placement[]|Placement $placementAdvices  Array of Placement objects (or single placement object) for item (optional)
     * @param string $id   URL of content-item (optional)
     */
    public function __construct($placementAdvices = null, $id = null)
    {
        parent::__construct(Item::TYPE_LTI_LINK, $placementAdvices, $id);
        $this->setMediaType(Item::LTI_LINK_MEDIA_TYPE);
    }

    /**
     * Add a custom parameter for the content-item.
     * @param string      $name  Name of parameter
     * @param string|null $value Value of parameter
     */
    public function addCustom(string $name, ?string $value = null)
    {
        if (!empty($name)) {
            if (!empty($value)) {
                $this->custom[$name] = $value;
            } else {
                reset($this->custom[$name]);
            }
        }
    }

    /**
     * Set a line-item for the content-item.
     * @param LineItem $lineItem Line-item
     */
    public function setLineItem(LineItem $lineItem)
    {
        $this->lineItem = $lineItem;
    }

    /**
     * Set an availability time period for the content-item.
     * @param string|TimePeriod $available Time period  //UK: check changed from TimePeriod to string
     */
    public function setAvailable($available)
    {
        $this->available = $available;
    }

    /**
     * Set a submission time period for the content-item.
     * @param string|TimePeriod $submission Time period  //UK: check changed from TimePeriod to string
     */
    public function setSubmission($submission)
    {
        $this->submission = $submission;
    }

    /**
     * Set whether the content-item should not be allowed to be updated.
     * @param bool|null $noUpdate True if the item should not be updatable
     */
    public function setNoUpdate(?bool $noUpdate)
    {
        $this->noUpdate = $noUpdate;
    }

    /**
     * Wrap the content item to form an item complying with the application/vnd.ims.lti.v1.contentitems+json media type.
     *
     * @return object
     */
    public function toJsonldObject(): object
    {
        $item = parent::toJsonldObject();
        if (!empty($this->lineItem)) {
            $item->lineItem = $this->lineItem->toJsonldObject();
        }
        if (!is_null($this->noUpdate)) {
            $item->noUpdate = $this->noUpdate;
        }
        //ToDo: Check
//        if (!is_null($this->available)) {
//            $item->available = $this->available->toJsonldObject();
//        }
//        if (!is_null($this->submission)) {
//            $item->submission = $this->submission->toJsonldObject();
//        }
        if (!empty($this->custom)) {
            $item->custom = $this->custom;
        }

        return $item;
    }

    /**
     * Wrap the content items to form a complete value for the https://purl.imsglobal.org/spec/lti-dl/claim/content_items claim.
     *
     * @return object
     */
    public function toJsonObject(): object
    {
        $item = parent::toJsonObject();
        if (!empty($this->lineItem)) {
            $item->lineItem = $this->lineItem->toJsonObject();
        }
        if (!is_null($this->noUpdate)) {
            $item->noUpdate = $this->noUpdate;
        }
        //ToDo: Check
//        if (!is_null($this->available)) {
//            $item->available = $this->available->toJsonObject();
//        }
//        if (!is_null($this->submission)) {
//            $item->submission = $this->submission->toJsonObject();
//        }
        if (!empty($this->custom)) {
            $item->custom = $this->custom;
        }

        return $item;
    }

    /**
     * Extract content-item details from its JSON representation.
     * @param object $item A JSON object representing an LTI link content-item
     */
    protected function fromJsonObject(object $item)
    {
        parent::fromJsonObject($item);
        foreach (get_object_vars($item) as $name => $value) {
            switch ($name) {
                case 'custom':
                    foreach ($item->custom as $paramName => $paramValue) {
                        $this->addCustom($paramName, $paramValue);
                    }
                    break;
                case 'lineItem':
                    $this->setLineItem(LineItem::fromJsonObject($item->lineItem));
                    break;
                case 'available':
                    $this->setAvailable(TimePeriod::fromJsonObject($item->available));
                    break;
                case 'submission':
                    $this->setSubmission(TimePeriod::fromJsonObject($item->submission));
                    break;
                case 'noUpdate':
                    $this->noUpdate = $item->noUpdate;
                    break;
            }
        }
    }
}
