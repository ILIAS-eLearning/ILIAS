<?php

declare(strict_types=1);

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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\UI\Component\Component;

/**
 * Class ForumGlobalScreenToolsProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ForumGlobalScreenToolsProvider extends AbstractDynamicToolProvider
{
    public const SHOW_FORUM_THREADS_TOOL = 'show_forum_threads_tool';
    public const REF_ID = 'ref_id';
    public const FORUM_THEAD = 'frm_thread';
    public const FORUM_THREAD_ROOT = 'frm_thread_root';
    public const FORUM_BASE_CONTROLLER = 'frm_base_controller';
    public const PAGE = 'frm_thread_page';

    public function isInterestedInContexts(): \ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection
    {
        return $this->context_collection->main()->repository()->administration();
    }

    public function getToolsForContextStack(
        \ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts $called_contexts
    ): array {
        $iff = function (string $id): IdentificationInterface {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        $l = function (string $content): Component {
            return $this->dic->ui()->factory()->legacy($content);
        };

        $tools = [];

        $additionalData = $called_contexts->getLast()->getAdditionalData();
        if ($additionalData->exists(self::SHOW_FORUM_THREADS_TOOL) && $additionalData->get(self::SHOW_FORUM_THREADS_TOOL) === true) {
            $thread = $additionalData->get(self::FORUM_THEAD);
            $controller = $additionalData->get(self::FORUM_BASE_CONTROLLER);
            $root = $additionalData->get(self::FORUM_THREAD_ROOT);

            if ($root instanceof ilForumPost) {
                $title = $this->dic->language()->txt('forums_articles');
                $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('frm', $title);

                $tools[] = $this->factory
                    ->tool($iff('Forum|Tree'))
                    ->withTitle($title)
                    ->withSymbol($icon)
                    ->withContentWrapper(static function () use ($l, $controller, $thread, $root): Component {
                        $exp = new ilForumExplorerGUI(
                            'frm_exp_' . $thread->getId(),
                            $controller,
                            'viewThread',
                            $thread,
                            $root
                        );

                        return $l($exp->getHTML(true));
                    });
            }
        }

        return $tools;
    }
}
