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
use ILIAS\FileDelivery\Delivery\ResponseBuilder\PHPResponseBuilder;
use ILIAS\FileUpload\MimeType;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class StreamDelivery extends BaseDelivery
{
    public function __construct(
        private DataSigner $data_signer,
        \ILIAS\HTTP\Services $http,
        ResponseBuilder $response_builder
    ) {
        parent::__construct($http, $response_builder);
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
            Disposition::ATTACHMENT
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
        $r = $this->response_builder->buildForStream(
            $r,
            $stream
        );
        $this->saveAndClose($r);
    }

    public function deliverFromToken(string $token): never
    {
        // check if $token has a sub-request, such as .../index.html
        $parts = explode('/', $token);
        $sub_request = null;
        if (count($parts) > 1) {
            $token = $parts[0];
            $sub_request = implode('/', array_slice($parts, 1));
        }

        $r = $this->http->response();
        $payload = $this->data_signer->verifyStreamToken($token);
        if (!$payload instanceof FilePayload) {
            $this->notFound($r);
        }
        // handle direct access to file
        if ($sub_request === null) {
            $r = $this->setGeneralHeaders(
                $r,
                $payload->getUri(),
                $payload->getMimeType(),
                $payload->getFilename(),
                Disposition::from($payload->getDisposition())
            );

            $this->http->saveResponse(
                $this->response_builder->buildForStream(
                    $r,
                    Streams::ofResource(fopen($payload->getUri(), 'rb'))
                )
            );
        } else { // handle subrequest, aka file in a ZIP
            $requested_zip = $payload->getUri();
            $sub_request = urldecode($sub_request);
            $file_inside_zip_uri = "zip://$requested_zip#$sub_request";
            $file_inside_zip_stream = fopen($file_inside_zip_uri, 'rb');

            if ($file_inside_zip_stream === false) {
                $this->notFound($r);
            }

            $r = $this->setGeneralHeaders(
                $r,
                $file_inside_zip_uri,
                $this->determineMimeType($file_inside_zip_uri),
                basename($sub_request),
                Disposition::INLINE // subrequests are always inline per default, browsers may change this to download
            );

            // we must use PHPResponseBuilder here, because the streams inside zips cant be delivered using XSendFile or others
            $response_builder = new PHPResponseBuilder();

            $this->http->saveResponse(
                $response_builder->buildForStream(
                    $r,
                    Streams::ofResource($file_inside_zip_stream, true)
                )
            );
        }
        $this->http->sendResponse();
        $this->http->close();
    }

    private function determineMimeType(string $file_inside_zip_uri): string
    {
        $suffix = strtolower(pathinfo($file_inside_zip_uri, PATHINFO_EXTENSION));
        if (isset($this->mime_type_map[$suffix])) {
            if (is_array($this->mime_type_map[$suffix]) && isset($this->mime_type_map[$suffix][0])) {
                return $this->mime_type_map[$suffix][0];
            }

            return $this->mime_type_map[$suffix];
        }

        $mime_type = mime_content_type($file_inside_zip_uri);
        if ($mime_type === 'application/octet-stream') {
            $mime_type = mime_content_type(substr($file_inside_zip_uri, 6));
        }
        return $mime_type ?: 'application/octet-stream';
    }
}
