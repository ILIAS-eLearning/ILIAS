<?php
    namespace XapiProxy;
    
    use GuzzleHttp\Client;
    use GuzzleHttp\Promise;
    use GuzzleHttp\RequestOptions;
    use GuzzleHttp\Psr7\Request;
    //use GuzzleHttp\Exception\ConnectException;
    //use GuzzleHttp\Exception\RequestException;
    use GuzzleHttp\Psr7\Uri;

    class XapiProxyRequest {

        private $dic;
        private $xapiproxy;
        private $request;
        private $xapiProxyResponse;

        public function __construct() {
            $this->dic = $GLOBALS['DIC'];
            $this->xapiproxy = $this->dic['xapiproxy'];
            $this->request = $this->dic->http()->request();
        }

        public function handle() {
            $this->xapiProxyResponse = $this->xapiproxy->getXapiProxyResponse();
            $request = $this->dic->http()->request();
            $cmdParts = $this->xapiproxy->cmdParts();
            if (count($cmdParts) === 4) {
                if ($cmdParts[3] === "statements") {
                    $this->xapiproxy->log()->debug($this->msg("handleStatementsRequest"));
                    $this->handleStatementsRequest($request);
                } else {
                    $this->xapiproxy->log()->debug($this->msg("Not handled xApi Query: " . $cmdParts[3]));
                    $this->handleProxy($request);
                }
            } else {
                $this->xapiproxy->log()->error($this->msg("Wrong xApi Query: " . $request->getUri()));
                $this->handleProxy($request);
            }
        }

        private function handleStatementsRequest($request) {
            $method = $this->xapiproxy->method();
            if ($method === "post" || $method === "put") {
                $this->handlePostPutStatementsRequest($request);
            }
            else {
                // get Method is not handled yet
                $this->handleProxy($request);
            }
        }

        private function handlePostPutStatementsRequest($request)
        {
            $body = $request->getBody()->getContents();
            if (empty($body)) {
                $this->xapiproxy->log()->warning($this->msg("empty body in handlePostPutRequest"));
                $this->handleProxy($request);
            }
            else {
                try {
                    $this->xapiproxy->log()->debug($this->msg("process statements"));
                    $retArr = $this->xapiproxy->processStatements($request, $body);
                    if (is_array($retArr)) {
                        $body = json_encode($retArr[0]); // new body with allowed statements
                        $fakePostBody = $retArr[1]; // fake post php array of ALL statments as if all statements were processed
                    }
                }
                catch(Exception $e) {
                    $this->xapiproxy->log()->error($this->msg($e->getMessage()));
                    $this->xapiProxyResponse->exitProxyError();
                }
                try {
                    $body = $this->xapiproxy->modifyBody($body);
                    $req = new Request($request->getMethod(),$request->getUri(),$request->getHeaders(),$body);
                    $this->handleProxy($req, $fakePostBody);
                }
                catch(Exception $e) {
                    $this->xapiproxy->log()->error($this->msg($e->getMessage()));
                    $this->handleProxy($request, $fakePostBody);
                }
            }
        }

        private function handleProxy($request, $fakePostBody = NULL) {
            
            $endpointDefault = $this->xapiproxy->getDefaultLrsEndpoint();
            $endpointFallback = $this->xapiproxy->getFallbackLrsEndpoint();
        
            $this->xapiproxy->log()->debug($this->msg("endpointDefault: " . $endpointDefault));
            $this->xapiproxy->log()->debug($this->msg("endpointFallback: " . $endpointFallback));
            
            $keyDefault =  $this->xapiproxy->getDefaultLrsKey();
            $secretDefault =  $this->xapiproxy->getDefaultLrsSecret();
            $authDefault = 'Basic ' . base64_encode($keyDefault . ':' . $secretDefault);
        
            $hasFallback = ($endpointFallback === "") ? FALSE : TRUE;
        
            if ($hasFallback) {
                $keyFallback = $this->xapiproxy->getFallbackLrsKey();
                $secretFallback = $this->xapiproxy->getFallbackLrsSecret();
                $authFallback = 'Basic ' . base64_encode($keyFallback . ':' . $secretFallback);
            }
            
            $req_opts = array(
                RequestOptions::VERIFY => true,
                RequestOptions::CONNECT_TIMEOUT => 10,
                RequestOptions::HTTP_ERRORS => false
            );
            $cmd = $this->xapiproxy->cmdParts()[2];
            $upstreamDefault = $endpointDefault.$cmd;
            $uriDefault = new Uri($upstreamDefault);
            $body = $request->getBody()->getContents();
            $reqDefault = $this->createProxyRequest($request,$uriDefault,$authDefault,$body);
            
            if ($hasFallback) {
                $upstreamFallback = $endpointFallback.$cmd;
                $uriFallback = new Uri($upstreamFallback);
                $reqFallback = $this->createProxyRequest($request,$uriFallback,$authFallback,$body);
            }

            $httpclient = new Client();
            if ($hasFallback) {
                $promises = [
                    'default' 	=> $httpclient->sendAsync($reqDefault, $req_opts),
                    'fallback'	=> $httpclient->sendAsync($reqFallback, $req_opts)
                ];
                
                // this would throw first ConnectionException
                // $responses = Promise\unwrap($promises);
                try {
                    $responses = Promise\settle($promises)->wait();
                }
                catch(Exception $e) {
                    $this->xapiproxy->log()->error($this->msg($e->getMessage()));
                }
               
                $defaultOk = $this->xapiProxyResponse->checkResponse($responses['default'], $endpointDefault);
                $fallbackOk = $this->xapiProxyResponse->checkResponse($responses['fallback'], $endpointFallback);
                
                if ($defaultOk) {
                    try {
                        $this->xapiProxyResponse->handleResponse($reqDefault, $responses['default']['value'], $fakePostBody);
                    }
                    catch (Exception $e) {
                        $this->xapiproxy->error($this->msg("XAPI exception from Default LRS: " . $endpointDefault . " (sent HTTP 500 to client): " . $e->getMessage()));
                        $this->xapiProxyResponse->exitProxyError();
                    }
                    
                }
                elseif ($fallbackOk) {
                    try {
                        $this->xapiProxyResponse->handleResponse($reqFallback, $responses['fallback']['value'], $fakePostBody);
                    }
                    catch (Exception $e) {
                        $this->xapiproxy->error($this->msg("XAPI exception from Default LRS: " . $endpointDefault . " (sent HTTP 500 to client): " . $e->getMessage()));
                        $this->xapiProxyResponse->exitProxyError();
                    }
                }
                else {
                    $this->xapiProxyResponse->exitResponseError();
                }
            }
            else {
                $promises = [
                    'default' => $httpclient->sendAsync($reqDefault, $req_opts)
                ];
                // this would throw first ConnectionException
                // $responses = Promise\unwrap($promises);
                try {
                    $responses = Promise\settle($promises)->wait();
                }
                catch(Exception $e) {
                    $this->xapiproxy->log()->error($this->msg($e->getMessage()));
                }
                if ($this->xapiProxyResponse->checkResponse($responses['default'], $endpointDefault)) {
                    try {
                        $this->xapiProxyResponse->handleResponse($reqDefault, $responses['default']['value'], $fakePostBody);
                    }
                    catch(Exception $e) {
                        $this->xapiproxy->error($this->msg("XAPI exception from Default LRS: " . $endpointDefault . " (sent HTTP 500 to client): " . $e->getMessage()));
                        $this->xapiProxyResponse->exitProxyError();
                    }
                }
                else {
                    $this->xapiProxyResponse->exitResponseError();
                }
            }
        }

        // Cookies?, ServerRequestParams required?
        private function createProxyRequest($request, $uri, $auth, $body) {
            $headers = array(
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Authorization' => $auth
            );
    
            if ($request->hasHeader('X-Experience-API-Version')) {
                $headers['X-Experience-API-Version'] = $request->getHeader('X-Experience-API-Version');
            }
    
            if ($request->hasHeader('Referrer')) {
                $headers['Referrer'] = $request->getHeader('Referrer');
            }
    
            if ($request->hasHeader('Content-Type')) {
                $headers['Content-Type'] = $request->getHeader('Content-Type');
            }
    
            if ($request->hasHeader('Origin')) {
                $headers['Origin'] = $request->getHeader('Origin');
            }
    
            if ($request->hasHeader('Content-Length')) {
                $contentLength = $request->getHeader('Content-Length');
                if (is_array($contentLength) && $contentLength[0] === '') {
                    $contentLength = array(0);
                }
                elseif ($contentLength === '') {
                    $contentLength = array(0);
                }
                $headers['Content-Length'] = $contentLength;
            }
    
            if ($request->hasHeader('Connection')) {
                $headers['Connection'] = $request->getHeader('Connection');
            }

            //$this->xapiproxy->log()->debug($this->msg($body));

            $req = new Request(strtoupper($request->getMethod()),$uri,$headers,$body);
    
            return $req;
        }

        private function msg($msg) {
            return $this->xapiproxy->msg($msg);
        }
    }
?>
