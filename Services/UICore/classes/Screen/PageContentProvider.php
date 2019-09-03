<?php namespace ILIAS\UICore;

use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class ilPageContentProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PageContentProvider extends AbstractModificationProvider implements ModificationProvider
{

    /**
     * @var string
     */
    private static $content = "";


    /**
     * @param string $content
     */
    public static function setContent(string $content) : void
    {
        self::$content = $content;
    }


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }


    /**
     * @inheritDoc
     */
    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
    {
        return $this->globalScreen()->layout()->factory()->content()->withModification(function (Legacy $content) : Legacy {
            $ui = $this->dic->ui();

            return $ui->factory()->legacy($ui->renderer()->render($content) . self::$content);
        })->withLowPriority();
    }
}
