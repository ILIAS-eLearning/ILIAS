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

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationPluginProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuPluginProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarPluginProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationPluginProvider;
use ILIAS\GlobalScreen\Scope\Toast\Provider\AbstractToastPluginProvider;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolPluginProvider;

class PluginProviderCollection implements ProviderCollection
{
    private ?AbstractModificationPluginProvider $modification_provider = null;
    private ?AbstractStaticMainMenuPluginProvider $main_bar_provider = null;
    private ?AbstractDynamicToolPluginProvider $tool_provider = null;
    private ?AbstractStaticMetaBarPluginProvider $meta_bar_provider = null;
    private ?AbstractNotificationPluginProvider $notification_provider = null;
    private ?AbstractToastPluginProvider $toast_provider = null;

    public function getModificationProvider(): ?AbstractModificationPluginProvider
    {
        return $this->modification_provider;
    }

    public function setModificationProvider(AbstractModificationPluginProvider $modification_provider): self
    {
        $this->modification_provider = $modification_provider;

        return $this;
    }

    public function getMainBarProvider(): ?AbstractStaticMainMenuPluginProvider
    {
        return $this->main_bar_provider;
    }

    public function setMainBarProvider(AbstractStaticMainMenuPluginProvider $static_mai_menu_provider): self
    {
        $this->main_bar_provider = $static_mai_menu_provider;

        return $this;
    }

    public function getToolProvider(): ?AbstractDynamicToolPluginProvider
    {
        return $this->tool_provider;
    }

    public function setToolProvider(AbstractDynamicToolPluginProvider $dynamic_tool_provider): self
    {
        $this->tool_provider = $dynamic_tool_provider;

        return $this;
    }

    public function getMetaBarProvider(): ?AbstractStaticMetaBarPluginProvider
    {
        return $this->meta_bar_provider;
    }

    public function setMetaBarProvider(AbstractStaticMetaBarPluginProvider $static_meta_bar_provider): self
    {
        $this->meta_bar_provider = $static_meta_bar_provider;

        return $this;
    }

    public function getNotificationProvider(): ?AbstractNotificationPluginProvider
    {
        return $this->notification_provider;
    }

    public function setNotificationProvider(AbstractNotificationPluginProvider $notification_provider): self
    {
        $this->notification_provider = $notification_provider;

        return $this;
    }

    public function getToastProvider(): ?AbstractToastPluginProvider
    {
        return $this->toast_provider;
    }

    public function setToastProvider(AbstractToastPluginProvider $toast_provider): self
    {
        $this->toast_provider = $toast_provider;

        return $this;
    }
}
