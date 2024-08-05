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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\MarkdownRenderer;
use ILIAS\UI\Component\Input\Field\Markdown as MarkdownInterface;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Markdown extends Textarea implements MarkdownInterface
{
    /** @var array<string, Glyph> */
    protected array $additional_actions = [];

    /** @var string[]|null */
    protected ?array $allowed_actions = null;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        protected MarkdownRenderer $md_renderer,
        string $label,
        ?string $byline,
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    public function withAdditionalAction(Glyph $icon, string $action_name): static
    {
        $clone = clone $this;
        $clone->additional_actions[$action_name] = $icon;
        return $clone;
    }

    public function getAdditionalActions(): array
    {
        return $this->additional_actions;
    }

    public function withAllowedActions(array $action_names): static
    {
        $clone = clone $this;
        $clone->allowed_actions = $action_names;
        return $clone;
    }

    /** @return string[]|null */
    public function getAllowedActions(): ?array
    {
        return $this->allowed_actions;
    }

    public function getMarkdownRenderer(): MarkdownRenderer
    {
        return $this->md_renderer;
    }
}
