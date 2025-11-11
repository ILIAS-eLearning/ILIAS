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

namespace ILIAS\StaticURL\Handler;

use ILIAS\StaticURL\Request\Request;
use ILIAS\StaticURL\Request\RequestBuilder;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Response\Factory;
use ILIAS\StaticURL\Context;
use ILIAS\StaticURL\Builder\StandardURIBuilder;
use ILIAS\StaticURL\Response\MaybeCanHandlerAfterLogin;
use ILIAS\StaticURL\Response\CannotReach;
use ILIAS\StaticURL\Response\CannotHandle;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal Do not create own instances of this class.
 */
class HandlerService
{
    /**
     * @var Handler[]
     */
    private array $handlers = [];
    private Factory $response_factory;

    public function __construct(
        private RequestBuilder $request_builder,
        private Context $context,
        Handler ...$handlers,
    ) {
        $this->response_factory = new Factory($context);
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getNamespace()] = $handler;
        }
    }

    /**
     * @return never
     */
    public function performRedirect(URI $base_uri): void
    {
        $http = $this->context->http();

        $request = $this->request_builder->buildRequest(
            $http,
            $this->context->refinery(),
            $this->handlers
        );
        if (!$request instanceof Request) {
            throw new \RuntimeException('No request could be built');
        }

        $handler = $this->handlers[$request->getNamespace()] ?? null;
        if (!$handler instanceof Handler) {
            throw new \InvalidArgumentException('No handler found for namespace ' . $request->getNamespace());
        }
        $response = $handler->handle($request, $this->context, $this->response_factory);
        $uri_builder = new StandardURIBuilder(ILIAS_HTTP_PATH, false);

        switch (true) {
            case $response instanceof MaybeCanHandlerAfterLogin:
                $target = $uri_builder->buildTarget(
                    $request->getNamespace(),
                    $request->getReferenceId(),
                    $request->getAdditionalParameters()
                );
                $full_uri = $base_uri . "/login.php?target=";
                $full_uri .= str_replace('/', '_', rtrim($target, '/')); // TODO: ILIAS currently need this like this
                if (!$this->context->isUserLoggedIn()) {
                    $full_uri .= '&cmd=force_login&lang=' . $this->context->getUserLanguage();
                }
                $full_uri = $this->appendUnknownParameters($this->context, $full_uri); // Read the comment below
                break;
            case $response instanceof CannotReach:
            case $response instanceof CannotHandle:
                $this->context->mainTemplate()->setOnScreenMessage(
                    'failure',
                    $this->context->lng()->txt('permission_denied'),
                    true
                );
                $full_uri = $base_uri . '/index.php';
                break;
            default:
                // Perform Redirect
                $uri_path = $response->getURIPath() ?? '';
                $base_path = $base_uri->getPath() ?? '';
                if ($base_path !== '' && $base_path !== '/') {
                    $uri_path = str_replace(rtrim($base_path, '/') . '/', '', $uri_path);
                }
                $full_uri = $base_uri . '/' . trim((string) $uri_path, '/');
                break;
        }

        $http->saveResponse(
            $http->response()->withAddedHeader('Location', $full_uri)
        );
        $http->sendResponse();
        $http->close();
    }

    /**
     * @deprecated Thsi piece of code comes from the old goto.php and should be removed as soon as possible.
     * Or this can be moved to the place where is is needed.
     */
    private function appendUnknownParameters(Context $context, string $full_uri): string
    {
        if ($context->http()->wrapper()->query()->has('soap_pw')) {
            return \ilUtil::appendUrlParameterString(
                $full_uri,
                'soap_pw=' . $context->http()->wrapper()->query()->retrieve(
                    'soap_pw',
                    $context->refinery()->kindlyTo()->string()
                )
            );
        }
        if ($context->http()->wrapper()->query()->has('ext_uid')) {
            return \ilUtil::appendUrlParameterString(
                $full_uri,
                'ext_uid=' . $context->http()->wrapper()->query()->retrieve(
                    'ext_uid',
                    $context->refinery()->kindlyTo()->string()
                )
            );
        }

        return $full_uri;
    }
}
