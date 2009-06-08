<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
* Browser check
*
* @version $Id$
*/
class ilBrowser
{
	var $BROWSER_AGENT;
	var $BROWSER_VER;
	var $BROWSER_PLATFORM;
	var $br;
	var $p;
	var $data;

	function ilBrowser()
	{
		$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
		/*
			Determine browser and version
		*/
		if(ereg('MSIE ([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER = $log_version[1];
			$this->BROWSER_AGENT = 'IE';
		}
		elseif(ereg('Opera ([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version) ||
			ereg('Opera/([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'OPERA';
		}
		elseif(ereg('Safari ([0-9/.]*)',$HTTP_USER_AGENT,$log_version) ||
			ereg('Safari/([0-9/.]*)',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'Safari';
		}
		elseif(ereg('Firefox ([0-9/.]*)',$HTTP_USER_AGENT,$log_version) ||
			ereg('Firefox/([0-9/.]*)',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'Firefox';
		}
		elseif(eregi('iCab ([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version) ||
			eregi('iCab/([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			$this->BROWSER_AGENT = 'iCab';
		}
		elseif(eregi('Mozilla ([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version) ||
			eregi('Mozilla/([0-9].[0-9a-zA-Z]{1,4})',$HTTP_USER_AGENT,$log_version))
		{
			$this->BROWSER_VER   = $log_version[1];
			if (ereg('Gecko',$HTTP_USER_AGENT,$log_version))
			{
				$this->BROWSER_AGENT = 'Mozilla';
			}
			else
			{
				$this->BROWSER_AGENT = 'Netscape';
			}
		}
		elseif(ereg('Konqueror/([0-9].[0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version) ||
			ereg('Konqueror/([0-9].[0-9]{1,2})',$HTTP_USER_AGENT,$log_version))
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

	function returnArray()
	{
		$this->data = array(
			'agent'    => $this->getAgent(),
			'version'  => $this->getVersion(false),
			'platform' => $this->getPlatform()
		);

		return $this->data;
	}

	function getAgent()
	{
		return $this->BROWSER_AGENT;
	}

	function getVersion($a_as_array = true)
	{
		return explode(".", $this->BROWSER_VER);
	}

	function getPlatform()
	{
		return $this->BROWSER_PLATFORM;
	}

	function isLinux()
	{
		if($this->getPlatform()=='Linux')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isUnix()
	{
		if($this->getPlatform()=='Unix')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isBeos()
	{
		if($this->getPlatform()=='Beos')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isMac()
	{
		if($this->getPlatform()=='Mac')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isWindows()
	{
		if($this->getPlatform()=='Win')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isIE()
	{
		if($this->getAgent()=='IE')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* means: Netscape/Mozilla without Gecko engine
	*/
	function isNetscape()
	{
		if($this->getAgent()=='Netscape')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* means: Netscape/Mozilla with Gecko engine
	*/
	function isMozilla()
	{
		if($this->getAgent()=='Mozilla')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isOpera()
	{
		if($this->getAgent()=='OPERA')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isSafari()
	{
		if($this->getAgent()=='Safari')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function isFirefox()
	{
		if($this->getAgent()=='Firefox')
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
