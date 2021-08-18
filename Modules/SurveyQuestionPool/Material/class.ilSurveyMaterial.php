<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Survey material class
 *
 * @author		Helmut Schottmüller <ilias@aurealis.de>
 */
class ilSurveyMaterial
{
    const MATERIAL_TYPE_INTERNALLINK = 0;
    const MATERIAL_TYPE_URL = 1;
    const MATERIAL_TYPE_FILE = 2;
    
    protected $data;

    /**
    * ilSurveyMaterial constructor
    */
    public function __construct()
    {
        $this->data = array(
            'type' => self::MATERIAL_TYPE_INTERNALLINK,
            'internal_link' => '',
            'title' => '',
            'url' => '',
            'filename' => ''
        );
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            switch ($name) {
                case 'internal_link':
                case 'import_id':
                case 'material_title':
                case 'text_material':
                case 'file_material':
                case 'external_link':
                    return (strlen($this->data[$name])) ? $this->data[$name] : null;
                    break;
                default:
                    return $this->data[$name];
            }
        }
        return null;
    }
}
