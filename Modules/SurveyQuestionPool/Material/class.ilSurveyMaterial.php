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
 * Survey material class
 * @author		Helmut Schottmüller <ilias@aurealis.de>
 * @todo move to propert dto, get rid of magic functions
 */
class ilSurveyMaterial
{
    public const MATERIAL_TYPE_INTERNALLINK = 0;
    public const MATERIAL_TYPE_URL = 1;
    public const MATERIAL_TYPE_FILE = 2;
    protected array $data;

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

    public function __set(string $name, string $value): void
    {
        $this->data[$name] = $value;
    }

    public function __get(string $name): ?string
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
                default:
                    return $this->data[$name];
            }
        }
        return null;
    }
}
