<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ilNotesGlobalScreenProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilNotesGlobalScreenProvider extends AbstractStaticMainMenuProvider
{

    /**
     * @var IdentificationInterface
     */
    protected $top_item;


    public function __construct(\ILIAS\DI\Container $dic)
    {
        parent::__construct($dic);
        $this->top_item = (new ilPDGlobalScreenProvider($dic))->getTopItem();
    }


    /**
     * Some other components want to provide Items for the main menu which are
     * located at the PD TopTitem by default. Therefore we have to provide our
     * TopTitem Identification for others
     *
     * @return IdentificationInterface
     */
    public function getTopItem() : IdentificationInterface
    {
        return $this->top_item;
    }


    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $action = function () use ($dic) : string {
            $c = "jumpToNotes";
            if ($dic->settings()->get("disable_notes")) {
                $c = "jumpToComments";
            }

            return "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=" . $c;
        };
        $action = $action();
        $title = function () use ($dic) : string {
            $dic->language()->loadLanguageModule("notes");
            $t = $dic->language()->txt("notes");
            if (!$dic->settings()->get("disable_notes") && !$dic->settings()->get("disable_comments")) {
                $t = $dic->language()->txt("notes_and_comments");
            }
            if ($dic->settings()->get("disable_notes")) {
                $t = $dic->language()->txt("notes_comments");
            }

            return $t;
        };
        $title = $title();

        return [$this->mainmenu->link($this->if->identifier('mm_pd_notes'))
                    ->withTitle($title)
                    ->withAction($action)
                    ->withParent($this->getTopItem())
                    ->withPosition(11)
                    ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                    ->withAvailableCallable(
                        function () use ($dic) {
                            return (bool) (!$dic->settings()->get("disable_notes") || !$dic->settings()->get("disable_comments"));
                        }
                    )];
    }
}
