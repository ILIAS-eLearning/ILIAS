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

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\NullItemInformation;

/**
 * Class NullProviderFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullProviderFactory implements ProviderFactory
{
    /**
     * @inheritDoc
     */
    public function getModificationProvider() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMainBarProvider() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMainBarItemInformation() : ItemInformation
    {
        return new  NullItemInformation();
    }

    /**
     * @inheritDoc
     */
    public function getToolProvider() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getMetaBarProvider() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getNotificationsProvider() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getProviderByClassName(string $class_name) : Provider
    {
        return new NullProvider();
    }

    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isRegistered(string $class_name) : bool
    {
        return false;
    }
}
