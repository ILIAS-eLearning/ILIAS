<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/UICore/classes/class.ilTemplate.php");

class ilObservationsDiagrammGUI {
	const COLOR_ROSA = "ta_div_result_rosa";
	const COLOR_GREY = "ta_div_result_grey";
	const COLOR_GREEN = "ta_div_result_green";

	const REQ_BLOCK_HEIGHT = 40;
	const REQ_SPACER_HEIGHT = 20;
	const MAX_POINTS = 5;

	public function __construct($parent_obj) {
		global $tpl;

		$this->gTpl = $tpl;
		$this->parent_obj = $parent_obj;
	}

	public function render() {
		$obj_id = $this->parent_obj->getObjId();
		$actions = $this->parent_obj->getActions();
		$settings = $this->parent_obj->getSettings();

		$career_goal_id = $settings->getCareerGoalId();
		$career_gol_obj = \ilObjectFactory::getInstanceByObjId($career_goal_id);

		$values = array("min" => $career_gol_obj->getSettings()->getLowmark()
						, "max" => $career_gol_obj->getSettings()->getShouldSpecification()
					);

		$obs = $actions->getObservationsCumulative($obj_id);
		$req_res = $actions->getRequestresultCumulative(array_keys($obs));

		$tpl = new \ilTemplate("tpl.talent_assessment_observations_diagramm.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");

		$counter = 1;
		foreach($req_res as $req) {
			foreach($req as $req_det) {
				$counter++;
				$width = $this->getWidth($req_det["middle"]);
				$color = $this->getColor($req_det["middle"], $values);
				$tpl->setCurrentBlock("req_row");
				$tpl->setVariable("TITLE", $req_det["title"]);
				$tpl->setVariable("WIDTH", $width);
				$tpl->setVariable("COLOR", $color);
				$tpl->parseCurrentBlock();
			}
		}

		$tpl->setCurrentBlock("vert_line");
		$tpl->setVariable("VERT_LINE_LEFT", $this->getWidth($values["min"]));
		$tpl->setVariable("VERT_LINE_COLOR", self::COLOR_ROSA);
		$tpl->setVariable("VERT_LINE_HEIGHT", ((self::REQ_SPACER_HEIGHT + self::REQ_BLOCK_HEIGHT) * $counter + self::REQ_SPACER_HEIGHT));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("vert_line");
		$tpl->setVariable("VERT_LINE_LEFT", $this->getWidth($values["max"]));
		$tpl->setVariable("VERT_LINE_COLOR", self::COLOR_GREEN);
		$tpl->setVariable("VERT_LINE_HEIGHT", ((self::REQ_SPACER_HEIGHT + self::REQ_BLOCK_HEIGHT) * $counter + self::REQ_SPACER_HEIGHT));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	protected function getWidth($middle) {
		return ($middle * 100) / self::MAX_POINTS;
	}

	protected function getColor($middle, $values) {
		$min = $values["min"];
		$max = $values["max"];

		if($middle <= $min) {
			return self::COLOR_ROSA;
		} else if($middle >= $max) {
			return self::COLOR_GREEN;
		} else {
			return self::COLOR_GREY;
		}
	}
}