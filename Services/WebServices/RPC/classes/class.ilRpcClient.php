<?php
include_once './Services/WebServices/RPC/classes/class.ilRpcClientException.php';
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilRpcClient
 *
 * @author Fabian Wolf <wolf@leifos.com>
 * @ingroup ServicesWebServicesRPC
 *
 * List of all known RPC methods...
 *
 * RPCIndexHandler:
 * @method void index() index(string $client, bool $bool) Prefix/Package: RPCIndexHandler
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
    /** @var string */
    protected $url;
    /** @var string */
    protected $prefix = '';
    /** @var int */
    protected $timeout = 0;
    /** @var string */
    protected $encoding = '';

    /**
     * ilRpcClient constructor.
     * @param string $a_url URL to connect to
     * @param string $a_prefix Optional prefix for method names
     * @param int $a_timeout The maximum number of seconds to allow ilRpcClient to connect.
     * @param string $a_encoding Character encoding
     * @throws ilRpcClientException
     */
    public function __construct($a_url, $a_prefix = '', $a_timeout = 0, $a_encoding = 'utf-8')
    {
        if (!extension_loaded('xmlrpc')) {
            ilLoggerFactory::getLogger('wsrv')->error('RpcClient Xmlrpc extension not enabled');
            throw new ilRpcClientException('Xmlrpc extension not enabled.', 50);
        }

        $this->url = (string) $a_url;
        $this->prefix = (string) $a_prefix;
        $this->timeout = (int) $a_timeout;
        $this->encoding = (string) $a_encoding;
    }

    /**
     * Magic caller to all RPC functions
     *
     * @param string $a_method Method name
     * @param array $a_params Argument array
     * @return mixed Returns either an array, or an integer, or a string, or a boolean according to the response returned by the XMLRPC method.
     * @throws ilRpcClientException
     */
    public function __call($a_method, $a_params)
    {
        //prepare xml post data
        $method_name = str_replace('_', '.', $this->prefix . $a_method);
        $rpc_options = array(
            'verbosity'=>'newlines_only',
            'escaping' => 'markup'
        );

        if ($this->encoding) {
            $rpc_options['encoding'] = $this->encoding;
        }

        $post_data = xmlrpc_encode_request($method_name, $a_params, $rpc_options);

        //try to connect to the given url
        try {
            include_once './Services/WebServices/Curl/classes/class.ilCurlConnection.php';
            $curl = new ilCurlConnection($this->url);
            $curl->init();
            $curl->setOpt(CURLOPT_HEADER, 'Content-Type: text/xml');
            $curl->setOpt(CURLOPT_POST, (strlen($post_data) > 0));
            $curl->setOpt(CURLOPT_POSTFIELDS, $post_data);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);

            if ($this->timeout > 0) {
                $curl->setOpt(CURLOPT_TIMEOUT, $this->timeout);
            }
            ilLoggerFactory::getLogger('wsrv')->info('RpcClient request to ' . $this->url . ' / ' . $method_name);
            $xml_resp = $curl->exec();
        } catch (ilCurlConnectionException $e) {
            ilLoggerFactory::getLogger('wsrv')->error('RpcClient could not connect to ' . $this->url . ' Reason ' . $e->getCode() . ': ' . $e->getMessage());
            throw new ilRpcClientException($e->getMessage(), $e->getCode());
        }

        //prepare output, throw exception if rpc fault is detected
        $resp = xmlrpc_decode($xml_resp, $this->encoding);

        //xmlrpc_is_fault can just handle arrays as response
        if (is_array($resp)&& xmlrpc_is_fault($resp)) {
            ilLoggerFactory::getLogger('wsrv')->error('RpcClient recieved error ' . $resp['faultCode'] . ': ' . $resp['faultString']);
            include_once './Services/WebServices/RPC/classes/class.ilRpcClientException.php';
            throw new ilRpcClientException('RPC-Server returned fault message: ' . $resp['faultString'], $resp['faultCode']);
        }

        return $resp;
    }
}
