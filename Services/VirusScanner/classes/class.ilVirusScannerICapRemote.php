<?php
//hsuhh-patch: begin
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface to the ClamAV virus protector
 * @author        Ralf Schenk <rs@databay.de>
 * @version       $Id$
 * @extends       ilVirusScanner
 */


require_once "./Services/VirusScanner/classes/class.ilVirusScanner.php";

class ilVirusScannerICapRemote extends ilVirusScanner
{
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var resource */
    private $socket;
    /** @var string */
    public $userAgent = 'PHP-CLIENT/0.1.0';

    /**
     * ilVirusScannerICap constructor.
     * @param $a_scancommand
     * @param $a_cleancommand
     */
    public function __construct($a_scancommand, $a_cleancommand)
    {
        parent::__construct($a_scancommand, $a_cleancommand);
        $this->host = IL_ICAP_HOST;
        $this->port = IL_ICAP_PORT;
    }

    /**
     * @param $service
     * @return array
     */
    public function options($service)
    {
        $request  = $this->getRequest('OPTIONS', $service);
        $response = $this->send($request);
        if(strlen($response) > 0) {
            return $this->parseResponse($response);
        }
        return [];
    }

    /**
     * @param       $method
     * @param       $service
     * @param array $body
     * @param array $headers
     * @return string
     */
    public function getRequest($method, $service, $body = [], $headers = [])
    {
        if (!array_key_exists('Host', $headers)) {
            $headers['Host'] = $this->host;
        }
        if (!array_key_exists('User-Agent', $headers)) {
            $headers['User-Agent'] = $this->userAgent;
        }
        if (!array_key_exists('Connection', $headers)) {
            $headers['Connection'] = 'close';
        }
        $bodyData     = '';
        $hasBody      = false;
        $encapsulated = [];
        foreach ($body as $type => $data) {
            switch ($type) {
                case 'req-hdr':
                case 'res-hdr':
                    $encapsulated[$type] = strlen($bodyData);
                    $bodyData            .= $data;
                    break;
                case 'req-body':
                case 'res-body':
                    $encapsulated[$type] = strlen($bodyData);
                    $bodyData            .= dechex(strlen($data)) . "\r\n";
                    $bodyData            .= $data;
                    $bodyData            .= "\r\n";
                    $hasBody             = true;
                    break;
            }
        }
        if ($hasBody) {
            $bodyData .= "0\r\n\r\n";
        } elseif (count($encapsulated) > 0) {
            $encapsulated['null-body'] = strlen($bodyData);
        }
        if (count($encapsulated) > 0) {
            $headers['Encapsulated'] = '';
            foreach ($encapsulated as $section => $offset) {
                $headers['Encapsulated'] .= $headers['Encapsulated'] === '' ? '' : ', ';
                $headers['Encapsulated'] .= "{$section}={$offset}";
            }
        }
        $request = "{$method} icap://{$this->host}/{$service} ICAP/1.0\r\n";
        foreach ($headers as $header => $value) {
            $request .= "{$header}: {$value}\r\n";
        }
        $request .= "\r\n";
        $request .= $bodyData;
        return $request;
    }

    /**
     * @param $request
     * @return string
     */
    public function send($request)
    {
        $response = '';
        try{
            $this->connect();
            socket_write($this->socket, $request);
            while ($buffer = socket_read($this->socket, 2048)) {
                $response .= $buffer;
            }
            $this->disconnect();
        } catch(ErrorException $e){
            $this->log->warning("Cannot connect to icap://{$this->host}:{$this->port} (Socket error: " . $this->getLastSocketError() . ")");
        }
        return $response;
    }

    /**
     * @return bool
     * @throws ErrorException
     */
    private function connect()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        try{
            if (!socket_connect($this->socket, $this->host, $this->port)) {
                return false;
            }
        } catch(ErrorException $e){
            throw $e;
        }

        return true;
    }

    /**
     * Get last error code from socket object
     * @return int Socket error code
     */
    public function getLastSocketError()
    {
        return socket_last_error($this->socket);
    }

    /**
     *
     */
    private function disconnect()
    {
        socket_shutdown($this->socket);
        socket_close($this->socket);
    }

    /**
     * @param $string
     * @return array
     */
    private function parseResponse($string)
    {
        $response = [
            'protocol' => [],
            'headers'  => [],
            'body'     => [],
            'rawBody'  => ''
        ];
        foreach (preg_split('/\r?\n/', $string) as $line) {
            if ([] === $response['protocol']) {
                if (0 !== strpos($line, 'ICAP/')) {
                    throw new RuntimeException('Unknown ICAP response');
                }
                $parts                = preg_split('/ +/', $line, 3);
                $response['protocol'] = [
                    'icap'    => isset($parts[0]) ? $parts[0] : '',
                    'code'    => isset($parts[1]) ? $parts[1] : '',
                    'message' => isset($parts[2]) ? $parts[2] : '',
                ];
                continue;
            }
            if ('' === $line) {
                break;
            }
            $parts = preg_split('/: /', $line, 2);
            if (isset($parts[0])) {
                $response['headers'][$parts[0]] = isset($parts[1]) ? $parts[1] : '';
            }
        }
        $body = preg_split('/\r?\n\r?\n/', $string, 2);
        if (isset($body[1])) {
            $response['rawBody'] = $body[1];
            if (array_key_exists('Encapsulated', $response['headers'])) {
                $encapsulated = [];
                $params       = preg_split('/, /', $response['headers']['Encapsulated']);
                if (count($params) > 0) {
                    foreach ($params as $param) {
                        $parts = preg_split('/=/', $param);
                        if (count($parts) !== 2) {
                            continue;
                        }
                        $encapsulated[$parts[0]] = $parts[1];
                    }
                }
                foreach ($encapsulated as $section => $offset) {
                    $data = substr($body[1], $offset);
                    switch ($section) {
                        case 'req-hdr':
                        case 'res-hdr':
                            $response['body'][$section] = preg_split('/\r?\n\r?\n/', $data, 2)[0];
                            break;
                        case 'req-body':
                        case 'res-body':
                            $parts = preg_split('/\r?\n/', $data, 2);
                            if (count($parts) === 2) {
                                $response['body'][$section] = substr($parts[1], 0, hexdec($parts[0]));
                            }
                            break;
                    }
                }
            }
        }
        return $response;
    }

    /**
     * @param       $service
     * @param array $body
     * @param array $headers
     * @return array
     */
    public function respMod($service, $body = [], $headers = [])
    {
        $request  = $this->getRequest('RESPMOD', $service, $body, $headers);
        $response = $this->send($request);
        return $this->parseResponse($response);
    }

    /**
     * @param       $service
     * @param array $body
     * @param array $headers
     * @return array
     */
    public function reqMod($service, $body = [], $headers = [])
    {
        $request  = $this->getRequest('REQMOD', $service, $body, $headers);
        $response = $this->send($request);
        return $this->parseResponse($response);
    }
}
//hsuhh-patch: end