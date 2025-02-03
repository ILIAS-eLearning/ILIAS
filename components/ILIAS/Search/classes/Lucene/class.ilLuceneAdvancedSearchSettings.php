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

declare(strict_types=1);
/**
* En/disable single lom/advanced meta data fields
*
* @author Stefan Meyer <meyer@leifos.com>
* @ingroup ServicesSearch
*/
class ilLuceneAdvancedSearchSettings
{
    private static ?ilLuceneAdvancedSearchSettings $instance = null;
    private array $fields = [];

    protected ilSetting $storage;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->storage = new ilSetting('lucene_adv_search');
        $this->read();
    }

    public static function getInstance(): ilLuceneAdvancedSearchSettings
    {
        if (self::$instance instanceof ilLuceneAdvancedSearchSettings) {
            return self::$instance;
        }
        return self::$instance = new ilLuceneAdvancedSearchSettings();
    }

    /**
     * check if field is active
     */
    public function isActive(string $a_field): bool
    {
        return $this->fields[$a_field] ?: false;
    }

    public function setActive(string $a_field, bool $a_status): void
    {
        $this->fields[$a_field] = $a_status;
    }

    public function save(): void
    {
        foreach ($this->fields as $name => $status) {
            $this->storage->set($name, $status ? "1" : "0");
        }
    }

    private function read(): void
    {
        foreach (ilLuceneAdvancedSearchFields::getFields() as $name => $translation) {
            $this->fields[$name] = (bool) $this->storage->get($name, 'true');
        }
    }
}
