<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Survey category class
 *
 * The ilSurveyCategory class encapsules a survey category
 *
 * @author		Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyCategory
{
    private $arrData;
    
    /**
    * ilSurveyPhrases constructor
    */
    public function __construct($title = null, $other = 0, $neutral = 0, $label = null, $scale = null)
    {
        $this->arrData = array(
            "title" => $title,
            "other" => $other,
            "neutral" => $neutral,
            "label" => $label,
            "scale" => $scale
        );
    }
    
    /**
    * Object getter
    */
    public function __get($value)
    {
        switch ($value) {
            case 'other':
            case 'neutral':
                return ($this->arrData[$value]) ? 1 : 0;
                break;
            default:
                if (array_key_exists($value, $this->arrData)) {
                    return $this->arrData[$value];
                } else {
                    return null;
                }
                break;
        }
    }

    /**
    * Object setter
    */
    public function __set($key, $value)
    {
        switch ($key) {
            default:
                $this->arrData[$key] = $value;
                break;
        }
    }
}
