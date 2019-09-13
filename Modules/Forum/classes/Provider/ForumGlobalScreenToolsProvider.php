<?php declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;

/**
 * Class ForumGlobalScreenToolsProvider
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
            return $this->globalScreen()->identification()->fromSerializedIdentification($id);
        };
        $l   = function (string $content) {
            return $this->dic->ui()->factory()->legacy($content);
        };

        $tools = [];

        $queryParams = $this->dic->http()->request()->getQueryParams();
        $refId       = (int) ($queryParams['ref_id'] ?? 0);
        $threadId    = (int) ($queryParams['thr_pk'] ?? 0);

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_FORUM_THREADS_TOOL) && $additional_data->get(self::SHOW_FORUM_THREADS_TOOL) === true) {
            $isModerator = $this->dic->access()->checkAccess('moderate_frm', '', $refId);
            $thread      = new ilForumTopic((int) $threadId, $isModerator);

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

            $tools[] = $this->factory
                ->tool($iff('Forum|Tree'))
                ->withTitle($this->dic->language()->txt("tree"))
                ->withContent($l($exp->getHTML()));
        }

        return $tools;
    }
}
