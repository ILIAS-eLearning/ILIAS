<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;

class ilServicesFileServicesTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;
    
    public function testSanitizing() : void
    {
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getWhiteListedSuffixes')
                 ->willReturn(['pdf', 'jpg']);
        
        $sanitizer = new ilFileServicesFilenameSanitizer($settings);
        $this->assertTrue($sanitizer->isClean('/lib/test.pdf'));
        $this->assertFalse($sanitizer->isClean('/lib/test.xml'));
        $this->assertEquals('/lib/testxml.sec', $sanitizer->sanitize('/lib/test.xml'));
    }
    
    public function testBlacklistedUpload() : void
    {
        $rbac = $this->createMock(ilRbacSystem::class);
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['pdf']);
        $stream = $this->createMock(FileStream::class);
        $meta = new Metadata('filename.pdf', 42, 'application/pdf');
        
        $processor = new ilFileServicesPreProcessor(
            $rbac,
            $settings,
            'the reason',
            0
        );
        // will be rejected since blacklistet
        $rbac->expects($this->once())->method('checkAccess')->willReturn(false);
        $status = $processor->process($stream, $meta);
        $this->assertEquals(ProcessingStatus::REJECTED, $status->getCode());
    }
    
    public function testBlacklistedUploadWithPermission() : void
    {
        $rbac = $this->createMock(ilRbacSystem::class);
        $settings = $this->createMock(ilFileServicesSettings::class);
        $settings->expects($this->once())
                 ->method('getBlackListedSuffixes')
                 ->willReturn(['pdf']);
        $stream = $this->createMock(FileStream::class);
        $meta = new Metadata('filename.pdf', 42, 'application/pdf');
        
        $processor = new ilFileServicesPreProcessor(
            $rbac,
            $settings,
            'the reason',
            0
        );
        // is ok since user has permission
        $rbac->expects($this->once())->method('checkAccess')->willReturn(true);
        $status = $processor->process($stream, $meta);
        $this->assertEquals(ProcessingStatus::OK, $status->getCode());
    }
}
