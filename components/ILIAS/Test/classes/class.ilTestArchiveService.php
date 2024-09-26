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

use ILIAS\Test\Results\Presentation\TitlesBuilder as ResultsTitlesBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\ResourceStorage\Services as IRSS;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestArchiveService
{
    protected ?ilTestParticipantData $participantData = null;

    public function __construct(
        private readonly ilObjTest $test_obj,
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly ilObjUser $user,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly IRSS $irss,
        private readonly ServerRequestInterface $request,
        private readonly ilObjectDataCache $obj_cache,
        private readonly ilTestParticipantAccessFilterFactory $participant_access_filter_factory,
        private readonly ilTestHTMLGenerator $html_generator
    ) {
        $this->participantData = null;
    }

    public function getParticipantData(): ?ilTestParticipantData
    {
        return $this->participantData;
    }

    public function setParticipantData(ilTestParticipantData $participantData): void
    {
        $this->participantData = $participantData;
    }

    public function archivePassesByActives($passesByActives): void
    {
        foreach ($passesByActives as $activeId => $passes) {
            foreach ($passes as $pass) {
                $this->archiveActivesPass($activeId, $pass);
            }
        }
    }

    public function archiveActivesPass(int $active_id, int $pass): void
    {
        $content = $this->renderOverviewContent($active_id, $pass);
        $filename = $this->buildOverviewFilename($active_id, $pass);
        $this->html_generator->generateHTML($content, $filename);
        $archiver = new ilTestArchiver(
            $this->lng,
            $this->db,
            $this->user,
            $this->ui_factory,
            $this->ui_renderer,
            $this->irss,
            $this->request,
            $this->obj_cache,
            $this->participant_access_filter_factory,
            $this->test_obj->getId()
        );
        $archiver->setParticipantData($this->getParticipantData());
        $archiver->handInTestResult($active_id, $pass, $filename);
        $archiver->handInParticipantUploadedResults($active_id, $pass, $this->test_obj);
        unlink($filename);
    }

    /**
     * @param $activeId
     * @param $pass
     * @return string
     */
    private function renderOverviewContent($activeId, $pass): string
    {
        $results = $this->test_obj->getTestResult(
            $activeId,
            $pass,
            false
        );

        $gui = new ilTestServiceGUI($this->test_obj);
        $testResultHeaderLabelBuilder = new ResultsTitlesBuilder($this->lng, $this->obj_cache);

        return $gui->getPassListOfAnswers(
            $results,
            $activeId,
            $pass,
            true,
            false,
            false,
            true,
            false,
            null,
            $testResultHeaderLabelBuilder
        );
    }

    /**
     * @param $activeId
     * @param $pass
     * @return string
     */
    private function buildOverviewFilename($activeId, $pass): string
    {
        $tmpFileName = ilFileUtils::ilTempnam();
        return dirname($tmpFileName) . '/scores-' . $this->test_obj->getId() . '-' . $activeId . '-' . $pass . '.html';
    }
}
