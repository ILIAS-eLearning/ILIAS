<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Statically used helper class for generating links to the mail form user interface
 *
 * @version: $Id$
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailFormCall
{
	/**
	 * HTTP-GET parameter for the referer url
	 */
	const REFERER_KEY = 'r';

	/**
	 * Session parameter for the hash
	 */
	const SIGNATURE_KEY = 'sig';

	/**
	 * @static
	 * @param mixed $gui
	 * @param string $cmd
	 * @param array $gui_params
	 * @param array $mail_params
	 * @return string
	 */
	public static function getLinkTarget($gui, $cmd, Array $gui_params = array(), Array $mail_params = array())
	{
		return self::getTargetUrl('&amp;', $gui, $cmd, $gui_params, $mail_params);
	}

	/**
	 * @static
	 * @param mixed $gui
	 * @param string $cmd
	 * @param array $gui_params
	 * @param array $mail_params
	 * @return string
	 */
	public static function getRedirectTarget($gui, $cmd, Array $gui_params = array(), Array $mail_params = array())
	{
		return self::getTargetUrl('&', $gui, $cmd, $gui_params, $mail_params);
	}

	/**
	 * @static
	 * @param string $argument_separator
	 * @param mixed $gui
	 * @param string $cmd
	 * @param array $gui_params
	 * @param array $mail_params
	 * @return string
	 */
	protected static function getTargetUrl($argument_separator, $gui, $cmd, Array $gui_params = array(), Array $mail_params = array())
	{
		$mparams = '';
		$referer = '';

		foreach($mail_params as $key => $value)
		{
			$mparams .= $argument_separator . $key . '=' . $value;
		}

		if(is_object($gui))
		{
			/**
			 * @var $ilCtrl ilCtrl
			 */
			global $ilCtrl;
			$ilCtrlTmp = clone $ilCtrl;
			foreach($gui_params as $key => $value)
			{
				$ilCtrlTmp->setParameter($gui, $key, $value);
			}
			$referer = $ilCtrlTmp->getLinkTarget($gui, $cmd, '', false, false);
		}
		else if(is_string($gui))
		{
			$referer = $gui;
		}

		$referer = $argument_separator . self::REFERER_KEY . '=' . rawurlencode(base64_encode($referer));

		return 'ilias.php?baseClass=ilMailGUI' . $referer . $mparams;
	}

	/**
	 * @static
	 * @param array $request_params
	 */
	public static function storeReferer($request_params)
	{
		if(isset($request_params[self::REFERER_KEY]))
		{
			$_SESSION[self::REFERER_KEY]   = base64_decode(rawurldecode($request_params[self::REFERER_KEY]));
			$_SESSION[self::SIGNATURE_KEY] = base64_decode(rawurldecode($request_params[self::SIGNATURE_KEY]));
		}
		else
		{
			unset($_SESSION[self::REFERER_KEY]);
			unset($_SESSION[self::SIGNATURE_KEY]);
		}
	}

	/**
	 * Get preset signature
	 *
	 * @return string signature
	 */
	public static function getSignature()
	{
		$sig = $_SESSION[self::SIGNATURE_KEY];

		unset($_SESSION[self::SIGNATURE_KEY]);

		return $sig;
	}

	/**
	 * @static
	 * @return string
	 */
	public static function getRefererRedirectUrl()
	{
		$url = $_SESSION[self::REFERER_KEY];

		if(strlen($url))
		{
			$parts = parse_url($url);
			if(isset($parts['query']) && strlen($parts['query']))
			{
				$url .= '&returned_from_mail=1';
			}
			else
			{
				$url .= '?returned_from_mail=1';
			}
		}

		unset($_SESSION[self::REFERER_KEY]);

		return $url;
	}

	/**
	 * @static
	 * @return bool
	 */
	public static function isRefererStored()
	{
		return isset($_SESSION[self::REFERER_KEY]) && strlen($_SESSION[self::REFERER_KEY]) ? true : false;
	}
}