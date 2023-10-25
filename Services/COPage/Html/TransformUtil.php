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

declare(strict_types=1);

namespace ILIAS\COPage\Html;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class TransformUtil
{
    protected const PH_START = "{{{{{";
    protected const PH_END = "}}}}}";

    public function getPosOfPlaceholder(string $html, string $tag, int $offset = 0): ?int
    {
        $p = strpos($html, self::PH_START . $tag, $offset);
        return is_int($p) ? $p : null;
    }

    public function getEndPosOfPlaceholder(string $html, int $offset = 0): ?int
    {
        $p = strpos($html, self::PH_END, $offset);
        return is_int($p) ? ($p + strlen(self::PH_END)) : null;
    }

    public function getPlaceholderParamString(string $html, string $tag): ?string
    {
        $start = $this->getPosOfPlaceholder($html, $tag);
        if (is_int($start)) {
            $end = $this->getEndPosOfPlaceholder($html, $start);
            $tag_string = substr($html, $start + strlen(self::PH_START), $end - $start - strlen(self::PH_START) - strlen(self::PH_END));
            return $tag_string;
        }
        return null;
    }

    public function getPlaceholderParams(string $html, string $tag): ?array
    {
        $tag_string = $this->getPlaceholderParamString($html, $tag);
        if (is_string($tag_string)) {
            return explode(";", $tag_string);
        }
        return null;
    }

    /**
     * parameters of start and end tag must match
     * {{{{{StartTag;a;b;c}}}}}...inner content...{{{{{EndTag;a;b;c}}}}}
     */
    public function getInnerContentOfPlaceholders(string $html, string $start_tag, string $end_tag): ?string
    {
        $start1 = $this->getPosOfPlaceholder($html, $start_tag);
        if (is_int($start1)) {
            $end1 = $this->getEndPosOfPlaceholder($html, $start1);
            $param_str = $this->getPlaceholderParamString($html, $start_tag);
            $end_tag_with_params = str_replace($start_tag . ";", $end_tag . ";", $param_str);
            $start2 = $this->getPosOfPlaceholder($html, $end_tag_with_params);
            if (is_int($end1) && is_int($start2)) {
                $end2 = $this->getEndPosOfPlaceholder($html, $start2);
                if (is_int($end2)) {
                    return substr($html, $end1, $start2 - $end1);
                }
            }
        }
        return null;
    }

    /**
     * parameters of start and end tag must match
     * {{{{{StartTag;a;b;c}}}}}...inner content...{{{{{EndTag;a;b;c}}}}}
     * ...replacement...
     */
    public function replaceInnerContentAndPlaceholders(string $html, string $start_tag, string $end_tag, string $replacement): ?string
    {
        $start1 = $this->getPosOfPlaceholder($html, $start_tag);
        if (is_int($start1)) {
            $end1 = $this->getEndPosOfPlaceholder($html, $start1);
            $param_str = $this->getPlaceholderParamString($html, $start_tag);
            $end_tag_with_params = str_replace($start_tag . ";", $end_tag . ";", $param_str);
            $start2 = $this->getPosOfPlaceholder($html, $end_tag_with_params);
            if (is_int($end1) && is_int($start2)) {
                $end2 = $this->getEndPosOfPlaceholder($html, $start2);
                if (is_int($end2)) {
                    return substr($html, 0, $start1) .
                        $replacement .
                        substr($html, $end2);
                }
            }
        }
        return null;
    }


}
