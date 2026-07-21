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

namespace ILIAS\Exercise\PermanentLink;

use ILIAS\Exercise\InternalGUIService;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\UICore\PageContentProvider;
use ILIAS\StaticURL\Services as StaticUrl;
use ILIAS\Data\ReferenceId;

/**
 * Link to exercise: goto.php?target=exc_<exc_ref_id>
 * Link to assignment: goto.php?target=exc_<exc_ref_id>_<ass_id>
 * Link to grades screen of assignment: goto.php?target=exc_<exc_ref_id>_<ass_id>_grades
 * Link to download screen of member in assignment: goto.php?target=exc_<exc_ref_id>_<ass_id>_<member_id>_setdownload
 */
class PermanentLinkManager
{
    protected StaticUrl $static_url;
    protected InternalGUIService $gui;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui
    ) {
        global $DIC;
        /** @var StaticUrl $static_url */
        $this->static_url = $DIC['static_url'];

        $this->domain = $domain;
        $this->gui = $gui;
    }

    protected function _setPermanentLink(array $append): void
    {
        $request = $this->gui->request();
        $ref_id = $request->getRefId();
        $uri = $this->static_url->builder()->build(
            'exc', // namespace
            $ref_id > 0 ? new ReferenceId($ref_id) : null, // ref_id
            $append // additional parameters
        );
        PageContentProvider::setPermaLink((string) $uri);
    }

    public function setPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getDefaultAppend(
                $request->getAssId()
            )
        );
    }

    public function getDefaultAppend(int $ass_id): array
    {
        $append = [];
        if ($ass_id > 0) {
            $append[] = $ass_id;
        }
        return $append;
    }

    public function getPermanentLink(int $ref_id, int $ass_id): string
    {
        $append = $this->getDefaultAppend($ass_id);
        return $this->_getPermanentLink($ref_id, $append);
    }

    protected function _getPermanentLink(int $ref_id, array $append): string
    {
        $uri = $this->static_url->builder()->build(
            'exc', // namespace
            $ref_id > 0 ? new ReferenceId($ref_id) : null, // ref_id
            $append // additional parameters
        );

        return (string) $uri;
    }

    public function getDownloadSubmissionLink(int $ref_id, int $ass_id, int $user_id): string
    {
        return $this->_getPermanentLink($ref_id, [$ass_id, $user_id, "setdownload"]);
    }

    public function setGradesPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getGradesAppend(
                $request->getAssId()
            )
        );
    }

    public function getGradesLink(int $ref_id, int $ass_id): string
    {
        return $this->_getPermanentLink($ref_id, [$ass_id, "grades"]);
    }

    public function getGradesAppend(int $ass_id): array
    {
        return [$ass_id, "grades"];
    }

    public function setGivenFeedbackPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getGivenFeedbackAppend(
                $request->getAssId(),
                $request->getPeerId()
            )
        );
    }

    public function getGivenFeedbackLink(int $ref_id, int $ass_id, int $peer_id): string
    {
        return $this->_getPermanentLink($ref_id, [$ass_id, $peer_id, "given"]);
    }

    public function getGivenFeedbackAppend(int $ass_id, int $peer_id): array
    {
        return [$ass_id, $peer_id, "given"];
    }

    public function setReceivedFeedbackPermanentLink(): void
    {
        $request = $this->gui->request();
        $this->_setPermanentLink(
            $this->getReceivedFeedbackAppend(
                $request->getAssId()
            )
        );
    }

    public function getReceivedFeedbackLink(int $ref_id, int $ass_id): string
    {
        return $this->_getPermanentLink($ref_id, [$ass_id, "received"]);
    }

    public function getReceivedFeedbackAppend(int $ass_id): array
    {
        return [$ass_id, "received"];
    }

    public function getOpenSubmissionsLink(int $ref_id, int $ass_id, int $user_id): string
    {
        return $this->_getPermanentLink($ref_id, [$ass_id, $user_id, "opensubmission"]);
    }

}
