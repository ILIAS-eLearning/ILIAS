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
		$this->addColumn($this->lng->txt(''), 'title');
		$this->addColumn($this->lng->txt(''), 'editor');
		$this->addColumn($this->lng->txt(''), 'last');
		$this->addColumn($this->lng->txt(''), 'actions');


		$this->setSelectAllCheckbox("cat_ids[]");
		$this->setEnableAllCommand(true);
		$this->addMultiCommand('delete', "txt('delete')");

	}

}
