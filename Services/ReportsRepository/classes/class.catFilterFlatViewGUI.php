<?php
class catFilterFlatViewGUI {
	protected $sequence;

	public function __construct($parent_obj, $sequence, $display_filter, $cmd_save) {
		global $ilCtrl, $lng;

		$this->gCtrl = $ilCtrl;
		$this->gLng = $lng;


		$this->parent_obj = $parent_obj;
		$this->sequence = $sequence;
		$this->display_filter = $display_filter;
		$this->cmd_save = $cmd_save;
	}

	public function render($post = null) {
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormTplGUI.php");
		$form = new catPropertyFormTplGUI();
		$form->setTemplate("tpl.cat_filter_flat_view.html", "Services/ReportsRepository");
		$form->setFormAction($this->gCtrl->getFormAction($this->parent_obj));
		$form->setShowTopButtons(false);
		$form->getTemplate()->setVariable("BTN_CMD", $this->cmd_save);
		$form->getTemplate()->setVariable("BTN_VALUE", $this->gLng->txt("gev_filter"));

		$form_tpl = $form->getTemplate();

		$result = array();

		while($next_filter_gui = $this->display_filter->getNextFilterGUI($this->sequence, $result)) {
			//Just to enable walking through tree
			$new_path = array($next_filter_gui->path() => "val");
			$result = $new_path + $result;

			//if sequence lvl > 2 is reached cry
			if(substr_count($next_filter_gui->path(), "_") > 1) {
				throw new Exception("lass das");
			}

			//sequences in main sequence render filter sidy by side
			if(substr_count($next_filter_gui->path(), "_") == 1) {
				$tpl = new ilTemplate("tpl.cat_filter_flat_view_sbs_element.html", true, true, "Services/ReportsRepository");
			} else {
				$tpl = new ilTemplate("tpl.cat_filter_flat_view_element.html", true, true, "Services/ReportsRepository");
			}

			if($post !== null) {
				$val = $post[$next_filter_gui->path()];
				$next_filter_gui->setValue($val);
			}

			if($next_filter_gui instanceof catFilterMultiselectGUI) {
				$tpl->touchBlock("checkbox");
			}

			$tpl->setVariable("FILTER_TITLE", $next_filter_gui->formElement()->getTitle());
			$tpl->setVariable("FILTER_GUI", $next_filter_gui->formElement()->render());
			$tpl->setVariable("FILTER_INFO", $next_filter_gui->formElement()->getInfo());
			$tpl->setVariable("FILTER_PATH", $next_filter_gui->path());

			$form_tpl->setCurrentBlock("filter_element");
			$form_tpl->setVariable("FILTER_GUI", $tpl->get());
			$form_tpl->parseCurrentBlock();
		}

		return $form->getHtml();
	}
}