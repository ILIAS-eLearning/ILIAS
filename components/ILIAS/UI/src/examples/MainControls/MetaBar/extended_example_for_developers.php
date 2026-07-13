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

namespace ILIAS\UI\examples\MainControls\MetaBar;

use ILIAS\DI\Container;

/**
 * ---
 * description: >
 *   This is a rather extended example on the usage of the Notification Item async
 *   functionality provided by src/UI/templates/js/Item/notification.js.
 *   See notification.js for a detailed description of the function. Note that
 *   we use some il.DemoScope to store some JS for Demo purposes, it contains the
 *   following three items:
 *    - DemoScopeRemaining: Integer, Counting how many Items are still there
 *    - DemoScopeAdded: Integer, Counting how many Items have been addedf
 *    - DemoScopeItem: Most importantly, the Notification Object for executing all the
 *        Async logic.
 *
 *   The functions of the public interface of interest featured here are:
 *    - getNotificationItemObject($item_or_object_inside_item): Most importantly, returning
 *         the Item Object, for access to all other functions of the interface.
 *
 *    - replaceByAsyncItem(url,send_data): Replaces the item completely with a new retrieved async.
 *    - replaceContentByAsyncItemContent(url,send_data): Only replaces the data around the item
 *         (title, description and such)
 *    - addAsyncAggregate(url,send_data): Adds one aggregate retrieved async (the sub-like items).
 *    - getCounterObjectIfAny(): Gets an instance of the counter for manual manipulations.
 *
 *   Of further Interest could be (not featured here):
 *    - getCloseButtonOfItem(): Getting a jQuery instance of the close button, e.g. for attaching
 *      more interactions.
 *
 * expected output: >
 *   ILIAS shows a link "See UI in fullscreen-mode". Clicking the link opens a
 *   standard page with the MetaBar in the header. The Notification Center in the
 *   MetaBar contains the Chat example with async functionality. The page content
 *   provides buttons to add, reset or set chat notifications.
 * ---
 * @return string
 */
function extended_example_for_developers(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = $DIC->http()->request()->getUri()->__toString() . '&extended_metabar_ui=1';
    $txt = $f->legacy()->content('<p>
            The extended MetaBar example opens in fullscreen to showcase the
            Notification Item async functionality in the proper page layout.
            Use the Notification Center (bell icon) in the MetaBar and the
            buttons below to try the async features.
            </p>');

    $page_demo = $f->link()->standard('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $txt,
        $page_demo
    ]);
}

function buildMetabarWithNotifications($f, $notification_center)
{
    $help = $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#');
    $search = $f->button()->bulky($f->symbol()->glyph()->search(), 'Search', '#');
    $user = $f->button()->bulky($f->symbol()->glyph()->user(), 'User', '#');


    $metabar = $f->mainControls()->metabar()
                 ->withAdditionalEntry('search', $search)
                 ->withAdditionalEntry('help', $help)
                 ->withAdditionalEntry('notification', $notification_center)
                 ->withAdditionalEntry('user', $user);

    return $metabar;
}

/**
 * Handle async requests for the notification item. Exits if request was async.
 */
