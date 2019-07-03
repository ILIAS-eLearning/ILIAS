<?php

use ILIAS\GlobalScreen\Scope\Layout\Modifier\ContentModifier;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractFinalModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ilPageContentProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPageContentProvider extends AbstractFinalModificationProvider implements FinalModificationProvider
{

    private static $content = "";


    /**
     * @param string $content
     */
    public static function setContent(string $content) : void
    {
        self::$content = $content;
    }


    public function getContentModifier() : ContentModifier
    {

        return new class(self::$content) implements ContentModifier
        {

            private $content = "";


            /**
             *  constructor.
             *
             * @param string $content
             */
            public function __construct(string $content) { $this->content = $content; }


            public function getContent(Legacy $current) : Legacy
            {
                return new \ILIAS\UI\Implementation\Component\Legacy\Legacy($this->content);
            }
        };
    }
}
