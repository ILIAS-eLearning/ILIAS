<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class for matching question terms
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingTerm
{
    protected $arrData;

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
            mt_srand((double) microtime() * 1000000);
            $identifier = mt_rand(1, 100000);
        }
        $this->arrData = array(
            'text' => $text,
            'picture' => $picture,
            'identifier' => $identifier
        );
    }

    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case "text":
            case "picture":
                if (strlen($this->arrData[$value])) {
                    return $this->arrData[$value];
                } else {
                    return null;
                }
                break;
            case "identifier":
                return $this->arrData[$value];
                break;
            default:
                return null;
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            case "text":
            case "picture":
            case "identifier":
                $this->arrData[$key] = $value;
                break;
            default:
                break;
        }
    }
}
