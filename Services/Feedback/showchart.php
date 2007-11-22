<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* ShowChart
*
* @author	Helmuth Antholzer <helmuth.antholzer@maguma.com>
* @version	$Id$
*/	
		include_once('../../Modules/SurveyQuestionPool/phplot/phplot.php');
		
		$data = unserialize(base64_decode($_GET['data']));
		$legend = unserialize(base64_decode($_GET['legend']));
		$title = base64_decode($_GET['title']);
		if($legend == NULL)
			$legend = array();
		if($data == NULL)
			$data = array(array(0,''));
		
		$chart_type = $_GET['chart_type'];
		
		$graph = new PHPlot(600,350);
	
		
		$graph->SetShading(0);
		$graph->SetPlotType($chart_type);
		$graph->SetDataType('text-data');
		
		$graph->SetTitle($title."\n\r\n\r");

		$graph->SetXDataLabelPos('plotdown');

		$graph->setLegend($legend);
		$graph->SetDataValues($data);
		$graph->DrawGraph();
		$graph->PrintImage();
?>