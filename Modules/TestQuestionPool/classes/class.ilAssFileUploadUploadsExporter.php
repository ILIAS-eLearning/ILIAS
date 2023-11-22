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

use ILIAS\Filesystem\Stream\Streams;

/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssFileUploadUploadsExporter
{
    public const ZIP_FILE_MIME_TYPE = 'application/zip';
    public const ZIP_FILE_EXTENSION = '.zip';
    private Closure $acces_filter;
    private \ILIAS\ResourceStorage\Services $irss;
    private \ILIAS\Filesystem\Util\Archive\Archives $archive;
    private \ILIAS\FileDelivery\Services $file_delivery;

    /**
     * @var string
     */
    private $test_title;

    /**
     * @var ilObjFileHandlingQuestionType
     */
    private $question;

    /**
     * @var string
     */
    private $finalZipFilePath;

    /**
     * @var string
     */
    private $tempZipFilePath;

    /**
     * @var string
     */
    private $tempDirPath;

    /**
     * @var string
     */
    private $mainFolderName;

    /**
     * @param ilDBInterface $db
     */
    public function __construct(
        private ilDBInterface $db,
        private ilLanguage $lng,
        private int $ref_id,
        private int $test_id,
    ) {
        global $DIC;
        $f = new ilTestParticipantAccessFilterFactory($DIC->access());
        $this->acces_filter = $f->getAccessStatisticsUserFilter($this->ref_id);

        $this->irss = $DIC->resourceStorage();
        $this->archive = $DIC->archives();
        $this->file_delivery = $DIC->fileDelivery();
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }


    public function getTestId(): int
    {
        return $this->test_id;
    }


    /**
     * @return string
     */
    public function getTestTitle(): string
    {
        return $this->test_title;
    }


    public function setTestTitle(string $test_title): void
    {
        $this->test_title = $test_title;
    }

    /**
     * @return ilObjFileHandlingQuestionType
     */
    public function getQuestion(): ilObjFileHandlingQuestionType
    {
        return $this->question;
    }

    /**
     * @param ilObjFileHandlingQuestionType $question
     */
    public function setQuestion($question): void
    {
        $this->question = $question;
    }

    public function buildAndDownload(): void
    {
        $solution_data = $this->getFileUploadSolutionData();

        $participant_data = $this->getParticipantData($solution_data);

        $this->collectUploadedFiles($solution_data, $participant_data);
    }

    private function initFilenames(): void
    {
        $this->tempDirPath = ilFileUtils::ilTempnam();

        $this->tempZipFilePath = ilFileUtils::ilTempnam($this->tempDirPath) . self::ZIP_FILE_EXTENSION;

        $this->mainFolderName = ilFileUtils::getASCIIFilename(
            str_replace(' ', '', $this->getTestTitle() . '_' . $this->question->getTitle())
        );
    }

    private function getFileUploadSolutionData(): array
    {
        $query = "
			SELECT tst_solutions.solution_id, tst_solutions.pass, tst_solutions.active_fi, tst_solutions.question_fi,
				tst_solutions.value1, tst_solutions.value2, tst_solutions.tstamp
			FROM tst_solutions, tst_active, qpl_questions
			WHERE tst_solutions.active_fi = tst_active.active_id
			AND tst_solutions.question_fi = qpl_questions.question_id
			AND tst_solutions.question_fi = %s
			AND tst_active.test_fi = %s
			ORDER BY tst_solutions.active_fi, tst_solutions.tstamp
		";

        $res = $this->db->queryF(
            $query,
            array("integer", "integer"),
            array($this->question->getId(), $this->getTestId())
        );

        $solutionData = array();

        while ($row = $this->db->fetchAssoc($res)) {
            if (!isset($solutionData[$row['active_fi']])) {
                $solutionData[ $row['active_fi'] ] = array();
            }

            if (!isset($solutionData[ $row['active_fi'] ][ $row['pass'] ])) {
                $solutionData[ $row['active_fi'] ][ $row['pass'] ] = array();
            }

            $solutionData[ $row['active_fi'] ][ $row['pass'] ][] = $row;
        }

        return $solutionData;
    }

    private function getParticipantData($solutionData): ilTestParticipantData
    {
        $activeIds = array();

        foreach ($solutionData as $activeId => $passes) {
            $activeIds[] = $activeId;
        }

        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setActiveIdsFilter($activeIds);
        $participantData->setParticipantAccessFilter($this->acces_filter);
        $participantData->load($this->getTestId());

        return $participantData;
    }

    private function collectUploadedFiles(array $solution_data, ilTestParticipantData $participant_data): void
    {
        $streams = [];

        foreach ($solution_data as $activeId => $passes) {
            if (!in_array($activeId, $participant_data->getActiveIds(), true)) {
                continue;
            }

            foreach ($passes as $pass => $files) {
                foreach ($files as $file) {
                    // path inside zip
                    $dir = $this->mainFolderName;
                    $dir .= $participant_data->getFileSystemCompliantFullnameByActiveId($activeId) . '/';
                    $dir .= $this->getPassSubDirName($file['pass']) . '/';

                    // IRSS Version
                    if ($file['value2'] === 'rid') {
                        $revision = $this->irss->manage()->getCurrentRevision(
                            $rid = $this->irss->manage()->find($file['value1'])
                        );
                        $streams[$dir . $revision->getTitle()] = $this->irss->consume()->stream($rid)->getStream();
                        continue;
                    }

                    // Legacy Version
                    $file_dir = $this->question->getFileUploadPath(
                        $this->getTestId(),
                        $activeId,
                        $this->question->getId()
                    );

                    $legacy_file_path = $file_dir . $file['value1'];
                    if (!is_file($legacy_file_path)) {
                        continue;
                    }

                    $streams[$dir . $file['value2']] = Streams::ofResource(fopen($legacy_file_path, 'rb'));
                }
            }
        }

        $zip = $this->archive->zip($streams);

        $this->file_delivery->delivery()->attached(
            $zip->get(),
            $this->getDispoZipFileName(),
            'application/zip'
        );
    }

    private function getPassSubDirName($pass): string
    {
        return $this->lng->txt('pass') . '_' . ($pass + 1);
    }

    private function createFileUploadCollectionZipFile(): void
    {
        ilFileUtils::zip($this->tempDirPath . '/' . $this->mainFolderName, $this->tempZipFilePath);

        $pathinfo = pathinfo($this->tempZipFilePath);
        $this->finalZipFilePath = dirname($pathinfo['dirname']) . '/' . $pathinfo['basename'];

        try {
            ilFileUtils::rename($this->tempZipFilePath, $this->finalZipFilePath);
        } catch (\ilFileUtilsException $e) {
            \ilLoggerFactory::getRootLogger()->error($e->getMessage());
        }
    }

    public function getFinalZipFilePath(): string
    {
        return $this->finalZipFilePath;
    }

    public function getDispoZipFileName(): string
    {
        return ilFileUtils::getASCIIFilename(
            $this->test_title . '_' . $this->question->getTitle() . self::ZIP_FILE_EXTENSION
        );
    }

    public function getZipFileMimeType(): string
    {
        return self::ZIP_FILE_MIME_TYPE;
    }
}
