<?php

namespace ILIAS\HTTP\Agent;

use ilException;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * This library is borrowed from the phpGroupWare API
 * http://www.phpgroupware.org/api
 * Modifications made by Sascha Hofmann <sascha.hofmann@uni-koeln.de>
 */
class AgentDetermination
{
    protected string $agent_name;
    protected string $agent_version;
    protected string $agent_platform;
    protected string $accept;
    protected string $user_agent;
    protected bool $is_mobile = false;
    protected bool $is_generic = false;
    
    protected array $devices = [
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
    ];
    
    public function __construct()
    {
        $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'] ?? 'undefined';
        /*
            Determine browser and version
        */
        if (preg_match('/MSIE (\d.\d{1,2})/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = 'IE';
        } elseif (preg_match('/Opera (\d.\d{1,2})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Opera\/(\d.\d{1,2})/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = 'OPERA';
        } elseif (preg_match('/Safari ([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Safari\/([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = 'Safari';
        } elseif (preg_match('/Firefox ([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Firefox\/([0-9\/.]*)/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = 'Firefox';
        } elseif (preg_match('/iCab (\d.[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/iCab\/(\d.[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = 'iCab';
        } elseif (preg_match('/Mozilla (\d.[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Mozilla\/(\d.[0-9a-zA-Z]{1,4})/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = preg_match('/Gecko/', $HTTP_USER_AGENT, $log_version) ? 'Mozilla' : 'Netscape';
        } elseif (preg_match('/Konqueror\/(\d.\d.\d{1,2})/', $HTTP_USER_AGENT, $log_version) ||
            preg_match('/Konqueror\/(\d.\d{1,2})/', $HTTP_USER_AGENT, $log_version)) {
            $this->agent_version = $log_version[1];
            $this->agent_name = 'Konqueror';
        } else {
            $this->agent_version = 0;
            $this->agent_name = 'OTHER';
        }
        
        /*
            Determine platform
        */
        if (strstr($HTTP_USER_AGENT, 'Win')) {
            $this->agent_platform = 'Win';
        } elseif (strstr($HTTP_USER_AGENT, 'Mac')) {
            $this->agent_platform = 'Mac';
        } elseif (strstr($HTTP_USER_AGENT, 'Linux')) {
            $this->agent_platform = 'Linux';
        } elseif (strstr($HTTP_USER_AGENT, 'Unix')) {
            $this->agent_platform = 'Unix';
        } elseif (strstr($HTTP_USER_AGENT, 'Beos')) {
            $this->agent_platform = 'Beos';
        } else {
            $this->agent_platform = 'Other';
        }
        
        // Mobile detection
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'undefined';
        $this->accept = $_SERVER['HTTP_ACCEPT'] ?? 'undefined';
        
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            $this->is_mobile = true;
        } elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos(
            $this->accept,
            'application/vnd.wap.xhtml+xml'
        ) > 0) {
            $this->is_mobile = true;
        } else {
            foreach (array_keys($this->devices) as $device) {
                if ($this->isDevice($device)) {
                    $this->is_mobile = true;
                }
            }
        }
    }
    
    public function isMobile() : bool
    {
        if ($this->is_mobile) {
            return true;
        }
        
        if (preg_match(
            '/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
            $this->user_agent
        ) || preg_match(
            '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',
            substr($this->user_agent, 0, 4)
        )) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @param string $name
     * @param array  $arguments
     * @throws ilException
     */
    public function __call($name, $arguments)
    {
        throw new \BadFunctionCallException('no generic method-call possible, please contact the maintainer');
        /** @noRector  */
        /*$device = substr($name, 2);
        if ($name === 'is' . ucfirst($device) && array_key_exists(strtolower($device), $this->devices)) {
            return $this->isDevice($device);
        } else {
            throw new ilException('Method ' . $name . ' not defined');
        }*/
    }
    
    public function isIpad() : bool
    {
        return $this->isDevice('ipad');
    }
    
    protected function isDevice(string $device) : bool
    {
        $var = 'is' . ucfirst($device);
        $return = $this->{$var} ?? (bool) preg_match(
            '/' . $this->devices[strtolower($device)] . '/i',
            $this->user_agent
        );
        if ($device !== 'generic' && $return === true) {
            $this->is_generic = false;
        }
        
        return $return;
    }
}
