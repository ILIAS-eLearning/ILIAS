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
		$this->id             = $data['unit_id'];
		$this->unit           = $data['unit'];
		$this->factor         = $data['factor'];
		$this->baseunit       = $data['baseunit_fi'];
		$this->baseunit_title = $data['baseunit_title'];
		$this->category       = $data['category'];
		$this->sequence       = $data['sequence'];
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
	
	function setId($id)
	{
		$this->id = $id;
	}

	function getId()
	{
		return $this->id;
	}

	function setUnit($unit)
	{
		$this->unit = $unit;
	}

	function getUnit()
	{
		return $this->unit;
	}

	function setSequence($sequence)
	{
		$this->sequence = $sequence;
	}

	function getSequence()
	{
		return $this->sequence;
	}

	function setFactor($factor)
	{
		$this->factor = $factor;
	}

	function getFactor()
	{
		return $this->factor;
	}

	function setBaseUnit($baseunit)
	{
		if (is_numeric($baseunit) && $baseunit > 0)
		{
			$this->baseunit = $baseunit;
		}
		else
		{
			$this->baseunit = null;
		}
	}

	function getBaseUnit()
	{
		if (is_numeric($this->baseunit) && $this->baseunit > 0)
		{
			return $this->baseunit;
		}
		else
		{
			return $this->id;
		}
	}
	
	function setCategory($category)
	{
		$this->category = $category;
	}
	
	function getCategory()
	{
		return $this->category;
	}

	function getDisplayString()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$unit = $this->getUnit();
		if(strcmp('-qpl_qst_formulaquestion_' . $unit . '-', $lng->txt('qpl_qst_formulaquestion_' . $unit)) != 0)
		{
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
		global $ilDB;
		
		$res = $ilDB->queryF('SELECT factor FROM il_qpl_qst_fq_unit WHERE unit_id = %s',
		array('integer'), array($a_unit_id));
		
		$row = $ilDB->fetchAssoc($res);
		
		return $row['factor'];
	}
}