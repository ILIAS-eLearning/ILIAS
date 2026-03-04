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

/**
 * @author Fabian Wolf <wolf@leifos.com>
 *
 * List of all known RPC methods...
 *
 * RPCIndexHandler:
 * @method bool index() index(string $client, bool $bool) Prefix/Package: RPCIndexHandler
 * @method void indexObjects() indexObjects(string $client, array $object_ids) Prefix/Package: RPCIndexHandler
 *
 * RPCTransformationHandler:
 * @method string ilFO2PDF() ilFO2PDF(string $fo_string) Prefix/Package: RPCTransformationHandler Return: base64
 *
 * RPCSearchHandler:
 * @method string searchMail() searchMail(string $client, int $user_id, string $query, int $mail_folder_id) Prefix/Package: RPCSearchHandler Return:xml
 * @method string highlight() highlight(string $client, array $object_ids, string $query) Prefix/Package: RPCSearchHandler Return: string
 * @method string searchUsers() searchUser(string $client, string $query) Prefix/Package: RPCSearchHandler Return: xml
 * @method string search() search(string $client, string $query, int $page_nr) Prefix/Package: RPCSearchHandler Return: xml
 *
 * Other:
 * @method void ping() ping() Prefix/Package: RPCebug
 * @method void refreshSettings() refreshSettings(string $client) Prefix/Package: RPCAdministration
 */
class ilRpcClient
{
    protected string $url;
    protected string $prefix = '';
    protected int $timeout = 0;

    protected ilLogger $logger;

    /**
     * @param string $url     URL to connect to
     * @param string $prefix  Optional prefix for method names
     * @param int    $timeout The maximum number of seconds to allow ilRpcClient to connect.
     * @throws ilRpcClientException
     */
    public function __construct(string $url, string $prefix = '', int $timeout = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->url = $url;
        $this->prefix = $prefix;
        $this->timeout = $timeout;
    }

    /**
     * @param string $method Method name
     * @param (string|int|bool|int[])[] $parameters Argument array
     * @return string|stdClass Depends on the response returned by the XMLRPC method.
     * @throws ilRpcClientException
     */
    public function __call(string $method, array $parameters): string|bool|stdClass
    {
        //prepare xml post data
        $method_name = str_replace('_', '.', $this->prefix . $method);

        $post_data = $this->encodeRequest($method_name, $parameters);

        //try to connect to the given url
        try {
            $curl = new ilCurlConnection($this->url);
            $curl->init(false);
            $curl->setOpt(CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
            $curl->setOpt(CURLOPT_POST, (strlen($post_data) > 0));
            $curl->setOpt(CURLOPT_POSTFIELDS, $post_data);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);

            if ($this->timeout > 0) {
                $curl->setOpt(CURLOPT_TIMEOUT, $this->timeout);
            }
            $this->logger->debug('RpcClient request to ' . $this->url . ' / ' . $method_name);
            $xml_response = $curl->exec();
        } catch (ilCurlConnectionException $e) {
            $this->logger->error(
                'RpcClient could not connect to ' . $this->url . ' ' .
                'Reason ' . $e->getCode() . ': ' . $e->getMessage()
            );
            throw new ilRpcClientException($e->getMessage(), $e->getCode());
        }

        //return output, throw exception if rpc fault is detected
        return $this->handleResponse($xml_response);
    }

    /**
     * @param (string|int|bool|int[])[] $parameters
     * @throws ilRpcClientException
     */
    protected function encodeRequest(string $method, array $parameters): string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $method_call = $xml->createElement('methodCall');
        $method_name = $xml->createElement('methodName', $method);
        $params = $xml->createElement('params');

        foreach ($parameters as $parameter) {
            match (true) {
                is_string($parameter) => $encoded_parameter = $this->encodeString($parameter),
                is_int($parameter) => $encoded_parameter = $this->encodeInteger($parameter),
                is_bool($parameter) => $encoded_parameter = $this->encodeBoolean($parameter),
                $this->isListOfIntegers($parameter) => $encoded_parameter = $this->encodeListOfIntegers(...$parameter),
                default => throw new ilRpcClientException(
                    'Invalid parameter type, only string, int, bool, and int[] are supported.'
                )
            };
            $params->appendChild($xml->importNode($this->wrapParameter($encoded_parameter)->documentElement, true));
        }

        $method_call->appendChild($method_name);
        $method_call->appendChild($params);

