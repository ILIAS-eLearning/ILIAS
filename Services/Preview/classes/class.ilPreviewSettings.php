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

/**
 * This class encapsulates accesses to settings which are relevant for the
 * preview functionality of ILIAS.
 *
 * @author Stefan Born <stefan.born@phzh.ch>
 * @version $Id$
 *
 * @package ServicesPreview
 */
class ilPreviewSettings
{
    public const MAX_PREVIEWS_DEFAULT = 5;
    public const MAX_PREVIEWS_MIN = 1;
    public const MAX_PREVIEWS_MAX = 20;

    private const IMAGE_SIZE_DEFAULT = 280;
    private const IMAGE_SIZE_MIN = 50;
    private const IMAGE_SIZE_MAX = 600;

    private const IMAGE_QUALITY_DEFAULT = 85;
    private const IMAGE_QUALITY_MIN = 20;
    private const IMAGE_QUALITY_MAX = 100;

    /**
     * The instance of the ilPreviewSettings.
     */
    private static ?\ilPreviewSettings $instance = null;

    /**
     * Settings object
     */
    private ?\ilSetting $settings = null;

    /**
     * Indicates whether the preview functionality is enabled.
     */
    private bool $preview_enabled = true;

    /**
     * Defines the maximum number of previews pictures per object.
     */
    private int $max_previews = self::MAX_PREVIEWS_DEFAULT;

    /**
     * Defines the maximum width and height of the preview images.
     */
    private int $image_size = self::IMAGE_SIZE_DEFAULT;

    /**
     * Defines the quality (compression) of the preview images (1-100).
     */
    private int $image_quality = self::IMAGE_QUALITY_DEFAULT;

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->settings = new ilSetting("preview");
        $this->preview_enabled = (bool) $this->settings->get('preview_enabled', '0') === true;
        $this->max_previews = $this->settings->get('max_previews_per_object', self::MAX_PREVIEWS_DEFAULT);
    }

    /**
     * Sets whether the preview functionality is enabled.
     *
     * @param bool $a_value The new value
     */
    public static function setPreviewEnabled(bool $a_value) : void
    {
        $instance = self::getInstance();
        $instance->preview_enabled = $a_value === true;
        $instance->settings->set('preview_enabled', $instance->preview_enabled);
    }

    /**
     * Gets whether the preview functionality is enabled.
     *
     * @return bool The current value
     */
    public static function isPreviewEnabled() : bool
    {
        return self::getInstance()->preview_enabled;
    }

    /**
     * Sets the maximum number of preview pictures per object.
     *
     * @param int $a_value The new value
     */
    public static function setMaximumPreviews(int $a_value) : void
    {
        $instance = self::getInstance();
        $instance->max_previews = self::adjustNumeric($a_value, self::MAX_PREVIEWS_MIN, self::MAX_PREVIEWS_MAX, self::MAX_PREVIEWS_DEFAULT);
        $instance->settings->set('max_previews_per_object', $instance->max_previews);
    }

    /**
     * Gets the maximum number of preview pictures per object.
     *
     * @return int The current value
     */
    public static function getMaximumPreviews() : int
    {
        return self::getInstance()->max_previews;
    }

    /**
     * Sets the size of the preview images in pixels.
     *
     * @param int $a_value The new value
     */
    public static function setImageSize(int $a_value) : void
    {
        $instance = self::getInstance();
        $instance->image_size = self::adjustNumeric($a_value, self::IMAGE_SIZE_MIN, self::IMAGE_SIZE_MAX, self::IMAGE_SIZE_DEFAULT);
        $instance->settings->set('preview_image_size', $instance->image_size);
    }

    /**
     * Gets the size of the preview images in pixels.
     *
     * @return int The current value
     */
    public static function getImageSize() : int
    {
        return self::getInstance()->image_size;
    }

    /**
     * Sets the quality (compression) of the preview images (1-100).
     *
     * @param int $a_value The new value
     */
    public static function setImageQuality(int $a_value) : void
    {
        $instance = self::getInstance();
        $instance->image_quality = self::adjustNumeric($a_value, self::IMAGE_QUALITY_MIN, self::IMAGE_QUALITY_MAX, self::IMAGE_QUALITY_DEFAULT);
        $instance->settings->set('preview_image_quality', $instance->image_quality);
    }

    /**
     * Gets the quality (compression) of the preview images (1-100).
     *
     * @return int The current value
     */
    public static function getImageQuality() : int
    {
        return self::getInstance()->image_quality;
    }

    /**
     * Gets the instance of the ilPreviewSettings.
     */
    private static function getInstance() : \ilPreviewSettings
    {
        if (self::$instance === null) {
            self::$instance = new ilPreviewSettings();
        }

        return self::$instance;
    }

    private static function adjustNumeric($value, int $min, int $max, int $default) : int
    {
        // is number?
        if (is_numeric($value)) {
            // don't allow to large numbers
            $value = (int) $value;
            if ($value < $min) {
                $value = $min;
            } elseif ($value > $max) {
                $value = $max;
            }
        } else {
            $value = $default;
        }

        return $value;
    }
}
