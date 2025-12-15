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

namespace ILIAS\Questions\Presentation\Layout\Definitions;

use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\Presentation\Definitions\Editability;
use ILIAS\Data\URI;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class EnvironmentImplementation implements Environment
{
    private const array QUERY_PARAMETER_NAME_SPACE = ['q'];
    private const string TOKEN_STRING_ACTION = 'a';
    private const string TOKEN_STRING_STEP = 's';
    private const string TOKEN_STRING_QUESTION_ID = 'q';
    private const string TOKEN_TYPE_HASH = 't';

    private ?Properties $properties = null;

    private bool $default_step = false;

    private URLBuilder $url_builder;
    private readonly URLBuilderToken $action_token;
    private readonly URLBuilderToken $step_token;
    private readonly URLBuilderToken $question_id_token;
    private readonly URLBuilderToken $type_hash_token;

    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly HTTPServices $http,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly Factory $definitions_factory,
        private readonly Editability $editability,
        URI $base_uri
    ) {
        $this->acquireURLBuilderAndParameters($base_uri);
    }

    public function getDefinitionsFactory(): Factory
    {
        return $this->definitions_factory;
    }

    public function getUrlBuilder(): URLBuilder
    {
        return $this->url_builder;
    }

    public function getUrlBuilderWithStepParameter(string $step): URLBuilder
    {
        return $this->getUrlBuilder()->withParameter($this->step_token, $step);
    }

    public function withDefaultStep(): self
    {
        $clone = clone $this;
        $clone->default_step = true;
        return $clone;
    }

    public function getStep(): string
    {
        return $this->default_step
            ? ''
            : $this->retrieveStringValueForToken($this->step_token, self::TOKEN_STRING_STEP);
    }

    public function getEditability(): Editability
    {
        return $this->editability;
    }

    public function getProperties(): ?Properties
    {
        return $this->properties;
    }

    public function withProperties(Properties $properties): self
    {
        $clone = clone $this;
        $clone->properties = $properties;
        return $clone;
    }

    public function getAction(): string
    {
        return $this->retrieveStringValueForToken($this->action_token);
    }

    public function withActionParameter(string $action): self
    {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->action_token, $action);
        return $clone;
    }

    public function withQuestionIdParameter(Uuid $question_id): self
    {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->question_id_token, $question_id->toString());
        return $clone;
    }

    public function withAnswerFormTypeHashParameter(string $type_hash): URLBuilder
    {
        $clone = clone $this;
        $clone->url_builder = $this->url_builder
            ->withParameter($this->type_hash_token, $type_hash);
        return $clone;
    }

    public function getQuestionId(): ?Uuid
    {
        return $this->http->wrapper()->query()->retrieve(
            $this->question_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->custom()->transformation(
                    fn($v): Uuid => $this->uuid_factory->fromString($v)
                ),
                $this->refinery->always(null)
            ])
        );
    }

    public function getTypeClassHast(): string
    {
        return $this->retrieveStringValueForToken($this->type_hash_token);
    }

    public function setParametersForQuestionCmds(): void
    {
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            $this->question_id_token->getName(),
            $this->getQuestionId()->toString()
        );
    }

    private function acquireURLBuilderAndParameters(URI $base_uri): void
    {
        [
            $this->url_builder,
            $this->action_token,
            $this->step_token,
            $this->question_id_token,
            $this->type_hash_token
        ] = (new URLBuilder($base_uri))
            ->acquireParameters(
                self::QUERY_PARAMETER_NAME_SPACE,
                self::TOKEN_STRING_ACTION,
                self::TOKEN_STRING_STEP,
                self::TOKEN_STRING_QUESTION_ID,
                self::TOKEN_TYPE_HASH
            );
    }

    private function retrieveStringValueForToken(
        URLBuilderToken $token
    ): string {
        return $this->http->wrapper()->query()->retrieve(
            $token->getName(),
            $this->buildStringTrafo()
        );
    }

    private function buildStringTrafo(): Transformation
    {
        return $this->refinery->byTrying([
            $this->refinery->kindlyTo()->string(),
            $this->refinery->always('')
        ]);
    }
}
