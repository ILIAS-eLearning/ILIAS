<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilAssSelfAssessmentQuestionFormatter
 */
class ilAssSelfAssessmentQuestionFormatter implements ilAssSelfAssessmentMigrator
{
    /**
     * Original code copied from \assQuestion::formatSAQuestion (author: akill)
     * @param $html string
     * @return string
     */
    public function format($string): string
    {
        $string = $this->handleLineBreaks($string);
        $string = ilRTE::_replaceMediaObjectImageSrc($string, 1);
        $string = str_replace("</li><br />", "</li>", $string);
        $string = str_replace("</li><br>", "</li>", $string);
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
    protected function handleLineBreaks($string): string
    {
        if (!ilUtil::isHTML($string)) {
            $string = nl2br($string);
        }

        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function convertLatexSpanToTex($string): string
    {
        // we try to save all latex tags
        $try = true;
        $ls = '<span class="latex">';
        $le = '</span>';
        while ($try) {
            // search position of start tag
            $pos1 = strpos($string, $ls);
            if (is_int($pos1)) {
                $pos2 = strpos($string, $le, $pos1);
                if (is_int($pos2)) {
                    // both found: replace end tag
                    $string = substr($string, 0, $pos2) . "[/tex]" . substr($string, $pos2 + 7);
                    $string = substr($string, 0, $pos1) . "[tex]" . substr($string, $pos1 + 20);
                } else {
                    $try = false;
                }
            } else {
                $try = false;
            }
        }

        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function stripHtmlExceptSelfAssessmentTags($string): string
    {
        $tags = self::getSelfAssessmentTags();

        $tstr = "";

        foreach ($tags as $t) {
            $tstr .= "<" . $t . ">";
        }

        $string = ilUtil::secureString($string, true, $tstr);

        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    public function migrateToLmContent($string): string
    {
        $string = $this->convertLatexSpanToTex($string);
        $string = $this->stripHtmlExceptSelfAssessmentTags($string);
        return $string;
    }

    /**
     * @param assQuestion $question
     */
    public static function prepareQuestionForLearningModule(assQuestion $question): void
    {
        $question->migrateContentForLearningModule(new self());
    }

    /**
     * Get tags allowed in question tags in self assessment mode
     * @return array array of tags
     */
    public static function getSelfAssessmentTags(): array
    {
        // set tags we allow in self assessment mode
        $st = ilUtil::getSecureTags();

        // we allow these tags, since they are typically used in the Tiny Assessment editor
        // and should not be deleted, if questions are copied from pools to learning modules
        $not_supported = array("img", "p");
        $tags = array();

        /// BH 01-03-2018: added P tag to allowed tags due to missing newline problems
        $tags[] = 'p';
        /// BH 01-03-2018: added P tag to allowed tags due to missing newline problems

        foreach ($st as $s) {
            if (!in_array($s, $not_supported)) {
                $tags[] = $s;
            }
        }

        return $tags;
    }
}
