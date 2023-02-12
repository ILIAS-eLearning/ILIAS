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
    const COMPLETED = 'http://adlnet.gov/expapi/verbs/completed';
    const PASSED = 'http://adlnet.gov/expapi/verbs/passed';
    const FAILED = 'http://adlnet.gov/expapi/verbs/failed';
    const SATISFIED = 'http://adlnet.gov/expapi/verbs/satisfied';
    const PROGRESSED = 'http://adlnet.gov/expapi/verbs/progressed';
    const EXPERIENCED = 'http://adlnet.gov/expapi/verbs/experienced';

    /**
     * @var array
     */
    protected $verbs = array(
        'http://adlnet.gov/expapi/verbs/abandoned',
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
        'http://adlnet.gov/expapi/verbs/satisfied',
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
        return true;//not necessary for dynamic verbs: in_array($verb, $this->verbs);
    }
    
    /**
     * @param string $shortVerbId
     * @return string
     */
    public function getVerbUri($verb)
    {
        return 'http://adlnet.gov/expapi/verbs/' . $verb;
    }

    /**
     * @return array
     */
    public function getDynamicSelectOptions($verbs)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $options = array(
            '' => $DIC->language()->txt('cmix_all_verbs')
        );
        
        foreach ($verbs as $verb) {
            $verb = $verb['_id'];
            $options[urlencode($verb)] = self::getVerbTranslation(
                $DIC->language(),
                $verb
            );
        }
        
        return $options;
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
        $verbMatch = preg_match('/\/([^\/]+)$/', $verb, $matches);
        $shortVerb = $matches[1];
        $langVar = preg_replace('/http(s)?:\/\//', '', $verb);
        $langVar = str_replace('.', '', $langVar);
        $langVar = str_replace('/', '_', $langVar);
        $langVar = 'cmix_' . $langVar;
        $translatedVerb = $lng->txt($langVar);
        // check no translation found?
        if (strpos($translatedVerb, '-cmix_') === 0) {
            return $shortVerb;
        } else {
            return $translatedVerb;
        }
    }
    
    /**
     * @return ilCmiXapiVerbList
     */
    public static function getInstance()
    {
        return new self();
    }
}
