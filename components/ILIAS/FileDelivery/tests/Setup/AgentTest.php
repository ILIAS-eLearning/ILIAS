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

namespace ILIAS\Tests\FileDelivery\Setup;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\FileDelivery\Setup\Agent;
use ILIAS\FileDelivery\Setup\FileDeliverySetupConfig;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class AgentTest extends TestCase
{
    private function agent(): Agent
    {
        $refinery = new Refinery(new DataFactory(), $this->createStub(Language::class));
        return new Agent($refinery);
    }

    public function testHasConfig(): void
    {
        $this->assertTrue($this->agent()->hasConfig());
    }

    public function testAgentNameIsContentIsolation(): void
    {
        // the agent name is the top-level key for this agent in config.json
        $this->assertSame('content_isolation', $this->agent()->getAgentName());
    }

    public function testTransformsActivatedConfig(): void
    {
        $config = $this->agent()->getArrayToConfigTransformation()->transform([
            'activated' => true,
            'content_domain' => 'https://content.example.org',
        ]);

        $this->assertInstanceOf(FileDeliverySetupConfig::class, $config);
        $this->assertTrue($config->isIsolationActivated());
        $this->assertSame('https://content.example.org', $config->getIsolationContentDomain());
    }

    public function testTransformsNullToDisabledConfig(): void
    {
        $config = $this->agent()->getArrayToConfigTransformation()->transform(null);

        $this->assertInstanceOf(FileDeliverySetupConfig::class, $config);
        $this->assertFalse($config->isIsolationActivated());
        $this->assertNull($config->getIsolationContentDomain());
    }

    public function testTransformsEmptyArrayToDisabledConfig(): void
    {
        $config = $this->agent()->getArrayToConfigTransformation()->transform([]);

        $this->assertFalse($config->isIsolationActivated());
    }

    public function testEmptyStringDomainTreatedAsNull(): void
    {
        $config = $this->agent()->getArrayToConfigTransformation()->transform([
            'activated' => false,
            'content_domain' => '',
        ]);

        $this->assertFalse($config->isIsolationActivated());
        $this->assertNull($config->getIsolationContentDomain());
    }

    public function testActivatedWithoutDomainThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->agent()->getArrayToConfigTransformation()->transform([
            'activated' => true,
        ]);
    }
}
