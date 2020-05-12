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

class ilLTIConsumerResultService
{

    /**
     * @var ilLTIConsumerResult
     */
    protected $result = null;

    /**
     * @var  Array properties: name => value
     */
//    protected $properties = array();

    /**
     * @var integer
     */
    protected $availability = 0;

    /**
     * @var float
     */
    protected $mastery_score = 1;

    /**
     * @var Array fields: name => value
     */
    protected $fields = array();

    /**
     * @var string the message reference id
     */
    protected $message_ref_id = '';
    /**
     * @var string  the requested operation
     */
    protected $operation = '';


    /**
     * @return float
     */
    public function getMasteryScore() : float
    {
        return $this->mastery_score;
    }

    /**
     * @param float $mastery_score
     */
    public function setMasteryScore(float $mastery_score)
    {
        $this->mastery_score = $mastery_score;
    }

    /**
     * @return int
     */
    public function getAvailability() : int
    {
        return $this->availability;
    }

    /**
     * @param int $availability
     */
    public function setAvailability(int $availability)
    {
        $this->availability = $availability;
    }

    /**
     * @return bool
     */
    public function isAvailable() : bool
    {
        if ($this->availability == 0) {
            return false;
        }
        return true;
    }



    /**
     * Constructor: general initialisations
     */
    public function __construct()
    {
    }
    
    /**
     * Handle an incoming request from the LTI tool provider
     */
    public function handleRequest()
    {
        try {
            // get the request as xml
            $xml = simplexml_load_file('php://input');
            $this->message_ref_id = (string) $xml->imsx_POXHeader->imsx_POXRequestHeaderInfo->imsx_messageIdentifier;
            $request = current($xml->imsx_POXBody->children());
            $this->operation = str_replace('Request', '', $request->getName());

            $token = ilCmiXapiAuthToken::getInstanceByToken($request->resultRecord->sourcedGUID->sourcedId);

            $this->result = ilLTIConsumerResult::getByKeys($token->getObjId(), $token->getUsrId(), false);
            if (empty($this->result)) {
                $this->respondUnauthorized("lti_consumer_results_id not found!");
                return;
            }


            // check the object status
            $this->readProperties($this->result->obj_id);

            if (!$this->isAvailable()) {
                $this->respondUnsupported();
                return;
            }

            // Verify the signature
            $this->readFields($this->result->obj_id);
            $result = $this->checkSignature($this->fields['KEY'], $this->fields['SECRET']);
            if ($result instanceof Exception) {
                $this->respondUnauthorized($result->getMessage());
                return;
            }

            // Dispatch the operation
            switch ($this->operation) {
                case 'readResult':
                    $this->readResult($request);
                    break;

                case 'replaceResult':
                    $this->replaceResult($request);
                    $this->updateLP();
                    break;

                case 'deleteResult':
                    $this->deleteResult($request);
                    $this->updateLP();
                    break;

                default:
                    $this->respondUnknown();
                    break;
            }
        } catch (Exception $exception) {
            $this->respondBadRequest($exception->getMessage());
        }
    }

    /**
     * Read a stored result
     * @param SimpleXMLElement $request
     */
    protected function readResult($request)
    {
        $response = $this->loadResponse('readResult.xml');
        $response = str_replace('{message_id}', md5(rand(0, 999999999)), $response);
        $response = str_replace('{message_ref_id}', $this->message_ref_id, $response);
        $response = str_replace('{operation}', $this->operation, $response);
        $response = str_replace('{result}', $this->result->result, $response);

        header('Content-type: application/xml');
        echo $response;
    }

    /**
     * Replace a stored result
     * @param SimpleXMLElement $request
     */
    protected function replaceResult($request)
    {
        $result = (string) $request->resultRecord->result->resultScore->textString;
        if (!is_numeric($result)) {
            $code = "failure";
            $severity = "status";
            $description = "The result is not a number.";
        } elseif ($result < 0 or $result > 1) {
            $code = "failure";
            $severity = "status";
            $description = "The result is out of range from 0 to 1.";
        } else {
            $this->result->result = (float) $result;
            $this->result->save();

            #if ($result >= $this->getMasteryScore())
            #{
            #    $lp_status = ilLTIConsumerLPStatus::LP_STATUS_COMPLETED_NUM;
            #}
            #else
            #{
            #    $lp_status = ilLTIConsumerLPStatus::LP_STATUS_FAILED_NUM;
            #}
            #$lp_percentage = 100 * $result;
            #ilLTIConsumerLPStatus::trackResult($this->result->usr_id, $this->result->obj_id, $lp_status, $lp_percentage);

            $code = "success";
            $severity = "status";
            $description = sprintf("Score for %s is now %s", $this->result->id, $this->result->result);
        }

        $response = $this->loadResponse('replaceResult.xml');
        $response = str_replace('{message_id}', md5(rand(0, 999999999)), $response);
        $response = str_replace('{message_ref_id}', $this->message_ref_id, $response);
        $response = str_replace('{operation}', $this->operation, $response);
        $response = str_replace('{code}', $code, $response);
        $response = str_replace('{severity}', $severity, $response);
        $response = str_replace('{description}', $description, $response);

        header('Content-type: application/xml');
        echo $response;
    }

