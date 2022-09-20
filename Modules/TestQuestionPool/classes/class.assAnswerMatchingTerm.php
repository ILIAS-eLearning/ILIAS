<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
