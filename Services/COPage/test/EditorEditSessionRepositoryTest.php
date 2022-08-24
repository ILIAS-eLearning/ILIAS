<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class EditorEditSessionRepositoryTest extends TestCase
{
    //protected $backupGlobals = false;
    protected \ILIAS\COPage\Editor\EditSessionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new \ILIAS\COPage\Editor\EditSessionRepository();
        $this->repo->clear();
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test clear
     */
    public function testClear(): void
    {
        $repo = $this->repo;
        $repo->setPageError("page_error");
        $repo->setSubCmd("sub_cmd");
        $repo->setQuestionPool(4);
        $repo->setTextLang(10, "en");
        $repo->setMediaPool(5);
        $repo->clear([10]);
        $this->assertEquals(
            "00",
            $repo->getPageError() .
            $repo->getSubCmd() .
            $repo->getTextLang(4) .
            $repo->getMediaPool() .
            $repo->getQuestionPool()
        );
    }

    /**
     * Test page error
     */
    public function testPageError(): void
    {
        $repo = $this->repo;
        $repo->setPageError("page_error");
        $this->assertEquals(
            "page_error",
            $repo->getPageError()
        );
    }

    /**
     * Test sub-command
     */
    public function testSubCmd(): void
    {
        $repo = $this->repo;
        $repo->setSubCmd("sub");
        $this->assertEquals(
            "sub",
            $repo->getSubCmd()
        );
    }

    /**
     * Test question pool
     */
    public function testQuestionPool(): void
    {
        $repo = $this->repo;
        $repo->setQuestionPool(15);
        $this->assertEquals(
            15,
            $repo->getQuestionPool()
        );
    }

    /**
     * Test media pool
     */
    public function testMediaPool(): void
    {
        $repo = $this->repo;
        $repo->setMediaPool(12);
        $this->assertEquals(
            12,
            $repo->getMediaPool()
        );
    }

    /**
     * Test text lang
     */
    public function testTextLang(): void
    {
        $repo = $this->repo;
        $repo->setTextLang(17, "fr");
        $this->assertEquals(
            "fr",
            $repo->getTextLang(17)
        );
    }
}
