<?php declare(strict_types=1);

/*
  +-----------------------------------------------------------------------------+
  | ILIAS open source                                                           |
  +-----------------------------------------------------------------------------+
  | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
  |                                                                             |
  | This program is free software; you can redistribute it and/or               |
  | modify it under the terms of the GNU General Public License                 |
  | as published by the Free Software Foundation; either version 2              |
  | of the License, or (at your option) any later version.                      |
  |                                                                             |
  | This program is distributed in the hope that it will be useful,             |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
  | GNU General Public License for more details.                                |
  |                                                                             |
  | You should have received a copy of the GNU General Public License           |
  | along with this program; if not, write to the Free Software                 |
  | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
  +-----------------------------------------------------------------------------+
 */

/**
 * Wrapper for php's curl functions
 *
 * @defgroup ServicesWebServicesCurl Services/WebServices/Curl
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesWebServicesCurl
 */
class ilCurlConnection
{
    protected string $url = '';

    /** @var CurlHandle|resource|null $ch */
    protected $ch = null;
    private string $header_plain = '';
    private array $header_arr = array();
    private string $response_body = '';

    public function __construct(string $a_url = '')
    {
        $this->url = $a_url;

        if (!self::_isCurlExtensionLoaded()) {
            throw new ilCurlConnectionException('Curl extension not enabled.');
        }
    }

    final public static function _isCurlExtensionLoaded() : bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }
        return true;
    }

    public function getResponseHeader() : string
    {
        return $this->header_plain;
    }

    public function getResponseHeaderArray() : array
    {
        return $this->header_arr;
    }

    final public function init(bool $set_proxy = true) : bool
    {
        // teminate existing handles
        $this->close();
        if ($this->url !== '') {
            $this->ch = curl_init($this->url);
        } else {
            $this->ch = curl_init();
        }
        if (!$this->ch) {
            throw new ilCurlConnectionException('Cannot init curl connection.');
        }
        if (curl_errno($this->ch)) {
            throw new ilCurlConnectionException(curl_error($this->ch), curl_errno($this->ch));
        }

        if ($set_proxy) {
            // use a proxy, if configured by ILIAS
            $proxy = ilProxySettings::_getInstance();
            if ($proxy->isActive()) {
                $this->setOpt(CURLOPT_HTTPPROXYTUNNEL, true);

                if (!empty($proxy->getHost())) {
                    $this->setOpt(CURLOPT_PROXY, $proxy->getHost());
                } else {
                    throw new ilCurlConnectionException("Proxy host is not set on activated proxy");
                }
                if (!empty($proxy->getPort())) {
                    $this->setOpt(CURLOPT_PROXYPORT, $proxy->getPort());
                }
            }
        }
        return true;
    }

    /**
     * Wrapper for curl_setopt
     * @param int $a_option
     * @param mixed $a_value
     * @return bool
     */
    final public function setOpt(int $a_option, $a_value) : bool
    {
        if (!curl_setopt($this->ch, $a_option, $a_value)) {
            throw new ilCurlConnectionException('Invalid option given for: ' . $a_option, curl_errno($this->ch));
        }
        return true;
    }

    /**
     * Wrapper for curl_exec
     * @return bool|string
     */
    final public function exec()
    {
        // Add header function
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'parseHeader'));
        if (($res = curl_exec($this->ch)) === false) {
            if (($err = curl_error($this->ch)) !== '') {
                throw new ilCurlConnectionException($err, curl_errno($this->ch));
            }

            throw new ilCurlConnectionException('Error calling curl_exec().');
        }
        return $res;
    }

    public function parseResponse(string $a_response) : void
    {
        $header_size = $this->getInfo(CURLINFO_HEADER_SIZE);

        $this->header_plain = substr($a_response, 0, $header_size);
        $this->response_body = substr($a_response, $header_size);
    }

    public function getResponseBody() : string
    {
        return $this->response_body;
    }

    /**
     * Get information about a specific transfer
     *
     * @param int option e.g CURLINFO_EFFECTIVE_URL
     * @return mixed
     *
     */
    public function getInfo($opt = 0)
    {
        if ($opt) {
            $res = curl_getinfo($this->ch, $opt);
        } else {
            $res = curl_getinfo($this->ch);
        }
        return $res;
    }

    /**
     * Parse respone header
     * @param mixed $handle
     * @param string $header
     * @return int strlen of header
     */
    private function parseHeader($handle, string $header) : int
    {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) === 2) { // ignore invalid headers
            $this->header_arr[strtolower(trim($header[0]))] = trim($header[1]);
        }
        return $len;
    }

    final public function close() : void
    {
        if ($this->ch !== null) {
            curl_close($this->ch);
            $this->ch = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
