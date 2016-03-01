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
		$this->display_filter = new \CaT\Filter\DisplayFilter(new \CaT\Filter\FilterGUIFactory());
	}

	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd("showFilter");

		switch($cmd) {
			case "showFilter":
			case "saveFilter":
				$this->$cmd();
				break;
			default:
				throw new Exception("Command not found");
		}
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
				, $f->dateperiod("l24", "d24")
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
			$post_values = $this->cleanPostValues($post_values);
			$filter_values = $this->buildFilterValues($fs, $post_values);

			//Muss so aufgerufen werden. Sonst funktioniert das Mapping nicht!!!
			echo call_user_func_array(array($fs, "content"), $filter_values);

			$this->buildReport($fs);
		}
	}

	protected function saveFilter() {
		$this->showFilter($_POST["filter"]);
	}

	protected function cleanPostValues(array $post_values) {
		foreach ($post_values as $key => $value) {
			if(!$uns = unserialize($value)) {
				$uns = $value;
			}

			$post_values[$key] = $uns;
		}

		return $post_values;
	}

	protected function buildFilterValues(\CaT\Filter\Filters\Sequence $sequence, array $post_values) {
		$navi = new \CaT\Filter\Navigator($sequence);
		$ret = array();

		while ($filter = $this->display_filter->getNextFilter($navi)) {
			if($filter instanceof \CaT\Filter\Filters\Sequence) {
				$navi->enter();
				$filter = $navi->current();
			}

			$current_class = get_class($filter);
			$value = $post_values[$navi->path()];
			switch($current_class) {
				case "CaT\Filter\Filters\DatePeriod":
					$start = new DateTime($value["start"]["date"]["y"]."-".$value["start"]["date"]["m"]."-".$value["start"]["date"]["d"]);
					$end = new DateTime($value["end"]["date"]["y"]."-".$value["end"]["date"]["m"]."-".$value["end"]["date"]["d"]);
					array_push($ret, $start);
					array_push($ret, $end);
					break;
				case "CaT\Filter\Filters\OneOf":
					$choice = $value["option"];
					$value = $value[$choice];
					array_push($ret, (int)$choice);
					array_push($ret, $value);
					break;
				case "CaT\Filter\Filters\Text":
				case "CaT\Filter\Filters\Multiselect":
				case "CaT\Filter\Filters\Singleselect":
					array_push($ret, $value);
					break;
				case "CaT\Filter\Filters\Option":
					array_push($ret, (bool)$value);
					break;
				default:
					throw new \Exception("Filter class not known");
			}
		}

		return $ret;
	}

	protected function buildReport(\CaT\Filter\Filters\Sequence $sequence) {
		echo "So nun darf gearbeitet werden!";
	}
}