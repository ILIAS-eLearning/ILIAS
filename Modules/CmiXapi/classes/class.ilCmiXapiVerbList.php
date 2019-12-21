<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiVerbList
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiVerbList
{
    /**
     * @var array
     */
    protected $verbs = array(
        'http://adlnet.gov/expapi/verbs/answered',
        'http://adlnet.gov/expapi/verbs/asked',
        'http://adlnet.gov/expapi/verbs/attempted',
        'http://adlnet.gov/expapi/verbs/attended',
        'http://adlnet.gov/expapi/verbs/commented',
        'http://adlnet.gov/expapi/verbs/completed',
        'http://adlnet.gov/expapi/verbs/exited',
        'http://adlnet.gov/expapi/verbs/experienced',
        'http://adlnet.gov/expapi/verbs/failed',
        'http://adlnet.gov/expapi/verbs/imported',
        'http://adlnet.gov/expapi/verbs/initialized',
        'http://adlnet.gov/expapi/verbs/interacted',
        'http://adlnet.gov/expapi/verbs/launched',
        'http://adlnet.gov/expapi/verbs/mastered',
        'http://adlnet.gov/expapi/verbs/passed',
        'http://adlnet.gov/expapi/verbs/preferred',
        'http://adlnet.gov/expapi/verbs/progressed',
        'http://adlnet.gov/expapi/verbs/registered',
        'http://adlnet.gov/expapi/verbs/responded',
        'http://adlnet.gov/expapi/verbs/resumed',
        'http://adlnet.gov/expapi/verbs/scored',
        'http://adlnet.gov/expapi/verbs/shared',
        'http://adlnet.gov/expapi/verbs/suspended',
        'http://adlnet.gov/expapi/verbs/terminated',
        'http://adlnet.gov/expapi/verbs/voided'
    );
    
    /**
     * @param string $verbId
     * @return bool
     */
    public function isValidVerb($verb)
    {
        return in_array($verb, $this->verbs);
    }
    
    /**
     * @return array
     */
    public function getSelectOptions()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $options = array(
            '' => $DIC->language()->txt('cmix_all_verbs')
        );
        
        foreach ($this->verbs as $verb) {
            $options[urlencode($verb)] = self::getVerbTranslation(
                $DIC->language(),
                $verb
            );
        }
        
        return $options;
    }
    
    /**
     * @param ilLanguage $lng
     * @param string $verb
     * @return string
     */
    public static function getVerbTranslation(ilLanguage $lng, $verb)
    {
        $langVar = str_replace('http://', '', $verb);
        $langVar = str_replace('.', '', $langVar);
        $langVar = str_replace('/', '_', $langVar);
        $langVar = 'cmix_' . $langVar;
        
        return $lng->txt($langVar);
    }
    
    /**
     * @return ilCmiXapiVerbList
     */
    public static function getInstance()
    {
        return new self();
    }
}
