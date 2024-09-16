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

namespace ILIAS\Test\Questions\Presentation;

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper as RequestWrapper;
use ILIAS\HTTP\Services as HTTPService;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\URI;

class QuestionsTableQuery
{
    protected ServerRequestInterface $request;
    protected RequestWrapper $request_wrapper;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $row_id_token;

    public function __construct(
        HTTPService $http,
        protected Refinery $refinery,
        protected DataFactory $data_factory,
        array $namespace
    ) {
        $this->request = $http->request();
        $this->request_wrapper = $http->wrapper()->query();

        $url_builder = $this->getUrlBuilder();
        list($url_builder, $action_token, $row_id_token) = $url_builder->acquireParameters(
            $namespace,
            'action',
            'ids'
        );
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;
    }

    private function getUrlBuilder(): URLBuilder
    {
        return new URLBuilder($this->data_factory->uri($this->getHereURL()));
    }

    private function getHereURL(): string
    {
        /**
         * getUri() may return http:// for servers behind a proxy; the request
         * will be blocked due to insecure targets on an otherwise secure connection.
         * getUriFromGlobals() includes the port (getUri does not) - but it's
         * the port from the actual machine, not the proxy.
         */
        $url = $this->request->getUriFromGlobals();
        $port = ':' . (string) $url->getPort();
        $url = str_replace($port, ':', $url->__toString()) ?? $url->__toString();
        return $url;
    }

    public function getQueryCommand(): ?string
    {
        if (! $this->request_wrapper->has($this->action_token->getName())) {
            return null;
        }
        return $this->request_wrapper->retrieve(
            $this->action_token->getName(),
            $this->refinery->kindlyTo()->string()
        );
    }

    public function getRowIds(\ilObjTest $obj_test): ?array
    {
        if ($this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->identity()
        ) === ['ALL_OBJECTS']) {
            return array_map(
                fn($record) => $record['question_id'],
                $obj_test->getTestQuestions()
            );
        }
        return $this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->always(null)
                ])
            )
        );
    }

    public function getActionURL(string $action): URI
    {
        return $this->url_builder->withParameter(
            $this->action_token,
            $action
        )->buildURI();
    }

    public function getRowBoundURLBuilder(string $action): array
    {
        return [
            $this->url_builder->withParameter($this->action_token, $action),
            $this->row_id_token
        ];
    }
}
