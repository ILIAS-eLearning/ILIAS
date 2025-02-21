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

namespace ILIAS\ILIASObject\Creation;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Render add new item selector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class AddNewItemGUI
{
    private \ilLanguage $lng;
    private \ilObjectDefinition $obj_definition;
    private \ilSetting $settings;
    private \ilAccessHandler $access;
    private \ilCtrl $ctrl;
    private \ilToolbarGUI $toolbar;
    private \ilGlobalTemplateInterface $tpl;

    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;

    /**
     * @param array<ILIAS\ILIASObject\Creation\AddNewItemElement> $elements
     * The Key MUST contain the object type or the
     */
    public function __construct(
        private array $elements = []
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('wsp');
        $this->obj_definition = $DIC["objDefinition"];
        $this->settings = $DIC->settings();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];

        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];

        $this->lng->loadLanguageModule("rep");
        $this->lng->loadLanguageModule("cntr");
    }

    /**
     * Add new item selection to current page incl. toolbar (trigger) and overlay
     */
    public function render(): void
    {
        if ($this->elements === []) {
            return;
        }
        $content = $this->ui_factory->menu()->drilldown($this->lng->txt('object_list'), $this->buildAddNewItemsMenu($this->elements));
        $modal = $this->ui_factory->modal()->roundtrip($this->lng->txt('cntr_add_new_item'), $content);
        $button = $this->ui_factory->button()->primary($this->lng->txt('cntr_add_new_item'), $modal->getShowSignal());

        $this->toolbar->addComponent($button);
        $this->tpl->setVariable(
            'IL_OBJECT_ADD_NEW_ITEM_MODAL',
            $this->ui_renderer->render($modal)
        );
    }

    /**
     * @return 	array<Component\Menu\Sub|Component\Clickable|Divider\Horizontal>
     */
    private function buildAddNewItemsMenu(array $elements): ?array
    {
        $sub_menu = [];

        foreach ($elements as $element) {
            if ($element->getType() === AddNewItemElementTypes::Group) {
                $sub_menu[] = $this->ui_factory->menu()->sub(
                    $element->getLabel(),
                    $this->buildAddNewItemsMenu($element->getSubElements())
                );
            }
            if ($element->getType() === AddNewItemElementTypes::Object) {
                $sub_menu[] = $this->ui_factory->link()->bulky(
                    $element->getIcon(),
                    $element->getLabel(),
                    $element->getCreationUri()
                );
                continue;
            }
        }

        return $sub_menu;
    }
}
