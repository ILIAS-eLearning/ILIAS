<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilAssFileUploadUploadsExporter
{
    const ZIP_FILE_MIME_TYPE = 'application/zip';
    const ZIP_FILE_EXTENSION = '.zip';

    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;
    
    /**
     * @var integer
     */
    protected $refId;
    
    /**
     * @var integer
     */
    private $testId;

    /**
     * @var string
     */
    private $testTitle;

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
    public function __construct(ilDBInterface $db, ilLanguage $lng)
    {
        $this->db = $db;
        $this->lng = $lng;
    }
    
    /**
     * @return int
     */
    public function getRefId() : int
    {
        return $this->refId;
    }
    
    /**
     * @param int $refId
     */
    public function setRefId($refId) : void
    {
        $this->refId = $refId;
    }

    /**
     * @return int
     */
    public function getTestId() : int
    {
        return $this->testId;
    }

    /**
     * @param int $testId
     */
    public function setTestId($testId) : void
    {
        $this->testId = $testId;
    }

    /**
     * @return string
     */
    public function getTestTitle() : string
    {
        return $this->testTitle;
    }

    /**
     * @param string $testTitle
     */
    public function setTestTitle($testTitle) : void
    {
        $this->testTitle = $testTitle;
    }

    /**
     * @return ilObjFileHandlingQuestionType
     */
    public function getQuestion() : ilObjFileHandlingQuestionType
    {
        return $this->question;
    }

    /**
     * @param ilObjFileHandlingQuestionType $question
     */
    public function setQuestion($question) : void
    {
        $this->question = $question;
    }

    public function build() : void
    {
        $this->initFilenames();
        
        $solutionData = $this->getFileUploadSolutionData();
        
        $participantData = $this->getParticipantData($solutionData);
        
        $this->collectUploadedFiles($solutionData, $participantData);

        $this->createFileUploadCollectionZipFile();

        $this->removeFileUploadCollection();
    }
    
    private function initFilenames() : void
    {
        $this->tempDirPath = ilFileUtils::ilTempnam();
        
        $this->tempZipFilePath = ilFileUtils::ilTempnam($this->tempDirPath) . self::ZIP_FILE_EXTENSION;
        
        $this->mainFolderName = ilFileUtils::getASCIIFilename(
            str_replace(' ', '', $this->getTestTitle() . '_' . $this->question->getTitle())
        );
    }
    
    private function getFileUploadSolutionData() : array
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
    
    private function getParticipantData($solutionData) : ilTestParticipantData
    {
        $activeIds = array();
            
        foreach ($solutionData as $activeId => $passes) {
            $activeIds[] = $activeId;
        }

        require_once 'Modules/Test/classes/class.ilTestParticipantData.php';
        $participantData = new ilTestParticipantData($this->db, $this->lng);
        $participantData->setActiveIdsFilter($activeIds);
        $participantData->setParticipantAccessFilter(
            ilTestParticipantAccessFilter::getAccessStatisticsUserFilter($this->getRefId())
        );
        $participantData->load($this->getTestId());
        
        return $participantData;
    }
    
    private function collectUploadedFiles($solutionData, ilTestParticipantData $participantData) : void
    {
        foreach ($solutionData as $activeId => $passes) {
            if (!in_array($activeId, $participantData->getActiveIds())) {
                continue;
            }
            
            foreach ($passes as $pass => $files) {
                foreach ($files as $file) {
                    $uploadedFileDir = $this->question->getFileUploadPath(
                        $this->getTestId(),
                        $activeId,
                        $this->question->getId()
                    );

                    // #20317
                    if (!is_file($uploadedFileDir . $file['value1'])) {
                        continue;
                    }

                    $destinationDir = $this->tempDirPath . '/' . $this->mainFolderName . '/';
                    $destinationDir .= $participantData->getFileSystemCompliantFullnameByActiveId($activeId) . '/';
                    $destinationDir .= $this->getPassSubDirName($file['pass']) . '/';
                    
                    ilFileUtils::makeDirParents($destinationDir);

                    copy($uploadedFileDir . $file['value1'], $destinationDir . $file['value2']);
                }
            }
        }
    }
    
    private function getPassSubDirName($pass) : string
    {
        return $this->lng->txt('pass') . '_' . ($pass + 1);
    }
    
    private function createFileUploadCollectionZipFile() : void
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

    private function removeFileUploadCollection() : void
    {
        ilFileUtils::delDir($this->tempDirPath);
    }
    
    public function getFinalZipFilePath() : string
    {
        return $this->finalZipFilePath;
    }
    
    public function getDispoZipFileName() : string
    {
        return ilFileUtils::getASCIIFilename(
            $this->mainFolderName . self::ZIP_FILE_EXTENSION
        );
    }
    
    public function getZipFileMimeType() : string
    {
        return self::ZIP_FILE_MIME_TYPE;
    }
}
