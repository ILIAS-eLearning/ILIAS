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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper as RequestWrapper;
use ILIAS\Data\URI;

class ConsecutiveScoringURLs
{
    private URLBuilderToken $action_token;
    private URLBuilderToken $question_token;
    private URLBuilderToken $user_token;
    private URLBuilderToken $attempt_token;
    private URLBuilderToken $force_redirect_token;
    private Transformation $refine_string;
    private Transformation $refine_int;

    public function __construct(
        private URLBuilder $url_builder,
        array $namespace,
        private readonly Refinery $refinery,
        private readonly RequestWrapper $request_wrapper,
        private readonly \ilCtrl $ctrl,
    ) {
        [
            $this->url_builder,
            $this->action_token,
            $this->question_token,
            $this->user_token,
            $this->attempt_token,
            $this->force_redirect_token,
        ] = $this->url_builder->acquireParameters(
            $namespace,
            'action',
            'question',
            'user',
            'attempt',
            'force',
        );
    }

    private function retrieveString(URLBuilderToken $token): ?string
    {
        return $this->request_wrapper->retrieve(
            $token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
    }
    private function retrieveInt(URLBuilderToken $token): ?int
    {
        return $this->request_wrapper->retrieve(
            $token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );
    }

    public function buildURI(): URI
    {
        return $this->url_builder->buildURI();
    }

    public function getAction(): ?string
    {
        return $this->retrieveString($this->action_token);
    }

    public function withAction(string $act): self
    {
        $clone = clone $this;
        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->action_token, $act);
        return $clone;
    }

    public function withFragment(string $fragment): self
    {
        $clone = clone $this;
        $clone->url_builder = $clone->url_builder
            ->withFragment($fragment);
        return $clone;
    }

    public function withForceRedirect(): self
    {
        $clone = clone $this;
        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->force_redirect_token, (string) time());
        return $clone;
    }

    public function getIdParameters(): array
    {
        return [
            $this->retrieveInt($this->question_token),
            $this->retrieveInt($this->user_token),
            $this->retrieveInt($this->attempt_token)
        ];
    }

    public function withIdParameters(int $qid, int $uid, int $attempt): self
    {
        $clone = clone $this;
        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->question_token, (string) $qid)
            ->withParameter($clone->user_token, (string) $uid)
            ->withParameter($clone->attempt_token, (string) $attempt);
        return $clone;
    }

    public function withUserId(int $uid): self
    {
        $clone = clone $this;
        $clone->url_builder = $clone->url_builder
            ->withParameter($clone->user_token, (string) $uid);
        return $clone;
    }

    public function getUserId(): int
    {
        return $this->retrieveInt($this->user_token);
    }

    public function redirect(): void
    {
        $this->ctrl->redirectToURL(
            $this->url_builder->buildURI()->__toString()
        );
    }
}
