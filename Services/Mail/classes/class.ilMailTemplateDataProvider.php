<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Mail/classes/class.ilMailTemplate.php';

/**
 * Class ilMailTemplateDataProvider
 * @author            Nadia Ahmad <nahmad@databay.de> 
 */
class ilMailTemplateDataProvider
{
	/**
	 * @var array ilMailTemplate
	 */
	protected $mail_templates = array();

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	/**
	 * ilMailTemplateDataProvider constructor.
	 */
	public function __construct()
	{
		global $DIC;

		$this->db = $DIC->database();

		$this->read();
	}

	/**
	 *
	 */
	private function read()
	{
		$res = $this->db->query('SELECT * FROM mail_man_tpl');
		while($row = $this->db->fetchAssoc($res))
		{
			$this->mail_templates[$row['tpl_id']] = new ilMailTemplate($row);
		}
	}

	/**
	 * @return array
	 */
	public function getTableData()
	{
		$list = array();
		foreach($this->mail_templates as $objMailTpl)
		{
			$list[] = array(
				'tpl_id'  => $objMailTpl->getTplId(),
				'title'   => $objMailTpl->getTitle(),
				'context' => $objMailTpl->getContext(),
				'lang'    => $objMailTpl->getLang()
			);
		}

		return $list;
	}

	/**
	 * @param  int $tpl_id
	 * @return ilMailTemplate
	 */
	public function getTemplateById($tpl_id)
	{
		return $this->mail_templates[$tpl_id];
	}

	/**
	 * @param int $context_id
	 * @return ilMailTemplate[]
	 */
	public function getTemplateByContextId($context_id)
	{
		return array_filter($this->mail_templates, function(ilMailTemplate $template) use ($context_id) {
			return $context_id === $template->getContext();
		});
	}

	/**
	 * @param array $tpl_ids
	 */
	public function deleteTemplates($tpl_ids = array())
	{
		if(count($tpl_ids) > 0)
		{
			$this->db->manipulate('
				DELETE FROM mail_man_tpl WHERE ' .
				$this->db->in('tpl_id', $tpl_ids, false, 'integer')
			);
		}
	}
}