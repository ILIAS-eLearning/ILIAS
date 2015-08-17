<?php

include_once (dirname(__FILE__).DIRECTORY_SEPARATOR."config.php");

class Parse {

	private $pushPayload;
	private $parseUrl;
	private $parseAppId;
	private $parseRestKey;
	private $messageType;
	private $notificationFlag = '1';

	public function Parse() {
		$this->parseUrl = PARSE_PUSH_URL;
		$this->parseAppId = PARSE_APP_ID;
		$this->parseRestKey = PARSE_REST_KEY;
	}

	/* Can only be used after calling init() */
	public function sendNotification($channel, $messageData, $isChatroom = '0', $isAnnouncement = '0') {

		if(function_exists('curl_version') && !empty($channel) && $messageData ) {
			if(empty($isAnnouncement)){
				$channel = "C_".$channel;

				/* Used in Android to filter local and server notifications */

				if (strpos($messageData['m'], "has shared a file") !== false) {
					$this->messageType = "2";
				}

				if($isChatroom == '1') {
					$this->messageType = "C".$this->messageType;
				} elseif ($isChatroom == '0') {
					$this->messageType = "O".$this->messageType;
				}

				$messageData['from_noti'] = '1';

				$breaks = array("<br />","<br>","<br/>");
				$messageData['m'] = str_ireplace($breaks, "\n", $messageData['m']);
				$messageData['m'] = strip_tags ( $messageData['m'] );
				$messageData['m'] = htmlspecialchars_decode($messageData['m']);

				$notificationData =	array('alert' => $messageData['m'],'t' => $this->messageType,'m' => $messageData,'action' => "PARSE_MSG",'sound' => 'default');

				if($this->endsWith($this->messageType, '3')) {
					$notificationData['avchat'] = "1";
					$this->notificationFlag = '0';
				}
			} else {
				$this->messageType = "A".$this->messageType;
				$messageData['from_noti'] = '1';
				$messageData['alert'] = strip_tags ( $messageData['m'] );
				$notificationData =	array('alert' => $messageData['alert'],'t' => $this->messageType,'m' => $messageData,'action' => "PARSE_MSG",'sound' => 'default');
			}

			if($this->notificationFlag === '1') {

				if($isChatroom == '1') {
					$notificationData['isCR'] = $isChatroom;
				}
				if($isAnnouncement == '1') {
					$notificationData['isANN'] = $isAnnouncement;
				}

				$pushPayload = json_encode(array( "channels" => array($channel), "data" => $notificationData));
				$curl = curl_init();
				curl_setopt($curl,CURLOPT_URL,$this->parseUrl);
				curl_setopt($curl,CURLOPT_PORT,443);
				curl_setopt($curl,CURLOPT_POST,1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($curl,CURLOPT_POSTFIELDS,$pushPayload);
				curl_setopt($curl,CURLOPT_HTTPHEADER,
					array(
						"X-Parse-Application-Id: " . $this->parseAppId,
						"X-Parse-REST-API-Key: " . $this->parseRestKey,
						"Content-Type: application/json"
						));
				$response = curl_exec($curl);
			}

			if(!$response) {
				echo ('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
			}

			curl_close($curl);
		} else {
			echo "Missing or invalid parameters.";
		}
	}

	function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}

	function endsWith($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}
}

?>