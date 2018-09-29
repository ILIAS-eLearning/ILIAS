<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilImagemapCorrectionsInputGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/TestQuestionPool
 */
class ilImagemapCorrectionsInputGUI extends ilImagemapFileInputGUI
{
	public function checkInput()
	{
		
	}
	
	public function insert($a_tpl)
	{
		global $lng;
		
		$template = new ilTemplate("tpl.prop_imagemapquestioncorrection_input.html", true, true, "Modules/TestQuestionPool");
		
		if ($this->getImage() != "")
		{
			$template->setCurrentBlock("image");
			if (count($this->getAreas()))
			{
				include_once "./Modules/TestQuestionPool/classes/class.ilImagemapPreview.php";
				$preview = new ilImagemapPreview($this->getImagePath().$this->getValue());
				foreach ($this->getAreas() as $index => $area)
				{
					$preview->addArea($index, $area->getArea(), $area->getCoords(), $area->getAnswertext(), "", "", true, $this->getLineColor());
				}
				$preview->createPreview();
				$imagepath = $this->getImagePathWeb() . $preview->getPreviewFilename($this->getImagePath(), $this->getValue()) . "?img=" . time();
				$template->setVariable("SRC_IMAGE", $imagepath);
			}
			else
			{
				$template->setVariable("SRC_IMAGE", $this->getImage());
			}
			$template->setVariable("ALT_IMAGE", $this->getAlt());
			$template->setVariable("POST_VAR_D", $this->getPostVar());
			$template->parseCurrentBlock();
		}
		
		if(is_array($this->getAreas()) && $this->getAreas())
		{
			$counter = 0;
			foreach ($this->getAreas() as $area)
			{
				if (strlen($area->getPoints()))
				{
					$template->setCurrentBlock('area_points_value');
					$template->setVariable('VALUE_POINTS', $area->getPoints());
					$template->parseCurrentBlock();
				}
				if( $this->getPointsUncheckedFieldEnabled() )
				{
					if (strlen($area->getPointsUnchecked()))
					{
						$template->setCurrentBlock('area_points_unchecked_value');
						$template->setVariable('VALUE_POINTS_UNCHECKED', $area->getPointsUnchecked());
						$template->parseCurrentBlock();
					}
					
					$template->setCurrentBlock('area_points_unchecked_field');
					$template->parseCurrentBlock();
				}
				$template->setCurrentBlock('row');
				if (strlen($area->getAnswertext()))
				{
					$template->setVariable('ANSWER_AREA', $area->getAnswertext());
				}
				$template->setVariable('POST_VAR_R', $this->getPostVar());
				$template->setVariable('TEXT_SHAPE', strtoupper($area->getArea()));
				$template->setVariable('VALUE_SHAPE', $area->getArea());
				$coords = preg_replace("/(\d+,\d+,)/", "\$1 ", $area->getCoords());
				$template->setVariable('VALUE_COORDINATES', $area->getCoords());
				$template->setVariable('TEXT_COORDINATES', $coords);
				$template->setVariable('COUNTER', $counter);
				$template->parseCurrentBlock();
				$counter++;
			}
			$template->setCurrentBlock("areas");
			$template->setVariable("TEXT_NAME", $lng->txt("ass_imap_hint"));
			if( $this->getPointsUncheckedFieldEnabled() )
			{
				$template->setVariable("TEXT_POINTS", $lng->txt("points_checked"));
				
				$template->setCurrentBlock('area_points_unchecked_head');
				$template->setVariable("TEXT_POINTS_UNCHECKED", $lng->txt("points_unchecked"));
				$template->parseCurrentBlock();
			}
			else
			{
				$template->setVariable("TEXT_POINTS", $lng->txt("points"));
			}
			$template->setVariable("TEXT_SHAPE", $lng->txt("shape"));
			$template->setVariable("TEXT_COORDINATES", $lng->txt("coordinates"));
			$template->setVariable("TEXT_COMMANDS", $lng->txt("actions"));
			$template->parseCurrentBlock();
		}
		
		$template->setVariable("POST_VAR", $this->getPostVar());
		$template->setVariable("ID", $this->getFieldId());
		$template->setVariable("TXT_BROWSE", $lng->txt("select_file"));
		$template->setVariable("TXT_MAX_SIZE", $lng->txt("file_notice")." ".
			$this->getMaxFileSizeString());
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $template->get());
		$a_tpl->parseCurrentBlock();
		
		global $tpl;
		#$tpl->addJavascript("./Services/Form/js/ServiceFormWizardInput.js");
		#$tpl->addJavascript("./Modules/TestQuestionPool/templates/default/imagemap.js");
	}
}