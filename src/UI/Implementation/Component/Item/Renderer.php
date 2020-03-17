<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Item\Notification) {
            return $this->renderNotification($component, $default_renderer);
        } elseif ($component instanceof Component\Item\Group) {
            return $this->renderGroup($component, $default_renderer);
        } elseif ($component instanceof Component\Item\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
    }

    protected function renderGroup(Component\Item\Group $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.group.html", true, true);
        $title = $component->getTitle();
        $items = $component->getItems();

        // items
        foreach ($items as $item) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }

        if ($title != "") {
            $tpl->setCurrentBlock("title");
            $tpl->setVariable("TITLE", $title);
            $tpl->parseCurrentBlock();
        }

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        } else {
            $tpl->setVariable("ACTIONS", "");
        }



        return $tpl->get();
    }

    protected function renderStandard(Component\Item\Item $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.item_standard.html", true, true);

        $this->renderTitle($component, $default_renderer, $tpl);
        $this->renderDescription($component, $default_renderer, $tpl);
        $this->renderProperties($component, $default_renderer, $tpl);
        // color
        $color = $component->getColor();
        if ($color !== null) {
            $tpl->setCurrentBlock("color");
            $tpl->setVariable("COLOR", $color->asHex());
            $tpl->parseCurrentBlock();
        }

        // lead
        $lead = $component->getLead();
        if ($lead != null) {
            if (is_string($lead)) {
                $tpl->setCurrentBlock("lead_text");
                $tpl->setVariable("LEAD_TEXT", $lead);
                $tpl->parseCurrentBlock();
            }
            if ($lead instanceof Component\Image\Image) {
                $tpl->setCurrentBlock("lead_image");
                $tpl->setVariable("LEAD_IMAGE", $default_renderer->render($lead));
                $tpl->parseCurrentBlock();
            }
            if ($lead instanceof Component\Symbol\Icon\Icon) {
                $tpl->setCurrentBlock("lead_icon");
                $tpl->setVariable("LEAD_ICON", $default_renderer->render($lead));
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("lead_start_icon");
                $tpl->parseCurrentBlock();
            } else {
                $tpl->setCurrentBlock("lead_start");
                $tpl->parseCurrentBlock();
            }
            $tpl->touchBlock("lead_end");
        }
        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }
        return $tpl->get();
    }

    protected function renderNotification(Component\Item\Notification $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getTemplate("tpl.item_notification.html", true, true);
        $this->renderTitle($component, $default_renderer, $tpl);
        $this->renderDescription($component, $default_renderer, $tpl);
        $this->renderProperties($component, $default_renderer, $tpl);
        $tpl->setVariable("LEAD_ICON", $default_renderer->render($component->getLeadIcon()));

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }

        // additional content
        if ($component->getAdditionalContent()) {
            $tpl->setCurrentBlock("additional_content");
            $tpl->setVariable("ADDITIONAL_CONTENT", $default_renderer->render($component->getAdditionalContent()));
            $tpl->parseCurrentBlock();
        }

        $aggregates_html = "";

        // aggregate notification
        $title = $this->getUIFactory()->button()->bulky($this->getUIFactory()->symbol()->glyph()->back(), $this->txt("back"), "");
        $aggregates_html = $default_renderer->render(
            $this->getUIFactory()->mainControls()->slate()->notification($default_renderer->render($title), $component->getAggregateNotifications())
        );

        $toggleable = true;
        if (!empty($component->getAggregateNotifications())) {
            $toggleable = false;
        }

        $component = $component->withAdditionalOnLoadCode(
            function ($id) use ($toggleable) {
                return "il.UI.item.notification.getNotificationItemObject($($id)).registerAggregates($toggleable);";
            }
        );

        //Bind id
        $item_id = $this->bindJavaScript($component);

        $tpl->setCurrentBlock("aggregate_notifications");
        $tpl->setVariable("AGGREGATES", $aggregates_html);
        $tpl->setVariable("PARENT_ID", $item_id);
        $tpl->parseCurrentBlock();

        // close action
        if ($component->getCloseAction()) {
            $url = $component->getCloseAction();
            $close_action = $this->getUIFactory()->button()->close()->withAdditionalOnLoadCode(
                function ($id) use ($url, $item_id) {
                    return "il.UI.item.notification.getNotificationItemObject($($id)).registerCloseAction('$url',1);";
                }
            );
            $tpl->setVariable("CLOSE_ACTION", $default_renderer->render($close_action));
        }

        $tpl->setCurrentBlock("id");
        $tpl->setVariable('ID', $item_id);
        $tpl->parseCurrentBlock();


        return $tpl->get();
    }

    protected function renderTitle(
        Component\Item\Item $component,
        RendererInterface $default_renderer,
        \ILIAS\UI\Implementation\Render\Template $tpl
    ) {
        $title = $component->getTitle();
        if ($title instanceof \ILIAS\UI\Component\Button\Shy || $title instanceof \ILIAS\UI\Component\Link\Standard) {
            $title = $default_renderer->render($title);
        }
        $tpl->setVariable("TITLE", $title);
    }

    protected function renderDescription(
        Component\Item\Item $component,
        RendererInterface $default_renderer,
        \ILIAS\UI\Implementation\Render\Template $tpl
    ) {
        // description
        $desc = $component->getDescription();
        if (trim($desc) != "") {
            $tpl->setCurrentBlock("desc");
            $tpl->setVariable("DESC", $desc);
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderProperties(
        Component\Item\Item $component,
        RendererInterface $default_renderer,
        \ILIAS\UI\Implementation\Render\Template $tpl
    ) {
        // properties
        $props = $component->getProperties();
        if (count($props) > 0) {
            $cnt = 0;
            foreach ($props as $name => $value) {
                if ($value instanceof \ILIAS\UI\Component\Button\Shy) {
                    $value = $default_renderer->render($value);
                }
                $cnt++;
                if ($cnt % 2 == 1) {
                    $tpl->setCurrentBlock("property_row");
                    $tpl->setVariable("PROP_NAME_A", $name);
                    $tpl->setVariable("PROP_VAL_A", $value);
                } else {
                    $tpl->setVariable("PROP_NAME_B", $name);
                    $tpl->setVariable("PROP_VAL_B", $value);
                    $tpl->parseCurrentBlock();
                }
            }
            if ($cnt % 2 == 1) {
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("properties");
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Item/notification.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Component\Item\Standard::class,
            Component\Item\Group::class,
            Component\Item\Notification::class
        );
    }
}
