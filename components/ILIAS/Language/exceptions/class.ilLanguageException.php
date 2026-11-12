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

/**
 * Class for language related exception handling in ILIAS.
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 *
 */
class ilLanguageException extends ilException
{
    /** @var list<string> */
    private array $unstorable_keys = [];

    /**
     * @param list<string> $keys
     */
    public static function forUnstorableValues(array $keys): self
    {
        $e = new self("Invalid or unsupported characters in language entries: " . implode(", ", $keys));
        $e->unstorable_keys = $keys;
        return $e;
    }

    /**
     * @return list<string>
     */
    public function getUnstorableKeys(): array
    {
        return $this->unstorable_keys;
    }
}
