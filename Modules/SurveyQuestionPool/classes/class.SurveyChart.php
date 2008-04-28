<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class SurveyChart
* 
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesSurveyQuestionPool
*/

class SurveyChart
{
	var $graph;
	var $graphData;
	
	function SurveyChart($GraphTyp,$XSize,$YSize,$Titel,$XLabel,$YLabel,$DataArray)
	{
		$this->graphData = array();
		include_once ("./Modules/SurveyQuestionPool/phplot/phplot.php");
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
		if (strlen($Titel) > 40)
		{
			$this->graph->SetFont("title", "benjamingothic.ttf", 6);
		}
		if (strlen($Titel) > 80)
		{
			$Titel = substr($Titel, 0, 80) . "...";
		}
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
