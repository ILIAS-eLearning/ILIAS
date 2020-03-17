<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Formula Question Unit
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id: class.assFormulaQuestionUnit.php 404 2009-04-27 04:56:49Z hschottm $
* @ingroup ModulesTestQuestionPool
* */
class assFormulaQuestionUnit
{
    private $unit = '';
    private $factor = 0.0;
    private $baseunit = 0;
    private $baseunit_title = '';
    private $id = 0;
    private $category = 0;
    private $sequence = 0;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param array $data
     */
    public function initFormArray(array $data)
    {
        $this->id = $data['unit_id'];
        $this->unit = $data['unit'];
        $this->factor = $data['factor'];
        $this->baseunit = $data['baseunit_fi'];
        $this->baseunit_title = $data['baseunit_title'];
        $this->category = $data['category'];
        $this->sequence = $data['sequence'];
    }

    /**
     * @param string $baseunit_title
     */
    public function setBaseunitTitle($baseunit_title)
    {
        $this->baseunit_title = $baseunit_title;
    }

    /**
     * @return string
     */
    public function getBaseunitTitle()
    {
        return $this->baseunit_title;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    public function getUnit()
    {
        return $this->unit;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function setFactor($factor)
    {
        $this->factor = $factor;
    }

    public function getFactor()
    {
        return $this->factor;
    }

    public function setBaseUnit($baseunit)
    {
        if (is_numeric($baseunit) && $baseunit > 0) {
            $this->baseunit = $baseunit;
        } else {
            $this->baseunit = null;
        }
    }

    public function getBaseUnit()
    {
        if (is_numeric($this->baseunit) && $this->baseunit > 0) {
            return $this->baseunit;
        } else {
            return $this->id;
        }
    }
    
    public function setCategory($category)
    {
        $this->category = $category;
    }
    
    public function getCategory()
    {
        return $this->category;
    }

    public function getDisplayString()
    {
        /**
         * @var $lng ilLanguage
         */
        global $DIC;
        $lng = $DIC['lng'];

        $unit = $this->getUnit();
        if (strcmp('-qpl_qst_formulaquestion_' . $unit . '-', $lng->txt('qpl_qst_formulaquestion_' . $unit)) != 0) {
            $unit = $lng->txt('qpl_qst_formulaquestion_' . $unit);
        }
        return $unit;
    }

    /**
     * @param integer $a_unit_id
     * @return mixed
     */
    public static function lookupUnitFactor($a_unit_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $res = $ilDB->queryF(
            'SELECT factor FROM il_qpl_qst_fq_unit WHERE unit_id = %s',
            array('integer'),
            array($a_unit_id)
        );
        
        $row = $ilDB->fetchAssoc($res);
        
        return $row['factor'];
    }
}
