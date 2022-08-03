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
 * Class to represent a line-item object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class LineItem
{

    /**
     * Label of line-item.
     *
     * @var string|null $label
     */
    private ?string $label = null;

    /**
     * Maximum score of line-item.
     *
     * @var int|null $scoreMaximum
     */
    private ?int $scoreMaximum = null;

    /**
     * Resource ID associated with line-item.
     *
     * @var string|null $resourceId
     */
    private ?string $resourceId = null;

    /**
     * Tag of line-item.
     *
     * @var string|null $tag
     */
    private ?string $tag = null;

    /**
     * Class constructor.
     * @param string      $label        Label
     * @param int         $scoreMaximum Maximum score
     * @param string|null $resourceId   Resource ID (optional)
     * @param string|null $tag          Tag (optional)
     */
    public function __construct(string $label, int $scoreMaximum, string $resourceId = null, string $tag = null)
    {
        $this->label = $label;
        $this->scoreMaximum = $scoreMaximum;
        $this->resourceId = $resourceId;
        $this->tag = $tag;
    }

    /**
     * Generate the JSON-LD object representation of the line-item.
     *
     * @return object
     */
    public function toJsonldObject() : object
    {
        $lineItem = new \stdClass();

        $lineItem->{'@type'} = 'LineItem';
        $lineItem->label = $this->label;
        $lineItem->reportingMethod = 'http://purl.imsglobal.org/ctx/lis/v2p1/Result#normalScore';
        if (!empty($this->resourceId)) {
            $lineItem->assignedActivity = new \stdClass();
            $lineItem->assignedActivity->activityId = $this->resourceId;
        }
        $lineItem->scoreConstraints = new \stdClass();
        $lineItem->scoreConstraints->{'@type'} = 'NumericLimits';
        $lineItem->scoreConstraints->normalMaximum = $this->scoreMaximum;

        return $lineItem;
    }

    /**
     * Generate the JSON object representation of the line-item.
     *
     * @return object
     */
    public function toJsonObject() : object
    {
        $lineItem = new \stdClass();

        $lineItem->label = $this->label;
        $lineItem->scoreMaximum = $this->scoreMaximum;
        if (!empty($this->resourceId)) {
            $lineItem->resourceId = $this->resourceId;
        }
        if (!empty($this->tag)) {
            $lineItem->tag = $this->tag;
        }

        return $lineItem;
    }

    /**
     * Generate a LineItem object from its JSON or JSON-LD representation.
     * @param object $item A JSON or JSON-LD object representing a content-item
     * @return LineItem|null  The LineItem object
     */
    public static function fromJsonObject(object $item) : ?LineItem
    {
        $obj = null;
        $label = null;
        $reportingMethod = null;
        $scoreMaximum = null;
        $activityId = null;
        $tag = null;
        $available = null;
        $submission = null;
        foreach (get_object_vars($item) as $name => $value) {
            switch ($name) {
                case 'label':
                    $label = $item->label;
                    break;
                case 'reportingMethod':
                    $reportingMethod = $item->reportingMethod;
                    break;
                case 'scoreConstraints':
                    $scoreConstraints = $item->scoreConstraints;
                    break;
                case 'scoreMaximum':
                    $scoreMaximum = $item->scoreMaximum;
                    break;
                case 'assignedActivity':
                    if (isset($item->assignedActivity->activityId)) {
                        $activityId = $item->assignedActivity->activityId;
                    }
                    break;
                case 'resourceId':
                    $activityId = $item->resourceId;
                    break;
                case 'tag':
                    $tag = $item->tag;
                    break;
            }
        }
        if (is_null($scoreMaximum) && $label && $reportingMethod && $scoreConstraints) {
            foreach (get_object_vars($scoreConstraints) as $name => $value) {
                $method = str_replace('Maximum', 'Score', $name);
                if (substr($reportingMethod, -strlen($method)) === $method) {
                    $scoreMaximum = $value;
                    break;
                }
            }
        }
        if (!is_null($scoreMaximum)) {
            $obj = new LineItem($label, $scoreMaximum, $activityId, $tag);
        }

        return $obj;
    }
}
