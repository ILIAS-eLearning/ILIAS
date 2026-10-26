<?php

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

declare(strict_types=1);

use ceLTIc\LTI\OAuth\OAuthRequest;
use ceLTIc\LTI\OAuth\OAuthServer;
use ceLTIc\LTI\OAuth\OAuthSignatureMethod_HMAC_SHA1;
use ceLTIc\LTI\OAuth\OAuthUtil;
use ceLTIc\LTI\OAuthDataStore;
use ceLTIc\LTI\Platform;

/**
 * Basic Outcomes service of LTI 1.1: reads, replaces and deletes the result of an object from the POX
 * requests of a tool, after checking their OAuth1 signature.
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIConsumerResultService
{
    protected ?ilLTI1p1ConsumerResult $result = null;

    /**
     * @var integer
     */
    protected int $availability = 0;

    /**
     * @var float
     */
    protected float $mastery_score = 1;

    /**
     * @var Array fields: name => value
     */
    protected array $fields = [];

    /**
     * @var string the message reference id
     */
    protected string $message_ref_id = '';
    /**
     * @var string  the requested operation
     */
    protected string $operation = '';

    /**
     * @var array{int, string, string}|null the answer sent once the request is handled: status, content type, body
     */
    private ?array $response = null;


    public function getMasteryScore(): float
    {
        return $this->mastery_score;
    }

    public function setMasteryScore(float $mastery_score): void
    {
        $this->mastery_score = $mastery_score;
    }

    public function setAvailability(int $availability): void
    {
        $this->availability = $availability;
    }

    public function isAvailable(): bool
    {
        if ($this->availability === ilLTITool::AVAILABILITY_NONE) {
            return false;
        }
        return true;
    }

    /**
     * Handle an incoming request from the LTI tool provider and end it with the answer
     */
    public function handleRequest(): never
    {
        $this->processRequest();

        ilLTIResponse::send(...($this->response ?? [400, ilLTIResponse::CONTENT_TYPE_TEXT, '']));
    }

    private function processRequest(): void
    {
        try {

            global $DIC;
            $logger = $DIC->logger()->forComponent('lti');
            // get the request as xml; the raw body is signed through oauth_body_hash
            $raw_body = (string) file_get_contents('php://input');
            $xml = simplexml_load_string($raw_body);
            $this->message_ref_id = (string) $xml->imsx_POXHeader->imsx_POXRequestHeaderInfo->imsx_messageIdentifier;
            $children = (array) $xml->imsx_POXBody->children();
            $request = current($children);

            $ns = $xml->getNamespaces(true);
            $body = $xml->children($ns[''])->imsx_POXBody;

            $this->operation = str_replace('Request', '', $request->getName());

            $request = $body->{$this->operation . 'Request'};
            $token = ilCmiXapiAuthToken::getInstanceByToken((string) $request->resultRecord->sourcedGUID->sourcedId);
            $logger->debug('LTI Basic Outcomes request {operation} for object {obj_id} and user {usr_id}', [
                'operation' => $this->operation,
                'obj_id' => $token->getObjId(),
                'usr_id' => $token->getUsrId(),
            ]);

            $this->result = ilLTI1p1ConsumerResult::getByKeys($token->getObjId(), $token->getUsrId());
            if ($this->result === null) {
                $logger->warning('LTI Basic Outcomes request refused: the object {obj_id} has no result of the user {usr_id}', [
                    'obj_id' => $token->getObjId(),
                    'usr_id' => $token->getUsrId(),
                ]);
                $this->respondUnauthorized("lti_consumer_results_id not found!");
                return;
            }


            // check the object status
            $this->readProperties($this->result->obj_id);

            if (!$this->isAvailable()) {
                $logger->warning('LTI Basic Outcomes request refused: the tool of the object {obj_id} is not available', [
                    'obj_id' => $this->result->obj_id,
                ]);
                $this->respondUnsupported();
                return;
            }

            // Verify the signature
            $this->readFields($this->result->obj_id);
            try {
                $this->checkSignature($this->fields['KEY'], $this->fields['SECRET'], $raw_body);
            } catch (Exception $e) {
                $logger->warning('LTI Basic Outcomes request for the object {obj_id} refused: {reason}', [
                    'obj_id' => $this->result->obj_id,
                    'reason' => $e->getMessage(),
                ]);
                $this->respondUnauthorized();
                return;
            }

            // Dispatch the operation
            switch ($this->operation) {
                case 'readResult':
                    $this->readResult();
                    break;

                case 'replaceResult':
                    $this->replaceResult($request);
                    $this->updateLP();
                    break;

                case 'deleteResult':
                    $this->deleteResult();
                    $this->updateLP();
                    break;

                default:
                    $logger->warning('LTI Basic Outcomes request refused: unknown operation {operation}', [
                        'operation' => $this->operation,
                    ]);
                    $this->respondUnknown();
                    break;
            }
        } catch (Exception $exception) {
            $DIC->logger()->forComponent('lti')->warning('LTI Basic Outcomes request refused: ' . $exception->getMessage());
            // an operation already answered keeps its answer, only the learning progress failed after it
            if ($this->response === null) {
                $this->respondBadRequest($exception->getMessage());
            }
        }
    }

    /**
     * Read a stored result
     */
    protected function readResult(): void
    {
        $this->respond('readResult.xml', ['{result}' => (string) $this->result->result]);
    }

    /**
     * Replace a stored result
     */
    protected function replaceResult(SimpleXMLElement $request): void
    {
        global $DIC;
        $logger = $DIC->logger()->forComponent('lti');

        $result = (string) $request->resultRecord->result->resultScore->textString;
        if (!is_numeric($result)) {
            $code = "failure";
            $severity = "status";
            $description = "The result is not a number.";
        } elseif ($result > 1) {
            $code = "failure";
            $severity = "status";
            $description = "The result is out of range from 0 to 1.";
        } else {
            $this->result->result = (float) $result;
            $this->result->setAttended(true);
            $this->result->save();

            $code = "success";
            $severity = "status";
            $description = sprintf("Score for %s is now %s", $this->result->id, $this->result->result);
        }
        $logger->info('LTI Basic Outcomes result of the user {usr_id} in the object {obj_id}: {result} ({code})', [
            'usr_id' => $this->result->usr_id,
            'obj_id' => $this->result->obj_id,
            'result' => $result,
            'code' => $code,
        ]);

        $this->respond('replaceResult.xml', [
            '{code}' => $code,
            '{severity}' => $severity,
            '{description}' => $description,
        ]);
    }

    /**
     * Delete a stored result
     */
    protected function deleteResult(): void
    {
        $this->result->result = null;
        $this->result->setAttended(false);
        $this->result->save();

        global $DIC;
        $DIC->logger()->forComponent('lti')->info('LTI Basic Outcomes result of the user {usr_id} in the object {obj_id} deleted', [
            'usr_id' => $this->result->usr_id,
            'obj_id' => $this->result->obj_id,
        ]);
        $code = "success";
        $severity = "status";

        $this->respond('deleteResult.xml', ['{code}' => $code, '{severity}' => $severity]);
    }


    /**
     * Load the XML template for the response
     */
    protected function loadResponse(string $a_name): string
    {
        return file_get_contents(__DIR__ . '/responses/' . $a_name);
    }

    /**
     * Answers with a response template, with the placeholders every response shares and the given ones replaced.
     *
     * @param string $a_name
     * @param array $replacements
     */
    private function respond(string $a_name, array $replacements = []): void
    {
        $replacements = array_merge([
            '{message_id}' => bin2hex(random_bytes(16)),
            '{message_ref_id}' => $this->message_ref_id,
            '{operation}' => $this->operation,
        ], $replacements);

        $this->response = [
            200,
            ilLTIResponse::CONTENT_TYPE_XML,
            str_replace(array_keys($replacements), array_values($replacements), $this->loadResponse($a_name)),
        ];
    }


    /**
     * Send a response that the operation is not supported
     * This depends on the status of the object
     */
    protected function respondUnsupported(): void
    {
        $this->respond('unsupported.xml');
    }

    /**
     * Send an "unknown operation" response
     */
    protected function respondUnknown(): void
    {
        $this->respond('unknown.xml');
    }

    /**
     * Send a "bad request" response
     */
    protected function respondBadRequest(?string $message = null): void
    {
        $this->response = [
            400,
            ilLTIResponse::CONTENT_TYPE_TEXT,
            $message ?? 'This is not a well-formed LTI Basic Outcomes Service request.',
        ];
    }

    /**
     * Send an "unauthorized" response
     * @param string|null $message  response message
     */
    protected function respondUnauthorized(?string $message = null): void
    {
        $this->response = [
            401,
            ilLTIResponse::CONTENT_TYPE_TEXT,
            $message ?? 'This request could not be authorized.',
        ];
    }

    /**
     * Read the LTI Consumer object properties
     */
    public function readProperties(int $a_obj_id): void
    {
        global $DIC;

        $query = "
			SELECT lti_ext_provider.availability, lti_consumer_settings.mastery_score
			FROM lti_ext_provider, lti_consumer_settings
			WHERE lti_ext_provider.id = lti_consumer_settings.provider_id
			AND lti_consumer_settings.obj_id = %s
		";

        $res = $DIC->database()->queryF($query, ['integer'], [$a_obj_id]);

        if ($row = $DIC->database()->fetchAssoc($res)) {
            $this->setAvailability((int) $row['availability']);
            $this->setMasteryScore((float) $row['mastery_score']);
        }
    }

    /**
     * Read the LTI Consumer object fields
     */
    private function readFields(int $a_obj_id): void
    {
        global $DIC;

        $query = "
			SELECT lti_ext_provider.provider_key, lti_ext_provider.provider_secret, lti_consumer_settings.launch_key, lti_consumer_settings.launch_secret
                FROM lti_ext_provider, lti_consumer_settings
			WHERE lti_ext_provider.id = lti_consumer_settings.provider_id
			AND lti_consumer_settings.obj_id = %s
		";

        $res = $DIC->database()->queryF($query, ['integer'], [$a_obj_id]);

        while ($row = $DIC->database()->fetchAssoc($res)) {
            if (strlen($row["launch_key"]) > 0) {
                $this->fields["KEY"] = $row["launch_key"];
            } else {
                $this->fields["KEY"] = $row["provider_key"];
            }
            if (strlen($row["launch_key"]) > 0) {
                $this->fields["SECRET"] = $row["launch_secret"];
            } else {
                $this->fields["SECRET"] = $row["provider_secret"];
            }
        }
    }

    /**
     * Check the request signature, its nonce and the hash of its body
     * @throws Exception in case of failure
     */
    private function checkSignature(string $a_key, string $a_secret, string $raw_body): void
    {
        // the nonces of the requests are kept per object, so that a request cannot be replayed
        $platform = new Platform(new ilLTIDataConnector());

        $platform->setKey($a_key);
        $platform->secret = $a_secret;
        $platform->setRecordId($this->result->obj_id);

        $store = new OAuthDataStore($platform);

        $server = new OAuthServer($store);
        $method = new OAuthSignatureMethod_HMAC_SHA1();

        $server->add_signature_method($method);

        $request_headers = OAuthUtil::get_headers();
        if (isset($request_headers['Authorization']) && str_starts_with($request_headers['Authorization'], 'OAuth ')) {
            $parameters = OAuthUtil::split_header($request_headers['Authorization']);
        }
        $request = OAuthRequest::from_request(null, null, $parameters ?? []);
        $server->verify_request($request);

        // the body hash is one of the signed parameters of the header
        $body_hash = (string) $request->get_parameter('oauth_body_hash');
        if ($body_hash === '' || !hash_equals(base64_encode(sha1($raw_body, true)), $body_hash)) {
            throw new Exception('Missing or invalid oauth_body_hash');
        }
    }

    protected function updateLP(): void
    {
        if (!($this->result instanceof ilLTI1p1ConsumerResult)) {
            return;
        }

        ilLPStatusWrapper::_updateStatus($this->result->getObjId(), $this->result->getUsrId());
    }
}