    /**
     * Delete a stored result
     * @param SimpleXMLElement $request
     */
    protected function deleteResult($request)
    {
        $this->result->result = null;
        $this->result->save();

        #$lp_status = ilLTIConsumerLPStatus::LP_STATUS_IN_PROGRESS_NUM;
        #$lp_percentage = 0;
        #ilLTIConsumerLPStatus::trackResult($this->result->usr_id, $this->result->obj_id, $lp_status, $lp_percentage);

        $code = "success";
        $severity = "status";

        $response = $this->loadResponse('deleteResult.xml');
        $response = str_replace('{message_id}', md5(rand(0, 999999999)), $response);
        $response = str_replace('{message_ref_id}', $this->message_ref_id, $response);
        $response = str_replace('{operation}', $this->operation, $response);
        $response = str_replace('{code}', $code, $response);
        $response = str_replace('{severity}', $severity, $response);

        header('Content-type: application/xml');
        echo $response;
    }


    /**
     * Load the XML template for the response
     * @param string    file name
     * @return string   file content
     */
    protected function loadResponse($a_name)
    {
        return file_get_contents('./Modules/LTIConsumer/responses/' . $a_name);
    }


    /**
     * Send a response that the operation is not supported
     * This depends on the status of the object
     */
    protected function respondUnsupported()
    {
        $response = $this->loadResponse('unsupported.xml');
        $response = str_replace('{message_id}', md5(rand(0, 999999999)), $response);
        $response = str_replace('{message_ref_id}', $this->message_ref_id, $response);
        $response = str_replace('{operation}', $this->operation, $response);

        header('Content-type: application/xml');
        echo $response;
    }

    /**
     * Send a "unknown operation" response
     */
    protected function respondUnknown()
    {
        $response = $this->loadResponse('unknown.xml');
        $response = str_replace('{message_id}', md5(rand(0, 999999999)), $response);
        $response = str_replace('{message_ref_id}', $this->message_ref_id, $response);
        $response = str_replace('{operation}', $this->operation, $response);

        header('Content-type: application/xml');
        echo $response;
    }


    /**
     * Send a "bad request" response
     * @param string  response message
     */
    protected function respondBadRequest($message = null)
    {
        header('HTTP/1.1 400 Bad Request');
        header('Content-type: text/plain');
        if (isset($message)) {
            echo $message;
        } else {
            echo 'This is not a well-formed LTI Basic Outcomes Service request.';
        }
    }


    /**
     * Send an "unauthorized" response
     * @param   string response message
     *
     */
    protected function respondUnauthorized($message = null)
    {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-type: text/plain');
        if (isset($message)) {
            echo $message;
        } else {
            echo 'This request could not be authorized.';
        }
    }


    /**
     * Read the LTI Consumer object properties
     *
     * @param integer $a_obj_id
     */
    private function readProperties($a_obj_id)
    {
        global $DIC;

        $query = "
			SELECT lti_ext_provider.availability, lti_consumer_settings.mastery_score 
			FROM lti_ext_provider, lti_consumer_settings
			WHERE lti_ext_provider.id = lti_consumer_settings.provider_id
			AND lti_consumer_settings.obj_id = %s
		";
        
        $res = $DIC->database()->queryF($query, array('integer'), array($a_obj_id));

        if ($row = $DIC->database()->fetchAssoc($res)) {
            //$this->properties = $row;
            $this->setAvailability((int) $row['availability']);
            $this->setMasteryScore((float) $row['mastery_score']);
        }
    }

    /**
     * Read the LTI Consumer object fields
     *
     * @param integer $a_obj_id
     */
    private function readFields($a_obj_id)
    {
        global $DIC;

        $query = "
			SELECT lti_ext_provider.provider_key, lti_ext_provider.provider_secret, lti_consumer_settings.launch_key, lti_consumer_settings.launch_secret
			FROM lti_ext_provider, lti_consumer_settings
			WHERE lti_ext_provider.id = lti_consumer_settings.provider_id
			AND lti_consumer_settings.obj_id = %s
		";
        
        $res = $DIC->database()->queryF($query, array('integer'), array($a_obj_id));
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            if (strlen($row["launch_key"] > 0)) {
                $this->fields["KEY"] = $row["launch_key"];
            } else {
                $this->fields["KEY"] = $row["provider_key"];
            }
            if (strlen($row["launch_key"] > 0)) {
                $this->fields["SECRET"] = $row["launch_secret"];
            } else {
                $this->fields["SECRET"] = $row["provider_secret"];
            }
        }
    }

    /**
     * Check the reqest signature
     * @return mixed	Exception or true
     */
    private function checkSignature($a_key, $a_secret)
    {
        require_once('./Modules/LTIConsumer/lib/OAuth.php');
        require_once('./Modules/LTIConsumer/lib/TrivialOAuthDataStore.php');

        $store = new TrivialOAuthDataStore();
        $store->add_consumer($this->fields['KEY'], $this->fields['SECRET']);

        $server = new OAuthServer($store);
        $method = new OAuthSignatureMethod_HMAC_SHA1();
        $server->add_signature_method($method);

        $request = OAuthRequest::from_request();
        try {
            $server->verify_request($request);
        } catch (Exception $e) {
            return $e;
        }
        return true;
    }
    
    protected function updateLP()
    {
        if (!($this->result instanceof ilLTIConsumerResult)) {
            return;
        }
        
        ilLPStatusWrapper::_updateStatus($this->result->getObjId(), $this->result->getUsrId());
    }
}
