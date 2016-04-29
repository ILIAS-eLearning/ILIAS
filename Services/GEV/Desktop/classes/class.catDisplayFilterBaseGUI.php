<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Test Baseclass for cat Filter GUIS
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
*/

class catDisplayFilterBaseGUI {
	protected $gCtrl;
	protected $gTpl;

	public function __construct() {
		global $ilCtrl;

		$this->gCtrl = $ilCtrl;
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory(), new \CaT\Filter\TypeFactory());
		$this->display_filter = new \CaT\Filter\DisplayFilter(new \CaT\Filter\FilterGUIFactory(), new \CaT\Filter\TypeFactory());
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd("showFilter");

		switch($cmd) {
			case "showFilter":
			case "saveFilter":
			case "flatFilter":
			case "saveFlatFilter":
				$this->$cmd();
				break;
			default:
				throw new Exception("Command not found");
		}
	}

	protected function flatFilter() {
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_form = new catFilterFlatViewGUI($this, $this->buildFilter(), $this->display_filter, "saveFlatFilter");
		echo $filter_form->render();
	}

	protected function saveFlatFilter() {
		$fs = $this->buildFilter();

		$filter_values = $this->display_filter->buildFilterValues($fs, $_POST["filter"]);

		//Muss so aufgerufen werden. Sonst funktioniert das Mapping nicht!!!
		call_user_func_array(array($fs, "content"), $filter_values);

		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_form = new catFilterFlatViewGUI($this, $fs, $this->display_filter, "saveFlatFilter");
		echo $filter_form->render($_POST["filter"]);
	}

	protected function buildFilter() {
		$f = $this->factory;

		return
		$f->sequence
			( $f->text("l1", "d2")
			, $f->sequence
				( $f->text("l21", "d21")
				, $f->multiselect("l22", "d22", array("a"=>"A","b"=>"B","c"=>"C"))->default_choice(array("a","c"))
				, $f->option("l23", "d23")
				, $f->dateperiod("l24", "d24")->default_begin(new DateTime("2012-01-01"))->default_end(new DateTime("2017-12-31"))->period_min(new DateTime("2000-01-01"))
				)
				->map(function($t21, $a22, $b23, $dt241, $dt242) {
					return " Stefan";
				}, $this->factory->type_factory()->string())
			, $f->multiselect("l2", "d2", array("a"=>"A","b"=>"B","c"=>"C"))->default_choice(array("b"))
			, $f->option("l3", "d3")
			, $f->dateperiod("l4", "d4")
			, $f->one_of("l5", "d5"
				, $f->text("l51", "d51")
				, $f->multiselect("l52", "d52", array("a"=>"A","b"=>"B","c"=>"C"))->default_choice(array("a","c"))
				, $f->option("l53", "d53")
				, $f->dateperiod("l54", "d54")
				)
				->map(function($choice, $value) {
					return "choice: $choice";
				}, $this->factory->type_factory()->string())
			, $f->text("l6", "d6")
			, $f->singleselect("l22", "d22", array("Bernd"=>"A","Karsten"=>"B","Peter"=>"C"))->default_choice("Peter")
			)
			->map(function($t1, $s2, $a2, $b3, $dt41, $dt42, $s5, $t6, $s7) {
				return "Hello ".$s2." ($s7)";
			}, $this->factory->type_factory()->string());

	}

	protected function showFilter(array $post_values = array()) {
		$fs = $this->buildFilter();

		if($gui = $this->display_filter->getNextFilterGUI($fs, $post_values)){

			require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
			$form = new ilPropertyFormGUI();
			$form->setTitle("Filter GUI Test");
			$form->setFormAction($this->gCtrl->getFormAction($this));
			$form->addCommandButton("saveFilter","Weiter");

			$gui->fillForm($form);
			$gui->addHiddenInputs($form, $post_values);

			echo $form->getHTML();
		} else {
			$filter_values = $this->display_filter->buildFilterValues($fs, $post_values);

			//Muss so aufgerufen werden. Sonst funktioniert das Mapping nicht!!!
			echo call_user_func_array(array($fs, "content"), $filter_values);

			$this->buildReport($fs);
		}
	}

	protected function saveFilter() {
		$this->showFilter($_POST["filter"]);
	}

	protected function buildReport(\CaT\Filter\Filters\Sequence $sequence) {
		echo "So nun darf gearbeitet werden!";
	}
}