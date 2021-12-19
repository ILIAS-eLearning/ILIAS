<?php declare(strict_types=1);

/**
 * Class for calling theMathJax server
 */
class ilMathJaxServer
{
    protected ilMathJaxConfig $config;

    /**
     * Constructor
     * @param ilMathJaxConfig $config
     * @param array           $options  will be sent json encoded to the server
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
    public function call(array $options): string
    {
        if (extension_loaded('cURL')) {
            return $this->callByCurl($options);
        }
        else {
            return $this->callByStreamContext($options);
        }
    }

    /**
     * Call the mathjax server by curl
     */
    protected function callByCurl(array $options): string
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
            }
            else {
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