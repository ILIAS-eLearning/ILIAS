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

namespace ILIAS\COPage\Editor;

/**
 * Editing session repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EditSessionRepository
{
    protected const BASE_SESSION_KEY = 'copg_';
    protected const ERROR_KEY = self::BASE_SESSION_KEY . 'error';
    protected const SUB_CMD_KEY = self::BASE_SESSION_KEY . 'sub_cmd';
    protected const TXT_LANG_KEY = self::BASE_SESSION_KEY . 'text_lang';
    protected const MEP_KEY = self::BASE_SESSION_KEY . 'mep';
    protected const QPL_KEY = self::BASE_SESSION_KEY . 'qpl';

    public function __construct()
    {
    }

    public function clear(?array $text_ref_ids = null): void
    {
        $this->clearPageError();
        $this->clearMediaPool();
        $this->clearQuestionPool();
        $this->clearSubCmd();
        if (is_array($text_ref_ids)) {
            foreach ($text_ref_ids as $text_ref_id) {
                $this->clearTextLang($text_ref_id);
            }
        }
    }

    public function clearPageError(): void
    {
        \ilSession::clear(self::ERROR_KEY);
    }

    /**
     * @param string|array $error
     */
    public function setPageError($error): void
    {
        \ilSession::set(self::ERROR_KEY, $error);
    }

    /**
     * @return string|array
     */
    public function getPageError()
    {
        return \ilSession::get(self::ERROR_KEY) ?? "";
    }

    public function clearSubCmd(): void
    {
        \ilSession::clear(self::SUB_CMD_KEY);
    }

    public function setSubCmd(string $sub_cmd): void
    {
        \ilSession::set(self::SUB_CMD_KEY, $sub_cmd);
    }

    public function getSubCmd(): string
    {
        return \ilSession::get(self::SUB_CMD_KEY) ?? "";
    }

    public function clearTextLang(int $ref_id): void
    {
        \ilSession::clear(self::TXT_LANG_KEY . "_" . $ref_id);
    }

    public function setTextLang(int $ref_id, string $lang_key): void
    {
        \ilSession::set(self::TXT_LANG_KEY . "_" . $ref_id, $lang_key);
    }

    public function getTextLang(int $ref_id): string
    {
        return \ilSession::get(self::TXT_LANG_KEY . "_" . $ref_id) ?? "";
    }

    public function clearMediaPool(): void
    {
        \ilSession::clear(self::MEP_KEY);
    }

    public function setMediaPool(int $pool_id): void
    {
        \ilSession::set(self::MEP_KEY, $pool_id);
    }

    public function getMediaPool(): int
    {
        return \ilSession::get(self::MEP_KEY) ?? 0;
    }

    public function clearQuestionPool(): void
    {
        \ilSession::clear(self::QPL_KEY);
    }

    public function setQuestionPool(int $pool_id): void
    {
        \ilSession::set(self::QPL_KEY, $pool_id);
    }

    public function getQuestionPool(): int
    {
        return \ilSession::get(self::QPL_KEY) ?? 0;
    }
}
