<?php

declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\String;

use ILIAS\Data\Factory;

class Group
{
    /**
     * @var Factory
     */
    private $dataFactory;

    /**
     * @var \ilLanguage
     */
    private $language;

    public function __construct(Factory $dataFactory, \ilLanguage $language)
    {
        $this->dataFactory = $dataFactory;
        $this->language = $language;
    }

    /**
     * Creates a constraint that can be used to check if a string
     * has reached a minimum length
     *
     * @param int $minimum - minimum length of a string that will be checked
     *                       with the new constraint
     * @return HasMinLength
     */
    public function hasMinLength(int $minimum): HasMinLength
    {
        return new HasMinLength($minimum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a constraint that can be used to check if a string
     * has exceeded a maximum length
     *
     * @param int $maximum - maximum length of a strings that will be checked
     *                       with the new constraint
     * @return HasMaxLength
     */
    public function hasMaxLength(int $maximum): HasMaxLength
    {
        return new HasMaxLength($maximum, $this->dataFactory, $this->language);
    }

    /**
     * Creates a transformation that can be used to split a given
     * string by given delimiter.
     *
     * @param string $delimiter
     * @return SplitString
     */
    public function splitString(string $delimiter): SplitString
    {
        return new SplitString($delimiter, $this->dataFactory);
    }


    /**
     * Creates a transformation that strips tags from a string.
     *
     * Uses php's strip_tags under the hood.
     */
    public function stripTags(): StripTags
    {
        return new StripTags();
    }

    /**
     * Creates a transformation that can be used to format a text for the title capitalization presentation (Specification at https://docu.ilias.de/goto_docu_pg_1430_42.html)
     *
     * Throws a LogicException in the transform method, if a not supported language is passed
     *
     * @param string $language_key
     *
     * @return CaseOfLabel
     */
    public function caseOfLabel(string $language_key): CaseOfLabel
    {
        return new CaseOfLabel($language_key, $this->dataFactory);
    }

    /**
     * Creates a transformation to determine the estimated reading
     * time of a human adult (roughly 275 WPM)
     * If images should be taken into consideration, 12 seconds
     * are added to the first image, 11 for the second,
     * and minus an additional second for each subsequent image.
     * Any images after the tenth image are counted at three seconds.
     * The reading time returned in minutes as a integer value.
     *
     * @param bool $withImages
     * @return EstimatedReadingTime
     */
    public function estimatedReadingTime($withImages = false): EstimatedReadingTime
    {
        return new EstimatedReadingTime($withImages);
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
        return new Levenshtein();
    }
}