function handleAsyncRequestsIfAny(Container $dic): void
{
    $f = $dic->ui()->factory();
    $renderer = $dic->ui()->renderer();
    $refinery = $dic->refinery();
    $request_wrapper = $dic->http()->wrapper()->query();

    $icon = $f->symbol()->icon()->standard("chtr", "chtr");
    $title = $f->link()->standard("Some Title", "#");
    $item = $f->item()->notification($title, $icon);

    $async_close = $_SERVER['REQUEST_URI'] . '&close_item=true&async_load_replace=false&async_load_replace_content=false&async_add_aggregate=false';
    $closable_item = $item->withCloseAction($async_close);
    $async_replace_url = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=true&async_load_replace_content=false&async_add_aggregate=false';
    $async_replace_content_load_url = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=false&async_load_replace_content=true&async_add_aggregate=false';
    $async_add_aggregate = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=false&async_load_replace_content=false&async_add_aggregate=true';

    if ($request_wrapper->has('close_item') && $request_wrapper->retrieve('close_item', $refinery->kindlyTo()->string()) === "true") {
        $js = $f->legacy()->content("")->withOnLoadCode(function ($id) use ($async_replace_content_load_url) {
            return "
                il.DemoScopeRemaining--;
                il.DemoScopeItem.replaceContentByAsyncItemContent('$async_replace_content_load_url',{remaining: il.DemoScopeRemaining,added: il.DemoScopeAdded});
            ";
        });
        echo $renderer->renderAsync($js);
        exit;
    }

    if ($request_wrapper->has('async_load_replace') && $request_wrapper->retrieve('async_load_replace', $refinery->kindlyTo()->string()) === "true") {
        $remaining = $request_wrapper->retrieve("remaining", $refinery->kindlyTo()->int());
        $added = $request_wrapper->retrieve("added", $refinery->kindlyTo()->int());

        $items = [];
        for ($i = 1; $i < $added + 1; $i++) {
            $items[] = $closable_item->withDescription("This item is number: " . $i . " of a fix set of 10 entries.");
        }
        $replacement = $item->withDescription("Number of Async non-closed Aggregates: " . $remaining . ", totally created: " . $added)
            ->withAggregateNotifications($items);

        echo $renderer->renderAsync([$replacement]);
        exit;
    }

    if ($request_wrapper->has('async_load_replace_content') && $request_wrapper->retrieve('async_load_replace_content', $refinery->kindlyTo()->string()) === "true") {
        $remaining = $request_wrapper->retrieve("remaining", $refinery->kindlyTo()->int());
        $added = $request_wrapper->retrieve("added", $refinery->kindlyTo()->int());
        $replacement = $item->withDescription("Number of Async non-closed Aggregates: " . $remaining . ", totally created: " . $added);
        echo $renderer->renderAsync([$replacement]);
        exit;
    }

    if ($request_wrapper->has('async_add_aggregate') && $request_wrapper->retrieve('async_add_aggregate', $refinery->kindlyTo()->string()) === "true") {
        $added = $request_wrapper->retrieve("added", $refinery->kindlyTo()->int());
        $new_aggregate = $closable_item->withDescription("The item has been added, Nr: " . $added);
        echo $renderer->renderAsync([$new_aggregate]);
        exit;
    }
}

