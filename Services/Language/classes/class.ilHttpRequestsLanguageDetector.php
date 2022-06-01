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
 ********************************************************************
 */

require_once "Services/Language/interfaces/interface.ilLanguageDetector.php";

/**
 * Class ilHttpRequestsLanguageDetector
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup Services/Language
 */
class ilHttpRequestsLanguageDetector implements ilLanguageDetector
{
    protected string $header_value;

    public function __construct(string $header_value)
    {
        $this->header_value = $header_value;
    }

    /**
     * Returns the detected ISO2 language code
     * @throws ilLanguageException
     */
    public function getIso2LanguageCode() : string
    {
        if (strlen($this->header_value)) {
            $matches = array();
            // Format: de,de-DE;q=0.8,en-US;q=0.6,en;q=0.4
            preg_match_all("/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i", $this->header_value, $matches);
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

        require_once "Services/Language/exceptions/class.ilLanguageException.php";
        throw new ilLanguageException("Could not extract any language information from request.");
    }
}
