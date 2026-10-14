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

namespace ILIAS\Language;

interface Language
{
    /**
     * $n selects a plural form, e.g. txt($id, 5).
     */
    public function txt(string|LanguageIdentifier $a_topic, ?int $n = null): string;

    public function loadLanguageModule(string $a_module): void;

    public function getLangKey(): string;

    /** @param $key string|string[] */
    public function toJS($key): void;
}
