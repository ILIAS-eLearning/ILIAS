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
 ********************************************************************
 */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Notes\Service as NotesService;

class ilPollCommentsHandler
{
    protected NotesService $notes;
    protected GlobalHttpState $http;
    protected Refinery $refinery;

    protected string $redraw_url;

    public function __construct(
        NotesService $notes,
        GlobalHttpState $http,
        Refinery $refinery,
        string $redraw_url
    ) {
        $this->notes = $notes;
        $this->http = $http;
        $this->refinery = $refinery;
        $this->redraw_url = $redraw_url;
    }

    public function getRedrawURL(): string
    {
        return $this->redraw_url;
    }

    /**
     * Builds JavaScript Call to open CommentLayer via html link
     */
    public function commentJSCall(int $ref_id): string
    {
        $obj_id = $this->lookupObjectId($ref_id);
        $ajax_hash = $this->buildAjaxHashForPoll($ref_id, $obj_id);


        return $this->getListCommentsJSCallForPoll($ajax_hash, $ref_id);
    }

    public function getNumberOfCommentsForRedraw(): void
    {
        $poll_id = 0;
        if ($this->http->wrapper()->query()->has('poll_id')) {
            $poll_id = $this->http->wrapper()->query()->retrieve(
                'poll_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $number = $this->getNumberOfComments($poll_id);

        if ($number > 0) {
            echo "(" . $number . ")";
        } else {
            echo "";
        }

        exit();
    }

    public function getNumberOfComments(int $ref_id): int
    {
        $obj_id = $this->lookupObjectId($ref_id);
        $context = $this->notes->data()->context($obj_id, 0, 'poll');
        return $this->notes->domain()->getNrOfCommentsForContext($context);
    }

    protected function lookupObjectId(int $ref_id): int
    {
        return ilObject2::_lookupObjectId($ref_id);
    }

    protected function buildAjaxHashForPoll(int $ref_id, int $obj_id): string
    {
        return ilCommonActionDispatcherGUI::buildAjaxHash(
            ilCommonActionDispatcherGUI::TYPE_REPOSITORY,
            $ref_id,
            'poll',
            $obj_id
        );
    }

    protected function getListCommentsJSCallForPoll(
        string $ajax_hash,
        int $ref_id
    ): string {
        return ilNoteGUI::getListCommentsJSCall(
            $ajax_hash,
            "ilPoll.redrawComments(" . $ref_id . ");"
        );
    }
}
