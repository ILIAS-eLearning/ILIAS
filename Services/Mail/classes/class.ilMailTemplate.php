<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplate
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailTemplate
{
	/**
	 * @var int
	 */
	protected $tpl_id = 0;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
 	 * @var string
 	 */
	protected $context = '';

	/**
 	 * @var string
 	 */
	protected $lang = '';

	/**
 	 * @var string
 	 */
	protected $m_subject = '';

	/**
 	 * @var string
 	 */
	protected $m_message = '';

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	/**
	 * @param array $data
	 */
	public function __construct($data = NULL)
	{
		global $DIC;

		$this->db = $DIC->database();

		if($data)
		{
			$this->setTplId($data['tpl_id']);
			$this->setTitle($data['title']);
			$this->setContext($data['context']);
			$this->setLang($data['lang']);
			$this->setSubject($data['m_subject']);
			$this->setMessage($data['m_message']);
		}
	}

	/**
	 * @return int
	 */
	public function getTplId()
	{
		return $this->tpl_id;
	}

	/**
	 * @param int $tpl_id
	 */
	public function setTplId($tpl_id)
	{
		$this->tpl_id = $tpl_id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * @param string $context
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}

	/**
	 * @return string
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * @param string $lang
	 */
	public function setLang($lang)
	{
		$this->lang = $lang;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->m_subject;
	}

	/**
	 * @param string $m_subject
	 */
	public function setSubject($m_subject)
	{
		$this->m_subject = $m_subject;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->m_message;
	}

	/**
	 * @param string $m_message
	 */
	public function setMessage($m_message)
	{
		$this->m_message = $m_message;
	}

	public function insert()
	{
		$next_id = $this->db->nextId('mail_man_tpl');
		$this->db->insert('mail_man_tpl', array(
			'tpl_id'    => array('integer', $next_id),
			'title'     => array('text', $this->getTitle()),
			'context'   => array('text', $this->getContext()),
			'lang'      => array('text', $this->getLang()),
			'm_subject' => array('text', $this->getSubject()),
			'm_message' => array('text', $this->getMessage())
		));
	}

	public function update()
	{
		$this->db->update('mail_man_tpl',
			array(
				'title'     => array('text', $this->getTitle()),
				'context'   => array('text', $this->getContext()),
				'lang'      => array('text', $this->getLang()),
				'm_subject' => array('text', $this->getSubject()),
				'm_message' => array('text', $this->getMessage())
			),
			array(
				'tpl_id' => array('integer', $this->getTplId()),
			));
	}

	/**
	 * @param array $tpl_ids
	 */
	public function delete($tpl_ids = array())
	{
	
	}
}
