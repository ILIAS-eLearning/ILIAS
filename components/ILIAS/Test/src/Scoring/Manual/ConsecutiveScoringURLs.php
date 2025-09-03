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

//use ILIAS\Data\Factory as DataFactory;

class ConsecutiveScoringURLs
{
    private URLBuilderToken $act_token;
    private URLBuilderToken $qid_token;
    private URLBuilderToken $uid_token;
    private URLBuilderToken $pid_token;

    private Transformation $refine_string;
    private Transformation $refine_int;

    public function __construct(
        private URLBuilder $url_builder,
        array $namespace,
        private readonly Refinery $refinery,
        private readonly RequestWrapper $request_wrapper,
        private readonly \ilCtrl $ctrl,
    ) {
        list(
            $this->url_builder,
            $this->act_token,
            $this->qid_token,
            $this->uid_token,
            $this->pid_token
        ) = $this->url_builder->acquireParameters(
            $namespace,
            'act',
            'qid',
            'uid',
            'pid',
        );
    }

    protected function retrieveString(URLBuilderToken $token): ?string
    {
        return $this->request_wrapper->retrieve(
            $token->getName(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always(null)
            ])
        );
    }
    protected function retrieveInt(URLBuilderToken $token): ?int
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
        return $this->retrieveString($this->act_token);
    }

    public function withAction(string $act): self
    {
        //$clone = clone $this;
        $this->url_builder = $this->url_builder
            ->withParameter($this->act_token, $act);
        return $this;
    }

    public function withFragment(string $fragment): self
    {
        //$clone = clone $this;
        $this->url_builder = $this->url_builder
            ->withFragment($fragment);
        return $this;
    }

    public function getIdParameters(): array
    {
        return [
            $this->retrieveInt($this->qid_token),
            $this->retrieveInt($this->uid_token),
            $this->retrieveInt($this->pid_token)
        ];
    }

    public function withIdParameters(int $qid, int $uid, int $pid): self
    {
        $this->url_builder = $this->url_builder
            ->withParameter($this->qid_token, (string) $qid)
            ->withParameter($this->uid_token, (string) $uid)
            ->withParameter($this->pid_token, (string) $pid);
        return $this;
    }

    public function withUserId(int $uid): self
    {
        $this->url_builder = $this->url_builder
            ->withParameter($this->uid_token, (string) $uid);
        return $this;
    }

    public function getUserId(): int
    {
        return $this->retrieveInt($this->uid_token);
    }

    public function redirect(): void
    {
        $this->ctrl->redirectToURL(
            $this->url_builder->buildURI()->__toString()
        );
    }
}
