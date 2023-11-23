<?php

class ilTestArchiverTest extends ilTestBaseTestCase
{
    private ilTestArchiver $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilias();

        $this->testObj = new ilTestArchiver(0, 0);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestArchiver::class, $this->testObj);
    }

    public function testGetAndSetParticipantData(): void
    {
        $this->markTestSkipped();
    }

    public function testHandInParticipantQuestionMaterial(): void
    {
        $this->markTestSkipped();
    }

    public function testHandInParticipantMisc(): void
    {
        $this->markTestSkipped();
    }

    public function testHandInTestBestSolution(): void
    {
        $this->markTestSkipped();
    }

    public function testHandInBestSolutionQuestionMaterial(): void
    {
        $this->markTestSkipped();
    }

    public function testHandInTestResult(): void
    {
        $this->markTestSkipped();
    }

    public function testHasTestArchive(): void
    {
        $this->markTestSkipped();
    }

    public function testCreateArchiveForTest(): void
    {
        $this->markTestSkipped();
    }

    public function testGetTestArchive(): void
    {
        $this->markTestSkipped();
    }

    public function testEnsureTestArchiveIsAvailable(): void
    {
        $this->markTestSkipped();
    }

    public function testUpdateTestArchive(): void
    {
        $this->markTestSkipped();
    }

    public function testEnsureZipExportDirectoryExists(): void
    {
        $this->markTestSkipped();
    }

    public function testHasZipExportDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testCreateZipExportDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testGetZipExportDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testCompressTestArchive(): void
    {
        $this->markTestSkipped();
    }

    public function testHasPassDataDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testCreatePassDataDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testBuildPassDataDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testGetPassDataDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testEnsurePassDataDirectoryIsAvailable(): void
    {
        $this->markTestSkipped();
    }

    public function testHasPassMaterialsDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testCreatePassMaterialsDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testGetPassMaterialsDirectory(): void
    {
        $this->markTestSkipped();
    }

    public function testEnsurePassMaterialsDirectoryIsAvailable(): void
    {
        $this->markTestSkipped();
    }

    public function testReadArchiveDataIndex(): void
    {
        $this->markTestSkipped();
    }

    public function testAppendToArchiveDataIndex(): void
    {
        $this->markTestSkipped();
    }

    public function testDeterminePassDataPath(): void
    {
        $this->markTestSkipped();
    }

    public function testLogArchivingProcess(): void
    {
        $this->markTestSkipped();
    }

    public function testCountFilesInDirectory(): void
    {
        $this->markTestSkipped();
    }
}