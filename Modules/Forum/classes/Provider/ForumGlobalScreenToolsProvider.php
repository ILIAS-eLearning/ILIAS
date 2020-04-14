<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;

/**
 * Class ForumGlobalScreenToolsProvider
 *
 * @author Michael Jansen <mjansen@databay.de>
 */
class ForumGlobalScreenToolsProvider extends AbstractDynamicToolProvider
{
    const SHOW_FORUM_THREADS_TOOL = 'show_forum_threads_tool';


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
        $iff = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        $l = function (string $content) {
            return $this->dic->ui()->factory()->legacy($content);
        };

        $tools = [];

        $queryParams = $this->dic->http()->request()->getQueryParams();
        $refId = (int) ($queryParams['ref_id'] ?? 0);
        $threadId = (int) ($queryParams['thr_pk'] ?? 0);
        $target = (string) ($queryParams['target'] ?? '');

        if (strlen($target) > 0 && count($targetParts = explode('_', $target)) >= 3) {
            if (0 === $refId) {
                $refId = $targetParts[1];
            }

            if (0 === $threadId) {
                $threadId = $targetParts[2];
            }
        }

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_FORUM_THREADS_TOOL) && $additional_data->get(self::SHOW_FORUM_THREADS_TOOL) === true) {
            $isModerator = $this->dic->access()->checkAccess('moderate_frm', '', $refId);

            $title = $this->dic->language()->txt('tree');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->standard('frm', $title)->withIsOutlined(true);

            $tools[] = $this->factory
                ->tool($iff('Forum|Tree'))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(static function () use ($l, $threadId, $isModerator, $refId) {
                    $thread = new ilForumTopic((int) $threadId, $isModerator);
                    $exp = new ilForumExplorerGUI(
                        'frm_exp_' . $thread->getId(),
                        new ilObjForumGUI(
                            "",
                            $refId,
                            true,
                            false
                        ),
                        'viewThread',
                        $thread
                    );
                    return $l($exp->getHTML(true));
                });
        }

        return $tools;
    }
}
