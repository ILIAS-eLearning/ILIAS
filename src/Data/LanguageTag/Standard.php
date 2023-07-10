<?php

declare(strict_types=1);

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

namespace ILIAS\Data\LanguageTag;

use ILIAS\Data\LanguageTag;

class Standard extends LanguageTag
{
    private string $language;
    private ?string $extlang;
    private ?string $script;
    private ?string $region;
    private ?string $variant;
    private ?string $extension;
    private ?PrivateUse $privateuse;

    public function __construct(
        string $language,
        ?string $extlang,
        ?string $script,
        ?string $region,
        ?string $variant,
        ?string $extension,
        ?PrivateUse $privateuse
    ) {
        $this->language = $language;
        $this->extlang = $extlang;
        $this->script = $script;
        $this->region = $region;
        $this->variant = $variant;
        $this->extension = $extension;
        $this->privateuse = $privateuse;
    }

    public function language(): string
    {
        return $this->language;
    }

    public function extlang(): ?string
    {
        return $this->extlang;
    }

    public function script(): ?string
    {
        return $this->script;
    }

    public function region(): ?string
    {
        return $this->region;
    }

    public function variant(): ?string
    {
        return $this->variant;
    }

    public function extension(): ?string
    {
        return $this->extension;
    }

    public function privateuse(): ?PrivateUse
    {
        return $this->privateuse;
    }

    public function value(): string
    {
        $optional = array_filter([$this->script, $this->extlang, $this->region, $this->variant, $this->extension, $this->privateuse ? $this->privateuse->value() : null]);

        return implode('-', array_merge([$this->language], $optional));
    }
}
