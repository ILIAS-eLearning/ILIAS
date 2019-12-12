<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Language/interfaces/interface.ilLanguageDetector.php';

/**
 * Class ilHttpRequestsLanguageDetector
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilHttpRequestsLanguageDetector implements ilLanguageDetector
{
    /**
     * @var string
     */
    protected $header_value;

    /**
     * @param array $header_value
     */
    public function __construct($header_value)
    {
        $this->header_value = $header_value;
    }

    /**
     * Returns the detected ISO2 language code
     * @throws ilLanguageException
     * @return string
     */
    public function getIso2LanguageCode()
    {
        if (strlen($this->header_value)) {
            $matches  = array();
            // Format: de,de-DE;q=0.8,en-US;q=0.6,en;q=0.4
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $this->header_value, $matches);
            if (count($matches[1])) {
                $langs = array_combine($matches[1], $matches[4]);
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }

                arsort($langs, SORT_NUMERIC);

                $keys = array_keys($langs);
                if (isset($keys[0])) {
                    return substr($keys[0], 0, 2);
                }
            }
        }

        require_once 'Services/Language/exceptions/class.ilLanguageException.php';
        throw new ilLanguageException('Could not extract any language information from request.');
    }
}
