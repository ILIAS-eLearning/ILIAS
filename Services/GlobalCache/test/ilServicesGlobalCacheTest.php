<?php

declare(strict_types=1);

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

class ilServicesGlobalCacheTest extends TestCase
{
    /**
     * @return ilGlobalCacheSettings
     */
    private function getSettings(): ilGlobalCacheSettings
    {
        $settings = new ilGlobalCacheSettings();
        $settings->setActive(true);
        $settings->setActivatedComponents(['test']);
        $settings->setService(ilGlobalCache::TYPE_STATIC);
        return $settings;
    }

    public function testService(): void
    {
        $settings = $this->getSettings();
        ilGlobalCache::setup($settings);

        $cache = ilGlobalCache::getInstance('test');
        $this->assertTrue($cache->isActive());
        $this->assertEquals('test', $cache->getComponent());
        $this->assertEquals(0, $cache->getServiceType());

        $cache = ilGlobalCache::getInstance('test_2');
        $this->assertFalse($cache->isActive());
        $this->assertEquals('test_2', $cache->getComponent());
        $this->assertEquals(0, $cache->getServiceType());
    }

    public function testValues(): void
    {
        $settings = $this->getSettings();
        ilGlobalCache::setup($settings);
        $cache = ilGlobalCache::getInstance('test');

        $this->assertFalse($cache->isValid('test_key'));
        $cache->set('test_key', 'value');
        $this->assertTrue($cache->isValid('test_key'));
        $this->assertEquals('value', $cache->get('test_key'));
    }
}
