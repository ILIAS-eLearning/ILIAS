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
        $has_range = isset($server_params['HTTP_RANGE']) && $this->supportPartial();

        if ($request->getMethod() === 'HEAD') {
            if ($has_range) {
                return $this->buildPartialHeaders($request, $response, $stream)
                    ->withBody(Streams::ofString(''));
            }
            return $response->withStatus(200);
        }

        if ($has_range) {
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

        [$start, $end, $content_length] = $this->parseRange($request, $stream);

        $response = $response->withStatus(206);

        $range_length = $end - $start + 1;
        $fh = $stream->detach();

        // 8 MiB read buffer
        $buffer_size = 8 * 1024 * 1024;

        if ($stream->isSeekable()) {
            fseek($fh, $start);
            $remaining = $range_length;
            while (!feof($fh) && $remaining > 0) {
                $content = fread($fh, min($buffer_size, $remaining));
                if ($content === false) {
                    break;
                }
                $remaining -= strlen($content);
                $response->getBody()->write($content);
            }
            $output_length = $range_length - $remaining;
        } else {
            $length = min($range_length, $buffer_size);
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

    private function parseRange(
        RequestInterface|ServerRequestInterface $request,
        FileStream $stream,
    ): array {
        $content_length = $stream->getSize();
        $start = 0;
        $end = $content_length - 1;

        $range_header = $request->getHeaderLine('Range');
        if ($range_header && preg_match('%bytes=(\d+)-(\d+)?%i', $range_header, $match)) {
            $start = (int) $match[1];
            if (isset($match[2])) {
                $end = (int) $match[2];
            }
        }

        return [$start, $end, $content_length];
    }

    private function buildPartialHeaders(
        RequestInterface|ServerRequestInterface $request,
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        [$start, $end, $content_length] = $this->parseRange($request, $stream);

        return $response
            ->withStatus(206)
            ->withHeader(ResponseHeader::CONTENT_RANGE, "bytes {$start}-{$end}/{$content_length}")
            ->withHeader(ResponseHeader::CONTENT_LENGTH, (string) ($end - $start + 1));
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
