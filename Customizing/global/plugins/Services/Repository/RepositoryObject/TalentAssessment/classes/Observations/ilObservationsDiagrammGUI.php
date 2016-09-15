<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/UICore/classes/class.ilTemplate.php");

class ilObservationsDiagrammGUI {
	const SVG_WIDTH = "100%";
	const MAX_VALUE = "5";

	public function __construct($settings, $actions, \Closure $txt) {
		global $tpl;

		$this->gTpl = $tpl;

		$this->obj_id = $settings->getObjId();
		$this->actions = $actions;
		$this->settings = $settings;
		$this->txt = $txt;
	}

	public function render() {
		$tpl = new \ilTemplate("tpl.talent_assessment_observations_diagramm.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
	
		$svg_data = $this->getSVGData();
		$svg_data_encoded = base64_encode($svg_data);
		$tpl->setVariable("SVG", $svg_data_encoded);
		$tpl->setVariable("SVG_WIDTH", self::SVG_WIDTH);

		return $tpl->get();
	}

	public function getSVGData() {
		$svg_tpl = new \ilTemplate("tpl.assessment_result_graph.svg", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TalentAssessment");
		$svg = new ReportSVGRenderer($svg_tpl);

		$svg->setLegendDelimiterWidth(1);
		$svg->setLegendPositionVertical(0);
		$svg->setLegendBarVerticalPosition(10);
		$svg->setLegendBarHeight(1);
		$svg->setPaddingTop(10);
		$svg->setPaddingBottom(10);
		$svg->setInnerWidth(440);
		$svg->setCategoryGraphRowHeight(15);
		$svg->setCategoryBlockPadding(2);
		$svg->setGraphVerticalDistanceLegend(5);
		$svg->setCategoryBlockDelimiterWidth(4);



		$obs = $this->actions->getObservationsCumulative($this->obj_id);
		$req_res = $this->actions->getRequestresultCumulative(array_keys($obs));

		$svg->setLegendParams($this->txt("ta_failed"), $this->settings->getLowmark()
			, $this->txt("ta_maybe"), $this->settings->getShouldSpecification()
			, $this->txt("ta_passed"), self::MAX_VALUE);

		foreach($req_res as $title => $req) {
			$svg->addCategory($req["middle"],$title);
		}

		return $svg->render();
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	protected function txt($code) {
		assert('is_string($code)');

		$txt = $this->txt;

		return $txt($code);
	}
}