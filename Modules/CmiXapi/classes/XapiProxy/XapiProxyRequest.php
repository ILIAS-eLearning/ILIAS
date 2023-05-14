<?php

declare(strict_types=1);

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

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use ILIAS\DI\Container;

class XapiProxyRequest
{
    private Container $dic;

    private XapiProxy $xapiproxy;

    private XapiProxyResponse $xapiProxyResponse;

    private string $cmdPart2plus = "";
    private bool $checkGetStatements = true;

    public function __construct(XapiProxy $xapiproxy)
    {
        $this->dic = $GLOBALS['DIC'];
        $this->xapiproxy = $xapiproxy;
        $this->cmdPart2plus = "";
    }

    public function handle(): void
    {
        $this->xapiProxyResponse = $this->xapiproxy->getXapiProxyResponse();
        $request = $this->dic->http()->request();
        $cmdParts = $this->xapiproxy->cmdParts();
        $this->xapiproxy->log()->debug($this->msg(var_export($cmdParts, true)));
        if (count($cmdParts) === 5) {
            $cmd = $cmdParts[3];
            if ($cmd === "statements") {
                $this->handleStatementsRequest($request);
            } elseif ($cmd === "activities") {
                $this->handleActivitiesRequest($request);
            } elseif ($cmd === "activities/profile") {
                $this->handleActivitiesProfileRequest($request);
            } elseif ($cmd === "activities/state") {
                $this->handleActivitiesStateRequest($request);
            } elseif ($cmd === "agents") {
                $this->handleAgentsRequest($request);
            } elseif ($cmd === "agents/profile") {
                $this->handleAgentsProfileRequest($request);
            } elseif ($cmd === "about") {
                $this->handleAboutRequest($request);
            } else {
                $this->xapiproxy->log()->debug($this->msg("Wrong xApi Query: " . $request->getUri()));
                $this->xapiProxyResponse->exitBadRequest();
            }
        } else {
            $this->xapiproxy->log()->error($this->msg("Wrong xApi Query: " . $request->getUri()));
            $this->xapiProxyResponse->exitBadRequest();
        }
    }

    private function msg(string $msg): string
    {
        return $this->xapiproxy->msg($msg);
    }

    private function handleStatementsRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("handleStatementsRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $method = $this->xapiproxy->method();
        if ($method === "post" || $method === "put") {
            $this->handlePostPutStatementsRequest($request);
        } elseif ($method === "get") {
            $this->handleGetStatementsRequest($request);
        } else {
            $this->xapiProxyResponse->exitBadRequest();
        }
    }

    private function handleGetStatementsRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        if ($this->xapiproxy->cmdParts()[4] == "") {
            $this->xapiproxy->log()->warning($this->msg("unfiltered get statements requests are not allowed for security reasons"));
            $this->xapiProxyResponse->exitBadRequest();
        }
        $this->xapiproxy->log()->debug($this->msg("handleGetStatementsRequest: " . $request->getUri()));

