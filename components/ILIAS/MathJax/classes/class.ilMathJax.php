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
use ILIAS\DI\Container;
use ILIAS\MathJax\MathJaxFactory;

/**
 * Class for processing of latex formulas
 * @deprecated
 */
class ilMathJax
{
    public const PURPOSE_BROWSER = 'browser';                    // direct display of page in the browser
    public const PURPOSE_EXPORT = 'export';                      // html export of contents


    protected static ?self $_instance;
    protected ilMathJaxConfig $config;
    protected MathJaxFactory $factory;

    /**
     * Protected constructor to force the use of an initialized instance
     * @deprecated
     */
    protected function __construct(ilMathJaxConfig $config, MathJaxFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Singleton: get instance for use in ILIAS requests with a config loaded from the settings
     * @deprecated
     */
    public static function getInstance(): ilMathJax
    {
        global $DIC;

        if (!isset(self::$_instance)) {
            // #37803: here we can't use ilSettingsFactory because of race conditions in ilSettingsFactory::settingsFor()
            $repo = new ilMathJaxConfigSettingsRepository(new ilSetting('MathJax'));
            self::$_instance = new self($repo->getConfig(), new MathJaxFactory($repo));
        }
        return self::$_instance;
    }

    /**
     * Get an independent instance with a specific config
     * @deprecated
     */
    public static function getIndependent(ilMathJaxConfig $config, MathJaxFactory $factory): ilMathJax
    {
        return new self($config, $factory);
    }

    /**
     * Initialize the usage for a certain purpose
     * @deprecated
     */
    public function init(string $a_purpose = self::PURPOSE_BROWSER): ilMathJax
    {
        return $this;
    }


    /**
     * Include the Mathjax javascript(s) in the page template
     * @deprecated
     */
    public function includeMathJax(ilGlobalTemplateInterface $a_tpl = null): ilMathJax
    {
        foreach ($this->factory->uiConfig()->getResources() as $resource) {
            // use absolute paths here because it may be used in exports
            $a_tpl->addJavaScript(ILIAS_HTTP_PATH . '/public/' . $resource);
        }

        return $this;
    }

    /**
     * Replace all tex code within given start and end delimiters in a text
     * @deprecated
     */
    public function insertLatexImages(string $a_text, ?string $a_start = '[tex]', ?string $a_end = '[/tex]'): string
    {
        global $DIC;
        return $DIC->ui()->renderer()->render($DIC->ui()->factory()->legacy($a_text)->withLatexEnabled());
    }
}
