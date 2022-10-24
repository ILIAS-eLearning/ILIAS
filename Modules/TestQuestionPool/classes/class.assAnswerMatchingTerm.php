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

/**
* Class for matching question terms
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingTerm
{
    public string $text;
    public string $picture;
    public int $identifier;

    /**
    * assAnswerMatchingTerm constructor
    *
    * @param string $text Definition text
    * @param string $picture Definition picture
    * @param integer $identifier Random number identifier
    */
    public function __construct($text = "", $picture = "", $identifier = "")
    {
        if (strlen($identifier) == 0) {
            mt_srand((float) microtime() * 1000000);
            $identifier = mt_rand(1, 100000);
        }
        $this->text = (string) $text;
        $this->picture = (string) $picture;
        $this->identifier = $identifier;
    }
}
