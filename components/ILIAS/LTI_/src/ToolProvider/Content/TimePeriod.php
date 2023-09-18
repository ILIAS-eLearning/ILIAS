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
 * Class to represent a time period object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class TimePeriod
{
    /**
     * Start date/time.
     *
     * @var int|null $startDateTime
     */
    private ?int $startDateTime = null;

    /**
     * End date/time.
     *
     * @var int|null $endDateTime
     */
    private ?int $endDateTime = null;

    /**
     * Class constructor.
     * @param int $startDateTime Start date/time
     * @param int $endDateTime   End date/time
     */
    public function __construct(int $startDateTime, int $endDateTime)
    {
        $this->startDateTime = $startDateTime;
        $this->endDateTime = $endDateTime;
    }

    /**
     * Generate the JSON-LD object representation of the time period.
     *
     * @return object
     */
    public function toJsonldObject()
    {
        return $this->toJsonObject();
    }

    /**
     * Generate the JSON object representation of the image.
     *
     * @return \stdClass
     */
    public function toJsonObject(): \stdClass
    {
        $timePeriod = new \stdClass();
        if (!is_null($this->startDateTime)) {
            $timePeriod->startDateTime = gmdate('Y-m-d\TH:i:s\Z', $this->startDateTime);
        }
        if (!is_null($this->endDateTime)) {
            $timePeriod->endDateTime = gmdate('Y-m-d\TH:i:s\Z', $this->endDateTime);
        }

        return $timePeriod;
    }

    /**
     * Generate a LineItem object from its JSON or JSON-LD representation.
     * @param object $item A JSON or JSON-LD object representing a content-item
     * @return TimePeriod|null  The LineItem object
     */
    public static function fromJsonObject(object $item): ?TimePeriod
    {
        $obj = null;
        $startDateTime = null;
        $endDateTime = null;
        if (is_object($item)) {
            $url = null;
            foreach (get_object_vars($item) as $name => $value) {
                switch ($name) {
                    case 'startDateTime':
                        $startDateTime = strtotime($item->startDateTime);
                        break;
                    case 'endDateTime':
                        $endDateTime = strtotime($item->endDateTime);
                        break;
                }
            }
        } else {
            $url = $item;
        }
        if ($startDateTime || $endDateTime) {
            $obj = new TimePeriod($startDateTime, $endDateTime);
        }

        return $obj;
    }
}
