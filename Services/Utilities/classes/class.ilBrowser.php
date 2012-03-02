<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

  //**************************************************************************\
  //* Browser detect functions                                                 *
  // This file written by Miles Lott <milosch@phpgroupware.org>               *
  // Majority of code borrowed from Sourceforge 2.5                           *
  // Copyright 1999-2000 (c) The SourceForge Crew - http://sourceforge.net    *
  // Browser detection functions for phpGroupWare developers                  *
  // -------------------------------------------------------------------------*
  // This library is borrowed from the phpGroupWare API                       *
  // http://www.phpgroupware.org/api                                          *
  // Modifications made by Sascha Hofmann <sascha.hofmann@uni-koeln.de>       *
  //                                                                          *
  //**************************************************************************/

/**
 * 
 * Browser check
 *
 * @version $Id$
 * @author	Michael Jansen <mjansen@databay.de>
 * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 * Includes parts of php-mobile-project: Copyright (c) 2011, vic.stanciu
 * 
 */
class ilBrowser
{
	protected $BROWSER_AGENT;
	protected $BROWSER_VER;
	protected $BROWSER_PLATFORM;
	protected $br;
	protected $p;
	protected $data;
	protected $accept;
	protected $userAgent;
	protected $isMobile = false;
	protected $isAndroid = null;
	protected $isAndroidtablet = null;
	protected $isIphone = null;
  	protected $isIpad = null;
	protected $isBlackberry = null;
	protected $isOpera = null;
	protected $isPalm = null;
	protected $isWindows = null;
 	protected $isWindowsphone = null;
 	protected $isGeneric = null;
 	protected $devices = array(
		'android' => 'android.*mobile',
		'androidtablet' => 'android(?!.*mobile)',
		'blackberry' => 'blackberry',
		'blackberrytablet' => 'rim tablet os',
		'iphone' => '(iphone|ipod)',
		'ipad' => '(ipad)',
		'palm' => '(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)',
		'windows' => 'windows ce; (iemobile|ppc|smartphone)',
		'windowsphone' => 'windows phone os',
		'generic' => '(kindle|mobile|mmp|midp|o2|pda|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)'
	);	

