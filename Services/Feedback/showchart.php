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
		include_once('../../survey/phplot/phplot.php');
		
		$data = unserialize(base64_decode($_GET['data']));
		$legend = unserialize(base64_decode($_GET['legend']));
		$title = base64_decode($_GET['title']);
		if($legend == NULL)
			$legend = array();
		if($data == NULL)
			$data = array(array(0,''));
		
		$chart_type = $_GET['chart_type'];
		
		$graph = new PHPlot(600,350);
		/* switch($chart_type){
			case 'pie':
				//$graph->SetDataType('text-data-single');
				$graph->SetDataType('text-data');
				break;
			default:
				$graph->SetDataType('text-data');
				break;
			
		} */
		
		$graph->SetShading(0);
		$graph->SetPlotType($chart_type);
		$graph->SetDataType('text-data');
		
		$graph->SetTitle($title);
/*		$graph->SetXTitle('Zeit');
		$graph->SetYTitle('Bewertung');*/
		$graph->SetXDataLabelPos('plotdown');
		
	/*	$data = array(
	array("label 1",1.1,2,3,4),
	array("label 2",2,3,4,5),
	array("label 3",5,6,7,8),
	array("label 4",10,12,13,14)
);*/
		$graph->setLegend($legend);
		$graph->SetDataValues($data);
		$graph->DrawGraph();
		$graph->PrintImage();
?>