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

namespace ILIAS\FileDelivery\Delivery\ResponseBuilder;

use Psr\Http\Message\ResponseInterface;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\HTTP\Response\ResponseHeader;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PHPResponseBuilder implements ResponseBuilder
{
    public function getName(): string
    {
        return 'php';
    }

    public function buildForStream(
        ServerRequestInterface $request,
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        $response = $this->buildHeaders($response, $stream);
        $server_params = $request->getServerParams();

        if ($request->getMethod() === 'HEAD') {
            return $response->withStatus(200);
        }

        if (isset($server_params['HTTP_RANGE']) && $this->supportPartial()) {
            return $this->deliverPartial($request, $response, $stream);
        }
        return $this->deliverFull($response, $stream);
    }

    protected function buildHeaders(
        ResponseInterface $response,
        FileStream $stream
    ): ResponseInterface {
        $uri = $stream->getMetadata('uri');
        if ($this->supportPartial()) {
            $response = $response->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        }

        $response = $response->withHeader(ResponseHeader::CONTENT_LENGTH, $stream->getSize());
        try {
            $response = $response->withHeader(
                ResponseHeader::LAST_MODIFIED,
                date("D, j M Y H:i:s", @filemtime($uri) ?: time()) . " GMT"
            );
        } catch (\Throwable) {
        }

        return $response->withHeader(ResponseHeader::ETAG, md5((string) $uri));
    }

    protected function deliverFull(
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        $stream->rewind();
        return $response->withBody($stream);
    }

    protected function deliverPartial(
        RequestInterface|ServerRequestInterface $request,
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        if (!$this->supportPartial()) {
            return $response;
        }
        $request->getServerParams();

        $start = 0;
        $content_length = $stream->getSize();
        $end = null;

        $range_header = $request->getHeaderLine('Range');

        if ($range_header && preg_match(
            '%bytes=(\d+)-(\d+)?%i',
            $range_header,
            $match
        )) {
            $start = (int) $match[1];
            if (isset($match[2])) {
                $end = (int) $match[2];
            }
            $end ??= $content_length - 1;
        }

        $response = $response->withStatus(206);

        $length = $end - $start + 1;
        $fh = $stream->detach();

        // set $buffer_size to 8MB
        $buffer_size = 8048 * 1000; // 8,048,000 bytes

        $output_length = 0;
        if ($stream->isSeekable()) {
            fseek($fh, $start);
            while (!feof($fh) && $length > 0) {
                $chunk_size_requested = min($buffer_size, $end - $start);
                $content = fread($fh, $length);
                if ($content === false) {
                    break;
                }
                $length -= strlen($content);
                $response->getBody()->write($content);
                $output_length = strlen($content);
            }
        } else {
            $length = $buffer_size;
            $content = stream_get_contents($fh, $length, $start);
            $output_length = strlen($content);
            $response = $response->withBody(
                Streams::ofString($content)
            );
            $end = $start + $output_length - 1;
        }

        $response = $response->withHeader(
            ResponseHeader::CONTENT_RANGE,
            "bytes {$start}-{$end}/{$content_length}"
        );

        return $response->withHeader(ResponseHeader::CONTENT_LENGTH, $output_length);
    }

    public function supportPartial(): bool
    {
        return true;
    }

    public function supportStreaming(): bool
    {
        return true;
    }

    public function supportFileDeletion(): bool
    {
        return true;
    }

    public function supportsInlineDelivery(): bool
    {
        return true;
    }

    public function supportsAttachmentDelivery(): bool
    {
        return true;
    }
}
