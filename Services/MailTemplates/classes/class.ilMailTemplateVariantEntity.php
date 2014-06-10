<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilMailTemplateVariantEntity
{
	protected $id;
	protected $mail_types_fi;
	protected $language;
	protected $message_subject;
	protected $message_plain;
	protected $message_html;
	protected $created_date;
	protected $updated_date;
	protected $updated_usr_fi;
	protected $template_active;

	/** @var $ilDB ilDBMySql */
	protected $ilDB;

	public function setCreatedDate( $created_date )
	{
		$this->created_date = $created_date;
	}

	public function getCreatedDate()
	{
		return $this->created_date;
	}
	public function setMessageSubject( $message_subject )
	{
		$this->message_subject = $message_subject;
	}

	public function getMessageSubject()
	{
		return $this->message_subject;
	}

	public function setId( $id )
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setLanguage( $language )
	{
		$this->language = $language;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function setMailTypesFi( $mail_types_fi )
	{
		$this->mail_types_fi = $mail_types_fi;
	}

	public function getMailTypesFi()
	{
		return $this->mail_types_fi;
	}

	public function setMessageHtml( $message_html )
	{
		$this->message_html = $message_html;
	}

	public function getMessageHtml()
	{
		return $this->message_html;
	}

	public function setMessagePlain( $message_plain )
	{
		$this->message_plain = $message_plain;
	}

	public function getMessagePlain()
	{
		return $this->message_plain;
	}

	public function setTemplateActive( $template_active )
	{
		$this->template_active = $template_active;
	}

	public function getTemplateActive()
	{
		return $this->template_active;
	}

	public function setUpdatedDate( $updated_date )
	{
		$this->updated_date = $updated_date;
	}

	public function getUpdatedDate()
	{
		return $this->updated_date;
	}

	public function setUpdatedUsrFi( $updated_usr_fi )
	{
		$this->updated_usr_fi = $updated_usr_fi;
	}

	public function getUpdatedUsrFi()
	{
		return $this->updated_usr_fi;
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

	public function loadByTypeAndLanguage($mail_types_fi, $language)
	{
		$query = "SELECT id, mail_types_fi, language, message_subject, message_plain, message_html, created_date, 
			updated_date, updated_usr_fi, template_active FROM cat_mail_variants WHERE mail_types_fi = %s AND language = %s";
		$result = $this->ilDB->queryF(
			$query,
			array('integer', 'text'),
			array($mail_types_fi, $language)
		);

		if ($this->ilDB->numRows($result) != 1)
		{
			throw new Exception('Illegal response: numRows != 1 in '. $query);
		}
		$this->populateFromRow( $this->ilDB->fetchAssoc($result) );

		return;
	}
	
	public function existsByTypeAndLanguage()
	{
		$query = "SELECT id FROM cat_mail_variants WHERE mail_types_fi = %s AND language = %s";
		$result = $this->ilDB->queryF(
			$query,
			array('integer', 'text'),
			array($this->getMailTypesFi(), $this->getLanguage())
		);

		if ($this->ilDB->numRows($result) == 0)
		{
			return false;
		}
		return true;		
	}
	
	public function getEmptyVariant()
	{
		return;
	}

	protected function populateFromRow($row)
	{
		$this->setId($row['id']);
		$this->setMessageSubject($row['message_subject']);
		$this->setMessagePlain($row['message_plain']);
		$this->setMessageHtml($row['message_html']);
		$this->setCreatedDate($row['created_date']);
		$this->setUpdatedDate($row['updated_date']);
		$this->setUpdatedUsrFi($row['updated_usr_fi']);
		$this->setTemplateActive($row['template_active']);
		return;
	}

	public function save($a_mail_template_variant_entity = null)
	{
		if ($a_mail_template_variant_entity == null)
		{
			$a_mail_template_variant_entity = $this;
		}

		if ($a_mail_template_variant_entity->getId() == null)
		{
			$this->createEntity($a_mail_template_variant_entity);
		}
		else
		{
			$this->updateEntity($a_mail_template_variant_entity);
		}
		return;
	}

	protected function createEntity($a_template_variant_entity)
	{
		$this->ilDB->insert(
			'cat_mail_variants',
			array(
				'id'				=> array('integer',	$this->ilDB->nextId('cat_mail_variants')						),
				'mail_types_fi'		=> array('integer',	$a_template_variant_entity->getMailTypesFi()		        ),
				'language'		    => array('text', 	$a_template_variant_entity->getLanguage()           		),
				'message_subject'   => array('text',    $a_template_variant_entity->getMessageSubject()             ),
				'message_plain'	    => array('text', 	$a_template_variant_entity->getMessagePlain()	            ),
				'message_html'      => array('text',    $a_template_variant_entity->getMessageHtml()                ),
				'created_date'      => array('integer', time()                										),
				'updated_date'      => array('integer', time()                										),
				'updated_usr_fi'    => array('integer', $a_template_variant_entity->getUpdatedUsrFi()               ),
				'template_active'   => array('integer', $a_template_variant_entity->getTemplateActive()             )
			)
		);
	}

	protected function updateEntity($a_template_variant_entity)
	{
		$this->ilDB->update(
			'cat_mail_variants',
			array(
				'mail_types_fi'		=> array('integer',	$a_template_variant_entity->getMailTypesFi()		        ),
				'language'		    => array('text', 	$a_template_variant_entity->getLanguage()           		),
				'message_subject'   => array('text',    $a_template_variant_entity->getMessageSubject()             ),
				'message_plain'	    => array('text', 	$a_template_variant_entity->getMessagePlain()	            ),
				'message_html'      => array('text',    $a_template_variant_entity->getMessageHtml()                ),
				'created_date'      => array('integer', $a_template_variant_entity->getCreatedDate()                ),
				'updated_date'      => array('integer', $a_template_variant_entity->getUpdatedDate()                ),
				'updated_usr_fi'    => array('integer', $a_template_variant_entity->getUpdatedUsrFi()               ),
				'template_active'   => array('integer', $a_template_variant_entity->getTemplateActive()             )
			),
			array(
				'id'				=> array('integer', $a_template_variant_entity->getId()    						)
			)
		);
	}

}
