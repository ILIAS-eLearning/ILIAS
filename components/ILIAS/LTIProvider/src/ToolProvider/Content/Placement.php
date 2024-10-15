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
 * Class to represent a content-item placement object
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Placement
{
    /**
     * Embed placement type.
     */
    public const TYPE_EMBED = 'embed';

    /**
     * iFrame placement type.
     */
    public const TYPE_IFRAME = 'iframe';

    /**
     * Frame placement type.
     */
    public const TYPE_FRAME = 'frame';

    /**
     * Window placement type.
     */
    public const TYPE_WINDOW = 'window';

    /**
     * Popup placement type.
     */
    public const TYPE_POPUP = 'popup';

    /**
     * Overlay placement type.
     */
    public const TYPE_OVERLAY = 'overlay';

    /**
     * Location to open content in.
     *
     * @var string|null $documentTarget
     */
    public ?string $documentTarget = null;

    /**
     * Name of window target.
     *
     * @var string|null $windowTarget
     */
    private ?string $windowTarget = null;

    /**
     * Comma-separated list of window features.
     *
     * @var string|null $windowFeatures
     */
    private ?string $windowFeatures = null;

    /**
     * URL of iframe src.
     *
     * @var string|null $url
     */
    private ?string $url = null;

    /**
     * Width of item location.
     *
     * @var int|null $displayWidth
     */
    private ?int $displayWidth = null;

    /**
     * Height of item location.
     *
     * @var int|null $displayHeight
     */
    private ?int $displayHeight = null;

    /**
     * HTML to be embedded.
     *
     * @var string|null $html
     */
    private ?string $html = null;

    /**
     * Class constructor.
     * @param string      $documentTarget Location to open content in
     * @param int|null    $displayWidth   Width of item location (optional)
     * @param int|null    $displayHeight  Height of item location (optional)
     * @param string|null $windowTarget   Name of window target (optional)
     * @param string|null $windowFeatures List of window features (optional)
     * @param string|null $url            URL for iframe src (optional)
     * @param string|null $html           HTML to be embedded (optional)
     */
    public function __construct(
        string $documentTarget,
        ?int $displayWidth = null,
        ?int $displayHeight = null,
        ?string $windowTarget = null,
        ?string $windowFeatures = null,
        string $url = null,
        ?string $html = null
    ) {
        $this->documentTarget = $documentTarget;
        $this->displayWidth = $displayWidth;
        $this->displayHeight = $displayHeight;
        $this->windowTarget = $windowTarget;
        $this->windowFeatures = $windowFeatures;
        $this->url = $url;
        $this->html = $html;
    }

    /**
     * Generate the JSON-LD object representation of the placement.
     *
     * @return object
     */
    public function toJsonldObject(): object
    {
        if (!empty($this->documentTarget)) {
            $placement = new \stdClass();
            $placement->presentationDocumentTarget = $this->documentTarget;
            if (!is_null($this->displayHeight)) {
                $placement->displayHeight = $this->displayHeight;
            }
            if (!is_null($this->displayWidth)) {
                $placement->displayWidth = $this->displayWidth;
            }
            if (!empty($this->windowTarget)) {
                $placement->windowTarget = $this->windowTarget;
            }
        } else {
            $placement = null;
        }

        return $placement;
    }

    /**
     * Generate the JSON object representation of the placement.
     *
     * @return object
     */
    public function toJsonObject(): object
    {
        if (!empty($this->documentTarget)) {
            $placement = new \stdClass();
            switch ($this->documentTarget) {
                case self::TYPE_IFRAME:
                    if (!empty($this->url)) {
                        $placement->src = $this->url;
                    }
                    if (!is_null($this->displayWidth)) {
                        $placement->width = $this->displayWidth;
                    }
                    if (!is_null($this->displayHeight)) {
                        $placement->height = $this->displayHeight;
                    }
                    break;
                case self::TYPE_WINDOW:
                    if (!is_null($this->displayWidth)) {
                        $placement->width = $this->displayWidth;
                    }
                    if (!is_null($this->displayHeight)) {
                        $placement->height = $this->displayHeight;
                    }
                    if (!is_null($this->windowTarget)) {
                        $placement->targetName = $this->windowTarget;
                    }
                    if (!is_null($this->windowFeatures)) {
                        $placement->windowFeatures = $this->windowFeatures;
                    }
                    break;
                case self::TYPE_EMBED:
                    if (!empty($this->html)) {
                        $placement->html = $this->html;
                    }
                    break;
            }
        } else {
            $placement = null;
        }

        return $placement;
    }

    /**
     * Generate the Placement object from an item.
     * @param object      $item           JSON object of item
     * @param string|null $documentTarget Destination of placement to be generated (optional)
     * @return Placement
     */
    public static function fromJsonObject(object $item, string $documentTarget = null): ?Placement
    {
        $obj = null;
        $displayWidth = null;
        $displayHeight = null;
        $windowTarget = null;
        $windowFeatures = null;
        $url = null;
        $html = null;
        if (isset($item->{'@type'})) {  // Version 1
            if (empty($documentTarget) && isset($item->placementAdvice)) {
                if (isset($item->placementAdvice->presentationDocumentTarget)) {
                    $documentTarget = $item->placementAdvice->presentationDocumentTarget;
                }
            }
            if (!empty($documentTarget) && isset($item->placementAdvice)) {
                if (isset($item->placementAdvice->displayWidth)) {
                    $displayWidth = $item->placementAdvice->displayWidth;
                }
                if (isset($item->placementAdvice->displayHeight)) {
                    $displayHeight = $item->placementAdvice->displayHeight;
                }
                if (isset($item->placementAdvice->windowTarget)) {
                    $windowTarget = $item->placementAdvice->windowTarget;
                }
            }
            if (isset($item->url)) {
                $url = $item->url;
            }
        } else {  // Version 2
            if (empty($documentTarget)) {
                if (isset($item->embed)) {
                    $documentTarget = 'embed';
                } elseif (isset($item->iframe)) {
                    $documentTarget = 'iframe';
                } elseif (isset($item->window)) {
                    $documentTarget = 'window';
                }
            } elseif (!isset($item->{$documentTarget})) {
                $documentTarget = null;
            }
            if (!empty($documentTarget)) {
                if (isset($item->{$documentTarget}->width)) {
                    $displayWidth = $item->{$documentTarget}->width;
                }
                if (isset($item->{$documentTarget}->height)) {
                    $displayHeight = $item->{$documentTarget}->height;
                }
                if (isset($item->{$documentTarget}->targetName)) {
                    $windowTarget = $item->{$documentTarget}->targetName;
                }
                if (isset($item->{$documentTarget}->windowFeatures)) {
                    $windowFeatures = $item->{$documentTarget}->windowFeatures;
                }
                if (isset($item->{$documentTarget}->src)) {
                    $url = $item->{$documentTarget}->src;
                }
                if (isset($item->{$documentTarget}->html)) {
                    $html = $item->{$documentTarget}->html;
                }
            }
        }
        if (!empty($documentTarget)) {
            $obj = new Placement($documentTarget, $displayWidth, $displayHeight, $windowTarget, $windowFeatures, $url, $html);
        }

        return $obj;
    }
}
