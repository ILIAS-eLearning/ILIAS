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

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup\CLI\IOWrapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class IOWrapperTest extends TestCase
{
    private BufferedOutput $out;
    private IOWrapper $io;

    protected function setUp(): void
    {
        $this->out = new BufferedOutput();
        $this->io = new IOWrapper(new ArrayInput([]), $this->out);
    }

    public function testOutputWithinObjectiveIsMarkedInProgress(): void
    {
        $this->io->startObjective('Some objective', true);
        $this->io->inform('Some information');
        $this->io->finishedLastObjective();

        $output = $this->out->fetch();
        $this->assertStringContainsString('[in progress]', $output);
        $this->assertSame(2, substr_count($output, 'Some objective...'));
    }

    public function testOutputAfterFinishedObjectiveIsNotMarkedInProgress(): void
    {
        $this->io->startObjective('Some objective', true);
        $this->io->finishedLastObjective();
        $this->io->inform('Some information');

        $output = $this->out->fetch();
        $this->assertStringNotContainsString('[in progress]', $output);
        $this->assertStringContainsString('Some information', $output);
    }

    public function testOutputAfterFailedObjectiveIsNotMarkedInProgress(): void
    {
        $this->io->startObjective('Some objective', true);
        $this->io->failedLastObjective();
        $this->io->inform('Some information');

        $this->assertStringNotContainsString('[in progress]', $this->out->fetch());
    }

    public function testOutputWithinNextObjectiveIsMarkedInProgressAgain(): void
    {
        $this->io->startObjective('First objective', true);
        $this->io->finishedLastObjective();
        $this->io->startObjective('Second objective', true);
        $this->io->inform('Some information');
        $this->io->finishedLastObjective();

        $this->assertSame(1, substr_count($this->out->fetch(), '[in progress]'));
    }

    public function testOutputBeforeFirstObjectiveIsNotMarkedInProgress(): void
    {
        $this->out->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->io->inform('Some information');

        $this->assertStringNotContainsString('[in progress]', $this->out->fetch());
    }
}
