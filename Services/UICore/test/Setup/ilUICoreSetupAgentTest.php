<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class UICoreSetupAgentTest
 * @author Marvin Beym <mbeym@databay.de>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilUICoreSetupAgentTest extends TestCase
{
    private ilUICoreSetupAgent $agent;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->agent = new ilUICoreSetupAgent();
    }

    public function testAgentsNamedObjectives(): void
    {
        $this->assertArrayHasKey(
            'buildIlCtrlArtifacts',
            $this->agent->getNamedObjectives()
        );

        $this->assertArrayHasKey(
            'updateIlCtrlDatabase',
            $this->agent->getNamedObjectives()
        );
    }
}
