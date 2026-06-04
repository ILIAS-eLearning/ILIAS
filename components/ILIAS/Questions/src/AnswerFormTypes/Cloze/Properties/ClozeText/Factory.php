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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Properties\ClozeText;

use ILIAS\Data\Text\Factory as TextFactory;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Refinery\Factory as Refinery;
use Mustache\Engine;

class Factory
{
    public function __construct(
        private readonly Refinery $refinery,
        private readonly Engine $mustache_engine,
        private readonly UuidFactory $uuid_factory,
        private readonly TextFactory $text_factory
    ) {
    }

    public function buildFromTextString(string $text): Text
    {
        return new Text(
            $this->refinery,
            $this->mustache_engine,
            $this->text_factory,
            $this->text_factory->markdown($text)
        );
    }

    public function buildFromHiddenInputString(string $text): Text
    {
        return $this->buildFromTextString($this->unmaskTextFromOutputInHiddenInput($text));
    }

    private function unmaskTextFromOutputInHiddenInput(string $text): string
    {
        return str_replace(['&#123;&#123;', '&#125;&#125;'], ['{{', '}}'], $text);
    }
}
