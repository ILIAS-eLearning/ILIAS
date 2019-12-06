<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
require_once("Services/Style/System/classes/Documentation/class.ilSystemStyleDocumentationGUI.php");
require_once("Services/Style/System/classes/Documentation/class.ilKSDocumentationExplorerGUI.php");

/**
 * Class SystemStylesGlobalScreenToolProvider
 * @author Timon Amstutz
 */
class SystemStylesGlobalScreenToolProvider extends AbstractDynamicToolProvider
{
    const SHOW_MAIL_FOLDERS_TOOL = 'show_mail_folders_tool';

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->administration();
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(\ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $called_contexts) : array
    {
        $identification = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };

        $tools = [];

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->is(ilSystemStyleDocumentationGUI::SHOW_TREE,true)) {
            $exp = new ilKSDocumentationExplorerGUI();

            $title = $this->dic->language()->txt('documentation');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('stys', $title)->withIsOutlined(true);

            $tools[] = $this->factory
                ->tool($identification('system_styles_tree'))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContent($this->dic->ui()->factory()->legacy($exp->getHTML(true)));
        }

        return $tools;
    }
}
