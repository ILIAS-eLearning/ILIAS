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

use PHPUnit\Framework\TestCase;
use ILIAS\Certificate\CertificateEditorMode;

class CertificateEditorModeTest extends TestCase
{
    public function testReadAllowsPreviewAndExportAndRejectsMutations(): void
    {
        $mode = CertificateEditorMode::Read;

        $this->assertFalse($mode->canWrite());
        $this->assertTrue($mode->allowsCommand('certificateEditor'));
        $this->assertTrue($mode->allowsCommand('certificatePreview'));
        $this->assertTrue($mode->allowsCommand('certificateExportFO'));
        $this->assertFalse($mode->allowsCommand('certificateSave'));
        $this->assertFalse($mode->allowsCommand('certificateDelete'));
        $this->assertFalse($mode->allowsCommand('certificateDeleteConfirm'));
        $this->assertFalse($mode->allowsCommand('certificateImport'));
        $this->assertFalse($mode->allowsCommand('certificateUpload'));
    }

    public function testWriteAllowsMutatingCommands(): void
    {
        $mode = CertificateEditorMode::Write;

        $this->assertTrue($mode->canWrite());
        $this->assertTrue($mode->allowsCommand('certificateSave'));
        $this->assertTrue($mode->allowsCommand('certificateDelete'));
        $this->assertTrue($mode->allowsCommand('certificateDeleteConfirm'));
        $this->assertTrue($mode->allowsCommand('certificateImport'));
        $this->assertTrue($mode->allowsCommand('certificateUpload'));
    }
}
