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
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
 * @license http://unlicense.org/
 *
 * Includes parts of php-mobile-project: Copyright (c) 2011, vic.stanciu
 * Includes parts of http://detectmobilebrowsers.com/
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
        if (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER = $log_version[1];
            $this->BROWSER_AGENT = 'IE';
        } elseif (preg_match('/Opera ([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Opera\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER   = $log_version[1];
            $this->BROWSER_AGENT = 'OPERA';
        } elseif (preg_match('/Safari ([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Safari\/([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER   = $log_version[1];
            $this->BROWSER_AGENT = 'Safari';
        } elseif (preg_match('/Firefox ([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Firefox\/([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER   = $log_version[1];
            $this->BROWSER_AGENT = 'Firefox';
        } elseif (preg_match('/iCab ([0-9].[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/iCab\/([0-9].[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER   = $log_version[1];
            $this->BROWSER_AGENT = 'iCab';
        } elseif (preg_match('/Mozilla ([0-9].[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Mozilla\/([0-9].[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER   = $log_version[1];
            if (preg_match('/Gecko/', $HTTP_USER_AGENT, $log_version)) {
                $this->BROWSER_AGENT = 'Mozilla';
            } else {
                $this->BROWSER_AGENT = 'Netscape';
            }
        } elseif (preg_match('/Konqueror\/([0-9].[0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Konqueror\/([0-9].[0-9]{1,2})/', $HTTP_USER_AGENT, $log_version)) {
            $this->BROWSER_VER=$log_version[1];
            $this->BROWSER_AGENT='Konqueror';
        } else {
            $this->BROWSER_VER=0;
            $this->BROWSER_AGENT='OTHER';
        }

        /*
            Determine platform
        */
        if (strstr($HTTP_USER_AGENT, 'Win')) {
            $this->BROWSER_PLATFORM='Win';
        } elseif (strstr($HTTP_USER_AGENT, 'Mac')) {
            $this->BROWSER_PLATFORM='Mac';
        } elseif (strstr($HTTP_USER_AGENT, 'Linux')) {
            $this->BROWSER_PLATFORM='Linux';
        } elseif (strstr($HTTP_USER_AGENT, 'Unix')) {
            $this->BROWSER_PLATFORM='Unix';
        } elseif (strstr($HTTP_USER_AGENT, 'Beos')) {
            $this->BROWSER_PLATFORM='Beos';
        } else {
            $this->BROWSER_PLATFORM='Other';
        }
        
        // Mobile detection
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->accept = $_SERVER['HTTP_ACCEPT'];
        
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            $this->isMobile = true;
        } elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0) {
            $this->isMobile = true;
        } else {
            foreach ($this->devices as $device => $regexp) {
                if ($this->isDevice($device)) {
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
        if ($this->BROWSER_AGENT == 'IE') {
            $this->br = '<br/>';
            $this->p = '<p/>';
        } else {
            $this->br = '<br>';
            $this->p = '<p>';
        }
    }

    /**
     * @return bool
     */
    public function isMobile()
    {
        if ($this->isMobile) {
            return true;
        }

        if (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $this->userAgent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($this->userAgent, 0, 4))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return bool
     * @throws ilException
     */
    public function __call($name, $arguments)
    {
        $device = substr($name, 2);
        if ($name == 'is' . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
            return $this->isDevice($device);
        } else {
            throw new ilException('Method ' . $name . ' not defined');
        }
    }
    
    protected function isDevice($device)
    {
        $var = 'is' . ucfirst($device);
        $return = $this->$var === null ? (bool) preg_match('/' . $this->devices[strtolower($device)] . '/i', $this->userAgent) : $this->$var;
        if ($device != 'generic' && $return == true) {
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
        if ($this->getPlatform() == 'Linux') {
            return true;
        } else {
            return false;
        }
    }

    public function isUnix()
    {
        if ($this->getPlatform() == 'Unix') {
            return true;
        } else {
            return false;
        }
    }

    public function isBeos()
    {
        if ($this->getPlatform() == 'Beos') {
            return true;
        } else {
            return false;
        }
    }

    public function isMac()
    {
        if ($this->getPlatform() == 'Mac') {
            return true;
        } else {
            return false;
        }
    }

    public function isWindows()
    {
        if ($this->getPlatform() == 'Win') {
            return true;
        } else {
            return false;
        }
    }

    public function isIE()
    {
        if ($this->getAgent() == 'IE') {
            return true;
        } else {
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
        if ($this->getAgent() == 'Netscape') {
            return true;
        } else {
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
        if ($this->getAgent() == 'Mozilla') {
            return true;
        } else {
            return false;
        }
    }

    public function isOpera()
    {
        if ($this->getAgent() == 'OPERA') {
            return true;
        } else {
            return false;
        }
    }

    public function isSafari()
    {
        if ($this->getAgent() == 'Safari') {
            return true;
        } else {
            return false;
        }
    }

    public function isFirefox()
    {
        if ($this->getAgent() == 'Firefox') {
            return true;
        } else {
            return false;
        }
    }
}
