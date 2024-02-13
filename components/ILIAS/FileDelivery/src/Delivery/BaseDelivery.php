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

use ILIAS\FileDelivery\Delivery\ResponseBuilder\ResponseBuilder;
use ILIAS\HTTP\Response\ResponseHeader;
use Psr\Http\Message\ResponseInterface;
use ILIAS\FileDelivery\Token\Data\Stream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseDelivery
{
    public function __construct(
        protected \ILIAS\HTTP\Services $http,
        protected ResponseBuilder $response_builder
    ) {
    }

    protected function saveAndClose(
        ResponseInterface $r,
        string $path_to_delete = null
    ): never {
        $sender = function () use ($r) {
            $this->http->saveResponse($r);
            $this->http->sendResponse();
            $this->http->close();
        };

        if ($path_to_delete !== null && file_exists($path_to_delete)) {
            ignore_user_abort(true);
            set_time_limit(0);
            ob_start();

            $sender();

            ob_flush();
            ob_end_flush();
            flush();

            unlink($path_to_delete);
        } else {
            $sender();
        }
    }

    protected function setGeneralHeaders(
        ResponseInterface $r,
        string $uri,
        string $mime_type,
        string $file_name,
        Disposition $disposition = Disposition::INLINE
    ): ResponseInterface {
        $r = $r->withHeader('X-ILIAS-FileDelivery-Method', $this->response_builder->getName());
        $r = $r->withHeader(ResponseHeader::CONTENT_TYPE, $mime_type);
        $r = $r->withHeader(
            ResponseHeader::CONTENT_DISPOSITION,
            $disposition->value . '; filename="' . $file_name . '"'
        );
        $r = $r->withHeader(ResponseHeader::CACHE_CONTROL, 'max-age=31536000, immutable, private');
        $r = $r->withHeader(
            ResponseHeader::EXPIRES,
            date("D, j M Y H:i:s", strtotime('+5 days')) . " GMT"
        );

        return $r;
    }
}
