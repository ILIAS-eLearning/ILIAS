<?php

use ILIAS\GlobalScreen\Scope\Layout\Factory\Content;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ilPageContentProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPageContentProvider extends AbstractModificationProvider implements ModificationProvider
{

    /**
     * @inheritDoc
     */
    public function getContentModifier() : ?Content
    {
        return $this->globalScreen()->layout()->factory()->content()->withModification(function (Legacy $content) : Legacy {
            return $this->dic->ui()->factory()->legacy(self::$content);
        });
    }


    private static $content = "";


    /**
     * @param string $content
     */
    public static function setContent(string $content) : void
    {
        self::$content = $content;
    }
}
