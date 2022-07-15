<?php declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

class ilServicesPreviewTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup;
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected ilDBInterface $db_mock;
    
    protected function setUp() : void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();
        $this->db_mock = $DIC['ilDB'] = $this->createMock(ilDBInterface::class);
    }

    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }
    
    /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
    public function testRendererFactory() : void
    {
        // TODO eactivate Tests agian
        $this->markTestSkipped('Currently the implementation cant be tested');
        return;
        
        $factory = new ilRendererFactory();
        $preview = new ilPreview(0, 'file');
    
        $files_backup = $_FILES;
        
        $_FILES['file']['name'] = 'test.jpg';
        $this->assertInstanceOf(ilImageMagickRenderer::class, $factory->getRenderer($preview));
        
        $_FILES['file']['name'] = 'test.tiff';
        $this->assertInstanceOf(ilImageMagickRenderer::class, $factory->getRenderer($preview));
    
        $_FILES['file']['name'] = 'test.pdf';
        $this->assertInstanceOf(ilGhostscriptRenderer::class, $factory->getRenderer($preview));
        
        $_FILES['file']['name'] = 'test.eps';
        $this->assertInstanceOf(ilGhostscriptRenderer::class, $factory->getRenderer($preview));
    
        $_FILES['file']['name'] = 'test.mp4';
        $this->assertNull($factory->getRenderer($preview));
    
        $_FILES = $files_backup;
    }
}
