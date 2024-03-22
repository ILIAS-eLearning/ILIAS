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
use ILIAS\FileDelivery\Token\Data\Stream;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\HTTP\Response\ResponseHeader;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;

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
        if (isset($request->getServerParams()['HTTP_RANGE'])) {
            return $this->deliverPartial($request, $response, $stream);
        }
        return $this->deliverFull($response, $stream);
    }

    protected function buildHeaders(
        ResponseInterface $response,
        FileStream $stream
    ): ResponseInterface {
        $uri = $stream->getMetadata('uri');

        $response = $response->withHeader(ResponseHeader::ACCEPT_RANGES, 'bytes');
        $response = $response->withHeader(ResponseHeader::CONTENT_LENGTH, $stream->getSize());
        try {
            $response = $response->withHeader(
                ResponseHeader::LAST_MODIFIED,
                date("D, j M Y H:i:s", filemtime($uri) ?: time()) . " GMT"
            );
        } catch (\Throwable) {
        }

        return $response->withHeader(ResponseHeader::ETAG, md5($uri));
    }

    protected function deliverFull(
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        return $response->withBody($stream);
    }

    protected function deliverPartial(
        RequestInterface|ServerRequestInterface $request,
        ResponseInterface $response,
        FileStream $stream,
    ): ResponseInterface {
        if (!$this->support_partial) {
            return $response;
        }
        $server_params = $request->getServerParams();

        $byte_offset = 0;
        $byte_length = $content_length = $stream->getSize();

        if (isset($server_params['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $server['HTTP_RANGE'], $match)) {
            $byte_offset = (int) $match[1];
            if (isset($match[2])) {
                $finish_bytes = (int) $match[2];
                $byte_length = $finish_bytes + 1;
            } else {
                $finish_bytes = $content_length - 1;
            }
            $response = $response->withStatus(206, 'Partial Content');
            $response = $response->withHeader(
                ResponseHeader::CONTENT_RANGE, "bytes {$byte_offset}-{$finish_bytes}/{$content_length}"
            );
        }

        $byte_range = $byte_length - $byte_offset;

        $response = $response->withHeader(ResponseHeader::CONTENT_LENGTH, $byte_length);

        $buffer_size = 512 * 16;
        $bite_pool = $byte_range;

        $fh = $stream->detach();

        while ($bite_pool > 0) {
            $chunk_size_requested = min($buffer_size, $bite_pool);
            $buffer = fread($fh, $chunk_size_requested);
            $chunk_actual_size = strlen($buffer);

            if ($chunk_actual_size === 0) {
                throw new \RuntimeException("Chunksize became 0");
            }

            $bite_pool -= $chunk_actual_size;

            $response->getBody()->write($buffer);
        }

        return $response;
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
