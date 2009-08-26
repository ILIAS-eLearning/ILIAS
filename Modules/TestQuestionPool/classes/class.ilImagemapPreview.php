<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Image map image preview creator
*
* Takes an image and imagemap areas and creates a preview image containing
* the imagemap areas.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class ilImagemapPreview
{
	var $imagemap_filename;
	var $preview_filename;
	var $areas;
	var $points;
	var $linewidth_outer;
	var $linewidth_inner;
	var $lng;

	/**
	* ilImagemapPreview constructor
	*
	* Creates an instance of the ilImagemapPreview class
	*
	* @param integer $id The database id of a image map question object
	* @access public
	*/
	function ilImagemapPreview($imagemap_filename = "")
	{
		global $lng;
		$this->lng =& $lng;
		$this->imagemap_filename = $imagemap_filename;
		$this->preview_filename = $preview_filename;
		if (!@is_file($this->preview_filename))
		{
			$extension = ".jpg";
			if (preg_match("/.*\.(png|jpg|gif|jpeg)$/", $this->imagemap_filename, $matches))
			{
				$extension = "." . $matches[1];
			}
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			$this->preview_filename = ilUtil::ilTempnam() . $extension;
		}
		$this->areas = array();
		$this->points = array();
		$this->linewidth_outer = 4;
		$this->linewidth_inner = 2;
	}
	
	function getAreaCount()
	{
		return count($this->areas);
	}
	
	function getPointCount()
	{
		return count($this->points);
	}

	function addArea(
		$index,
		$shape,
		$coords,
		$title = "",
		$href = "",
		$target = "",
		$visible = true,
		$linecolor = "red",
		$bordercolor = "white",
		$fillcolor = "#FFFFFFA0"
	)
	{
		if (ini_get("safe_mode"))
		{
			if ((strpos($fillcolor, "#") !== false) || (strpos($fillcolor, "rgb") !== false))
			{
				$fillcolor = str_replace("\"", "", $fillcolor);
			}
		}
		$this->areas[$index] = array(
			"shape" => "$shape",
			"coords" => "$coords",
			"title" => "$title",
			"href" => "$href",
			"target" => "$target",
			"linecolor" => '"' . $linecolor . '"',
			"fillcolor" => '"' . $fillcolor . '"',
			"bordercolor" => '"' . $bordercolor . '"',
			"visible" => (int)$visible
		);
	}
	
	function addPoint(
		$index,
		$coords,
		$visible = true,
		$linecolor = "red",
		$bordercolor = "white",
		$fillcolor = "#FFFFFFA0"
	)
	{
		$this->points[$index] = array(
			"coords" => "$coords",
			"linecolor" => '"' . $linecolor . '"',
			"fillcolor" => '"' . $fillcolor . '"',
			"bordercolor" => '"' . $bordercolor . '"',
			"visible" => (int)$visible
		);
	}
	
	function getAreaIdent()
	{
		if (count($this->areas)+count($this->points) > 0)
		{
			$arr = array_merge(array_keys($this->areas), array_keys($this->points));
			sort($arr, SORT_NUMERIC);
			return "preview_" . join("_", $arr) . "_";
		}
		else
		{
			return "";
		}
	}

	function createPreview()
	{
		if (count($this->areas)+count($this->points)==0) return;
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$convert_prefix = ilUtil::getConvertCmd() . " -quality 100 ";
		foreach ($this->points as $point)
		{
			if ($point["visible"])
			{
				preg_match("/(\d+)\s*,\s*(\d+)/", $point["coords"], $matches);
				$x = $matches[1];
				$y = $matches[2];
				$r = 6;
				// draw a circle at the point
				$convert_cmd .= "-stroke " . $point["bordercolor"] . " -fill " . $point["fillcolor"] . " -strokewidth $this->linewidth_outer -draw \"line " .
				($x-$r) . "," . ($y-$r) .	" " . ($x+$r) . "," . ($y+$r) . "\" " .
				"-stroke " . $point["bordercolor"] . " -fill " . $point["fillcolor"] . " -strokewidth $this->linewidth_outer -draw \"line " .
				($x+$r) . "," . ($y-$r) .	" " . ($x-$r) . "," . ($y+$r) . "\" " .
				"-stroke " . $point["linecolor"] . " -fill " . $point["fillcolor"] . " -strokewidth $this->linewidth_inner -draw \"line " .
				($x-$r) . "," . ($y-$r) .	" " . ($x+$r) . "," . ($y+$r) . "\" " .
				"-stroke " . $point["linecolor"] . " -fill " . $point["fillcolor"] . " -strokewidth $this->linewidth_inner -draw \"line " .
				($x+$r) . "," . ($y-$r) .	" " . ($x-$r) . "," . ($y+$r) . "\" ";
			}
		}
		foreach ($this->areas as $area)
		{
			if ($area["visible"] and strcmp(strtolower($area["shape"]), "rect") == 0)
			{
				preg_match("/(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/", $area["coords"], $matches);
				$x0 = $matches[1];
				$y0 = $matches[2];
				$x1 = $matches[3];
				$y1 = $matches[4];
				// draw a rect around the selection
				$convert_cmd .=	"-stroke " . $area["bordercolor"] . " -fill " . $area["fillcolor"] . " -strokewidth $this->linewidth_outer -draw \"rectangle " .
				$x0 . "," . $y0 .	" " . ($x1) . "," . $y1 . "\" " .
				"-stroke " . $area["linecolor"] . " -fill " . $area["fillcolor"] . " -strokewidth $this->linewidth_inner -draw \"rectangle " .
				$x0 . "," . $y0 .	" " . ($x1) . "," . $y1 . "\" ";
			}
			else if ($area["visible"] and strcmp(strtolower($area["shape"]), "circle") == 0)
			{
				preg_match("/(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/", $area["coords"], $matches);
				$x = $matches[1];
				$y = $matches[2];
				$r = $matches[3];
				// draw a circle around the selection
				$convert_cmd .= "-stroke " . $area["bordercolor"] . " -fill " . $area["fillcolor"] . " -strokewidth $this->linewidth_outer -draw \"circle " .
				$x . "," . $y .	" " . ($x+$r) . "," . $y . "\" " .
				"-stroke " . $area["linecolor"] . " -fill " . $area["fillcolor"] . " -strokewidth $this->linewidth_inner -draw \"circle " .
				$x . "," . $y .	" " . ($x+$r) . "," . $y . "\" ";
			}
			else if ($area["visible"] and strcmp(strtolower($area["shape"]), "poly") == 0)
			{
				$obj = "polygon";
				// draw a polygon around the selection
				preg_match_all("/(\d+)\s*,\s*(\d+)/", $area["coords"], $matches, PREG_PATTERN_ORDER);
				if (count($matches[0]) == 2) $obj = "line";
				$convert_cmd .= "-stroke " . $area["bordercolor"] . " -fill " . $area["fillcolor"] . " -strokewidth $this->linewidth_outer -draw \"$obj ";
				for ($i = 0; $i < count($matches[0]); $i++)
				{
					$convert_cmd .= $matches[1][$i] . "," . $matches[2][$i] .	" ";
				}
				$convert_cmd .= "\" ";
				$convert_cmd .= "-stroke " . $area["linecolor"] . " -fill " . $area["fillcolor"] . " -strokewidth $this->linewidth_inner -draw \"$obj ";
				preg_match_all("/(\d+)\s*,\s*(\d+)/", $area["coords"], $matches, PREG_PATTERN_ORDER);
				for ($i = 0; $i < count($matches[0]); $i++)
				{
					$convert_cmd .= $matches[1][$i] . "," . $matches[2][$i] .	" ";
				}
				$convert_cmd .= "\" ";
			}
		}
		
		$source = ilUtil::escapeShellCmd($this->imagemap_filename);
		$target = ilUtil::escapeShellCmd($this->preview_filename);
		$convert_cmd = $convert_prefix . $convert_cmd .  $source ." " . $target;
		system($convert_cmd);
	}

	function getPreviewFilename($imagePath, $baseFileName)
	{
		$filename = $baseFileName;
		if (count($this->areas)+count($this->points)>0)
		{
			$pfile = $this->preview_filename;
			if (is_file($pfile))
			{
				$ident = $this->getAreaIdent();
				$previewfile = $imagePath . $ident . $baseFileName;
				if (@md5_file($previewfile) != @md5_file($pfile))
				{
					if (strlen($ident) > 0)
					{
						@copy($pfile, $previewfile);
					}
				}
				@unlink($pfile);
				if (strlen($pfile) == 0)
				{
					ilUtil::sendInfo($this->lng->txt("qpl_imagemap_preview_missing"));
				}
				else
				{
					$filename = basename($previewfile);
				}
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("qpl_imagemap_preview_missing"));
			}
		}
		return $filename;
	}

	/**
	* get imagemap html code
	* note: html code should be placed in template files
	*/
	function getImagemap($title)
	{
		$map = "<map name=\"$title\"> ";
		foreach ($this->areas as $area)
		{
			$map .= "<area alt=\"" . $area["title"] . "\"  title=\"" . $area["title"] . "\" ";
			$map .= "shape=\"" . $area["shape"] . "\" ";
			$map .= "coords=\"" .  $area["coords"] . "\" ";
			if ($area["href"])
			{
				$map .= "href=\"" . $area["href"] . "\" ";
				if ($area["target"])
				{
					$map .= "target=\"" . $area["target"] . "\" ";
				}
				$map .= "/>\n";
			}
			else
			{
				$map .= "nohref />\n";
			}
		}
		$map .= "</map>";
		return $map;
	}

}
?>
