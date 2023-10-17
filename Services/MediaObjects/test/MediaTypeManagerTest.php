<?php

use PHPUnit\Framework\TestCase;

class MediaTypeManagerTest extends TestCase
{
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $types;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    protected function getTypeManager(array $mime_blacklist = []): \ILIAS\MediaObjects\MediaType\MediaTypeManager
    {
        return new \ILIAS\MediaObjects\MediaType\MediaTypeManager($mime_blacklist);
    }

    public function testIsImage(): void
    {
        $this->assertEquals(
            true,
            $this->getTypeManager()->isImage("image/png")
        );
    }

    public function testIsVideo(): void
    {
        $this->assertEquals(
            true,
            $this->getTypeManager()->isVideo("video/webm")
        );
    }

    public function testIsAudio(): void
    {
        $this->assertEquals(
            true,
            $this->getTypeManager()->isAudio("audio/mpeg")
        );
    }

    public function testGetAudioSuffixes(): void
    {
        $this->assertEquals(
            ["mp3"],
            iterator_to_array($this->getTypeManager()->getAudioSuffixes())
        );
    }

    public function testGetVideoSuffixes(): void
    {
        $this->assertEquals(
            ["mp4", "webm"],
            iterator_to_array($this->getTypeManager()->getVideoSuffixes())
        );
    }

    public function testGetImageSuffixes(): void
    {
        $this->assertEquals(
            true,
            in_array("png", iterator_to_array($this->getTypeManager()->getImageSuffixes()))
        );
    }

    public function testGetOtherSuffixes(): void
    {
        $this->assertEquals(
            true,
            in_array("html", iterator_to_array($this->getTypeManager()->getOtherSuffixes()))
        );
    }

    public function testGetAudioMimeTypes(): void
    {
        $this->assertEquals(
            ["audio/mpeg"],
            iterator_to_array($this->getTypeManager()->getAudioMimeTypes())
        );
    }

    public function testGetVideoMimeTypes(): void
    {
        $this->assertEquals(
            true,
            in_array("video/mp4", iterator_to_array($this->getTypeManager()->getVideoMimeTypes()))
        );
    }

    public function testGetImageMimeTypes(): void
    {
        $this->assertEquals(
            true,
            in_array("image/jpeg", iterator_to_array($this->getTypeManager()->getImageMimeTypes()))
        );
    }

    public function testGetOtherMimeTypes(): void
    {
        $this->assertEquals(
            true,
            in_array("text/html", iterator_to_array($this->getTypeManager()->getOtherMimeTypes()))
        );
    }

    public function testGetAllowedVideoMimeTypes(): void
    {
        $tm = $this->getTypeManager(["video/webm"]);

        $this->assertEquals(
            true,
            in_array("video/mp4", iterator_to_array($tm->getAllowedVideoMimeTypes()), true)
        );
        $this->assertEquals(
            false,
            in_array("video/webm", iterator_to_array($tm->getAllowedVideoMimeTypes()), true)
        );
    }

    public function testGetAllowedVideoSuffixes(): void
    {
        $tm = $this->getTypeManager(["video/webm"]);

        $this->assertEquals(
            true,
            in_array("mp4", iterator_to_array($tm->getAllowedVideoSuffixes()), true)
        );
        $this->assertEquals(
            false,
            in_array("webm", iterator_to_array($tm->getAllowedVideoSuffixes()), true)
        );
        $this->assertEquals(
            false,
            in_array("png", iterator_to_array($tm->getAllowedVideoSuffixes()), true)
        );
    }

    public function testIsHtmlAllowed(): void
    {
        $tm = $this->getTypeManager([""]);
        $this->assertEquals(
            true,
            $tm->isHtmlAllowed()
        );
        $tm = $this->getTypeManager(["text/html"]);
        $this->assertEquals(
            false,
            $tm->isHtmlAllowed()
        );
    }
}
