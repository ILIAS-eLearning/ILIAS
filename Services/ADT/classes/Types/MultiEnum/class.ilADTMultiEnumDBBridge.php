<?php

require_once "Services/ADT/classes/Bridges/class.ilADTMultiDBBridge.php";

class ilADTMultiEnumDBBridge extends ilADTDBBridge
{
    const TABLE_NAME = 'adv_md_values_enum';

    /**
     * @var ilDBInterface
     */
    protected $db;

    protected $fake_single;
    
    const SEPARATOR = "~|~";

    public function __construct(ilADT $a_adt)
    {
        global $DIC;

        $this->db = $DIC->database();
        parent::__construct($a_adt);
    }

    public function getTable() : string
    {
        return self::TABLE_NAME;
    }

    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTMultiEnum);
    }
    
    public function setFakeSingle($a_status)
    {
        $this->fake_single = (bool) $a_status;
    }
    
    protected function doSingleFake()
    {
        return $this->fake_single;
    }
    
    public function readRecord(array $a_row)
    {
        if (isset($a_row[$this->getElementId()])) {
            $this->getADT()->addSelection($a_row[$this->getElementId()]);
        }
    }

    public function afterInsert()
    {
        return $this->afterUpdate();
    }

    public function afterUpdate()
    {
        $this->deleteIndices();
        $this->insertIndices();
    }

    public function prepareInsert(array &$a_fields)
    {
        $a_fields = [];
    }

    protected function deleteIndices()
    {
        $this->db->query(
            'delete from ' . $this->getTable() . ' ' .
            'where ' . $this->buildPrimaryWhere()
        );
    }

    protected function insertIndices()
    {
        foreach ($this->getADT()->getSelections() as $index)
        {
            $fields = $this->getPrimary();
            $fields['value_index'] = [ilDBConstants::T_INTEGER, $index];
            $num_row  = $this->db->insert($this->getTable(), $fields);
            ilLoggerFactory::getLogger('amet')->dump($num_row);
        }
    }

    public function supportsDefaultValueColumn() : bool
    {
        return false;
    }
}
