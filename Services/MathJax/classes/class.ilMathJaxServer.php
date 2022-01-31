<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class for calling theMathJax server
 * The server calls use cURL, if the extension is loaded, otherwise allow_url_fopen must be set in the php.ini
 */
class ilMathJaxServer
{
    protected ilMathJaxConfig $config;

    /**
     * Constructor
     */
    public function __construct(ilMathJaxConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Call the mathjax server
     * @param array $options will be sent json encoded to the server
     * @return string
     * @throws ilMathJaxException
     */
    public function call(array $options) : string
    {
        if (extension_loaded('cURL')) {
            return $this->callByCurl($options);
        } else {
            return $this->callByStreamContext($options);
        }
    }

    /**
     * Call the mathjax server by curl
     */
    protected function callByCurl(array $options) : string
    {
        $curl = curl_init($this->config->getServerAddress());
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options));
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->config->getServerTimeout());

        $response = (string) curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status != 200) {
            $lines = explode("\n", $response);
            if (isset($lines[1])) {
                throw new ilMathJaxException($lines[1]);
            } else {
                throw new ilMathJaxException('curl server call failed');
            }
        }
        return $response;
    }

    /**
     * Call the mathjax server by stream context
     */
    protected function callByStreamContext(array $options) : string
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST',
                    'content' => json_encode($options),
                    'header' => "Content-Type: application/json\r\n",
                    'timeout' => $this->config->getServerTimeout(),
                    'ignore_errors' => true
                )
            )
        );

        $response = file_get_contents($this->config->getServerAddress(), false, $context);
        if (empty($response)) {
            throw new ilMathJaxException('stream server call failed');
        }

        return $response;
    }
}
