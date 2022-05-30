<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Execution;

/**
 * Stores access codes of anonymous session
 * @author Alexander Killing <killing@leifos.de>
 */
class RunSessionRepo
{
    protected const KEY_BASE = "svy_run_";
    protected const KEY_ANONYM = self::KEY_BASE . "anonymous_id_";
    protected const KEY_EXTRT = self::KEY_BASE . "extrt_";
    protected const KEY_PAGE_ENTER = self::KEY_BASE . "enter_page";
    protected const KEY_PREVIEW_DATA = self::KEY_BASE . "preview_data";
    protected const KEY_ERRORS = self::KEY_BASE . "errors";
    protected const KEY_POST_DATA = self::KEY_BASE . "post_data";

    public function __construct()
    {
    }

    protected function getAnonymKey(int $survey_id) : string
    {
        return self::KEY_ANONYM . $survey_id;
    }

    public function issetCode(int $survey_id) : bool
    {
        return (
            \ilSession::has($this->getAnonymKey($survey_id)) &&
            is_string(\ilSession::get($this->getAnonymKey($survey_id))) &&
            \ilSession::get($this->getAnonymKey($survey_id)) !== ""
        );
    }

    public function setCode(int $survey_id, string $code) : void
    {
        \ilSession::set($this->getAnonymKey($survey_id), $code);
    }

    public function getCode(int $survey_id) : string
    {
        if (\ilSession::has($this->getAnonymKey($survey_id))) {
            return \ilSession::get($this->getAnonymKey($survey_id));
        }
        return "";
    }

    public function clearCode(int $survey_id) : void
    {
        \ilSession::clear($this->getAnonymKey($survey_id));
    }

    public function isExternalRaterValidated(int $ref_id) : bool
    {
        if (\ilSession::has(self::KEY_EXTRT . $ref_id)) {
            return (bool) \ilSession::get(self::KEY_EXTRT . $ref_id);
        }
        return false;
    }

    public function setExternalRaterValidation(int $ref_id, bool $valid) : void
    {
        \ilSession::set(self::KEY_EXTRT . $ref_id, $valid);
    }

    public function setPageEnter(int $time) : void
    {
        \ilSession::set(self::KEY_PAGE_ENTER, $time);
    }

    public function getPageEnter() : int
    {
        return \ilSession::get(self::KEY_PAGE_ENTER) ?? 0;
    }

    public function clearPageEnter() : void
    {
        \ilSession::clear(self::KEY_PAGE_ENTER);
    }

    protected function getPreviewDataKey(int $survey_id) : string
    {
        return self::KEY_PREVIEW_DATA . "_" . $survey_id;
    }

    public function setPreviewData(int $survey_id, int $question_id, array $data) : void
    {
        $all_data = $this->getAllPreviewData($survey_id);
        $all_data[$question_id] = $data;
        \ilSession::set($this->getPreviewDataKey($survey_id), $all_data);
    }

    protected function getAllPreviewData(int $survey_id) : array
    {
        if (\ilSession::has($this->getPreviewDataKey($survey_id))) {
            return \ilSession::get($this->getPreviewDataKey($survey_id));
        }
        return [];
    }

    public function getPreviewData(int $survey_id, int $question_id) : array
    {
        if (\ilSession::has($this->getPreviewDataKey($survey_id))) {
            $data = \ilSession::get($this->getPreviewDataKey($survey_id));
            return $data[$question_id] ?? [];
        }
        return [];
    }

    public function clearPreviewData(int $survey_id, int $question_id) : void
    {
        if (\ilSession::has($this->getPreviewDataKey($survey_id))) {
            $data = \ilSession::get($this->getPreviewDataKey($survey_id));
            unset($data[$question_id]);
            \ilSession::set($this->getPreviewDataKey($survey_id), $data);
        }
    }

    public function clearAllPreviewData(int $survey_id) : void
    {
        \ilSession::clear($this->getPreviewDataKey($survey_id));
    }

    public function setErrors(array $errors) : void
    {
        \ilSession::set(self::KEY_ERRORS, $errors);
    }

    public function getErrors() : array
    {
        return \ilSession::get(self::KEY_ERRORS) ?? [];
    }

    public function clearErrors() : void
    {
        \ilSession::clear(self::KEY_ERRORS);
    }

    public function setPostData(array $data) : void
    {
        \ilSession::set(self::KEY_POST_DATA, $data);
    }

    public function getPostData() : array
    {
        return \ilSession::get(self::KEY_POST_DATA);
    }

    public function clearPostData() : void
    {
        \ilSession::clear(self::KEY_POST_DATA);
    }
}
