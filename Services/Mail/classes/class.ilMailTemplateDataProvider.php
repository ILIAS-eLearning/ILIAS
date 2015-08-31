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

	public function __construct()
	{
		$this->read();
	}

	/**
	 *
	 */
	private function read()
	{
		global $ilDB;

		$res = $ilDB->query('SELECT * FROM mail_man_tpl');
		while($row = $ilDB->fetchAssoc($res))
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
	public function getTemplateByContexId($context_id)
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
		global $ilDB;

		if(count($tpl_ids) > 0)
		{
			$ilDB->manipulate('
			DELETE FROM mail_man_tpl WHERE ' .
				$ilDB->in('tpl_id', $tpl_ids, false, 'integer')
			);
		}			
	}
}