<?php declare(strict_types=1);
    /**
     * This file is part of ILIAS, a powerful learning management system
     * published by ILIAS open source e-Learning e.V.
     *
     * ILIAS is licensed with the GPL-3.0,
     * see https://www.gnu.org/licenses/gpl-3.0.en.html
     * You should have received a copy of said license along with the
     * source code, too.
     *
     * If this is not the case or you just want to try ILIAS, you'll find
     * us at:
     * https://www.ilias.de
     * https://github.com/ILIAS-eLearning
     *
     *********************************************************************/
    
    namespace XapiProxy;

    use GuzzleHttp\Psr7\Request;
    use GuzzleHttp\Psr7\Response;

    class XapiProxy extends XapiProxyPolyFill
    {
        private XapiProxyRequest $xapiProxyRequest;
        private XapiProxyResponse $xapiProxyResponse;

        public function __construct(string $client, string $token, ?bool $plugin = false)
        {
            parent::__construct($client, $token, $plugin);
            $this->log()->debug($this->msg('proxy initialized'));
        }

        public function setRequestParams(Request $request) : void
        {
            preg_match(self::PARTS_REG, $request->getUri(), $this->cmdParts);
        }

        public function token() : string
        {
            return $this->token;
        }

        public function client() : string
        {
            return $this->client;
        }

        public function lrsType() : ?\ilCmiXapiLrsType
        {
            return $this->lrsType;
        }

        public function replacedValues() : ?array
        {
            return $this->replacedValues;
        }

        public function specificAllowedStatements() : ?array
        {
            return $this->specificAllowedStatements;
        }

        public function blockSubStatements() : bool
        {
            return $this->blockSubStatements;
        }

        /**
         * @return mixed[]
         */
        public function cmdParts() : array
        {
            return $this->cmdParts;
        }

        public function method() : string
        {
            return $this->method;
        }

        public function getDefaultLrsEndpoint() : string
        {
            return $this->defaultLrsEndpoint;
        }

        public function getDefaultLrsKey() : string
        {
            return $this->defaultLrsKey;
        }

        public function getDefaultLrsSecret() : string
        {
            return $this->defaultLrsSecret;
        }

        public function getFallbackLrsEndpoint() : string
        {
            return $this->fallbackLrsEndpoint;
        }

        public function getFallbackLrsKey() : string
        {
            return $this->fallbackLrsKey;
        }

        public function getFallbackLrsSecret() : string
        {
            return $this->fallbackLrsSecret;
        }

        public function setXapiProxyRequest(XapiProxyRequest $xapiProxyRequest) : void
        {
            $this->xapiProxyRequest = $xapiProxyRequest;
        }

        public function getXapiProxyRequest() : XapiProxyRequest
        {
            return $this->xapiProxyRequest;
        }

        public function setXapiProxyResponse(XapiProxyResponse $xapiProxyResponse) : void
        {
            $this->xapiProxyResponse = $xapiProxyResponse;
        }

        public function getXapiProxyResponse() : XapiProxyResponse
        {
            return $this->xapiProxyResponse;
        }

        public function processStatements(\Psr\Http\Message\RequestInterface $request, $body) : ?array
        {
            // everything is allowed
            if (!is_array($this->specificAllowedStatements) && !$this->blockSubStatements) {
                $this->log()->debug($this->msg("all statement are allowed"));
                return null;
            }
            $obj = json_decode($body, false);
            // single statement object
            if (is_object($obj) && isset($obj->verb)) {
                $this->log()->debug($this->msg("json is object and statement"));
                $isSubStatement = $this->isSubStatementCheck($obj);
                $verb = $obj->verb->id;
                if ($this->blockSubStatements && $isSubStatement) {
                    $this->log()->debug($this->msg("sub-statement is NOT allowed, fake response - " . $verb));
                    $this->xapiProxyResponse->fakeResponseBlocked(null);
                }
                // $specificAllowedStatements
                if (!is_array($this->specificAllowedStatements)) {
                    return null;
                }
                if (in_array($verb, $this->specificAllowedStatements)) {
                    $this->log()->debug($this->msg("statement is allowed, do nothing - " . $verb));
                    return null;
                } else {
                    $this->log()->debug($this->msg("statement is NOT allowed, fake response - " . $verb));
                    $this->xapiProxyResponse->fakeResponseBlocked(null);
                }
            }
            // array of statement objects
            if (is_array($obj) && count($obj) > 0 && isset($obj[0]->verb)) {
                $this->log()->debug($this->msg("json is array of statements"));
                $ret = array();
                $up = array();
                foreach ($obj as $i => $singleObj) {
                    $ret[] = $singleObj->id;
                    // push every statementid for fakePostResponse
                    $isSubStatement = $this->isSubStatementCheck($singleObj);
                    $verb = $singleObj->verb->id;
                    if ($this->blockSubStatements && $isSubStatement) {
                        $this->log()->debug($this->msg("sub-statement is NOT allowed - " . $verb));
                    } else {
                        if (!is_array($this->specificAllowedStatements) || (is_array($this->specificAllowedStatements) && in_array($verb, $this->specificAllowedStatements))) {
                            $this->log()->debug($this->msg("statement is allowed - " . $verb));
                            array_push($up, $obj[$i]);
                        }
                    }
                }
                if ($up === []) { // nothing allowed
                    $this->log()->debug($this->msg("no allowed statements in array - fake response..."));
                    $this->xapiProxyResponse->fakeResponseBlocked("");
//                    $this->xapiProxyResponse->fakeResponseBlocked($ret);
                } elseif (count($up) !== count($ret)) { // mixed request with allowed and not allowed statements
                    $this->log()->debug($this->msg("mixed with allowed and unallowed statements"));
                    return array($up,$ret);
                } else {
                    // just return nothing
                    return null;
                }
            }
            return null;
        }

        public function modifyBody(string $body) : string
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
                        $this->setValue($obj, (string) $key, (string) $value);
                    }
                }
                $this->handleStatementEvaluation($obj); // ToDo
            }

            if (is_array($obj)) {
                for ($i = 0; $i < count($obj); $i++) {
                    if (is_array($this->replacedValues)) {
                        foreach ($this->replacedValues as $key => $value) {
                            $this->setValue($obj[$i], (string) $key, (string) $value);
                        }
                    }
                    $this->handleStatementEvaluation($obj[$i]); // ToDo
                }
            }
            return json_encode($obj);
        }

        private function handleStatementEvaluation(object $xapiStatement) : void
        {
            global $DIC;
            if ($this->plugin) {
                // ToDo: handle terminate -> delete session
                $this->setStatus($xapiStatement);
            } else {
                /* @var $object */
                $object = \ilObjectFactory::getInstanceByObjId($this->authToken->getObjId());
                if ((string) $object->getLaunchMode() === (string) \ilObjCmiXapi::LAUNCH_MODE_NORMAL) {
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

        private function setValue(object &$obj, string $path, string $value) : void
        {
            $path_components = explode('.', $path);
            if (count($path_components) == 1) {
                if (property_exists($obj, $path_components[0])) {
                    $obj->{$path_components[0]} = $value;
                }
            } else {
                if (property_exists($obj, $path_components[0])) {
                    $this->setValue($obj->{array_shift($path_components)}, implode('.', $path_components), $value);
                }
            }
        }

        private function setStatus(object $obj) : void
        {
//            if (isset($obj->verb) && isset($obj->actor) && isset($obj->object)) {
//                $verb = $obj->verb->id;
//                $score = 'NOT_SET';
//                if (array_key_exists($verb, $this->sniffVerbs)) {
//                    // check context
//                    if ($this->isSubStatementCheck($obj)) {
//                        $this->log()->debug($this->msg("statement is sub-statement, ignore status verb " . $verb));
//                        return;
//                    }
//                    if (isset($obj->result) && isset($obj->result->score) && isset($obj->result->score->scaled)) {
//                        $score = $obj->result->score->scaled;
//                    }
//                    $this->log()->debug($this->msg("handleLPStatus: " . $this->sniffVerbs[$verb] . " : " . $score));
//                    \ilObjXapiCmi5::handleLPStatusFromProxy($this->client, $this->token, $this->sniffVerbs[$verb], $score);//UK check
//                }
//            }
        }


        private function isSubStatementCheck(object $obj) : bool
        {
            if (
                isset($obj->context) &&
                isset($obj->context->contextActivities) &&
                is_array($obj->context->contextActivities->parent)
            ) {
                $this->log()->debug($this->msg("is Substatement"));
                return true;
            } else {
                $this->log()->debug($this->msg("is not Substatement"));
                return false;
            }
        }
    }
