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
use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\HTTP\Response\ResponseHeader;
use Psr\Http\Message\ResponseInterface;
use ILIAS\HTTP\GlobalHttpState;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class BaseDelivery
{
    protected const MIME_TYPE_MAP = __DIR__ . '/../../../FileUpload/src/mime_type_map.php';

    protected array $mime_type_map;

    public function __construct(
        protected GlobalHttpState $http,
        protected ResponseBuilder $response_builder,
        protected ResponseBuilder $fallback_response_builder,
        protected IsolationConfig $isolation = new IsolationConfig(false, null, null),
    ) {
        if (is_readable(self::MIME_TYPE_MAP)) {
            $map = include self::MIME_TYPE_MAP;
        }
        $this->mime_type_map = $map ?? [];
    }

    /**
     * When user content isolation is active, only requests targeting the
     * configured content domain may be served. Requests reaching the
     * delivery endpoint via the main ILIAS domain are rejected.
     */
    protected function isRequestHostAllowed(): bool
    {
        if (!$this->isolation->isActivated()) {
            return true;
        }

        $expected = $this->isolation->getContentHost();
        if ($expected === null) {
            return true;
        }

        return strcasecmp($this->http->request()->getUri()->getHost(), $expected) === 0;
    }

    /**
     * Inverse of {@see self::isRequestHostAllowed()} for the legacy/internal
     * delivery path: the content domain is reserved for signed token delivery
     * via deliver.php. Legacy or app-context delivery must never happen on the
     * content host, so callers reject such requests.
     */
    protected function isRequestOnContentHost(): bool
    {
        if (!$this->isolation->isActivated()) {
            return false;
        }

        $content_host = $this->isolation->getContentHost();
        if ($content_host === null) {
            return false;
        }

        return strcasecmp($this->http->request()->getUri()->getHost(), $content_host) === 0;
    }

    /**
     * Send an empty 404 response and terminate. Declared `never` so the type
     * system guarantees callers cannot fall through to actually serving a file
     * after a rejected request.
     */
    protected function notFound(ResponseInterface $r): never
    {
        $this->http->saveResponse($r->withStatus(404));
        $this->http->sendResponse();
        $this->http->close();
    }

    protected function saveAndClose(
        ResponseInterface $r,
        ?string $path_to_delete = null
    ): never {
        $sender = function () use ($r): void {
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

        return $this->applyIsolationHeaders($r);
    }

    /**
     * When isolation is active, harden delivery responses:
     *  - prevent MIME sniffing
     *  - mark as cross-origin so the main app may embed assets via <img>, <iframe>, …
     *  - allow CORS access only from the configured ILIAS domain
     *  - strip referrer to avoid leaking the content domain back to the app
     */
    protected function applyIsolationHeaders(ResponseInterface $r): ResponseInterface
    {
        if (!$this->isolation->isActivated()) {
            return $r;
        }

        $r = $r->withHeader(ResponseHeader::X_CONTENT_TYPE_OPTIONS, 'nosniff');
        $r = $r->withHeader('Cross-Origin-Resource-Policy', 'cross-origin');
        $r = $r->withHeader('Referrer-Policy', 'no-referrer');

        if (($ilias_domain = $this->isolation->getIliasDomain()) !== null) {
            $r = $r->withHeader(ResponseHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $ilias_domain);
            $r = $r->withAddedHeader('Vary', 'Origin');
        }

        return $r;
    }
}
