<?php
/**
 * This is a rather extended example on the usage of the Notification Item async
 * functionality provided by src/UI/templates/js/Item/notification.js.
 * See notification.js for a detailed description of the function. Note that
 * we use some il.DemoScope to store some JS for Demo purposes, it contains the
 * following three items:
 *  - DemoScopeRemaining: Integer, Counting how many Items are still there
 *  - DemoScopeAdded: Integer, Counting how many Items have been addedf
 *  - DemoScopeItem: Most importantly, the Notification Object for executing all the
 *      Async logic.
 *
 * The functions of the public interface of interest featured here are:
 *  - getNotificationItemObject($item_or_object_inside_item): Most importantly, returning
 *       the Item Object, for access to all other functions of the interface.
 *
 *  - replaceByAsyncItem(url,send_data): Replaces the item completely with a new retrieved async.
 *  - replaceContentByAsyncItemContent(url,send_data): Only replaces the data around the item
 *       (title, description and such)
 *  - addAsyncAggregate(url,send_data): Adds one aggregate retrieved async (the sub-like items).
 *  - getCounterObjectIfAny(): Gets an instance of the counter for manual manipulations.
 *
 * Of further Interest could be (not featured here):
 *  - getCloseButtonOfItem(): Getting a jQuery instance of the close button, e.g. for attaching
 *     more interactions.

 * @return string
 */
function extended_notifications()
{
    //Set up the gears as always
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Create some bare UI Components Notification Item to be used all over the place
    $icon = $f->symbol()->icon()->standard("chtr", "chtr");
    $title = $f->link()->standard("Some Title", "#");
    $item = $f->item()->notification($title, $icon);

    //We provide 4 async endpoints here
    //Endpoint if element ist closed
    $async_close = $_SERVER['REQUEST_URI'] . '&close_item=true&async_load_replace=false&async_load_replace_content=false&async_add_aggregate=false';
    //Attach this directly to all items to be closable (showing the Close Button)
    $closable_item = $item->withCloseAction($async_close);
    //Endpoint if replace button is pressed
    $async_replace_url = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=true&async_load_replace_content=false&async_add_aggregate=false';
    //Endpoint if for only replacing data of the item (description, title etc.)
    $async_replace_content_load_url = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=false&async_load_replace_content=true&async_add_aggregate=false';
    //Endpoint for adding one single aggreate item
    $async_add_aggregate = $_SERVER['REQUEST_URI'] . '&close_item=false&async_load_replace=false&async_load_replace_content=false&async_add_aggregate=true';

    if ($_GET['close_item'] === "true") {
        //Note that we passe back JS logic here for further processing here
        $js = $f->legacy("")->withOnLoadCode(function ($id) use ($async_replace_content_load_url) {
            return "
                il.DemoScopeRemaining--;
                il.DemoScopeItem.replaceContentByAsyncItemContent('$async_replace_content_load_url',{remaining: il.DemoScopeRemaining,added: il.DemoScopeAdded});
            ";
        });
        echo $renderer->renderAsync($js);
        exit;
    }

    if ($_GET['async_load_replace'] === "true") {
        $remaining = $_GET["remaining"];
        $added = $_GET["added"];

        //We create the amount of aggregates send to us by get and put an according
        //description into the newly create Notification Item
        $items = [];
        for ($i = 1; $i < $added + 1; $i++) {
            $items[] = $closable_item->withDescription("This item is number: " . $i . " of a fix set of 10 entries.");
        }
        $replacement = $item->withDescription("Number of Async non-closed Aggregates: " . $remaining . ", totally created: " . $added)
            ->withAggregateNotifications($items);

        echo $renderer->renderAsync([$replacement]);
        exit;
    }

    if ($_GET['async_load_replace_content'] === "true") {
        $remaining = $_GET["remaining"];
        $added = $_GET["added"];
        $replacement = $item->withDescription("Number of Async non-closed Aggregates: " . $remaining . ", totally created: " . $added);
        echo $renderer->renderAsync([$replacement]);
        exit;
    }

    if ($_GET['async_add_aggregate'] === "true") {
        $remaining = $_GET["remaining"];
        $added = $_GET["added"];

        $new_aggregate = $closable_item->withDescription("The item has been added, Nr: " . $added);

        echo $renderer->renderAsync([$new_aggregate]);
        exit;
    }

    //Button with attached js logic to add one new Notification, note, that
    //we also change the description of the already existing parent Notification
    //Item holding the aggregates.
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

    //Resetting all counts to 0, remove all aggregates
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

    //Set all counts to a fixed value of ten.
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

    /**
     * Important, this is the heart of the example. By creating our Notification Item
     * we attach in additionalOnLoad code the logic to store access to our freshly
     * created Notification Item.
     */
    $async_item = $item
        ->withDescription("This is the original Version after the Page has loaded. Will be replaced completely.")
        ->withAdditionalOnLoadCode(function ($id) {
            return "
                il.DemoScopeAdded = 0;
                il.DemoScopeRemaining = 0;
                il.DemoScopeItem = il.UI.item.notification.getNotificationItemObject($($id));
            ";
        });

    /**
     * Note the work from here on is usually done by the global screen. This is
     * just done to get the example up and running and to give it a more realistic
     * look. See ilias/src/GlobalScreen/Scope/Notification/README.md
     */
    return usuallyDoneByGlobalScreenProbablyIgnore($async_item, $f, $renderer, $add_button, $set_button, $reset_button);
}

function usuallyDoneByGlobalScreenProbablyIgnore($async_item, $f, $renderer, $add_button, $set_button, $reset_button)
{
    //Put the item in some slate.
    $async_slate = $f->mainControls()->slate()->notification("Chat", [$async_item]);


    //Just some candy, to give the whole example a more realistic look.
    $mail_icon = $f->symbol()->icon()->standard("mail", "mail");
    $mail_title = $f->link()->standard("Inbox", "link_to_inbox");
    $mail_notification_item = $f->item()->notification($mail_title, $mail_icon)
                                ->withDescription("You have 23 unread mails in your inbox")
                                ->withProperties(["Time" => "3 days ago"]);
    $mail_slate = $f->mainControls()->slate()->notification("Mail", [$mail_notification_item]);


    //Note
    $notification_glyph = $f->symbol()->glyph()->notification("notification", "notification")
                            ->withCounter($f->counter()->novelty(1));

    $notification_center = $f->mainControls()->slate()
                             ->combined("Notification Center", $notification_glyph)
                             ->withAdditionalEntry($async_slate)
                             ->withAdditionalEntry($mail_slate);

    $css_fix = "<style>.panel-primary .il-maincontrols-metabar{flex-direction: column;} .panel-primary .il-metabar-slates{position: relative;top: 0px;}</style>";
    return $css_fix . $renderer->render([buildMetabarWithNotifications($f, $notification_center),$add_button,$set_button,$reset_button]);
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
