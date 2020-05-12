<?php
function async()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $async_close = $_SERVER['REQUEST_URI'] . '&close_item=true';
    $async_replace_url = $_SERVER['REQUEST_URI'] . '&async_load_replace=true';
    $async_replace_content_load_url = $_SERVER['REQUEST_URI'] . '&async_load_replace_content=true';

    //Creating a Mail Notification Item
    $icon = $f->symbol()->icon()->standard("chtr", "chtr");
    $title = $f->link()->standard("Some Title", "#");
    $item = $f->item()->notification($title, $icon)->withCloseAction($async_close);

    $async_item = $item->withAggregateNotifications([$item->withDescription("Original Item")]);

    if ($_GET['async_load_replace']) {
        $replacement = $async_item
            ->withDescription("The Item has been replaced Async.")
            ->withAggregateNotifications([$item->withDescription("This is a freshly async delivered Item.")
                                          ,$item->withDescription("And a second one")]);
        echo $renderer->renderAsync([$replacement]);
        exit;
    }

    if ($_GET['async_load_replace_content']) {
        $replacement = $async_item
            ->withDescription("The content of the Item has been replaced Async.")
            ->withAggregateNotifications([$item->withDescription("You will never see this")]);
        echo $renderer->renderAsync($replacement);
        exit;
    }

    $async_replace = $async_item
        ->withDescription("The complete Item will be replaced Async")
        ->withAdditionalOnLoadCode(function ($id) use ($async_replace_url) {
            return "
                    var item = il.UI.item.notification.getNotificationItemObject($($id));
                    item.replaceByAsyncItem('$async_replace_url',{});
                ";
        });

    $async_replace_content = $async_item
        ->withDescription("The content of the Item will be replaced Async")
        ->withAdditionalOnLoadCode(function ($id) use ($async_replace_content_load_url) {
            return "
                    var item = il.UI.item.notification.getNotificationItemObject($($id));
                    item.replaceContentByAsyncItemContent('$async_replace_content_load_url',{});
                ";
        });

    return $renderer->render([$async_replace,$async_replace_content]);
}
