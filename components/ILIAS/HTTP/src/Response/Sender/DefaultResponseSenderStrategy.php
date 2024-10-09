<?php

namespace ILIAS\HTTP\Response\Sender;

use Psr\Http\Message\ResponseInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class DefaultResponseSenderStrategy
 *
 * The default response sender strategy rewinds the current body
 * stream and sends the entire stream out to the client.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class DefaultResponseSenderStrategy implements ResponseSenderStrategy
{
    private const METHOD_FPASSTHRU = 'fpassthru';
    private const METHOD_READFILE = 'readfile';
    private string $method;
    private int $chunk_size;
    private int $memory_limit;

    public function __construct()
    {
        $this->memory_limit = $this->initMemoryLimit();
        $this->chunk_size = $this->initChunkSize();
        $this->method = self::METHOD_FPASSTHRU;
    }

    private function initMemoryLimit(): int
    {
        $ini_memory_limit = ini_get('memory_limit');
        $memory_limit = null;
        if (preg_match('/^(\d+)(.)$/', $ini_memory_limit, $matches)) {
            switch (($matches[2] ?? null)) {
                case 'G':
                    $memory_limit = (int) $matches[1] * 1024 * 1024 * 1024; // nnnG -> nnn GB
                    break;
                case 'M':
                    $memory_limit = (int) $matches[1] * 1024 * 1024; // nnnM -> nnn MB
                    break;
                case 'K':
                    $memory_limit = (int) $matches[1] * 1024; // nnnK -> nnn KB
                    break;
                default:
                    $memory_limit = (int) $matches[1]; // nnn -> nnn B
            }
        }

        return $memory_limit ?? 128 * 1024 * 1024;
    }

    private function initChunkSize(): int
    {
        return (int) round(max($this->memory_limit / 4, 8 * 1024));
    }

    /**
     * Sends the rendered response to the client.
     *
     * @param ResponseInterface $response The response which should be send to the client.
     *
     * @throws ResponseSendingException Thrown if the response was already sent to the client.
     */
    public function sendResponse(ResponseInterface $response): void
    {
        //check if the request is already send
        if (headers_sent()) {
            throw new ResponseSendingException("Response was already sent.");
        }

        //set status code
        http_response_code($response->getStatusCode());

        //render all headers
        foreach (array_keys($response->getHeaders()) as $key) {
            // See Mantis #37385.
            if (strtolower($key) === 'set-cookie') {
                foreach ($response->getHeader($key) as $header) {
                    header("$key: " . $header, false);
                }
            } else {
                header("$key: " . $response->getHeaderLine($key));
            }
        }

        //rewind body stream
        $stream = $response->getBody();
        $stream->rewind();

        // check body size
        $body_size = $stream->getSize();
        if ($body_size > $this->memory_limit) {
            $this->method = self::METHOD_READFILE;
        }

        //detach psr-7 stream from resource
        $resource = $stream->detach();

        $sendStatus = false;

        if (is_resource($resource)) {
            set_time_limit(0);
            try {
                ob_end_clean(); // see https://mantis.ilias.de/view.php?id=32046
            } catch (\Throwable $t) {
            }
            switch ($this->method) {
                case self::METHOD_FPASSTHRU:
                    $sendStatus = fpassthru($resource);
                    break;
                case self::METHOD_READFILE:
                    // more memory friendly than fpassthru
                    $sendStatus = true;
                    while (!feof($resource)) {
                        echo $return = fread($resource, $this->chunk_size);
                        $sendStatus = $sendStatus && $return !== false;
                        file_put_contents(
                            'php://stderr',
                            sprintf(
                                "Memory E: %s\n",
                                memory_get_peak_usage(true) / 1024 / 1024
                            )
                        );

                    }
                    break;
            }

            //free up resources
            fclose($resource);
        }

        //check if the body was successfully send to the client
        if ($sendStatus === false) {
            throw new ResponseSendingException("Could not send body content to client.");
        }
    }
}
