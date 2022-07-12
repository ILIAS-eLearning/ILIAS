<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected string $response;
    
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
        
        $this->contentType = ilObjCmiXapi::getInstance($objId, false)->getContentType();
        
        $this->isMixedContentType = ilObjCmiXapi::getInstance($objId, false)->isMixedContentType();
        
        if (count($responseBody) > 0) {
            $this->response = current($responseBody);
            $this->statements = $this->response['statements'];
            $this->maxCount = $this->response['maxcount'];
        } else {
            $this->response = '';
            $this->statements = array();
            $this->maxCount = 0;
        }
        
        foreach (ilCmiXapiUser::getUsersForObject($objId) as $cmixUser) {
            $this->cmixUsersByIdent[$cmixUser->getUsrIdent()] = $cmixUser;
        }
    }
    
    public function getMaxCount() : int
    {
        return $this->maxCount;
    }
    
    /**
     * @return mixed[]
     */
    public function getStatements() : array
    {
        return $this->statements;
    }
    
    public function hasStatements() : bool
    {
        return (bool) count($this->statements);
    }
    
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTableData() : array
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
    protected function fetchDate(array $statement)
    {
        return $statement['timestamp'];
    }
    
    protected function fetchActor(array $statement) : \ilCmiXapiUser
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
    
    protected function fetchVerbId(array $statement) : string
    {
        return $statement['verb']['id'];
    }
    
    protected function fetchVerbDisplay(array $statement) : string
    {
        return $statement['verb']['display']['en-US'];
    }
    
    protected function fetchObjectName(array $statement) : string
    {
        $ret = urldecode($statement['object']['id']);
        $lang = self::getLanguageEntry($statement['object']['definition']['name'], $this->userLanguage);
        $langEntry = $lang['languageEntry'];
        if ($langEntry != '') {
            $ret = $langEntry;
        }
        return $ret;
    }
    
    protected function fetchObjectInfo(array $statement) : string
    {
        return $statement['object']['definition']['description']['en-US'];
    }

    /**
     *  with multiple language keys like [de-DE] [en-US]
     * @return array<string, mixed>
     */
    public static function getLanguageEntry(array $obj, string $userLanguage) : array
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
