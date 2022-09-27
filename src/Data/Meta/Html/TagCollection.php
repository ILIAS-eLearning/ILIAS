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
        $this->tags = $this->collapseTags($tags);
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
        yield from $this->tags;
    }

    /**
     * @param Tag[] $tags
     * @return Tag[]
     */
    protected function collapseTags(array $tags): array
    {
        $collapsed_tags = [];
        foreach ($tags as $tag) {
            if ($tag instanceof NullTag) {
                continue;
            }

            if (!$tag instanceof self) {
                $collapsed_tags[] = $tag;
                continue;
            }

            foreach ($tag->getTags() as $nested_tag) {
                $collapsed_tags[] = $nested_tag;
            }
        }

        return $collapsed_tags;
    }
}
