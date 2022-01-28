<?php
    namespace XapiProxy;

    class XapiProxyPolyFill {
        protected $client;
        protected $token;

        protected $plugin;
        protected $table_prefix;
        protected $lrsType;
        protected $authToken = NULL;
        protected $objId = NULL;
        protected $specificAllowedStatements = NULL;
        protected $replacedValues = NULL;
        protected $blockSubStatements = false;
        protected $cmdParts;
        protected $method;

        protected $defaultLrsEndpoint = '';
        protected $defaultLrsKey = '';
        protected $defaultLrsSecret = '';

        protected $fallbackLrsEndpoint = '';
        protected $fallbackLrsKey = '';
        protected $fallbackLrsSecret = '';

        const PARTS_REG = '/^(.*?xapiproxy\.php)(\/([^\?]+)?\??.*)/';

        protected $sniffVerbs = array (
            "http://adlnet.gov/expapi/verbs/completed" => "completed",
            "http://adlnet.gov/expapi/verbs/passed" => "passed",
            "http://adlnet.gov/expapi/verbs/failed" => "failed",
            "http://adlnet.gov/expapi/verbs/satisfied" => "passed"
        );
        
        const TERMINATED_VERB = "http://adlnet.gov/expapi/verbs/terminated";

        public function __construct($client, $token, $plugin=false) {
            $this->client = $client;
            $this->token = $token;
            $this->plugin = $plugin;
            if ($this->plugin) {
                $this->table_prefix = "xxcf";
            }
            else {
                $this->table_prefix = "cmix";
            }
            preg_match(self::PARTS_REG, $GLOBALS['DIC']->http()->request()->getUri(), $this->cmdParts);
            $this->method = strtolower($GLOBALS['DIC']->http()->request()->getMethod());
        }

        public function log() {
            global $log;
            if ($this->plugin) {
                return $log;
            }
            else {
                return \ilLoggerFactory::getLogger('cmix');
            }
        }

        public function msg($msg) {
            if ($this->plugin) {
                return "XapiCmi5Plugin: " . $msg;
            }
            else {
                return $msg;
            }
        }

        public function initLrs() {
            $this->log()->debug($this->msg('initLrs'));
            if ($this->plugin) {
                require_once __DIR__.'/../class.ilXapiCmi5LrsType.php';
                require_once __DIR__.'/../class.ilXapiCmi5AuthToken.php';
                try {
                    $authToken = \ilXapiCmi5AuthToken::getInstanceByToken($this->token);
                }
                catch (\ilXapiCmi5Exception $e) {
                    $this->log()->error($this->msg($e->getMessage()));
                    header('HTTP/1.1 401 Unauthorized');
                    header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                    header('Access-Control-Allow-Credentials: true');
                    exit;
                }
                $this->authToken = $authToken;
                $this->getLrsTypePlugin();
            }
            else {
                require_once __DIR__.'/../class.ilCmiXapiLrsType.php';
                require_once __DIR__.'/../class.ilCmiXapiAuthToken.php';
                try {
                    $authToken = \ilCmiXapiAuthToken::getInstanceByToken($this->token);
                }
                catch (\ilCmiXapiException $e) {
                    $this->log()->error($this->msg($e->getMessage()));
                    header('HTTP/1.1 401 Unauthorized');
                    header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                    header('Access-Control-Allow-Credentials: true');
                    exit;
                }

                $this->authToken = $authToken;
                $this->getLrsType();
            }
        }

        private function getLrsTypePlugin() {
            try {
                $lrsType = $this->getLrsTypeAndMoreByToken();
                if ($lrsType == null) {
                    // why not using $log?
                    $GLOBALS['DIC']->logger()->root()->log("XapiCmi5Plugin: 401 Unauthorized for token");
                    header('HTTP/1.1 401 Unauthorized');
                    header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                    header('Access-Control-Allow-Credentials: true');
                    exit;
                }
                $this->defaultLrsEndpoint = $lrsType->getDefaultLrsEndpoint();
                $this->defaultLrsKey = $lrsType->getDefaultLrsKey();
                $this->defaultLrsSecret = $lrsType->getDefaultLrsSecret();

                $this->fallbackLrsEndpoint = $lrsType->getFallbackLrsEndpoint();
                $this->fallbackLrsKey = $lrsType->getFallbackLrsKey();
                $this->fallbackLrsSecret = $lrsType->getFallbackLrsSecret();

                $this->lrsType = $lrsType;
            }
            catch(\Exception $e)
            {
                // why not using $log?
                $GLOBALS['DIC']->logger()->root()->log("XapiCmi5Plugin: " . $e->getMessage());
                header('HTTP/1.1 401 Unauthorized');
                header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                header('Access-Control-Allow-Credentials: true');
                exit;
            }
        }


        private function getLrsType() { // Core new > 6
            try {
                    
                $lrsType = $this->getLrsTypeAndMoreByToken();
                $this->defaultLrsEndpoint = $lrsType->getLrsEndpoint();
                $this->defaultLrsKey = $lrsType->getLrsKey();
                $this->defaultLrsSecret = $lrsType->getLrsSecret();
                $this->lrsType = $lrsType;
                // one query IS better :-)
                // $lrsType = new ilCmiXapiLrsType($authToken->getLrsTypeId());
                $objId = $this->authToken->getObjId();
                $this->objId = $objId;
                if (!$lrsType->isAvailable()) {
                    throw new \ilCmiXapiException(
                        'lrs endpoint (id=' . $this->authToken->getLrsTypeId() . ') unavailable (responded 401-unauthorized)'
                    );
                }
            } catch (\ilCmiXapiException $e) {
                $this->log()->error($this->msg($e->getMessage()));
                header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                header('Access-Control-Allow-Credentials: true');
                header('HTTP/1.1 401 Unauthorized');
                exit;
            }
            \ilCmiXapiUser::saveProxySuccess($this->authToken->getObjId(), $this->authToken->getUsrId(),$this->lrsType->getPrivacyIdent());
            return $lrsType;
        }
        /**
         * hybrid function, maybe two distinct functions would be better?
         */
        private function getLrsTypeAndMoreByToken() {
            $type_id = null;
            $lrs = null;
            $db = $GLOBALS['DIC']->database();
            $query ="SELECT {$this->table_prefix}_settings.lrs_type_id,
                                {$this->table_prefix}_settings.only_moveon, 
                                {$this->table_prefix}_settings.achieved, 
                                {$this->table_prefix}_settings.answered, 
                                {$this->table_prefix}_settings.completed, 
                                {$this->table_prefix}_settings.failed, 
                                {$this->table_prefix}_settings.initialized, 
                                {$this->table_prefix}_settings.passed, 
                                {$this->table_prefix}_settings.progressed, 
                                {$this->table_prefix}_settings.satisfied, 
                                {$this->table_prefix}_settings.c_terminated, 
                                {$this->table_prefix}_settings.hide_data, 
                                {$this->table_prefix}_settings.c_timestamp, 
                                {$this->table_prefix}_settings.duration, 
                                {$this->table_prefix}_settings.no_substatements,
                                {$this->table_prefix}_settings.privacy_ident
                        FROM {$this->table_prefix}_settings, {$this->table_prefix}_token 
                        WHERE {$this->table_prefix}_settings.obj_id = {$this->table_prefix}_token.obj_id AND {$this->table_prefix}_token.token = " . $db->quote($this->token, 'text');

            $res = $db->query($query);
            while ($row = $db->fetchObject($res)) 
            {
                $type_id = $row->lrs_type_id;
                if ($type_id) {
                    $lrs = ($this->plugin) ? new \ilXapiCmi5LrsType($type_id) : new \ilCmiXapiLrsType($type_id);
                }
    
                $sarr = [];
                if ((bool)$row->only_moveon) {
                    if ((bool)$row->achieved) {
                        $sarr[] = "https://w3id.org/xapi/dod-isd/verbs/achieved";
                    }
                    if ((bool)$row->answered) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/answered";
                        $sarr[] = "https://w3id.org/xapi/dod-isd/verbs/answered";
                    }
                    if ((bool)$row->completed) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/completed";
                        $sarr[] = "https://w3id.org/xapi/dod-isd/verbs/completed";
                    }
                    if ((bool)$row->failed) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/failed";
                    }
                    if ((bool)$row->initialized) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/initialized";
                        $sarr[] = "https://w3id.org/xapi/dod-isd/verbs/initialized";
                    }
                    if ((bool)$row->passed) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/passed";
                    }
                    if ((bool)$row->progressed) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/progressed";
                    }
                    if ((bool)$row->satisfied) {
                        $sarr[] = "https://w3id.org/xapi/adl/verbs/satisfied";
                    }
                    if ((bool)$row->c_terminated) {
                        $sarr[] = "http://adlnet.gov/expapi/verbs/terminated";
                    }
                    if (count($sarr) > 0) {
                        $this->specificAllowedStatements = $sarr;
                        $this->log()->debug($this->msg('getSpecificAllowedStatements: ' . var_export($this->specificAllowedStatements,TRUE))); 
                    }
                }
                if ((bool)$row->hide_data) {
                    $rarr = array();
                    if ((bool)$row->c_timestamp) $rarr['timestamp'] = '1970-01-01T00:00:00.000Z';
                    if ((bool)$row->duration) $rarr['result.duration'] = 'PT00.000S';
                    if (count($rarr) > 0) {
                        $this->replacedValues = $rarr;
                        $this->log()->debug($this->msg('getReplacedValues: ' . var_export($this->replacedValues,TRUE)));
                    }
                }
                if ((bool)$row->no_substatements) {
                    $this->blockSubStatements = true;
                    $this->log()->debug($this->msg('getBlockSubStatements: ' . $this->blockSubStatements));
                }
                $lrs->setPrivacyIdent((int)$row->privacy_ident);
            }
            return $lrs;
        }
     }
?>
