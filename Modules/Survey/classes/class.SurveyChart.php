<?php

class SurveyChart
{
	//IlliasCharts constructor
	/*
	* $GraphTyp = "bars" or "lines"
	*/

	var $graph;
	var $graphData;
	function SurveyChart($GraphTyp,$XSize,$YSize,$Titel,$XLabel,$YLabel,$DataArray)
	{
		$this->graphData = array();
		include_once ("phplot/phplot.php");
		$this->graph = new PHPlot($XSize,$YSize);
		switch ($GraphTyp)
		{
			case "pie":
				$this->graph->SetDataType("text-data-single");
				break;
			default:
				$this->graph->SetDataType("text-data");
				break;
		}
		$this->graph->SetPlotType($GraphTyp);
		$this->graph->SetTitle($Titel);
		$this->graph->SetXTitle($XLabel);
		$this->graph->SetYTitle($YLabel);
		$this->graph->SetXDataLabelPos("plotdown");
		$this->makeDataForOneQuestion($DataArray, $GraphTyp);
		$this->graph->SetDataValues($this->graphData);
		$this->draw();
	}

	function makeDataForOneQuestion($Data, $GraphTyp)
	{
		foreach ($Data as $value)
		{
			if (array_key_exists("title", $value))
			{
				switch ($GraphTyp)
				{
					case "pie":
						array_push($this->graphData, array($value["title"], $value["selected"], ""));
						break;
					default:
						array_push($this->graphData, array($value["title"], $value["selected"]));
						break;
				}
			}
			else
			{
				array_push($this->graphData, array($value["value"], $value["selected"]));
			}
		}
	}

	function draw()
	{
		$this->graph->DrawGraph();
		$this->graph->PrintImage();
	}
}

?>
