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
 */

declare(strict_types=1);

namespace ILIAS\Data\Meta\Html;

use Generator;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class TagCollection extends Tag
{
    /**
     * @var Tag[]
     */
    protected array $tags;

    public function __construct(Tag ...$tags)
    {
        $this->tags = $tags;
    }

    /**
     * @inheritDoc
     */
    public function toHtml(): string
    {
        $html = '';
        foreach ($this->getTags() as $tag) {
            $html .= $tag->toHtml();
            $html .= PHP_EOL;
        }

        return $html;
    }

    /**
     * @return Tag[]|Generator
     */
    public function getTags(): Generator
    {
        foreach ($this->tags as $tag) {
            yield from $tag->getTags();
        }
    }
}
