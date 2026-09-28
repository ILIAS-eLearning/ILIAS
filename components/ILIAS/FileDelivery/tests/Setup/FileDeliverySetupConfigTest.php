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

use ILIAS\FileDelivery\Setup\FileDeliverySetupConfig;
use ILIAS\Setup\Config;
use PHPUnit\Framework\TestCase;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class FileDeliverySetupConfigTest extends TestCase
{
    public function testDefaultIsDisabled(): void
    {
        $config = new FileDeliverySetupConfig();

        $this->assertInstanceOf(Config::class, $config);
        $this->assertFalse($config->isIsolationActivated());
        $this->assertNull($config->getIsolationContentDomain());
    }

    public function testActivatedWithContentDomain(): void
    {
        $config = new FileDeliverySetupConfig(
            true,
            'https://content.example.org',
        );

        $this->assertTrue($config->isIsolationActivated());
        $this->assertSame('https://content.example.org', $config->getIsolationContentDomain());
    }

    public function testInactiveWithNullDomainIsAllowed(): void
    {
        $config = new FileDeliverySetupConfig(false);

        $this->assertFalse($config->isIsolationActivated());
    }

    public function testActivatedWithoutContentDomainThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FileDeliverySetupConfig(true, null);
    }

    public function testActivatedWithoutAnyDomainThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FileDeliverySetupConfig(true);
    }

    public function testStoresNormalizedBareOrigin(): void
    {
        $config = new FileDeliverySetupConfig(true, 'content.example.org');

        $this->assertSame('https://content.example.org', $config->getIsolationContentDomain());
    }

    public function testActivatedWithContentPathThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FileDeliverySetupConfig(true, 'https://content.example.org/assets');
    }

    public function testActivatedWithNonHttpSchemeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FileDeliverySetupConfig(true, 'ftp://content.example.org');
    }

    public function testInactiveDoesNotValidateAndDropsUnusableDomain(): void
    {
        $config = new FileDeliverySetupConfig(false, 'https://content.example.org/path');

        $this->assertFalse($config->isIsolationActivated());
        $this->assertNull($config->getIsolationContentDomain());
    }
}
