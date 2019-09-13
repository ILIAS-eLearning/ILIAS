<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;

/**
 * Class MailGlobalScreenToolProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class MailGlobalScreenToolProvider extends AbstractDynamicToolProvider
{
    const SHOW_MAIL_FOLDERS_TOOL = 'show_mail_folders_tool';

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->main()->repository()->administration();
    }

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(\ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $called_contexts) : array
    {
        $identification = function ($id) {
            return $this->identification_provider->identifier($id);
        };

        $tools = [];

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_MAIL_FOLDERS_TOOL) && $additional_data->get(self::SHOW_MAIL_FOLDERS_TOOL) === true) {
            $exp = new ilMailExplorer(new ilMailGUI(), $this->dic->user()->getId());

            $tools[] = $this->factory
                ->tool($identification('tree'))
                ->withTitle($this->dic->language()->txt("mail_folders"))
                ->withContent($this->dic->ui()->factory()->legacy($exp->getHTML()));
        }

        return $tools;
    }
}
