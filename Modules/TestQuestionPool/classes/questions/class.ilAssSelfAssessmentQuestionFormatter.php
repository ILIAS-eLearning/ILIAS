<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssSelfAssessmentQuestionFormatter
 */
class ilAssSelfAssessmentQuestionFormatter
{
	/**
	 * @var bool
	 */
	protected $enabledMarkUpCheck = false;

	/**
	 * 
	 */
	public function enabledMarkupCheck()
	{
		$this->enabledMarkUpCheck = true;
	}
	
	/**
	 * Original code copied from \assQuestion::formatSAQuestion (author: akill)
	 * @param $html string
	 * @return string
	 */
	public function format($html)
	{
		$convertLineBreaks = false;
		if(!$this->enabledMarkUpCheck || !ilUtil::isHTML($html))
		{
			$convertLineBreaks = true;
		}

		require_once 'Services/RTE/classes/class.ilRTE.php';
		$a_q = (string) ilRTE::_replaceMediaObjectImageSrc($html, 0);

		if($convertLineBreaks)
		{
			$a_q = nl2br($a_q);
		}

		$a_q = str_replace("</li><br />", "</li>", $a_q);
		$a_q = str_replace("</li><br>", "</li>", $a_q);

		require_once 'Services/MathJax/classes/class.ilMathJax.php';
		$a_q = ilMathJax::getInstance()->insertLatexImages($a_q, "\[tex\]", "\[\/tex\]");
		$a_q = ilMathJax::getInstance()->insertLatexImages($a_q, "\<span class\=\"latex\">", "\<\/span>");

		$a_q = str_replace('{', '&#123;', $a_q);
		$a_q = str_replace('}', '&#125;', $a_q);

		return $a_q;
	}
}