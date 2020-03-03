<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeBackpack
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeBackpack
{
    protected $email; // [string]
    protected $uid; // [int]
    
    const URL_DISPLAYER = "https://backpack.openbadges.org/displayer/";
    
    public function __construct($a_email)
    {
        $this->email = $a_email;
    }
    
    protected function authenticate()
    {
        $json = $this->sendRequest(
            self::URL_DISPLAYER . "convert/email",
            array("email"=>$this->email),
            true
        );
        
        if (!isset($json->status) ||
            $json->status != "okay") {
            return false;
        }
        
        $this->uid = $json->userId;
        return true;
    }
    
    public function getGroups()
    {
        if ($this->authenticate()) {
            $json = $this->sendRequest(
                self::URL_DISPLAYER . $this->uid . "/groups.json"
            );
            
            $result = array();
            
            foreach ($json->groups as $group) {
                $result[$group->groupId] = array(
                    "title" => $group->name,
                    "size" => $group->badges
                );
            }
            
            return $result;
        }
    }
    
    public function getBadges($a_group_id)
    {
        if ($this->authenticate()) {
            $json = $this->sendRequest(
                self::URL_DISPLAYER . $this->uid . "/group/" . $a_group_id . ".json"
            );
            
            if ($json->status &&
                $json->status == "missing") {
                return false;
            }
            
            $result = array();
            
            foreach ($json->badges as $raw) {
                $badge = $raw->assertion->badge;
                                
                // :TODO: not sure if this works reliably
                $issued_on = is_numeric($raw->assertion->issued_on)
                    ? $raw->assertion->issued_on
                    : strtotime($raw->assertion->issued_on);
                
                $result[] = array(
                    "title" => $badge->name,
                    "description" => $badge->description,
                    "image_url" => $badge->image,
                    "criteria_url" => $badge->criteria,
                    "issuer_name" => $badge->issuer->name,
                    "issuer_url" => $badge->issuer->origin,
                    "issued_on" => new ilDate($issued_on, IL_CAL_UNIX)
                );
            }
            
            return $result;
        }
    }
    
    protected function sendRequest($a_url, array $a_param = array(), $a_is_post = false)
    {
        try {
            include_once "Services/WebServices/Curl/classes/class.ilCurlConnection.php";
            $curl = new ilCurlConnection();
            $curl->init();
                
            $curl->setOpt(CURLOPT_FRESH_CONNECT, true);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
            $curl->setOpt(CURLOPT_FORBID_REUSE, true);
            $curl->setOpt(CURLOPT_HEADER, 0);
            $curl->setOpt(CURLOPT_CONNECTTIMEOUT, 3);
            $curl->setOpt(CURLOPT_POSTREDIR, 3);
            
            // :TODO: SSL problems on test server
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);

            $curl->setOpt(CURLOPT_HTTPHEADER, array(
                    "Accept: application/json",
                    "Expect:"
            ));

            if ((bool) $a_is_post) {
                $curl->setOpt(CURLOPT_POST, 1);
                if (sizeof($a_param)) {
                    $curl->setOpt(CURLOPT_POSTFIELDS, http_build_query($a_param));
                }
            } else {
                $curl->setOpt(CURLOPT_HTTPGET, 1);
                if (sizeof($a_param)) {
                    $a_url = $a_url .
                        (strpos($a_url, "?") === false ? "?" : "") .
                        http_build_query($a_param);
                }
            }
            $curl->setOpt(CURLOPT_URL, $a_url);

            $answer = $curl->exec();
        } catch (Exception $ex) {
            ilUtil::sendFailure($ex->getMessage());
            return;
        }
        
        return json_decode($answer);
    }
}
