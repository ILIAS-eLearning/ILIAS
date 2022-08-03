<?php declare(strict_types=1);

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
    
    protected array $verbs = [
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
    ];

    public function isValidVerb(string $verb) : bool
    {
        return in_array($verb, $this->verbs);
    }

    public function getVerbUri(string $verb) : string
    {
        return 'http://adlnet.gov/expapi/verbs/' . $verb;
    }
    
    /**
     * @return array<string, mixed>
     */
    public function getSelectOptions() : array
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

    public static function getVerbTranslation(ilLanguage $lng, string $verb) : string
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
    
    public static function getInstance() : \ilCmiXapiVerbList
    {
        return new self();
    }
}