function renderExtendedMetaBarInFullscreenMode(Container $dic): string
{
    $f = $dic->ui()->factory();
    $renderer = $dic->ui()->renderer();

    $async_replace_url = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=true&async_load_replace_content=false&async_add_aggregate=false';
    $async_add_aggregate = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=false&async_load_replace_content=false&async_add_aggregate=true';

    $icon = $f->symbol()->icon()->standard("chtr", "chtr");
    $title = $f->link()->standard("Some Title", "#");
    $item = $f->item()->notification($title, $icon);
    $async_close = $_SERVER['REQUEST_URI'] . '&close_item=true&async_load_replace=false&async_load_replace_content=false&async_add_aggregate=false';
    $closable_item = $item->withCloseAction($async_close);

    $add_button = $f->button()->standard("Add Chat Notification", "#")
        ->withAdditionalOnLoadCode(function ($id) use ($async_replace_url, $async_add_aggregate) {
            return "
                $('#$id').click(function() {
                    il.DemoScopeItem.getCounterObjectIfAny().incrementNoveltyCount(1);
                    il.DemoScopeAdded++;
                    il.DemoScopeRemaining++;
                    il.DemoScopeItem.addAsyncAggregate('$async_add_aggregate',{remaining: il.DemoScopeAdded,added: il.DemoScopeAdded});
                    il.DemoScopeItem.replaceContentByAsyncItemContent('$async_replace_url',{remaining: il.DemoScopeRemaining,added: il.DemoScopeAdded});
                });";
        });

    $reset_button = $f->button()->standard("Reset Chat", "#")
        ->withAdditionalOnLoadCode(function ($id) use ($async_replace_url) {
            return "
                $('#$id').click(function() {
                    il.DemoScopeItem.getCounterObjectIfAny().decrementNoveltyCount(il.DemoScopeRemaining);
                    il.DemoScopeAdded = 0;
                    il.DemoScopeRemaining = 0;
                    il.DemoScopeItem.replaceByAsyncItem('$async_replace_url',{remaining: il.DemoScopeAdded,added: il.DemoScopeAdded});
                });";
        });

    $set_button = $f->button()->standard("Set to 10 chat entries", "#")
        ->withAdditionalOnLoadCode(function ($id) use ($async_replace_url) {
            return "
                $('#$id').click(function() {
                    il.DemoScopeItem.getCounterObjectIfAny().decrementNoveltyCount(il.DemoScopeRemaining);
                    il.DemoScopeItem.getCounterObjectIfAny().incrementNoveltyCount(10);
                    il.DemoScopeAdded = 10;
                    il.DemoScopeRemaining = 10;
                    il.DemoScopeItem.replaceByAsyncItem('$async_replace_url',{remaining: il.DemoScopeAdded,added: il.DemoScopeAdded});
                });";
        });

    $async_item = $item
        ->withDescription("This is the original Version after the Page has loaded. Will be replaced completely.")
        ->withAdditionalOnLoadCode(function ($id) {
            return "
                il.DemoScopeAdded = 0;
                il.DemoScopeRemaining = 0;
                il.DemoScopeItem = il.UI.item.notification.getNotificationItemObject($($id));
            ";
        });

    $async_slate = $f->mainControls()->slate()->notification("Chat", [$async_item]);

    $mail_icon = $f->symbol()->icon()->standard("mail", "mail");
    $mail_title = $f->link()->standard("Inbox", "link_to_inbox");
    $mail_notification_item = $f->item()->notification($mail_title, $mail_icon)
        ->withDescription("You have 23 unread mails in your inbox")
        ->withProperties(["Time" => "3 days ago"]);
    $mail_slate = $f->mainControls()->slate()->notification("Mail", [$mail_notification_item]);


    $notification_glyph = $f->symbol()->glyph()->notification()
        ->withCounter($f->counter()->novelty(1));

    $notification_center = $f->mainControls()->slate()
        ->combined("Notification Center", $notification_glyph)
        ->withAdditionalEntry($async_slate)
        ->withAdditionalEntry($mail_slate);

    $metabar = buildMetabarWithNotifications($f, $notification_center);

    $logo = $f->image()->responsive("assets/images/logo/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("assets/images/logo/HeaderIconResponsive.svg", "ILIAS");
    $breadcrumbs = $f->breadcrumbs([]);
    $mainbar = $f->mainControls()->mainBar();
    $footer = $f->mainControls()->footer()->withAdditionalText('Footer');
    $tc = $dic->ui()->factory()->toast()->container();

    $content = [
        $f->panel()->standard(
            'Notification Item Async Demo',
            $f->legacy()->content(
                "Use the Notification Center (bell icon) in the MetaBar above, then try these buttons:<br /><br />"
                . $renderer->render([$add_button, $set_button, $reset_button])
            )
        ),
    ];

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $responsive_logo,
        "./assets/images/logo/favicon.ico",
        $tc,
        $footer,
        'UI MetaBar Extended Demo',
        'ILIAS',
        'MetaBar Async Demo'
    )->withUIDemo(true);

    return $renderer->render($page);
}

global $DIC;
$request_wrapper = $DIC->http()->wrapper()->query();
$refinery = $DIC->refinery();

handleAsyncRequestsIfAny($DIC);

if ($request_wrapper->has('extended_metabar_ui')
    && $request_wrapper->retrieve('extended_metabar_ui', $refinery->kindlyTo()->int()) === 1
) {
    \ilInitialisation::initILIAS();
    echo renderExtendedMetaBarInFullscreenMode($DIC);
    exit();
}
