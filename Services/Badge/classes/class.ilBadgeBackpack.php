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
			self::URL_DISPLAYER."convert/email", 
			array("email"=>$this->email),
			true
		);
		
		if(!isset($json->status) ||
			$json->status != "okay")  
		{
			return false;
		}
		
		$this->uid = $json->userId;
		return true;
	}
	
	public function getGroups()
	{
		if($this->authenticate())
		{
			$json = $this->sendRequest(
				self::URL_DISPLAYER.$this->uid."/groups.json" 
			);
			
			$result = array();
			
			foreach($json->groups as $group)
			{
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
		if($this->authenticate())
		{
			$json = $this->sendRequest(
				self::URL_DISPLAYER.$this->uid."/group/".$a_group_id.".json"
			);
			
			if($json->status &&
				$json->status == "missing")  
			{
				return false;
			}
			
			$result = array();
			
			foreach($json->badges as $raw)
			{
				$badge = $raw->assertion->badge;
				
				$result[] = array(
					"title" => $badge->name,
					"description" => $badge->description,
					"image_url" => $badge->image,
					"criteria_url" => $badge->criteria,
					"issuer_name" => $badge->issuer->name,
					"issuer_url" => $badge->issuer->origin,
					"issued_on" => new ilDate($raw->assertion->issued_on, IL_CAL_DATE)
				);
			}
			
			return $result;			
		}
	}
	
	protected function sendRequest($a_url, array $a_param = array(), $a_is_post = false)
	{	
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			"Accept: application/json", 
			"Expect:"
		));
		
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($curl, CURLOPT_POSTREDIR, 3);
		
		if((bool)$a_is_post)
		{			
			curl_setopt($curl, CURLOPT_POST, 1);
			if(sizeof($a_param))
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($a_param));
			}
		}
		else 
		{
			curl_setopt($curl, CURLOPT_HTTPGET, 1);
			if(sizeof($a_param))
			{
				$a_url = $a_url.
					(strpos($a_url, "?") === false ? "?" : ""). 
					http_build_query($a_param);
			}
		}
				
		curl_setopt($curl, CURLOPT_URL, $a_url);
		
		$answer = curl_exec($curl);
		curl_close($curl);
	
        return json_decode($answer);
	}
}