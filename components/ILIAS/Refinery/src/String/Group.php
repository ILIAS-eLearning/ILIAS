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

namespace ILIAS\Refinery\String;

use ILIAS\Refinery\BuildTransformation;
use ILIAS\Refinery\Transformation;

class Group
{
    public function __construct(private readonly BuildTransformation $build_transformation)
    {
    }

    /**
     * Creates a constraint that can be used to check if a string
     * has reached a minimum length
     *
     * @param int $minimum - minimum length of a string that will be checked
     *                       with the new constraint
     * @return Constraint
     */
    public function hasMinLength(int $minimum): Constraint
    {
        return $this->build_trasformation->fromConstraint(new HasMinLength($minimum));
    }

    /**
     * Creates a constraint that can be used to check if a string
     * has exceeded a maximum length
     *
     * @param int $maximum - maximum length of a strings that will be checked
     *                       with the new constraint
     * @return Constraint
     */
    public function hasMaxLength(int $maximum): Constraint
    {
        return $this->build_trasformation->fromConstraint(new HasMaxLength($maximum));
    }

    /**
     * Creates a transformation that can be used to split a given
     * string by given delimiter.
     */
    public function splitString(string $delimiter): Transformation
    {
        return $this->build_trasformation->fromTransformable(new SplitString($delimiter));
    }


    /**
     * Creates a transformation that strips tags from a string.
     *
     * Uses php's strip_tags under the hood.
     */
    public function stripTags(): Transformation
    {
        return $this->build_trasformation->fromTransformable(new StripTags());
    }

    /**
     * Creates a transformation that can be used to format a text for the title capitalization presentation (Specification at https://docu.ilias.de/goto_docu_pg_1430_42.html)
     *
     * Throws a LogicException in the transform method, if a not supported language is passed
     */
    public function caseOfLabel(string $language_key): Transformation
    {
        return $this->build_trasformation->fromTransformable(new CaseOfLabel($language_key));
    }

    /**
     * Creates a transformation to determine the estimated reading
     * time of a human adult (roughly 275 WPM)
     * If images should be taken into consideration, 12 seconds
     * are added to the first image, 11 for the second,
     * and minus an additional second for each subsequent image.
     * Any images after the tenth image are counted at three seconds.
     * The reading time returned in minutes as a integer value.
     */
    public function estimatedReadingTime(bool $withImages = false): Transformation
    {
        return $this->build_trasformation->fromTransformable(new EstimatedReadingTime($withImages));
    }

    /**
     * Creates a transformation to replace URL's like www.ilias.de to <a href="www.ilias.de">www.ilias.de</a>. But does not replace URL's already in anchor tags.
     * Expects a string of mixed HTML and plain text.
     */
    public function makeClickable(): Transformation
    {
        return $this->build_trasformation->fromTransformable(new MakeClickable());
    }

    /**
     * This method returns an instance of the Levenshtein class, to call the constructor of the
     * LevenshteinTransformation class with either default values already set, or custom values for the cost
     * calculation of the Levenshtein distance function.
     *
     * @return Levenshtein
     */
    public function levenshtein(): Levenshtein
    {
        return new Levenshtein($this->build_trasformation);
    }

    /**
     * This method returns an instance of the UTFNormal class which can be used to get Transformations that can be used
     * to normalize a string to one of the Unicode Normalization Form (C, D, KC, KD).
     * See https://unicode.org/reports/tr15/ for more information.
     */
    public function utfnormal(): UTFNormal
    {
        return new UTFNormal($this->build_trasformation);
    }

    /**
     * This method returns an instance of the MarkdownFormattingToHTML class which can be used to tranform a markdown
     * formatted string to HTML.
     */
    public function markdown(): MarkdownFormattingToHTML
    {
        return new MarkdownFormattingToHTML($this->build_trasformation);
    }
}
