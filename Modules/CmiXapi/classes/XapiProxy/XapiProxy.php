<?php
    namespace XapiProxy;

    require_once __DIR__.'/XapiProxyPolyFill.php';

    class XapiProxy extends XapiProxyPolyFill {

        private $xapiProxyRequest;
        private $xapiProxyResponse;

        public function __construct($client, $token, $plugin=false) {
            parent::__construct($client, $token, $plugin);
            $this->log()->debug($this->msg('proxy initialized'));
        }

        public function setRequestParams($request) {
            preg_match(self::PARTS_REG, $request->getUri(), $this->cmdParts);
        }

        public function token() {
            return $this->token;
        }

        public function client() {
            return $this->client;
        }

        public function lrsType() {
            return $this->lrsType;
        }

        public function replacedValues() {
            return $this->replacedValues;
        }

        public function specificAllowedStatements() {
            return $this->specificAllowedStatements;
        }

        public function blockSubStatements() {
            return $this->blockSubStatements;
        }

        public function cmdParts() {
            return $this->cmdParts;
        }

        public function method() {
            return $this->method;
        }

        public function getDefaultLrsEndpoint() {
            return $this->defaultLrsEndpoint;
        }

        public function getDefaultLrsKey() {
            return $this->defaultLrsKey;
        }

        public function getDefaultLrsSecret() {
            return $this->defaultLrsSecret;
        }

        public function getFallbackLrsEndpoint() {
            return $this->fallbackLrsEndpoint;
        }

        public function getFallbackLrsKey() {
            return $this->fallbackLrsKey;
        }

        public function getFallbackLrsSecret() {
            return $this->fallbackLrsSecret;
        }

        public function setXapiProxyRequest($xapiProxyRequest) {
            $this->xapiProxyRequest = $xapiProxyRequest;
        }

        public function getXapiProxyRequest() {
            return $this->xapiProxyRequest;
        }

        public function setXapiProxyResponse($xapiProxyResponse) {
            $this->xapiProxyResponse = $xapiProxyResponse;
        }

        public function getXapiProxyResponse() {
            return $this->xapiProxyResponse;
        }

        public function processStatements($request, $body) {
            // everything is allowed
            if (!is_array($this->specificAllowedStatements) && !$this->blockSubStatements) {
                $this->log()->debug($this->msg("all statement are allowed"));
                return NULL;
            }
            $obj = json_decode($body, false);
            // single statement object
            if (is_object($obj) && isset($obj->verb)) {
                $this->log()->debug($this->msg("json is object and statement"));
                $isSubStatement = $this->isSubStatementCheck($obj);
                $verb = $obj->verb->id;
                if ($this->blockSubStatements && $isSubStatement) {
                    $this->log()->debug($this->msg("sub-statement is NOT allowed, fake response - " . $verb));
                    $this->xapiProxyResponse->fakeResponseBlocked(NULL);
                }
                // $specificAllowedStatements
                if (!is_array($this->specificAllowedStatements)) {
                    return NULL;
                }
                if (in_array($verb,$this->specificAllowedStatements)) {
                    $this->log()->debug($this->msg("statement is allowed, do nothing - " . $verb));
                    return NULL;
                }
                else {
                    $this->log()->debug($this->msg("statement is NOT allowed, fake response - " . $verb));
                    $this->xapiProxyResponse->fakeResponseBlocked(NULL);
                }
            }
            // array of statement objects
            if (is_array($obj) && count($obj) > 0 && isset($obj[0]->verb)) {
                $this->log()->debug($this->msg("json is array of statements"));
                $ret = array();
                $up = array();
                for ($i=0; $i<count($obj); $i++) {
                    array_push($ret,$obj[$i]->id); // push every statementid for fakePostResponse
                    $isSubStatement = $this->isSubStatementCheck($obj[$i]);
                    $verb = $obj[$i]->verb->id;
                    if ($this->blockSubStatements && $isSubStatement) {
                        $this->log()->debug($this->msg("sub-statement is NOT allowed - " .$verb));
                    }
                    else {
                        if (!is_array($this->specificAllowedStatements) || (is_array($this->specificAllowedStatements) && in_array($verb,$this->specificAllowedStatements))) {
                            $this->log()->debug($this->msg("statement is allowed - " . $verb));
                            array_push($up,$obj[$i]);
                        }
                    }
                }
                if (count($up) === 0) { // nothing allowed
                    $this->log()->debug($this->msg("no allowed statements in array - fake response..."));
                    $this->xapiProxyResponse->fakeResponseBlocked($ret);
                }
                elseif (count($up) !== count($ret)) { // mixed request with allowed and not allowed statements
                    $this->log()->debug($this->msg("mixed with allowed and unallowed statements"));
                    return array($up,$ret);
                }
                else {
                    // just return nothing
                    return NULL;
                }
            }
            return NULL;
        }

        public function modifyBody($body)
        {
            $obj = json_decode($body, false);

            if (json_last_error() != JSON_ERROR_NONE) {
                // JSON is not valid
                $this->log()->error($this->msg(json_last_error_msg()));
                return $body;
            }

            // $log->debug(json_encode($obj, JSON_PRETTY_PRINT)); // only in DEBUG mode for better performance
            if (is_object($obj)) {
                if (is_array($this->replacedValues)) {
                    foreach ($this->replacedValues as $key => $value) {
                        $this->setValue($obj,$key,$value);
                    }
                }
                $this->handleStatementEvaluation($obj); // ToDo
            }

            if (is_array($obj)) {
                for ($i = 0; $i < count($obj); $i++) {
                    if (is_array($this->replacedValues)) {
                        foreach ($this->replacedValues as $key => $value) {
                            $this->setValue($obj[$i],$key,$value);
                        }
                    }
                    $this->handleStatementEvaluation($obj[$i]); // ToDo
                }
            }
            return json_encode($obj);
        }
        
        private function handleStatementEvaluation($xapiStatement)
        {
            global $DIC;
            if ($this->plugin) {
                require_once __DIR__.'/../class.ilObjXapiCmi5.php';
                // ToDo: handle terminate -> delete session
                $this->setStatus($xapiStatement);
            }
            else {
                /* @var $object */
                $object = \ilObjectFactory::getInstanceByObjId($this->authToken->getObjId());
                if( (string)$object->getLaunchMode() === (string)\ilObjCmiXapi::LAUNCH_MODE_NORMAL ) {
                    // ToDo: check function hasContextActivitiesParentNotEqualToObject!
                    $statementEvaluation = new \ilXapiStatementEvaluation($this->log(), $object);
                    $statementEvaluation->evaluateStatement($xapiStatement, $this->authToken->getUsrId());

                    \ilLPStatusWrapper::_updateStatus(
                        $this->authToken->getObjId(),
                        $this->authToken->getUsrId()
                    );
                }
                if ($xapiStatement->verb->id == self::TERMINATED_VERB) {
                    // ToDo : only cmi5 or also xapi? authToken object still used after that?
                    $this->authToken->delete();
                }
            }
        }

        private function setValue(&$obj, $path, $value) {
            $path_components = explode('.', $path);
            if (count($path_components) == 1) {
                if (property_exists($obj,$path_components[0])) {
                    $obj->{$path_components[0]} = $value;
                }
            }
            else {
                if (property_exists($obj, $path_components[0])) {
                    $this->setValue($obj->{array_shift($path_components)}, implode('.', $path_components), $value);
                }
            }
        }

        private function setStatus($obj) {
            if (isset($obj->verb) && isset($obj->actor) && isset($obj->object)) {
                $verb = $obj->verb->id;
                $score = 'NOT_SET';
                if (array_key_exists($verb, $this->sniffVerbs)) {
                    // check context
                    if ($this->isSubStatementCheck($obj)) {
                        $this->log()->debug($this->msg("statement is sub-statement, ignore status verb " . $verb));
                        return;
                    }
                    if (isset($obj->result) && isset($obj->result->score) && isset($obj->result->score->scaled)) {
                        $score = $obj->result->score->scaled;
                    }
                    $this->log()->debug($this->msg("handleLPStatus: " . $this->sniffVerbs[$verb] . " : " . $score));
                    \ilObjXapiCmi5::handleLPStatusFromProxy($this->client, $this->token, $this->sniffVerbs[$verb], $score);//UK check
                }
            }
        }
	
	private function isSubStatementCheck($obj) {
            $object = \ilObjectFactory::getInstanceByObjId($this->authToken->getObjId()); // get ActivityId in Constructor for better performance, is also used in handleEvaluationStatement
            $objActivityId = $object->getActivityId();
            $statementActivityId = $obj->object->id;
            if ($statementActivityId != $objActivityId) {
                $this->log()->debug($this->msg("statement object id " . $statementActivityId . " != activityId " . $objActivityId));
                $this->log()->debug($this->msg("is Substatement"));
                return true;
            }
            else {
                $this->log()->debug($this->msg("is not Substatement"));
                return false;
            }
        }
     }
?>
