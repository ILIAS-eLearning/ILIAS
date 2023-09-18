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

namespace ILIAS\Glossary\Term;

/**
 * Stores repository clipboard data
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class TermSessionRepository
{
    public const KEY_BASE = "glo_term_";

    public function __construct()
    {
    }

    public function setLang(int $ref_id, string $lang): void
    {
        $key = self::KEY_BASE . $ref_id . "_lang";
        \ilSession::set($key, $lang);
    }

    public function getLang(int $ref_id): string
    {
        $key = self::KEY_BASE . $ref_id . "_lang";
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return "";
    }
}
