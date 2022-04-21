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
        $curl = curl_init($this->config->getServerAddress());
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options, JSON_THROW_ON_ERROR));
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->config->getServerTimeout());

        $response = (string) curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($status !== 200) {
            $lines = explode("\n", $response);
            if (isset($lines[1])) {
                throw new ilMathJaxException($lines[1]);
            }
            throw new ilMathJaxException('curl server call failed');
        }
        return $response;
    }
}
