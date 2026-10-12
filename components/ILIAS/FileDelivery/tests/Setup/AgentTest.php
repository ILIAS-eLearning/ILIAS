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
use ILIAS\FileDelivery\Setup\IsolationObjective;
use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class AgentTest extends TestCase
{
    private ?string $artefact = null;

    protected function tearDown(): void
    {
        if ($this->artefact !== null) {
            @unlink($this->artefact);
        }
    }

    private function agent(?string $artefact = null): Agent
    {
        $refinery = new Refinery(new DataFactory(), $this->createStub(Language::class));
        return new Agent($refinery, $artefact);
    }

    private function activatedArtefact(): string
    {
        $this->artefact = tempnam(sys_get_temp_dir(), 'isolation_') . '.php';
        file_put_contents($this->artefact, '<?php return ' . var_export([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://ilias.example.org',
        ], true) . ';');
        return $this->artefact;
    }

    /**
     * Settings the isolation objective of the update would write.
     */
    private function writtenByUpdate(Agent $agent, ?FileDeliverySetupConfig $config): array
    {
        $collection = $agent->getUpdateObjective($config);
        $this->assertInstanceOf(ObjectiveCollection::class, $collection);
        foreach ($collection->getObjectives() as $objective) {
            if ($objective instanceof IsolationObjective) {
                $file = tempnam(sys_get_temp_dir(), 'isolation_artifact_') . '.php';
                file_put_contents($file, $objective->build()->serialize());
                try {
                    return include $file;
                } finally {
                    @unlink($file);
                }
            }
        }
        $this->fail('No isolation objective in the update.');
    }

    public function testUpdateWithoutConfigKeepsCurrentSettings(): void
    {
        $written = $this->writtenByUpdate($this->agent($this->activatedArtefact()), null);

        $this->assertTrue($written[IsolationConfig::KEY_ACTIVATED]);
        $this->assertSame('https://content.example.org', $written[IsolationConfig::KEY_CONTENT_DOMAIN]);
    }

    public function testUpdateWithoutSectionKeepsCurrentSettings(): void
    {
        $agent = $this->agent($this->activatedArtefact());
        $config = $agent->getArrayToConfigTransformation()->transform(null);

        $this->assertFalse($config->isIsolationConfigured());
        $written = $this->writtenByUpdate($agent, $config);
        $this->assertTrue($written[IsolationConfig::KEY_ACTIVATED]);
        $this->assertSame('https://content.example.org', $written[IsolationConfig::KEY_CONTENT_DOMAIN]);
    }

    public function testUpdateWithSectionOverridesCurrentSettings(): void
    {
        $agent = $this->agent($this->activatedArtefact());
        $config = $agent->getArrayToConfigTransformation()->transform(['activated' => false]);

        $this->assertTrue($config->isIsolationConfigured());
        $written = $this->writtenByUpdate($agent, $config);
        $this->assertFalse($written[IsolationConfig::KEY_ACTIVATED]);
        $this->assertNull($written[IsolationConfig::KEY_CONTENT_DOMAIN]);
    }

    public function testUpdateWithoutConfigAndArtefactStaysDeactivated(): void
    {
        $written = $this->writtenByUpdate($this->agent('/does/not/exist.php'), null);

        $this->assertFalse($written[IsolationConfig::KEY_ACTIVATED]);
        $this->assertNull($written[IsolationConfig::KEY_CONTENT_DOMAIN]);
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

    public function testTransformsNullToUnconfiguredConfig(): void
    {
        $config = $this->agent()->getArrayToConfigTransformation()->transform(null);

        $this->assertInstanceOf(FileDeliverySetupConfig::class, $config);
        $this->assertFalse($config->isIsolationConfigured());
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
