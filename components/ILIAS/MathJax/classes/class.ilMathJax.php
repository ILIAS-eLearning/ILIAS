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

/**
 * Old ilMathJax class
 * This class is kept temporary to prevent PHP errors in the components calling it.
 * It has no function anymore.
 * It will be removes once all calls replaced
 *
 * @deprecated - use ILIAS\UI\Component\Legacy\LatexContent instead
 */
class ilMathJax
{
    public const PURPOSE_BROWSER = 'browser';
    public const PURPOSE_EXPORT = 'export';

    protected static ?self $_instance;

    /**
     * @deprecated - use ILIAS\UI\Component\Legacy\LatexContent instead
     */
    public static function getInstance(): ilMathJax
    {
        return self::$_instance ??= new self();
    }

    /**
     * @deprecated - use ILIAS\UI\Component\Legacy\LatexContent instead
     */
    public function setZoomFactor(float $a_factor): ilMathJax
    {
        return $this;
    }

    /**
     * @deprecated - use ILIAS\UI\Component\Legacy\LatexContent instead
     */
    public function init(string $a_purpose = self::PURPOSE_BROWSER): ilMathJax
    {
        return $this;
    }

    /**
     * @deprecated - use ILIAS\UI\Component\Legacy\LatexContent instead
     */
    public function includeMathJax(?ilGlobalTemplateInterface $a_tpl = null): ilMathJax
    {
        return $this;
    }

    /**
     * @deprecated - use ILIAS\UI\Component\Legacy\LatexContent instead
     */
    public function insertLatexImages(string $a_text, ?string $a_start = '[tex]', ?string $a_end = '[/tex]'): string
    {
        return $a_text;
    }
}
