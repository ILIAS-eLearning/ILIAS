<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Captcha/classes/class.ilSecurImageUtil.php';

/**
 * SecurImage Wrapper (very simply wrapper, does not abstract other captchas)
 * @author     Alex Killing <alex.killing@gmx.de>
 * @author     Michael Jansen <mjansen@databay.de>
 * @ingroup    ServicesCaptcha
 * @version    $Id$
 */
class ilSecurImage
{
	/**
	 * @var int
	 */
	const MAX_CAPTCHA_IMG_WIDTH = 430;

	/**
	 * @var int
	 */
	const MAX_CAPTCHA_IMG_HEIGHT = 160;

	/**
	 * @var int
	 */
	protected $image_width = 0;

	/**
	 * @var int
	 */
	protected $image_height = 0;

	/**
	 * @var Securimage
	 */
	protected $securimage;

	/**
	 * @var array
	 */
	protected static $supported_audio_languages = array(
		'fr', 'de'
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		ilSecurImageUtil::includeSecurImage();
		$this->securimage = new Securimage();
		if(!function_exists("imagettftext"))
		{
			$this->securimage->use_gd_font = true;
		}

		$this->securimage->num_lines = 3;
	}

	/**
	 * @return Securimage
	 */
	public function getSecureImageObject()
	{
		return $this->securimage;
	}

	/**
	 * @param $a_input
	 * @return bool
	 */
	public function check($a_input)
	{
		return $this->securimage->check($a_input);
	}

	/**
	 * 
	 */
	public function showImage()
	{
		chdir(ilSecurImageUtil::getDirectory());
		$this->securimage->show();
	}

	/**
	 *
	 */
	public function outputAudioFile()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		chdir(ilSecurImageUtil::getDirectory());
		if(
			$lng->lang_key != 'en' &&
			in_array($lng->lang_key, self::$supported_audio_languages)
		)
		{
			$this->securimage->audio_path = $this->securimage->securimage_path . '/audio/' . $lng->lang_key . '/';
		}
		$this->securimage->outputAudioFile();
	}

	/**
	 * @param int $image_height
	 * @throws InvalidArgumentException
	 */
	public function setImageHeight($image_height)
	{
		if(!is_numeric($image_height) || $image_height > self::MAX_CAPTCHA_IMG_HEIGHT)
		{
			throw new InvalidArgumentException('Please provide a valid image height (numeric value > 0 and <= ' . self::MAX_CAPTCHA_IMG_HEIGHT);
		}
		$this->image_height = $image_height;
	}

	/**
	 * @return int
	 */
	public function getImageHeight()
	{
		return $this->image_height;
	}

	/**
	 * @param int $image_width
	 * @throws InvalidArgumentException
	 */
	public function setImageWidth($image_width)
	{
		if(!is_numeric($image_width) || $image_width > self::MAX_CAPTCHA_IMG_WIDTH)
		{
			throw new InvalidArgumentException('Please provide a valid image width (numeric value > 0 and <= ' . self::MAX_CAPTCHA_IMG_WIDTH);
		}
		$this->image_width = $image_width;
	}

	/**
	 * @return int
	 */
	public function getImageWidth()
	{
		return $this->image_width;
	}
}