        $xml->appendChild($method_call);
        return $xml->saveXML();
    }

    protected function isListOfIntegers(mixed $parameter): bool
    {
        if (!is_array($parameter)) {
            return false;
        }
        foreach ($parameter as $entries) {
            if (!is_int($entries)) {
                return false;
            }
        }
        return true;
    }

    protected function wrapParameter(DOMDocument $encoded_parameter): DOMDocument
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $param = $xml->createElement('param');
        $value = $xml->createElement('value');

        $value->appendChild($xml->importNode($encoded_parameter->documentElement, true));
        $param->appendChild($value);

        $xml->appendChild($param);
        return $xml;
    }

    protected function encodeString(string $parameter): DOMDocument
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->appendChild($xml->createElement('string', $parameter));
        return $xml;
    }

    protected function encodeInteger(int $parameter): DOMDocument
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->appendChild($xml->createElement('int', (string) $parameter));
        return $xml;
    }

    protected function encodeBoolean(bool $parameter): DOMDocument
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->appendChild($xml->createElement('boolean', $parameter ? '1' : '0'));
        return $xml;
    }

    protected function encodeListOfIntegers(int ...$parameters): DOMDocument
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $array = $xml->createElement('array');
        $data = $xml->createElement('data');

        foreach ($parameters as $parameter) {
            $value = $xml->createElement('value');
            $value->appendChild($xml->importNode($this->encodeInteger($parameter)->documentElement, true));
            $data->appendChild($value);
        }
        $array->appendChild($data);

        $xml->appendChild($array);
        return $xml;
    }

    /**
     * Returns decoded response if not faulty, otherwise throws exception.
     * @throws ilRpcClientException
     */
    public function handleResponse(string $xml): string|bool|stdClass
    {
        $response = new DOMDocument('1.0', 'UTF-8');
        $response->preserveWhiteSpace = false;
        $response->loadXML($xml);

        if (!$response) {
            throw new ilRpcClientException('Invalid XML response');
        }

        $response_body = $response->documentElement->childNodes->item(0);

        if ($response_body === null) {
            throw new ilRpcClientException('Empty response');
        }

        $this->logger->dump($response_body);

        return match ($response_body->nodeName) {
            'params' => $this->decodeOKResponse($response_body),
            'fault' => $this->handleFaultResponse($response_body),
            default => throw new ilRpcClientException('Unexpected element in response: ' . get_class($response_body)),
        };
    }

    protected function decodeOKResponse(DOMElement $response_body): string|bool|stdClass
    {
        $param_child = $response_body->getElementsByTagName('value')->item(0)?->childNodes?->item(0);

        if ($param_child === null) {
            throw new ilRpcClientException('No value in response');
        }

        return match ($param_child->nodeName) {
            'string' => $this->decodeString($param_child),
            '#text' => $this->decodeString($param_child), // org.apache.xmlrpc returns java strings as unwrapped text node
            'base64' => $this->decodeBase64($param_child),
            'boolean' => $this->decodeBoolean($param_child),
            default => throw new ilRpcClientException('Unexpected element in response value: ' . $param_child->nodeName),
        };
    }

    protected function decodeString(DOMNode $string): string
    {
        return (string) $string->nodeValue;
    }

    protected function decodeBase64(DOMNode $base64): stdClass
    {
        return (object) base64_decode((string) $base64->nodeValue);
    }

    protected function decodeBoolean(DOMNode $boolean): bool
    {
        return (bool) $boolean->nodeValue;
    }

    /**
     * @throws ilRpcClientException
     */
    protected function handleFaultResponse(DOMElement $response_body): string
    {
        $fault_code = null;
        $fault_string = null;

        $members = $response_body->getElementsByTagName('member');
        foreach ($members as $member) {
            $name = $member->getElementsByTagName('name')->item(0)?->nodeValue;
            if ($name === 'faultCode') {
                if ($fault_code !== null) {
                    throw new ilRpcClientException('Multiple codes in fault response.');
                }
                $fault_code = (int) $member->getElementsByTagName('int')->item(0)?->nodeValue;
            }
            if ($name === 'faultString') {
                if ($fault_string !== null) {
                    throw new ilRpcClientException('Multiple strings in fault response.');
                }
                $fault_string = $member->getElementsByTagName('string')->item(0)?->nodeValue;
            }
        }

        if ($fault_code === null || $fault_string === null) {
            throw new ilRpcClientException('No code or no string in fault respsonse');
        }

        $this->logger->error('RpcClient recieved error ' . $fault_code . ': ' . $fault_string);
        throw new ilRpcClientException(
            'RPC-Server returned fault message: ' .
            $fault_string,
            $fault_code
        );
    }
}
