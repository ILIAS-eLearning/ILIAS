<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclDetailedViewDefinition
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDclDetailedViewDefinition extends ilPageObject
{
    const PARENT_TYPE = 'dclf';
    /**
     * @var bool
     */
    protected $active = false;
    /**
     * @var int
     */
    protected $table_id;
    /**
     * @var array Cache record views per table-id, key=table-id, value=view definition id
     */
    protected static $record_view_cache = array();


    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return self::PARENT_TYPE;
    }


    /**
     * Get all placeholders for table id
     *
     * @return array
     * @internal param int $a_table_id
     * @internal param bool $a_verbose
     *
     */
    public function getAvailablePlaceholders()
    {
        $all = array();

        $tableview = new ilDclTableView($this->getId());
        $table_id = $tableview->getTableId();
        $objTable = ilDclCache::getTableCache($table_id);
        $fields = $objTable->getRecordFields();
        $standardFields = $objTable->getStandardFields();

        foreach ($fields as $field) {
            $all[] = "[" . $field->getTitle() . "]";

            if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_REFERENCE) {
                $all[] = '[dclrefln field="' . $field->getTitle() . '"][/dclrefln]';
            }
            // SW 14.10.2015 http://www.ilias.de/mantis/view.php?id=16874
            //				if ($field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_ILIAS_REF) {
            //					$all[] = '[dcliln field="' . $field->getTitle() . '"][/dcliln]';
            //				}
        }

        foreach ($standardFields as $field) {
            $all[] = "[" . $field->getId() . "]";
        }

        return $all;
    }


    public static function exists($id)
    {
        return parent::_exists(self::PARENT_TYPE, $id);
    }


    public static function isActive($id)
    {
        return parent::_lookupActive($id, self::PARENT_TYPE);
    }
}
