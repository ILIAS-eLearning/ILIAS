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
		global $ilCtrl, $tpl;

		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory(), new \CaT\Filter\TypeFactory());
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

	protected function showFilter(array $post_values = array()) {
		$display_filter = new \CaT\Filter\DisplayFilter(new \CaT\Filter\FilterGUIFactory());
		
		$f1 = $this->factory->text("l1", "d1");
		$f2 = $this->factory->multiselect("l2", "d2", array("a"=>"A","b"=>"B","c"=>"C"));
		$f3 = $this->factory->option("l3", "d3");
		$f4 = $this->factory->dateperiod("l4", "d4");

		$f51 = $this->factory->text("l51", "d51");
		$f52 = $this->factory->multiselect("l52", "d52", array("a"=>"A","b"=>"B","c"=>"C"));
		$f53 = $this->factory->option("l53", "d53");
		$f54 = $this->factory->dateperiod("l54", "d54");
		$f5 = $this->factory->one_of("l5", "d5", $f51, $f52, $f53, $f54);

		$f6 = $this->factory->text("l6", "d6");
		//$fs = $this->factory->sequence($f1, $f2 ,$f3, $f4/*, $f5*/,$f6);

		$f21 = $this->factory->text("l21", "d21");
		$f22 = $this->factory->multiselect("l22", "d22", array("a"=>"A","b"=>"B","c"=>"C"));
		$f23 = $this->factory->option("l23", "d23");
		$f24 = $this->factory->dateperiod("l24", "d24");
		$fs2 = $this->factory->sequence($f21, $f22, $f23, $f24);

		$fs = $this->factory->sequence($f1, $fs2, $f2, $f3, $f4, $f5, $f6);

		if($gui = $display_filter->getNextFilterGUI($fs, $post_values)){

			require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
			$form = new ilPropertyFormGUI();
			$form->setTitle("Mööp");
			$form->setFormAction($this->gCtrl->getFormAction($this));
			$form->addCommandButton("saveFilter","Weiter");

			foreach ($post_values as $key => $value) {
				$hidden = new ilHiddenInputGUI("filter[$key]");
				if(is_array($value)) {
					$hidden->setValue(serialize($value));
				} else {
					$hidden->setValue($value);
				}
				
				$form->addItem($hidden);
			}

			$gui->fillForm($form);
			$this->gTpl->setContent($form->getHTML());
			$this->gTpl->show();
		} else {
			$post_values = $this->cleanPostValues($post_values);
			$this->buildReport($post_values);
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

	protected function buildReport(array $post_values) {
		echo "<pre>";
		var_dump($post_values);
		echo "</pre>";

		echo $post_values[5][$post_values[5]["option"]];
	}

}