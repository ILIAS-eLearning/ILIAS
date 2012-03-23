<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Captcha/classes/class.ilSecurImageUtil.php");

/**
 * SecurImage Wrapper (very simply wrapper, does not abstract other captchas)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ingroup	ServicesCaptcha
 * @version $Id$
 */
class ilSecurImage
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		ilSecurImageUtil::includeSecurImage();
		$this->securimage = new Securimage();
		if (!function_exists("imagettftext"))
		{
			$this->securimage->use_gd_font = true;
			$this->securimage->num_lines = 5;
		}
	}
	
	/**
	 * Check the input
	 */
	function check($a_input)
	{
		return $this->securimage->check($a_input);
	}
	
	/**
	 * Show image
	 *
	 * @param
	 * @return
	 */
	function showImage()
	{
		chdir(ilSecurImageUtil::getDirectory());
		$this->securimage->show();
	}
	
}
?>