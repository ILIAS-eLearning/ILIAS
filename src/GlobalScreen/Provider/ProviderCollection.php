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

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationPluginProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;

/**
 * Class PluginProviderCollection
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderCollection
{
    /**
     * @return AbstractModificationPluginProvider
     */
    public function getModificationProvider() : ?AbstractModificationPluginProvider;

    /**
     * @return AbstractStaticMainMenuPluginProvider
     */
    public function getMainBarProvider() : ?AbstractStaticMainMenuPluginProvider;

    /**
     * @return AbstractDynamicToolPluginProvider
     */
    public function getToolProvider() : ?AbstractDynamicToolPluginProvider;

    /**
     * @return AbstractStaticMetaBarPluginProvider
     */
    public function getMetaBarProvider() : ?AbstractStaticMetaBarPluginProvider;

    /**
     * @return AbstractNotificationPluginProvider
     */
    public function getNotificationProvider() : ?AbstractNotificationPluginProvider;
}
