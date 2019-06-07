<?php

declare(strict_types=1);

/**
 * Class ilObjStudyProgrammeAutoCategoriesGUI
 *
 * @author: Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilStudyProgrammeAutoCategoriesTableGUI extends ilTable2GUI
{

	public function __construct(
		$a_parent_obj,
		$a_parent_cmd="",
		$a_template_context=""
	) {

		$this->setId("sp_ac_list");
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.autocats_table_row.html", "Modules/StudyProgramme");
		$this->setShowRowsSelector(false);
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));

		$this->addColumn("", "", "1", true);
		$this->addColumn($this->lng->txt('title'), 'title');
		$this->addColumn($this->lng->txt('last_edited_by'), 'editor');
		$this->addColumn($this->lng->txt('last_edited'), 'last');
		$this->addColumn($this->lng->txt(''), 'actions');


		$this->setSelectAllCheckbox(ilObjStudyProgrammeAutoCategoriesGUI::CHECKBOX_CATEGORY_REF_IDS.'[]');
		$this->setEnableAllCommand(true);
		$this->addMultiCommand('delete', $this->lng->txt('delete'));

	}

	protected function fillRow($set) {
		list($ac, $title, $usr, $actions) = $set;
		$username = ilObjUser::_lookupName($ac->getLastEditorId());
		$editor = implode(' ', [
			$username['firstname'],
			$username['lastname'],
			'('.$username['login'] .')'
		]);

		$this->tpl->setVariable("ID", $ac->getCategoryRefId());
		$this->tpl->setVariable("TITLE", $title);
		$this->tpl->setVariable("EDITOR", $usr);
		$this->tpl->setVariable("LAST_EDITED", $ac->getLastEdited()->format('Y/m/d H:i:s'));
		$this->tpl->setVariable("ACTIONS", $actions);
	}
}