	/**
	 * 
	 * Constructor
	 * 
	 * @access	public
	 * 
	 */
	public function __construct()
	{
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		/*
			Determine browser and version
		*/
		if(preg_match('/MSIE ([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER = $log_version[1];
			$this->BROWSER_AGENT = 'IE';
		}
		elseif(preg_match('/Opera ([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version) ||
			preg_match('/Opera\/([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'OPERA';
		}
		elseif(preg_match('/Safari ([0-9\/.]*)/',$HTTP_USER_AGENT,$log_version) ||
			preg_match('/Safari\/([0-9\/.]*)/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'Safari';
		}
		elseif(preg_match('/Firefox ([0-9\/.]*)/',$HTTP_USER_AGENT,$log_version) ||
			preg_match('/Firefox\/([0-9\/.]*)/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'Firefox';
		}
		elseif(preg_match('/iCab ([0-9].[0-9a-zA-Z]{1,4})/',$HTTP_USER_AGENT,$log_version) ||
			preg_match('/iCab\/([0-9].[0-9a-zA-Z]{1,4})/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'iCab';
		}
		elseif(preg_match('/Mozilla ([0-9].[0-9a-zA-Z]{1,4})/',$HTTP_USER_AGENT,$log_version) ||
			preg_match('/Mozilla\/([0-9].[0-9a-zA-Z]{1,4})/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			if (preg_match('/Gecko/',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_AGENT = 'Mozilla';
			}
			else
			{
				$this->BROWSER_AGENT = 'Netscape';
			}
		}
		elseif(preg_match('/Konqueror\/([0-9].[0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version) ||
			preg_match('/Konqueror\/([0-9].[0-9]{1,2})/',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER=$log_version[1];
			$this->BROWSER_AGENT='Konqueror';
		}
		else
		{
			$this->BROWSER_VER=0;
			$this->BROWSER_AGENT='OTHER';
		}

		/*
			Determine platform
		*/
		if(strstr($HTTP_USER_AGENT,'Win'))
		{
			$this->BROWSER_PLATFORM='Win';
		}
		elseif(strstr($HTTP_USER_AGENT,'Mac'))
		{
			$this->BROWSER_PLATFORM='Mac';
		}
		elseif(strstr($HTTP_USER_AGENT,'Linux'))
		{
			$this->BROWSER_PLATFORM='Linux';
		}
		elseif(strstr($HTTP_USER_AGENT,'Unix'))
		{
			$this->BROWSER_PLATFORM='Unix';
		}
		elseif(strstr($HTTP_USER_AGENT,'Beos'))
		{
			$this->BROWSER_PLATFORM='Beos';
		}
		else
		{
			$this->BROWSER_PLATFORM='Other';
		}
		
		// Mobile detection
		$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->accept = $_SERVER['HTTP_ACCEPT'];
		
		if(isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))
		{
			$this->isMobile = true;
		}
		else if(strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0)
		{
			$this->isMobile = true;
		}
		else
		{
			foreach($this->devices as $device => $regexp)
			{
				if($this->isDevice($device))
				{
					$this->isMobile = true;
				}
			}
		}

/*
		echo "<br>Agent: $HTTP_USER_AGENT";
		echo "<br><b>Browser</b>";
		echo "<br>IE: ".$this->isIE();
		echo "<br>Netscape: ".$this->isNetscape();
		echo "<br>Mozilla: ".$this->isMozilla();
		echo "<br>Firefox: ".$this->isFirefox();
		echo "<br>Safari: ".$this->isSafari();
		echo "<br>Opera: ".$this->isOpera();
		echo "<br><b>OS</b>";
		echo "<br>Mac: ".$this->isMac();
		echo "<br>Windows: ".$this->isWindows();
		echo "<br>Linux: ".$this->isLinux();
		echo "<br>Unix: ".$this->isUnix();
		echo "<br>Beos: ".$this->isBeos();
		echo "<br><b>Summary</b>";
		echo "<br>OS: ".$this->getPlatform();
		echo "<br>Version: ".$this->getVersion(false);
		echo "<br>Agent: ".$this->getAgent();
*/

		// The br and p functions are supposed to return the correct
		// value for tags that do not need to be closed.  This is
		// per the xhmtl spec, so we need to fix this to include
		// all compliant browsers we know of.
		if($this->BROWSER_AGENT == 'IE')
		{
			$this->br = '<br/>';
			$this->p = '<p/>';
		}
		else
		{
			$this->br = '<br>';
			$this->p = '<p>';
		}
	}
		
	public function isMobile()
	{
		return $this->isMobile;
	}
	
	public function __call($name, $arguments)
	{
		$device = substr($name, 2);
		if($name == 'is' . ucfirst($device) && array_key_exists(strtolower($device), $this->devices))
		{
			return $this->isDevice($device);
		}
		else
		{
			trigger_error('Method '.$name.' not defined', E_USER_WARNING);
		}
	}
	
	protected function isDevice($device)
	{
		$var = 'is' . ucfirst($device);
		$return = $this->$var === null ? (bool) preg_match('/' . $this->devices[strtolower($device)] . '/i', $this->userAgent) : $this->$var;
		if($device != 'generic' && $return == true)
		{
			$this->isGeneric = false;
		}

		return $return;
	}

	public function returnArray()
	{
		$this->data = array(
			'agent'    => $this->getAgent(),
			'version'  => $this->getVersion(false),
			'platform' => $this->getPlatform()
		);

		return $this->data;
	}

	public function getAgent()
	{
		return $this->BROWSER_AGENT;
	}

	public function getVersion($a_as_array = true)
	{
		return explode('.', $this->BROWSER_VER);
	}

	public function getPlatform()
	{
		return $this->BROWSER_PLATFORM;
	}

	public function isLinux()
	{
		if($this->getPlatform() == 'Linux')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isUnix()
	{
		if($this->getPlatform() == 'Unix')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isBeos()
	{
		if($this->getPlatform() == 'Beos')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isMac()
	{
		if($this->getPlatform() == 'Mac')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isWindows()
	{
		if($this->getPlatform() == 'Win')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isIE()
	{
		if($this->getAgent() == 'IE')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 
	 * means: Netscape/Mozilla without Gecko engine
	 * 
	 */
	public function isNetscape()
	{
		if($this->getAgent() == 'Netscape')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * 
	 * means: Netscape/Mozilla with Gecko engine
	 * 
	 */
	public function isMozilla()
	{
		if($this->getAgent() == 'Mozilla')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isOpera()
	{
		if($this->getAgent() == 'OPERA')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isSafari()
	{
		if($this->getAgent() == 'Safari')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isFirefox()
	{
		if($this->getAgent() == 'Firefox')
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>