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

use ILIAS\FileDelivery\Isolation\IsolationConfig;
use ILIAS\FileDelivery\Setup\IsolationObjective;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Environment;
use ILIAS\Setup\UnachievableException;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class IsolationObjectiveTest extends TestCase
{
    public function testArtifactName(): void
    {
        $this->assertSame('isolation', (new IsolationObjective())->getArtifactName());
    }

    public function testBuildProducesContentDomainWithNullIliasDomain(): void
    {
        // build() has no Environment, so the ILIAS domain (derived from http_path)
        // stays null; it is filled in by buildIn().
        $objective = new IsolationObjective(
            true,
            'https://content.example.org',
        );

        $artifact = $objective->build();
        $this->assertInstanceOf(Artifact::class, $artifact);

        $value = $this->evaluateArtifact($artifact);

        $this->assertSame([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => null,
        ], $value);
    }

    public function testDefaultBuildIsDisabledArtifact(): void
    {
        $value = $this->evaluateArtifact((new IsolationObjective())->build());

        $this->assertSame([
            IsolationConfig::KEY_ACTIVATED => false,
            IsolationConfig::KEY_CONTENT_DOMAIN => null,
            IsolationConfig::KEY_ILIAS_DOMAIN => null,
        ], $value);
    }

    public function testBuildInDerivesIliasDomainFromHttpPath(): void
    {
        // http_path may carry a sub-directory; it is reduced to a bare origin.
        $env = $this->environmentWithHttpPath('https://app.example.org/ilias/index.php');

        $objective = new IsolationObjective(true, 'https://content.example.org');
        $value = $this->evaluateArtifact($objective->buildIn($env));

        $this->assertSame([
            IsolationConfig::KEY_ACTIVATED => true,
            IsolationConfig::KEY_CONTENT_DOMAIN => 'https://content.example.org',
            IsolationConfig::KEY_ILIAS_DOMAIN => 'https://app.example.org',
        ], $value);
    }

    public function testBuildInWithoutIniResourceLeavesIliasDomainNull(): void
    {
        $env = $this->createStub(Environment::class);
        $env->method('getResource')->willReturn(null);

        $objective = new IsolationObjective(true, 'https://content.example.org');
        $value = $this->evaluateArtifact($objective->buildIn($env));

        $this->assertNull($value[IsolationConfig::KEY_ILIAS_DOMAIN]);
        $this->assertTrue($value[IsolationConfig::KEY_ACTIVATED]);
    }

    public function testBuildInThrowsWhenContentHostEqualsIliasHost(): void
    {
        $env = $this->environmentWithHttpPath('https://same.example.org');

        $objective = new IsolationObjective(true, 'https://same.example.org');

        $this->expectException(UnachievableException::class);
        $objective->buildIn($env);
    }

    public function testBuildInDoesNotThrowForSameHostWhenInactive(): void
    {
        $env = $this->environmentWithHttpPath('https://same.example.org');

        $objective = new IsolationObjective(false, 'https://same.example.org');
        $value = $this->evaluateArtifact($objective->buildIn($env));

        $this->assertFalse($value[IsolationConfig::KEY_ACTIVATED]);
    }

    /**
     * Regression for the review concern that a generated PHP artefact could
     * allow code injection via an unescaped string. var_export() must escape
     * the value so the malicious payload survives as a literal string and is
     * never executed.
     */
    public function testMaliciousDomainIsEscapedAndNotExecuted(): void
    {
        $marker = sys_get_temp_dir() . '/isolation_injection_' . getmypid();
        @unlink($marker);

        $payload = "https://evil.example.org' . file_put_contents('" . $marker . "', 'pwned') . '";

        $objective = new IsolationObjective(true, $payload);
        $value = $this->evaluateArtifact($objective->build());

        // payload preserved verbatim, no code executed
        $this->assertSame($payload, $value[IsolationConfig::KEY_CONTENT_DOMAIN]);
        $this->assertFileDoesNotExist($marker);
    }

    private function environmentWithHttpPath(string $http_path): Environment
    {
        $ini = $this->createStub(\ilIniFile::class);
        $ini->method('readVariable')->willReturn($http_path);

        $env = $this->createStub(Environment::class);
        $env->method('getResource')->willReturn($ini);

        return $env;
    }

    /**
     * Execute the serialized artefact exactly the way the runtime loads it
     * (`include $path`) and return the produced array.
     *
     * @return array<string, mixed>
     */
    private function evaluateArtifact(Artifact $artifact): array
    {
        $file = tempnam(sys_get_temp_dir(), 'isolation_artifact_') . '.php';
        file_put_contents($file, $artifact->serialize());

        try {
            $value = include $file;
        } finally {
            @unlink($file);
        }

        $this->assertIsArray($value);
        return $value;
    }
}
