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

namespace ILIAS\Language\Activities;

trait ParsesLanguageKeyList
{
    // Every language key recognised by this component is exactly two lowercase ASCII letters
    // (e.g. "de", "en", "fr"), matching the `ilias_<key>.lang` file naming convention.
    private const LANGUAGE_KEY_FORMAT = '/^[a-z]{2}$/';

    /**
     * @return list<string>
     */
    private function toLanguageKeyList(mixed $value): array
    {
        if (!is_string($value) && !is_array($value)) {
            throw new InvalidInputException('language_keys must be a string or an array of strings.');
        }

        $values = is_array($value) ? $value : [$value];
        $language_keys = [];

        foreach ($values as $item) {
            if (!is_string($item)) {
                throw new InvalidInputException('language_keys must be a string or an array of strings.');
            }

            foreach (explode(',', (string) $item) as $language_key) {
                $language_key = trim($language_key);
                if ($language_key === '') {
                    continue;
                }

                if (preg_match(self::LANGUAGE_KEY_FORMAT, $language_key) !== 1) {
                    throw new InvalidInputException(
                        'Invalid language key "' . $language_key . '": a language key must be ' .
                        'exactly two lowercase ASCII letters (e.g. "de", "en", "fr").'
                    );
                }

                if (!in_array($language_key, $language_keys, true)) {
                    $language_keys[] = $language_key;
                }
            }
        }

        if ($language_keys === []) {
            throw new InvalidInputException('At least one language key is required.');
        }

        return $language_keys;
    }
}
