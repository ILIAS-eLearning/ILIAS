<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssSelfAssessmentQuestionFormatter
 */
class ilAssSelfAssessmentQuestionFormatter
{
	/**
	 * Original code copied from \assQuestion::formatSAQuestion (author: akill)
	 * @param $html string
	 * @return string
	 */
	public function format($string)
	{
		$string = $this->handleLineBreaks($string);

		require_once 'Services/RTE/classes/class.ilRTE.php';
		$string = (string) ilRTE::_replaceMediaObjectImageSrc($string, 1);

		$string = str_replace("</li><br />", "</li>", $string);
		$string = str_replace("</li><br>", "</li>", $string);

		require_once 'Services/MathJax/classes/class.ilMathJax.php';
		$string = ilMathJax::getInstance()->insertLatexImages($string, "\[tex\]", "\[\/tex\]");
		$string = ilMathJax::getInstance()->insertLatexImages($string, "\<span class\=\"latex\">", "\<\/span>");

		$string = str_replace('{', '&#123;', $string);
		$string = str_replace('}', '&#125;', $string);

		return $string;
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function handleLineBreaks($string)
	{
		if( !ilUtil::isHTML($string) )
		{
			$string = nl2br($string);
		}

		return $string;
	}
}