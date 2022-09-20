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

namespace ILIAS\SurveyQuestionPool\Export;

/**
 * Stores session data in import process
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ImportSessionRepository
{
    public const KEY_BASE = "svy_import_mob_xhtml";

    public function __construct()
    {
    }

    public function getMobs(): array
    {
        $entries = [];
        if (\ilSession::has(self::KEY_BASE)) {
            $entries = \ilSession::get(self::KEY_BASE);
        }
        return $entries;
    }

    public function addMob(string $label, string $uri, string $type = "", string $id = ""): void
    {
        $entries = [];
        if (\ilSession::has(self::KEY_BASE)) {
            $entries = \ilSession::get(self::KEY_BASE);
        }
        $entries[] = [
            "mob" => $label,
            "uri" => $uri,
            "type" => $type,
            "id" => $id
        ];
        \ilSession::set(self::KEY_BASE, $entries);
    }

    public function clearMobs(): void
    {
        if (\ilSession::has(self::KEY_BASE)) {
            \ilSession::clear(self::KEY_BASE);
        }
    }
}
