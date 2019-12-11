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
    public function getRefId()
    {
        return $this->refId;
    }
    
    /**
     * @param int $refId
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;
    }

    /**
     * @return int
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * @param int $testId
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;
    }

    /**
     * @return string
     */
    public function getTestTitle()
    {
        return $this->testTitle;
    }

    /**
     * @param string $testTitle
     */
    public function setTestTitle($testTitle)
    {
        $this->testTitle = $testTitle;
    }

    /**
     * @return ilObjFileHandlingQuestionType
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param ilObjFileHandlingQuestionType $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function build()
    {
        $this->initFilenames();
        
        $solutionData = $this->getFileUploadSolutionData();
        
        $participantData = $this->getParticipantData($solutionData);
        
        $this->collectUploadedFiles($solutionData, $participantData);

        $this->createFileUploadCollectionZipFile();

        $this->removeFileUploadCollection();
    }
    
    private function initFilenames()
    {
        $this->tempDirPath = ilUtil::ilTempnam();
        
        $this->tempZipFilePath = ilUtil::ilTempnam($this->tempDirPath) . self::ZIP_FILE_EXTENSION;
        
        $this->mainFolderName = ilUtil::getASCIIFilename(
            str_replace(' ', '', $this->getTestTitle() . '_' . $this->question->getTitle())
        );
    }
    
    private function getFileUploadSolutionData()
    {
        $query  = "
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
    
    private function getParticipantData($solutionData)
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
    
    private function collectUploadedFiles($solutionData, ilTestParticipantData $participantData)
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
                        
                    ilUtil::makeDirParents($destinationDir);

                    copy($uploadedFileDir . $file['value1'], $destinationDir . $file['value2']);
                }
            }
        }
    }
    
    private function getPassSubDirName($pass)
    {
        return $this->lng->txt('pass') . '_' . ($pass + 1);
    }
    
    private function createFileUploadCollectionZipFile()
    {
        ilUtil::zip($this->tempDirPath . '/' . $this->mainFolderName, $this->tempZipFilePath);
        
        $pathinfo = pathinfo($this->tempZipFilePath);
        $this->finalZipFilePath = dirname($pathinfo['dirname']) . '/' . $pathinfo['basename'];
        
        try {
            require_once 'Services/Utilities/classes/class.ilFileUtils.php';
            ilFileUtils::rename($this->tempZipFilePath, $this->finalZipFilePath);
        } catch (\ilFileUtilsException $e) {
            \ilLoggerFactory::getRootLogger()->error($e->getMessage());
        }
    }

    private function removeFileUploadCollection()
    {
        ilUtil::delDir($this->tempDirPath);
    }
    
    public function getFinalZipFilePath()
    {
        return $this->finalZipFilePath;
    }
    
    public function getDispoZipFileName()
    {
        return ilUtil::getASCIIFilename(
            $this->mainFolderName . self::ZIP_FILE_EXTENSION
        );
    }
    
    public function getZipFileMimeType()
    {
        return self::ZIP_FILE_MIME_TYPE;
    }
}
