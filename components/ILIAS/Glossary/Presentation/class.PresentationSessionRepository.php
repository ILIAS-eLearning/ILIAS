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

namespace ILIAS\Glossary\Presentation;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class PresentationSessionRepository
{
    public const KEY_BASE = "glo_presentation_";

    public function __construct()
    {
    }

    public function setPageLength(int $ref_id, int $page_length): void
    {
        $key = self::KEY_BASE . $ref_id . "_page_length";
        \ilSession::set($key, $page_length);
    }

    public function getPageLength(int $ref_id): int
    {
        $key = self::KEY_BASE . $ref_id . "_page_length";
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return 0;
    }

    public function setLetter(int $ref_id, string $letter): void
    {
        $key = self::KEY_BASE . $ref_id . "_letter";
        \ilSession::set($key, $letter);
    }

    public function getLetter(int $ref_id): string
    {
        $key = self::KEY_BASE . $ref_id . "_letter";
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return "";
    }

    public function setViewControlStart(int $ref_id, int $vc_start): void
    {
        $key = self::KEY_BASE . $ref_id . "_vc_start";
        \ilSession::set($key, $vc_start);
    }

    public function getViewControlStart(int $ref_id): int
    {
        $key = self::KEY_BASE . $ref_id . "_vc_start";
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return 0;
    }

    public function setViewControlLength(int $ref_id, int $vc_length): void
    {
        $key = self::KEY_BASE . $ref_id . "_vc_length";
        \ilSession::set($key, $vc_length);
    }

    public function getViewControlLength(int $ref_id): int
    {
        $key = self::KEY_BASE . $ref_id . "_vc_length";
        if (\ilSession::has($key)) {
            return \ilSession::get($key);
        }
        return 0;
    }
}