        try {
            $badRequest = false;
            if ($this->checkGetStatements) {
                $authToken = \ilCmiXapiAuthToken::getInstanceByToken($this->xapiproxy->token());
                $obj = \ilObjCmiXapi::getInstance($authToken->getRefId(), true);
                $access = \ilCmiXapiAccess::getInstance($obj);
                $params = $this->dic->http()->wrapper()->query();
                if ($params->has('statementId')) {
                    $this->xapiproxy->log()->debug($this->msg("single statementId requests can not be secured. It is not allowed to append any additional parameter like registration or activity (tested in LL7)"));
                    // single statementId can not be handled. it is not allowed to append a registration on single statement requests (tested in LL7)
                    $this->handleProxy($request);
                } else {
                    if ($params->has('activity')) {
                        // ToDo: how this can be verified? the object only knows the top activityId
                        $this->handleProxy($request);
                    } else {
                        $this->xapiproxy->log()->debug($this->msg("add activity: " . $obj->getActivityId()));
                        $this->cmdPart2plus .= "&activity=" . $obj->getActivityId() . "&related_activities=true";
                    }
                    if (!$access->hasOutcomesAccess($authToken->getUsrId())) {
                        // ToCheck
                        /*
                        if (!$access->hasStatementsAccess()) {
                            $this->xapiproxy->log()->warning($this->msg("statements access is not enabled"));
                            $this->xapiProxyResponse->exitBadRequest();
                        }
                        */
                        if ($obj->getContentType() == \ilObjCmiXapi::CONT_TYPE_CMI5) {
                            $regUserObject = \ilCmiXapiUser::getCMI5RegistrationFromAuthToken($authToken);
                        } else {
                            $regUserObject = \ilCmiXapiUser::getRegistrationFromAuthToken($authToken);
                        }
                        if ($params->has('registration')) {
                            $regParam = $params->retrieve('registration', $this->dic->refinery()->kindlyTo()->string());
                            if ($regParam != $regUserObject) {
                                $this->xapiproxy->log()->debug($this->msg("wrong registration: " . $regParam . " != " . $regUserObject));
                                $badRequest = true;
                            }
                        } else { // add registration
                            $this->xapiproxy->log()->debug($this->msg("add registration: " . $regUserObject));
                            $this->cmdPart2plus .= "&registration=" . $regUserObject;
                        }
                    }
                }
            }
            if ($badRequest) {
                $this->xapiProxyResponse->exitBadRequest();
            } else {
                $this->handleProxy($request);
            }
        } catch (\Exception $e) {
            $this->xapiproxy->log()->error($this->msg($e->getMessage()));
        }
    }

    private function handlePostPutStatementsRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("handlePostPutStatementsRequest: " . $request->getUri()));
        $body = $request->getBody()->getContents();
        $fakePostBody = null;
        if (empty($body)) {
            $this->xapiproxy->log()->warning($this->msg("empty body in handlePostPutRequest"));
            $this->handleProxy($request);
        } else {
            try {
                $this->xapiproxy->log()->debug($this->msg("process statements"));
                $retArr = $this->xapiproxy->processStatements($request, $body);
                if (is_array($retArr)) {
                    $body = json_encode($retArr[0]); // new body with allowed statements
                    $fakePostBody = $retArr[1]; // fake post php array of ALL statments as if all statements were processed
                }
            } catch (\Exception $e) {
                $this->xapiproxy->log()->error($this->msg($e->getMessage()));
                $this->xapiProxyResponse->exitProxyError();
            }
            try {
                $body = $this->xapiproxy->modifyBody($body);
                $req = new Request($request->getMethod(), $request->getUri(), $request->getHeaders(), $body);
                $this->handleProxy($req, $fakePostBody);
            } catch (\Exception $e) {
                $this->xapiproxy->log()->error($this->msg($e->getMessage()));
                $this->handleProxy($request, $fakePostBody);
            }
        }
    }

    private function handleActivitiesRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        //        $this->xapiproxy->log()->debug($this->msg("blocked handleActivitiesRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        //        $this->xapiProxyResponse->exitBadRequest();
        $this->xapiproxy->log()->debug($this->msg("handleActivitiesRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $this->handleProxy($request);
    }

    private function handleActivitiesProfileRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("handleActivitiesProfileRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $this->handleProxy($request);
    }

    private function handleActivitiesStateRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("handleActivitiesStateRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $this->handleProxy($request);
    }

    private function handleAgentsRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("blocked handleAgentsRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $this->xapiProxyResponse->exitBadRequest();
    }

    private function handleAgentsProfileRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("handleAgentsProfileRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $this->handleProxy($request);
    }

    private function handleAboutRequest(\Psr\Http\Message\RequestInterface $request): void
    {
        $this->xapiproxy->log()->debug($this->msg("handleAboutRequest (" . $this->xapiproxy->method() . "): " . $request->getUri()));
        $this->handleProxy($request);
    }

    private function handleProxy(\Psr\Http\Message\RequestInterface $request, $fakePostBody = null): void
    {
        $endpointDefault = $this->xapiproxy->getDefaultLrsEndpoint();
        $endpointFallback = $this->xapiproxy->getFallbackLrsEndpoint();

        $this->xapiproxy->log()->debug($this->msg("endpointDefault: " . $endpointDefault));
        $this->xapiproxy->log()->debug($this->msg("endpointFallback: " . $endpointFallback));

        $keyDefault = $this->xapiproxy->getDefaultLrsKey();
        $secretDefault = $this->xapiproxy->getDefaultLrsSecret();
        $authDefault = 'Basic ' . base64_encode($keyDefault . ':' . $secretDefault);

        $hasFallback = ($endpointFallback === "") ? false : true;

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
        $cmd = $this->xapiproxy->cmdParts()[2] . $this->cmdPart2plus;
        $upstreamDefault = $endpointDefault . $cmd;
        $uriDefault = new Uri($upstreamDefault);
        $body = $request->getBody()->getContents();
        $reqDefault = $this->createProxyRequest($request, $uriDefault, $authDefault, $body);

        if ($hasFallback) {
            $upstreamFallback = $endpointFallback . $cmd;
            $uriFallback = new Uri($upstreamFallback);
            $reqFallback = $this->createProxyRequest($request, $uriFallback, $authFallback, $body);
        }

        $httpclient = new Client();
        if ($hasFallback) {
            $promises = [
                'default' => $httpclient->sendAsync($reqDefault, $req_opts),
                'fallback' => $httpclient->sendAsync($reqFallback, $req_opts)
            ];

            // this would throw first ConnectionException
            // $responses = Promise\unwrap($promises);
            try {
                $responses = Promise\Utils::settle($promises)->wait();
            } catch (\Exception $e) {
                $this->xapiproxy->log()->error($this->msg($e->getMessage()));
            }

            $defaultOk = $this->xapiProxyResponse->checkResponse($responses['default'], $endpointDefault);
            $fallbackOk = $this->xapiProxyResponse->checkResponse($responses['fallback'], $endpointFallback);

            if ($defaultOk) {
                try {
                    $this->xapiProxyResponse->handleResponse(
                        $reqDefault,
                        $responses['default']['value'],
                        $fakePostBody
                    );
                } catch (\Exception $e) {
                    //                    $this->xapiproxy->error($this->msg("XAPI exception from Default LRS: " . $endpointDefault . " (sent HTTP 500 to client): " . $e->getMessage()));
                    $this->xapiProxyResponse->exitProxyError();
                }
            } elseif ($fallbackOk) {
                try {
                    $this->xapiProxyResponse->handleResponse(
                        $reqFallback,
                        $responses['fallback']['value'],
                        $fakePostBody
                    );
                } catch (\Exception $e) {
                    //                    $this->xapiproxy->error($this->msg("XAPI exception from Default LRS: " . $endpointDefault . " (sent HTTP 500 to client): " . $e->getMessage()));
                    $this->xapiProxyResponse->exitProxyError();
                }
            } else {
                $this->xapiProxyResponse->exitResponseError();
            }
        } else {
            $promises = [
                'default' => $httpclient->sendAsync($reqDefault, $req_opts)
            ];
            // this would throw first ConnectionException
            // $responses = Promise\unwrap($promises);
            try {
                $responses = Promise\Utils::settle($promises)->wait();
            } catch (\Exception $e) {
                $this->xapiproxy->log()->error($this->msg($e->getMessage()));
            }
            if ($this->xapiProxyResponse->checkResponse($responses['default'], $endpointDefault)) {
                try {
                    $this->xapiProxyResponse->handleResponse(
                        $reqDefault,
                        $responses['default']['value'],
                        $fakePostBody
                    );
                } catch (\Exception $e) {
                    //                    $this->xapiproxy->error($this->msg("XAPI exception from Default LRS: " . $endpointDefault . " (sent HTTP 500 to client): " . $e->getMessage()));
                    $this->xapiProxyResponse->exitProxyError();
                }
            } else {
                $this->xapiProxyResponse->exitResponseError();
            }
        }
    }

    private function createProxyRequest(\Psr\Http\Message\RequestInterface $request, \GuzzleHttp\Psr7\Uri $uri, string $auth, string $body): \GuzzleHttp\Psr7\Request
    {
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
            } elseif ($contentLength === '') {
                $contentLength = array(0);
            }
            $headers['Content-Length'] = $contentLength;
        }

        if ($request->hasHeader('Connection')) {
            $headers['Connection'] = $request->getHeader('Connection');
        }

        //$this->xapiproxy->log()->debug($this->msg($body));

        $req = new Request(strtoupper($request->getMethod()), $uri, $headers, $body);

        return $req;
    }
}
