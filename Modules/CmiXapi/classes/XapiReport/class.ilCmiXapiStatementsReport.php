<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    /**
     * @var array
     */
    protected $response;
    
    /**
     * @var array
     */
    protected $statements;
    
    /**
     * @var int
     */
    protected $maxCount;
    
    /**
     * @var ilCmiXapiUser[]
     */
    protected $cmixUsersByIdent;

    /**
     * @var string
     */
    protected $userLanguage;
     /**
     * @var ilObjCmiXapi::CONT_TYPE_GENERIC|CONT_TYPE_CMI5
     */
    protected $contentType;
    
     /**
     * @var bool
     */
    protected $isMixedContentType;

    public function __construct(string $responseBody, $objId)
    {
        global $DIC;
        $this->userLanguage = $DIC->user()->getLanguage();

        $responseBody = json_decode($responseBody, true);
        
        $this->contentType = ilObjCmiXapi::getInstance($objId,false)->getContentType();
        
        $this->isMixedContentType = ilObjCmiXapi::getInstance($objId,false)->isMixedContentType();
        
        if (count($responseBody)) {
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
    
    public function getMaxCount()
    {
        return $this->maxCount;
    }
    
    public function getStatements()
    {
        return $this->statements;
    }
    
    public function hasStatements()
    {
        return (bool) count($this->statements);
    }
    
    public function getTableData()
    {
        $data = [];
        
        foreach ($this->statements as $index => $statement) {
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
    
    protected function fetchDate($statement)
    {
        return $statement['timestamp'];
    }
    
    protected function fetchActor($statement)
    {
        if ($this->isMixedContentType)
        {
            $ident = str_replace('mailto:', '', $statement['actor']['mbox']);
            if (empty($ident)) {
                $ident = $statement['actor']['account']['name'];    
            }
        }
        elseif ($this->contentType == ilObjCmiXapi::CONT_TYPE_CMI5)
        {
            $ident = $statement['actor']['account']['name'];
        }
        else
        {
            $ident = str_replace('mailto:', '', $statement['actor']['mbox']);
        }
        return $this->cmixUsersByIdent[$ident];
    }
    
    protected function fetchVerbId($statement)
    {
        return $statement['verb']['id'];
    }
    
    protected function fetchVerbDisplay($statement)
    {
        return $statement['verb']['display']['en-US'];
    }
    
    protected function fetchObjectName($statement)
    {  
        $ret = urldecode($statement['object']['id']);   
        $lang = self::getLanguageEntry($statement['object']['definition']['name'],$this->userLanguage);
        $langEntry = $lang['languageEntry'];
        if ($langEntry != '') 
        {
            $ret = $langEntry;
        }
        return $ret;
    }
    
    protected function fetchObjectInfo($statement)
    {
        return $statement['object']['definition']['description']['en-US'];
    }

    /**
     * @var array
     *  with multiple language keys like [de-DE] [en-US]
     */
    
    public static function getLanguageEntry($obj,$userLanguage)
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
            foreach ($obj as $k => $v) 
            {
                // save $firstLanguage
                if ($firstLanguage == '')
                {
                    $f = '/^[a-z]+\-?.*/';
                    if (preg_match($f,$k))
                    {
                        $firstLanguageExists = true;
                        $firstLanguage = $k;
                        $firstLanguageEntry = $v;
                    }
                }
                // check defaultLanguage
                if ($k == $defaultLanguage)
                {
                    $defaultLanguageExists = true;
                    $defaultLanguageEntry = $v;
                }
                // check userLanguage
                $p = '/^' . $userLanguage . '\-?./';
                preg_match($p,$k);
                if (preg_match($p,$k))
                {
                    $userLanguageExists = true;
                    $userLanguage = $k;
                    $userLanguageEntry = $v; 
                }
            }
        }
        catch (Exception $e) {};

        if ($userLanguageExists)
        {
            $language = $userLanguage;
            $languageEntry = $userLanguageEntry;
        }
        elseif ($defaultLanguageExists)
        {
            $language = $userLanguage;
            $languageEntry = $userLanguageEntry;
        }
        elseif ( $firstLanguageExists)
        {
            $language = $firstLanguage;
            $languageEntry = $firstLanguageEntry;
        }
        return ['language' => $language, 'languageEntry' => $languageEntry];
    }
}
