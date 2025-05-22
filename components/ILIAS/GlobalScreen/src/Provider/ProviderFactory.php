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

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\DynamicToolProvider;
use ILIAS\GlobalScreen\Scope\Footer\Provider\StaticFooterProvider;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface ProviderFactory
{
    /**
     * @return ModificationProvider[]
     */
    public function getModificationProvider(): array;

    /**
     * @return StaticMainMenuProvider[]
     */
    public function getMainBarProvider(): array;

    /**
     * @return StaticFooterProvider[]
     */
    public function getFooterProvider(): array;

    /**
     * @return ItemInformation
     */
    public function getMainBarItemInformation(): ItemInformation;

    public function getFooterItemInformation(): \ILIAS\GlobalScreen\Scope\Footer\Collector\Information\ItemInformation;

    /**
     * @return DynamicToolProvider[]
     */
    public function getToolProvider(): array;

    /**
     * @return StaticMetaBarProvider[]
     */
    public function getMetaBarProvider(): array;

    /**
     * @return NotificationProvider[]
     */
    public function getNotificationsProvider(): array;

    /**
     * @param string $class_name
     * @return Provider
     */
    public function getProviderByClassName(string $class_name): Provider;

    /**
     * @param string $class_name
     * @return bool
     */
    public function isInstanceCreationPossible(string $class_name): bool;

    /**
     * @param string $class_name
     * @return bool
     */
    public function isRegistered(string $class_name): bool;
}
