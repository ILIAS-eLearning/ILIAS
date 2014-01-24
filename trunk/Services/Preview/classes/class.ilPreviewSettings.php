<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	const MAX_PREVIEWS_DEFAULT = 5;
	const MAX_PREVIEWS_MIN = 1;
	const MAX_PREVIEWS_MAX = 20;
	
	const IMAGE_SIZE_DEFAULT = 280;
	const IMAGE_SIZE_MIN = 50;
	const IMAGE_SIZE_MAX = 600;
	
	const IMAGE_QUALITY_DEFAULT = 85;
	const IMAGE_QUALITY_MIN = 20;
	const IMAGE_QUALITY_MAX = 100;
	
	/**
	 * The instance of the ilPreviewSettings.
	 * @var ilPreviewSettings
	 */
	private static $instance = null;
	
	/**
	 * Settings object
	 * @var ilSetting
	 */
	private $settings = null;

	/**
	 * Indicates whether the preview functionality is enabled.
	 * @var bool
	 */
	private $preview_enabled = true;

	/**
	 * Defines the maximum number of previews pictures per object.
	 * @var int
	 */
	private $max_previews = self::MAX_PREVIEWS_DEFAULT;
	
	/**
	 * Defines the maximum width and height of the preview images.
	 * @var int
	 */
	private $image_size = self::IMAGE_SIZE_DEFAULT;

	/**
	 * Defines the quality (compression) of the preview images (1-100).
	 * @var int
	 */
	private $image_quality = self::IMAGE_QUALITY_DEFAULT;

	/**
	 * Private constructor
	 */
	private function __construct()
	{
		$this->settings = new ilSetting("preview");
		$this->preview_enabled = $this->settings->get('preview_enabled', false) == true;
		$this->max_previews = $this->settings->get('max_previews_per_object', self::MAX_PREVIEWS_DEFAULT);
	}

	/**
	 * Sets whether the preview functionality is enabled.
	 * 
	 * @param bool $a_value The new value
	 */
	public static function setPreviewEnabled($a_value)
	{
		$instance = self::getInstance();
		$instance->preview_enabled = $a_value == true;
		$instance->settings->set('preview_enabled', $instance->preview_enabled);
	}
	
	/**
	 * Gets whether the preview functionality is enabled.
	 * 
	 * @return bool The current value
	 */
	public static function isPreviewEnabled()
	{
		return self::getInstance()->preview_enabled;
	}
	
	/**
	 * Sets the maximum number of preview pictures per object.
	 * 
	 * @param int $a_value The new value
	 */
	public static function setMaximumPreviews($a_value)
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
	public static function getMaximumPreviews()
	{
		return self::getInstance()->max_previews;
	}
	
	/**
	 * Sets the size of the preview images in pixels.
	 * 
	 * @param int $a_value The new value
	 */
	public static function setImageSize($a_value)
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
	public static function getImageSize()
	{
		return self::getInstance()->image_size;
	}
	
	/**
	 * Sets the quality (compression) of the preview images (1-100).
	 * 
	 * @param int $a_value The new value
	 */
	public static function setImageQuality($a_value)
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
	public static function getImageQuality()
	{
		return self::getInstance()->image_quality;
	}

	/**
	 * Gets the instance of the ilPreviewSettings.
	 * @return ilPreviewSettings
	 */
	private static function getInstance()
	{
		if (self::$instance == null)
			self::$instance = new ilPreviewSettings();
		
		return self::$instance;
	}
	
	/**
	 * Adjusts the numeric value to fit between the specified minimum and maximum.
	 * If the value is not numeric the default value is returned.
	 * 
	 * @param object $value The value to adjust.
	 * @param int $min The allowed minimum (inclusive).
	 * @param int $max The allowed maximum (inclusive).
	 * @param int $default The default value if the specified value is not numeric.
	 * @return The adjusted value.
	 */
	private static function adjustNumeric($value, $min, $max, $default)
	{
		// is number?
		if (is_numeric($value))
		{
			// don't allow to large numbers
			$value = (int)$value;
			if ($value < $min)
				$value = $min;
			else if ($value > $max)
				$value = $max;
		}
		else
		{
			$value = $default;
		}
		
		return $value;
	}
}
?>
