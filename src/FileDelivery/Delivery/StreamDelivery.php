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

namespace ILIAS\FileDelivery\Delivery;

use ILIAS\FileDelivery\Token\DataSigner;
use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\FileDelivery\Token\Data\Stream;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileDelivery\Token\Signer\Payload\FilePayload;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileDelivery\Token\Signer\Payload\ShortFilePayload;
use ILIAS\Filesystem\Stream\ZIPStream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class StreamDelivery extends BaseDelivery
{
    public const SUBREQUEST_SEPARATOR = '/-/';

    public function __construct(
        private DataSigner $data_signer,
        \ILIAS\HTTP\Services $http,
        ResponseBuilder $response_builder,
        ResponseBuilder $fallback_response_builder,
    ) {
        parent::__construct($http, $response_builder, $fallback_response_builder);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $r
     * @return void
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    protected function notFound(\Psr\Http\Message\ResponseInterface $r): void
    {
        $this->http->saveResponse($r->withStatus(404));
        $this->http->sendResponse();
        $this->http->close();
    }

    public function attached(
        FileStream $stream,
        string $download_file_name,
        ?string $mime_type = null
    ): never {
        $this->deliver(
            $stream,
            $download_file_name,
            $mime_type,
            Disposition::ATTACHMENT
        );
    }

    public function inline(
        FileStream $stream,
        string $download_file_name,
        ?string $mime_type = null
    ): never {
        $this->deliver(
            $stream,
            $download_file_name,
            $mime_type,
            Disposition::INLINE
        );
    }

    public function deliver(
        FileStream $stream,
        string $download_file_name,
        ?string $mime_type = null,
        Disposition $disposition = Disposition::INLINE
    ): never {
        $r = $this->http->response();
        $uri = $stream->getMetadata()['uri'];

        $r = $this->setGeneralHeaders(
            $r,
            $uri,
            $mime_type ?? mime_content_type($uri),
            $download_file_name,
            $disposition
        );
        if ($stream instanceof ZIPStream) {
            $this->response_builder = $this->fallback_response_builder;
        }

        $r = $this->response_builder->buildForStream(
            $this->http->request(),
            $r,
            $stream
        );
        $this->saveAndClose($r);
    }

    public function deliverFromToken(string $token): never
    {
        // check if $token has a sub-request, such as .../index.html
        $parts = explode(self::SUBREQUEST_SEPARATOR, $token);
        $sub_request = null;
        if (count($parts) > 1) {
            $token = $parts[0];
            $sub_request = implode('/', array_slice($parts, 1));
        }

        $r = $this->http->response();
        $payload = $this->data_signer->verifyStreamToken($token);

        switch (true) {
            case $payload instanceof FilePayload:
                $uri = $payload->getUri();
                $mime_type = $payload->getMimeType();
                $file_name = $payload->getFilename();
                $disposition = Disposition::tryFrom($payload->getDisposition()) ?? Disposition::INLINE;
                break;
            case $payload instanceof ShortFilePayload:
                $uri = $payload->getUri();
                $mime_type = $this->determineMimeType($uri);
                $file_name = $payload->getFilename();
                $disposition = Disposition::INLINE;
                break;
            default:
                $this->notFound($r);
        }
        unset($payload);

        // handle direct access to file

        if ($sub_request === null) {
            $r = $this->setGeneralHeaders(
                $r,
                $uri,
                $mime_type,
                $file_name,
                $disposition
            );

            $this->http->saveResponse(
                $this->response_builder->buildForStream(
                    $this->http->request(),
                    $r,
                    Streams::ofResource(fopen($uri, 'rb'))
                )
            );
        } else { // handle subrequest, aka file in a ZIP
            $requested_zip = $uri;
            $sub_request = urldecode($sub_request);
            // remove query
            $sub_request = explode('?', $sub_request)[0];

            try {
                $file_inside_ZIP = Streams::ofFileInsideZIP($requested_zip, $sub_request);
            } catch (\Throwable) {
                $this->notFound($r);
            }
            $file_inside_zip_uri = $file_inside_ZIP->getMetadata()['uri'];

            if ($file_inside_zip_stream === false) {
                $this->notFound($r);
            }

            // we must use PHPResponseBuilder here, because the streams inside zips cant be delivered using XSendFile or others
            $this->response_builder = $this->fallback_response_builder;

            $mime_type = $this->determineMimeType($file_inside_zip_uri);
            $r = $this->setGeneralHeaders(
                $r,
                $file_inside_zip_uri,
                $mime_type,
                basename($sub_request),
                Disposition::INLINE // subrequests are always inline per default, browsers may change this to download
            );


            $this->http->saveResponse(
                $this->response_builder->buildForStream(
                    $this->http->request(),
                    $r,
                    $file_inside_ZIP
                )
            );
        }
        $this->http->sendResponse();
        $this->http->close();
    }

    private function determineMimeType(string $filename): string
    {
        $suffix = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (isset($this->mime_type_map[$suffix])) {
            if (is_array($this->mime_type_map[$suffix]) && isset($this->mime_type_map[$suffix][0])) {
                return $this->mime_type_map[$suffix][0];
            }

            return $this->mime_type_map[$suffix];
        }

        $mime_type = mime_content_type($filename);
        if ($mime_type === 'application/octet-stream') {
            $mime_type = mime_content_type(substr($filename, 64));
        }
        return $mime_type ?: 'application/octet-stream';
    }
}
