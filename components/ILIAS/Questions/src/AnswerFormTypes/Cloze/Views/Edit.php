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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Views;

use ILIAS\Questions\AnswerForm\Views\Edit as EditViewInterface;
use ILIAS\Questions\AnswerFormTypes\Cloze\Type;
use ILIAS\Questions\Question\Persistence\UpdateQuery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\RequestInterface;

class Edit implements EditViewInterface
{
    private const string SET_GAP_TYPES = 'sgt';

    private ?Type $type = null;

    public function __construct(
        private readonly Language $lng,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly RequestInterface $request,
        private readonly DataFactory $data_factory
    ) {
    }

    public function create(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|UpdateQuery {
        return match($step) {
            self::SET_GAP_TYPES => $this->setGapTypes($url_builder, $step_token),
            default => [$this->buildBasicEditingForm($url_builder, $step_token)]
        };
    }

    public function edit(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|UpdateQuery {

    }

    public function other(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|UpdateQuery {

    }

    public function withAnswerForm(Type $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    private function buildBasicEditingForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): StandardForm {

    }
}
