<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerLaunch
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerLaunch
{
	private static $last_oauth_base_string = "";
	// protected $context = null;
	protected $ref_id;

	/**
	 * ilObjLTIConsumerLaunch constructor.
	 * @param int $a_id
	 * @param bool $a_reference
	 */
	public function __construct(int $a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}

	 /**
     * get info about the context in which the link is used
     * 
     * The most outer matching course or group is used
     * If not found the most inner category or root node is used
     * 
     * @param	array	list of valid types
     * @return 	array	context array ("ref_id", "title", "type")
     */
    public function getContext($a_valid_types = array('crs', 'grp', 'cat', 'root')) {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
		$tree = $DIC->repositoryTree();

        if (!isset($this->context)) {

            $this->context = array();

            // check fromm inner to outer
            $path = array_reverse($tree->getPathFull($this->ref_id));
            foreach ($path as $key => $row)
            {
                if (in_array($row['type'], $a_valid_types))
                {
					// take an existing inner context outside a course
					if (in_array($row['type'], array('cat', 'root')) && !empty($this->context))
					{
						break;
					}

					$this->context['id'] = $row['child'];
                    $this->context['title'] = $row['title'];
                    $this->context['type'] = $row['type'];

                    // don't break to get the most outer course or group
                }
            }
        }
        return $this->context;
    }




	public static function getLTIContextType($a_type)
	{
		switch ($a_type){
			case "crs":
				return "CourseOffering";
				break;
			case "grp":
				return "Group";
				break;
			case "root":
				return "urn:lti:context-type:ilias/RootNode";
				break;
			case "cat":
			default:
				return "urn:lti:context-type:ilias/Category";
				break;
		}
	}


	
	/**
	 * sign request data with OAuth
	 * 
	 * @param array (	"method => signature methos
	 * 					"key" => consumer key
	 * 					"secret" => shared secret
	 * 					"token"	=> request token
	 * 					"url" => request url
	 * 					data => array (key => value)
	 * 				)
	 * 						
	 * @return array	signed data
	 */
	public static function signOAuth($a_params)
	{
		require_once('./Modules/LTIConsumer/lib/OAuth.php');
		switch ($a_params['sign_method'])
		{
			case "HMAC_SHA1":
				$method = new OAuthSignatureMethod_HMAC_SHA1();
				break;
			case "PLAINTEXT":	
				$method = new OAuthSignatureMethod_PLAINTEXT();
				break;
			case "RSA_SHA1":	
				$method = new OAuthSignatureMethod_RSA_SHA1();
				break;
				
			default:
				return "ERROR: unsupported signature method!";
		}
		
		$consumer = new OAuthConsumer($a_params["key"], $a_params["secret"], $a_params["callback"]);
		$request = OAuthRequest::from_consumer_and_token($consumer, $a_params["token"], $a_params["http_method"], $a_params["url"], $a_params["data"]);
		$request->sign_request($method, $consumer, $a_params["token"]);
		
		// Pass this back up "out of band" for debugging
		self::$last_oauth_base_string = $request->get_signature_base_string();
		// die(self::$last_oauth_base_string);
		
		return $request->get_parameters();
	}
		
}