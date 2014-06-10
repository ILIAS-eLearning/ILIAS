<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailTemplateSettingsEntity
{
	protected $template_type_id = null;
	protected $template_category_name = null;
	protected $template_template_type = null;
	protected $template_consumer_location = null;

	protected $ilDB = null;
	
	public function setTemplateCategoryName($template_category_name)
	{
		$this->template_category_name = $template_category_name;
		return;
	}

	public function getTemplateCategoryName()
	{
		return $this->template_category_name;
	}

	public function setTemplateConsumerLocation($template_consumer_location)
	{
		$this->template_consumer_location = $template_consumer_location;
		return;
	}

	public function getTemplateConsumerLocation()
	{
		return $this->template_consumer_location;
	}

	public function setTemplateTemplateType($template_template_type)
	{
		$this->template_template_type = $template_template_type;
		return;
	}

	public function getTemplateTemplateType()
	{
		return $this->template_template_type;
	}

	public function setTemplateTypeId($template_type_id)
	{
		$this->template_type_id = $template_type_id;
		return;
	}

	public function getTemplateTypeId()
	{
		return $this->template_type_id;
	}

	public function setIlDB($ilDB)
	{
		$this->ilDB = $ilDB;
		return;
	}

	public function getIlDB()
	{
		return $this->ilDB;
	}

	public function loadById($a_id)
	{
		$query = "SELECT id, category_name, template_type, consumer_location FROM cat_mail_templates WHERE id = %s";
		$result = $this->ilDB->queryF(
						$query,
						array('integer'),
						array($a_id)
					);
		
		if ($this->ilDB->numRows($result) != 1)
		{
			throw new Exception('Illegal response: numRows != 1 in '. $query);
		}
		$this->populateFromRow( $this->ilDB->fetchAssoc($result) );
		
		return;
	}
	
	public function loadByCategoryAndTemplate($a_category, $a_template)
	{
		$query = "SELECT id, category_name, template_type, consumer_location 
				  FROM cat_mail_templates 
				  WHERE category_name = %s AND template_type = %s";
		$result = $this->ilDB->queryF(
			$query,
			array('text', 'text'),
			array($a_category, $a_template)
		);

		if ($this->ilDB->numRows($result) != 1)
		{
			throw new Exception('Illegal response: numRows != 1 in '. $query);
		}
		
		$this->populateFromRow( $this->ilDB->fetchAssoc($result) );

		return;		
	}
	
	protected function populateFromRow($row)
	{
		$this->setTemplateTypeId($row['id']);
		$this->setTemplateCategoryName($row['category_name']);
		$this->setTemplateTemplateType($row['template_type']);
		$this->setTemplateConsumerLocation($row['consumer_location']);
		return;
	}
	
	public function save($a_mail_template_settings_entity = null)
	{
		if ($a_mail_template_settings_entity == null)
		{
			$a_mail_template_settings_entity = $this;
		}
		
		if ($a_mail_template_settings_entity->getTemplateTypeId() == null)
		{
			$this->createEntity($a_mail_template_settings_entity);
		}
		else
		{
			$this->updateEntity($a_mail_template_settings_entity);	
		}
		return;
	}
	
	protected function createEntity($a_template_settings_entity = null)
	{
		if ($a_template_settings_entity == null)
		{
			$a_template_settings_entity = $this;
		}
		
		$this->ilDB->insert(
			'cat_mail_templates', 
			array(
				'id'				=> array('integer',	$this->ilDB->nextId('cat_mail_templates')						),
				'category_name'		=> array('text', 	$a_template_settings_entity->getTemplateCategoryName()		),
				'template_type'		=> array('text', 	$a_template_settings_entity->getTemplateTemplateType()		),
				'consumer_location'	=> array('text', 	$a_template_settings_entity->getTemplateConsumerLocation()	)
			)
		);
	}
	
	protected function updateEntity($a_template_settings_entity = null)
	{
		if ($a_template_settings_entity == null)
		{
			$a_template_settings_entity = $this;
		}
		
		$this->ilDB->update(
			'cat_mail_templates',
			array(
				'category_name'		=> array('text', 	$a_template_settings_entity->getTemplateCategoryName()		),
				'template_type'		=> array('text', 	$a_template_settings_entity->getTemplateTemplateType()		),
				'consumer_location'	=> array('text', 	$a_template_settings_entity->getTemplateConsumerLocation()	)
			),
			array(
				'id'				=> array('integer', 	$this->getTemplateTypeId()									)
			)
		);		
	}
	
	public function deleteEntity($a_template_settings_entity = null)
	{
		if ($a_template_settings_entity == null)
		{
			$a_template_settings_entity = $this;
		}
		
		$this->ilDB->manipulateF(
			'DELETE FROM cat_mail_templates WHERE id = %s', 
			array('integer'), 
			array($a_template_settings_entity->getTemplateTypeId())
		);
		
		$this->ilDB->manipulateF(
			'DELETE FROM cat_mail_variants WHERE mail_types_fi = %s',
			array('integer'),
			array($a_template_settings_entity->getTemplateTypeId())
		);
	}
	
	public function getAdapterClassInstance()
	{
		require_once './'.$this->getTemplateConsumerLocation();
		$name = basename($this->getTemplateConsumerLocation(), '.php');
		$name = substr($name, 6, strlen($name)-6);
		return new $name;
	}
}
