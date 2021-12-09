<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Implementation\Component\Button\Close;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Notification) {
            return $this->renderNotification($component, $default_renderer);
        } elseif ($component instanceof Component\Item\Group) {
            return $this->renderGroup($component, $default_renderer);
        } elseif ($component instanceof Component\Item\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }
        return "";
    }

    protected function renderGroup(Component\Item\Group $component, RendererInterface $default_renderer) : string
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

    protected function renderStandard(Component\Item\Item $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.item_standard.html", true, true);

        $this->renderTitle($component, $default_renderer, $tpl);
        $this->renderDescription($component, $tpl);
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
        $progress = $component->getProgress();
        if ($lead != null) {
            if (is_string($lead)) {
                if ($progress != null) {
                    $tpl->setCurrentBlock("lead_text_with_progress");
                    $tpl->touchBlock("item_with_lead_and_progress");
                } else {
                    $tpl->setCurrentBlock("lead_text");
                    $tpl->touchBlock("item_with_lead_only");
                }
                $tpl->setVariable("LEAD_TEXT", $lead);
                $tpl->parseCurrentBlock();
            }
            if ($lead instanceof Component\Image\Image) {
                if ($progress != null) {
                    $tpl->setCurrentBlock("lead_image_with_progress");
                    $tpl->touchBlock("item_with_lead_and_progress");
                } else {
                    $tpl->setCurrentBlock("lead_image");
                    $tpl->touchBlock("item_with_lead_only");
                }
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
            if ($progress != null && $lead instanceof Component\Symbol\Icon\Icon) {
                $tpl->setCurrentBlock("progress_end_with_lead_icon");
                $tpl->setVariable("PROGRESS", $default_renderer->render($progress));
                $tpl->parseCurrentBlock();
            } elseif ($progress != null) {
                $tpl->setCurrentBlock("progress_end");
                $tpl->setVariable("PROGRESS", $default_renderer->render($progress));
                $tpl->parseCurrentBlock();
            } else {
                $tpl->touchBlock("lead_end");
            }
        } elseif ($progress != null) {
            $tpl->touchBlock("item_with_progress_only");
            $tpl->setCurrentBlock("progress_end");
            $tpl->setVariable("PROGRESS", $default_renderer->render($progress));
            $tpl->parseCurrentBlock();
        }

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }

        return $tpl->get();
    }

    protected function renderNotification(Notification $component, RendererInterface $default_renderer) : string
    {
        $tpl = $this->getTemplate("tpl.item_notification.html", true, true);
        $this->renderTitle($component, $default_renderer, $tpl);
        $this->renderDescription($component, $tpl);
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

        // aggregate notification
        $title = $this->getUIFactory()->button()->bulky($this->getUIFactory()->symbol()->glyph()->back(), $this->txt("back"), "");
        $aggregates_html = $default_renderer->render(
            $this->getUIFactory()->mainControls()->slate()->notification($default_renderer->render($title), $component->getAggregateNotifications())
        );

        $toggleable = true;
        if (!empty($component->getAggregateNotifications())) {
            $toggleable = false;
        }

        /**
         * @var $component Notification
         */
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
            /**
             * @var $close_action Close
             */
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
        Template $tpl
    ) : void {
        $title = $component->getTitle();
        if ($title instanceof Shy || $title instanceof Link) {
            $title = $default_renderer->render($title);
        }
        $tpl->setVariable("TITLE", $title);
    }

    protected function renderDescription(
        Component\Item\Item $component,
        Template $tpl
    ) : void {
        // description
        $desc = $component->getDescription();
        if (!is_null($desc) && trim($desc) != "") {
            $tpl->setCurrentBlock("desc");
            $tpl->setVariable("DESC", $desc);
            $tpl->parseCurrentBlock();
        }
    }

    protected function renderProperties(
        Component\Item\Item $component,
        RendererInterface $default_renderer,
        Template $tpl
    ) : void {
        // properties
        $props = $component->getProperties();
        if (count($props) > 0) {
            $cnt = 0;
            foreach ($props as $name => $value) {
                if ($value instanceof Shy) {
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
    public function registerResources(ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Item/dist/notification.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Item\Standard::class,
            Component\Item\Group::class,
            Component\Item\Notification::class
        ];
    }
}
