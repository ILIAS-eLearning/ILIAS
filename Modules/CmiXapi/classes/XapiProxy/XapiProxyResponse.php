<?php
    namespace XapiProxy;
    
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Message\ResponseInterface;

    class XapiProxyResponse {

        private $dic;
        private $xapiproxy;
        //private $xapiProxyRequest;

        public function __construct() {
            $this->dic = $GLOBALS['DIC'];
            $this->xapiproxy = $this->dic['xapiproxy'];
        }

        public function checkResponse($response, $endpoint)
        {
            if ($response['state'] == 'fulfilled')
            {
                $status = $response['value']->getStatusCode();
                if ($status === 200 || $status === 204 || $status === 404)
                {
                    return true;
                }
                else
                {
                    $this->xapiproxy->log()->error("LRS error {$endpoint}: " . $response['value']->getBody());
                    return false;
                }
            }
            else {
                try
                {
                    $this->xapiproxy->log()->error("Connection error {$endpoint}: " . $response['reason']->getMessage());
                }
                catch(\Exception $e)
                {
                    $this->xapiproxy->log()->error("error {$endpoint}:" . $e->getMessage());
                }
                return false;
            }
        }
        
        public function handleResponse($request, $response, $fakePostBody = NULL) {
            // check transfer encoding bug
            if ($fakePostBody !== NULL) {
                $origBody = $response->getBody();
                $this->xapiproxy->log()->debug($this->msg("orig body: " . $origBody));
                $this->xapiproxy->log()->debug($this->msg("fake body: " . json_encode($fakePostBody)));
                // because there is a real response object, it should also be possible to override the response stream...
                // but this does the job as well:
                $this->fakeResponseBlocked($fakePostBody);
            }
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
            if (array_key_exists('Transfer-Encoding', $headers) && $headers['Transfer-Encoding'][0] == "chunked") {
                $this->xapiproxy->log()->debug($this->msg("sniff response transfer-encoding for unallowed Content-length"));
                $body = $response->getBody();
                unset($headers['Transfer-Encoding']);
                $headers['Content-Length'] = array(strlen($body));
                $response2 = new \GuzzleHttp\Psr7\Response($status,$headers,$body);
                $this->emit($response2);
            }
            else {
                $this->emit($response);
            }
        }

        public function fakeResponseBlocked($post=NULL) {
            $this->xapiproxy->log()->debug($this->msg("fakeResponseFromBlockedRequest"));
            if ($post===NULL) {
                $this->xapiproxy->log()->debug($this->msg("post === NULL"));
                header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                header('Access-Control-Allow-Credentials: true');
                header('X-Experience-API-Version: 1.0.3');
                header('HTTP/1.1 204 No Content');
                exit;
            }
            else {
                $ids = json_encode($post);
                $this->xapiproxy->log()->debug($this->msg("post: " . $ids));
                header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
                header('Access-Control-Allow-Credentials: true');
                header('X-Experience-API-Version: 1.0.3');
                header('Content-Length: ' . strlen($ids));
                header('Content-Type: application/json; charset=utf-8');
                header('HTTP/1.1 200 Ok');
                echo $ids;
                exit;
            }
        }

        public function exitResponseError() {
            header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
            header('Access-Control-Allow-Credentials: true');
            header('X-Experience-API-Version: 1.0.3');
            header("HTTP/1.1 412 Wrong Response");
            echo "HTTP/1.1 412 Wrong Response";
            exit;
        }
        
        public function exitProxyError() {
            header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
            header('Access-Control-Allow-Credentials: true');
            header('X-Experience-API-Version: 1.0.3');
            header("HTTP/1.1 500 XapiProxy Error (Ask For Logs)");
            echo "HTTP/1.1 500 XapiProxy Error (Ask For Logs)";
            exit;
        }

        public function sendData($obj) {
            $this->xapiproxy->log()->debug($this->msg("senData: " . $obj));
            header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
            header('Access-Control-Allow-Credentials: true');
            header('X-Experience-API-Version: 1.0.3');
            header('Content-Length: ' . strlen($obj));
            header('Content-Type: application/json; charset=utf-8');
            header('HTTP/1.1 200 Ok');
            echo $obj;
            exit;
        }

        public function emit($response) {
            $this->xapiproxy->log()->debug($this->msg('emitting response'));
            if (headers_sent()) {
                $this->xapiproxy->log()->error($this->msg("Headers already sent!"));
                $this->exitProxyError();
            }
            if (ob_get_level() > 0 && ob_get_length() > 0) {
                $this->xapiproxy->log()->error($this->msg("Outputstream not empty!"));
                $this->exitProxyError();
            }

            $reasonPhrase = $response->getReasonPhrase();
            $statusCode   = $response->getStatusCode();
            
            // header
            foreach ($response->getHeaders() as $header => $values) {
                $name  = ucwords($header, '-');
                $first = $name === 'Set-Cookie' ? false : true;
                foreach ($values as $value) {
                    header(sprintf(
                        '%s: %s',
                        $name,
                        $value
                    ), $first, $statusCode);
                    $first = false;
                }
            }

            // statusline
            header(sprintf(
                'HTTP/%s %d%s',
                $response->getProtocolVersion(),
                $statusCode,
                ($reasonPhrase ? ' ' . $reasonPhrase : '')
            ), true, $statusCode);
            
            // body
            echo $response->getBody();
        }

        private function msg($msg) {
            return $this->xapiproxy->msg($msg);
        }
    }
?>