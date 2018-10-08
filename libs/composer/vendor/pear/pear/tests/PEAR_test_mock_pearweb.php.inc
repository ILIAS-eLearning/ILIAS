<?php
class PEAR_test_mock_pearweb
{
    var $_config;
    var $_continue = false;

    function addHtmlConfig($address, $filename, $lastmodified = false)
    {
        $this->_config['html'][$address] = array(basename($filename), file_get_contents($filename),
            $lastmodified);
    }

    function addRESTConfig($address, $content, $content_type)
    {
        $this->_config['rest'][$address] = array($content, $content_type);
    }

    function receiveREST($address)
    {
        if (!isset($this->_config) || !is_array($this->_config)) {
            $ret = $this->tryParentREST($address);
            if (!$this->_continue) {
                die('No REST config for URL ' . $address);
            }

            if (!$ret) {
                return $this->do304($address);
            }

            return $this->do200() .
            "ETag: 12345678910\n" .
            "Last-Modified: 12345678910\n" .
            'Content-Type: ' . $ret[2]['content-type'] . "\n" .
            'Content-Length: ' . $ret[2]['content-length'] . "\n\n" .
            $ret[0];
        }

        if (!isset($this->_config['rest'][$address])) {
            $ret = $this->tryParentREST($address);
            if (!$this->_continue) {
                die('No REST config for URL ' . $address);
            }

            if (!$ret) {
                return $this->do304($address);
            }

            return $this->do200() .
            "ETag: 12345678910\n" .
            "Last-Modified: 12345678910\n" .
            'Content-Type: ' . $ret[2]['content-type'] . "\n" .
            'Content-Length: ' . $ret[2]['content-length'] . "\n\n" .
            $ret[0];
        } else {
            if (!$this->_config['rest'][$address][0]) {
                $this->_restCalls[] = array($address, '404');
                return $this->do404($address);
            }
            if ($this->_config['rest'][$address][0] === true) {
                $this->_restCalls[] = array($address, '304');
                return $this->do304($address);
            }
            $this->_restCalls[] = array($address, '200');
            return $this->do200() .
                "ETag: 12345678910\n" .
                "Last-Modified: 12345678910\n" .
                'Content-Type: ' . $this->_config['rest'][$address][1] . "\n" .
                'Content-Length: ' . strlen($this->_config['rest'][$address][0]) . "\n\n" .
                $this->_config['rest'][$address][0];
        }
    }

    function getRESTCalls()
    {
        $ret = $this->_restCalls;
        $this->_restCalls = array();
        return $ret;
    }

    function tryParentREST($address)
    {
        require_once 'PEAR/REST.php';
        $a = new PEAR_Config;
        $rest = new PEAR_REST($a, array());
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $ret = $rest->downloadHttp($address, false);
        PEAR::popErrorHandling();
        if (!PEAR::isError($ret)) {
            if (!$ret) {
                echo '$pearweb->addRESTConfig("' . $address . "\", true, 'text/xml');\n";
            } else {
                echo '$pearweb->addRESTConfig("' . $address . '", ' . var_export($ret[0], true)
                    . ", 'text/xml');\n";
            }
            return $ret;
        } else {
                echo '$pearweb->addRESTConfig("' . $address . "\", false, false);\n";
            return false;
        }
    }

    function receiveHttp($address)
    {
        if (!isset($this->_config) || !is_array($this->_config)) {
            return $this->do404($address);
        }
        if (!isset($this->_config['html'][$address])) {
            return $this->do404($address);
        } elseif (isset($this->_config['html'][$address][2])) {
            return $this->do200() .
                $this->_config['html'][$address][2] .
                'Content-Length: ' . strlen($this->_config['html'][$address][1]) . "\n\n" .
                $this->_config['html'][$address][1];
        } else {
            return $this->do200() .
                'Content-Length: ' . strlen($this->_config['html'][$address][1]) . "\n\n" .
                $this->_config['html'][$address][1];
        }
    }

    function do200()
    {
        return "HTTP/1.1 200 \n";
    }

    function do404($address)
    {
        return 'HTTP/1.1 404 ' . $address . ' Is not valid';
    }

    function do304($address)
    {
        return 'HTTP/1.1 304 ' . $address . ' Unmodified';
    }
}