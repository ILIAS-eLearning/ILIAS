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

/**
 * Class ilCmiXapiStatementsReport
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiStatementsReport
{
    protected array $response;

    protected array $statements;

    protected int $maxCount;

    /**
     * @var ilCmiXapiUser[]
     */
    protected array $cmixUsersByIdent;

    protected string $userLanguage;
    /**
    * @var ilObjCmiXapi::CONT_TYPE_GENERIC|ilObjCmiXapi::CONT_TYPE_CMI5
    */
    protected string $contentType;

    protected bool $isMixedContentType;

    public function __construct(string $responseBody, int $objId)
    {
        global $DIC;
        $this->userLanguage = $DIC->user()->getLanguage();

        $responseBody = json_decode($responseBody, true);

        if (ilObject::_lookupType($objId) == 'lti') {
            $this->contentType = ilObjCmiXapi::CONT_TYPE_GENERIC;
            $this->isMixedContentType = ilObjLTIConsumer::getInstance($objId, false)->isMixedContentType();
        } else {
            $this->contentType = ilObjCmiXapi::getInstance($objId, false)->getContentType();
            $this->isMixedContentType = ilObjCmiXapi::getInstance($objId, false)->isMixedContentType();
        }

        if (is_array($responseBody) && count($responseBody) > 0) {
            $this->response = current($responseBody);
            $this->statements = $this->response['statements'];
            $this->maxCount = $this->response['maxcount'];
        } else {
            $this->response = array();
            $this->statements = array();
            $this->maxCount = 0;
        }

        foreach (ilCmiXapiUser::getUsersForObject($objId) as $cmixUser) {
            $this->cmixUsersByIdent[$cmixUser->getUsrIdent()] = $cmixUser;
        }
    }

    public function getMaxCount(): int
    {
        return $this->maxCount;
    }

    /**
     * @return mixed[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function hasStatements(): bool
    {
        return (bool) count($this->statements);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTableData(): array
    {
        $data = [];

        foreach ($this->statements as $statement) {
            $data[] = [
                'date' => $this->fetchDate($statement),
                'actor' => $this->fetchActor($statement),
                'verb_id' => $this->fetchVerbId($statement),
                'verb_display' => $this->fetchVerbDisplay($statement),
                'object' => $this->fetchObjectName($statement),
                'object_info' => $this->fetchObjectInfo($statement),
                'statement' => json_encode($statement, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            ];
        }

        return $data;
    }

    /**
     * @return mixed
     */
    protected function fetchDate(array $statement): string
    {
        return $statement['timestamp'];
    }

    protected function fetchActor(array $statement): \ilCmiXapiUser
    {
        if ($this->isMixedContentType) {
            $ident = str_replace('mailto:', '', $statement['actor']['mbox']);
            if (empty($ident)) {
                $ident = $statement['actor']['account']['name'];
            }
        } elseif ($this->contentType == ilObjCmiXapi::CONT_TYPE_CMI5) {
            $ident = $statement['actor']['account']['name'];
        } else {
            $ident = str_replace('mailto:', '', $statement['actor']['mbox']);
        }
        return $this->cmixUsersByIdent[$ident];
    }

    protected function fetchVerbId(array $statement): string
    {
        return $statement['verb']['id'];
    }

    protected function fetchVerbDisplay(array $statement): string
    {
        try {
            return $statement['verb']['display']['en-US'];
        } catch (Exception $e) {
            return $statement['verb']['id'];
        }
    }

    protected function fetchObjectName(array $statement): string
    {
        try {
            $ret = urldecode($statement['object']['id']);
            if (array_key_exists('definition', $statement['object'])) {
                if (array_key_exists('name', $statement['object']['definition'])) {
                    $lang = self::getLanguageEntry($statement['object']['definition']['name'], $this->userLanguage);
                    if (array_key_exists('languageEntry', $lang)) {
                        $langEntry = $lang['languageEntry'];
                        if ($langEntry != '') {
                            $ret = $langEntry;
                        }
                    }
                }
            }
            return $ret;
        } catch (Exception $e) {
            ilObjCmiXapi::log()->error('error:' . $e->getMessage());
            return "";
        }
    }

    protected function fetchObjectInfo(array $statement): ?string
    {
        try {
            return $statement['object']['definition']['description']['en-US'];
        } catch (Exception $e) {
            ilObjCmiXapi::log()->debug('debug:' . $e->getMessage());
            return "";
        }
    }

    /**
     *  with multiple language keys like [de-DE] [en-US]
     * @return array<string, mixed>
     */
    public static function getLanguageEntry(array $obj, string $userLanguage): array
    {
        $defaultLanguage = 'en-US';
        $defaultLanguageEntry = '';
        $defaultLanguageExists = false;
        $firstLanguage = '';
        $firstLanguageEntry = '';
        $firstLanguageExists = false;
        $userLanguage = '';
        $userLanguageEntry = '';
        $userLanguageExists = false;
        $language = '';
        $languageEntry = '';
        try {
            foreach ($obj as $k => $v) {
                // save $firstLanguage
                if ($firstLanguage == '') {
                    $f = '/^[a-z]+-?.*/';
                    if (preg_match($f, $k)) {
                        $firstLanguageExists = true;
                        $firstLanguage = $k;
                        $firstLanguageEntry = $v;
                    }
                }
                // check defaultLanguage
                if ($k == $defaultLanguage) {
                    $defaultLanguageExists = true;
                    $defaultLanguageEntry = $v;
                }
                // check userLanguage
                $p = '/^' . $userLanguage . '-?./';
                preg_match($p, $k);
                if (preg_match($p, $k)) {
                    $userLanguageExists = true;
                    $userLanguage = $k;
                    $userLanguageEntry = $v;
                }
            }
        } catch (Exception $e) {
        };

        if ($userLanguageExists) {
            $language = $userLanguage;
            $languageEntry = $userLanguageEntry;
        } elseif ($defaultLanguageExists) {
            $language = $userLanguage;
            $languageEntry = $userLanguageEntry;
        } elseif ($firstLanguageExists) {
            $language = $firstLanguage;
            $languageEntry = $firstLanguageEntry;
        }
        return ['language' => $language, 'languageEntry' => $languageEntry];
    }
}
