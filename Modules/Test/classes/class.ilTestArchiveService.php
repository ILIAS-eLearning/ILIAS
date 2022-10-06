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

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestArchiveService
{
    /**
     * @var ilObjTest
     */
    protected $testOBJ;

    /**
     * @var ilTestParticipantData
     */
    protected $participantData;

    public function __construct(ilObjTest $testOBJ)
    {
        $this->testOBJ = $testOBJ;
        $this->participantData = null;
    }

    /**
     * @return ilTestParticipantData
     */
    public function getParticipantData(): ?ilTestParticipantData
    {
        return $this->participantData;
    }

    /**
     * @param ilTestParticipantData $participantData
     */
    public function setParticipantData(ilTestParticipantData $participantData)
    {
        $this->participantData = $participantData;
    }

    public function archivePassesByActives($passesByActives)
    {
        foreach ($passesByActives as $activeId => $passes) {
            foreach ($passes as $pass) {
                $this->archiveActivesPass($activeId, $pass);
            }
        }
    }

    public function archiveActivesPass($activeId, $pass)
    {
        $content = $this->renderOverviewContent($activeId, $pass);
        $filename = $this->buildOverviewFilename($activeId, $pass);

        ilTestPDFGenerator::generatePDF($content, ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename, PDF_USER_RESULT);

        $archiver = new ilTestArchiver($this->testOBJ->getId());
        $archiver->setParticipantData($this->getParticipantData());
        $archiver->handInTestResult($activeId, $pass, $filename);

        unlink($filename);
    }

    /**
     * @param $activeId
     * @param $pass
     * @return string
     */
    private function renderOverviewContent($activeId, $pass): string
    {
        $results = $this->testOBJ->getTestResult(
            $activeId,
            $pass,
            false
        );

        $gui = new ilTestServiceGUI($this->testOBJ);

        require_once 'Modules/Test/classes/class.ilTestResultHeaderLabelBuilder.php';
        $testResultHeaderLabelBuilder = new ilTestResultHeaderLabelBuilder($GLOBALS['DIC']->language(), $GLOBALS['DIC']['ilObjDataCache']);

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
        return dirname($tmpFileName) . '/scores-' . $this->testOBJ->getId() . '-' . $activeId . '-' . $pass . '.pdf';
    }
}
