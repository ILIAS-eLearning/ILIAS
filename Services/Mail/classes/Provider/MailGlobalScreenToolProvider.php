<?php declare(strict_types=1);
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

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;

/**
 * Class MailGlobalScreenToolProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class MailGlobalScreenToolProvider extends AbstractDynamicToolProvider
{
    public const SHOW_MAIL_FOLDERS_TOOL = 'show_mail_folders_tool';

    public function isInterestedInContexts() : ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->main()->repository()->administration();
    }

    public function getToolsForContextStack(
        ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $called_contexts
    ) : array {
        $identification = function ($id) : ILIAS\GlobalScreen\Identification\IdentificationInterface {
            return $this->identification_provider->contextAwareIdentifier($id);
        };

        $tools = [];

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_MAIL_FOLDERS_TOOL) &&
            $additional_data->get(self::SHOW_MAIL_FOLDERS_TOOL) === true
        ) {
            $title = $this->dic->language()->txt('mail_folders');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('mail', $title);

            $tools[] = $this->factory
                ->tool($identification('mail_folders_tree'))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () : ILIAS\UI\Component\Legacy\Legacy {
                    $exp = new ilMailExplorer(new ilMailGUI(), $this->dic->user()->getId());

                    return $this->dic->ui()->factory()->legacy($exp->getHTML(true));
                });
        }

        return $tools;
    }
}
