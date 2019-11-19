<?php declare(strict_types=1);

namespace ILIAS\OnScreenChat\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class OnScreenChatNotificationProvider
 *
 * Serving as starting template for setting up Notifications for the Chat
 *
 * @author Timon Amstutz
 */
class OnScreenChatNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        $factory = $this->globalScreen()->notifications()->factory();

        //Just some endpoint, to get more chat items generated from. Note that
        //we now use GET for the request, since POST is semantically problematic
        $async_url = $this->dic->ctrl()->getLinkTargetByClass('ilOnScreenChatGUI','getNewAsyncItem');


         // If I get this right, there is no way to list the number of chat notifications
         // already on server side. If so, you could just create an empty item for the moment.
        $chat_icon = $this->dic->ui()->factory()->symbol()->icon()->standard("chtr","conversations");
        $chat_title = $this->dic->ui()->factory()->link()->standard("Conversations", '#');
        $chat_notification_item = $this->dic->ui()->factory()->item()->notification($chat_title,$chat_icon)
            ->withDescription("No Conversations available")
            ->withAdditionalOnLoadCode(function($id) use ($async_url){
                //Note this just some random content to show case the basic pattern you might use
                //we add one after 5 seconds
                return "
                il.ChatScopeNotificationItem = il.UI.item.notification.getNotificationItemObject($($id));
                window.setTimeout(function(){
                    il.ChatScopeNotificationItem.getCounterObjectIfAny().incrementNoveltyCount(1);
                    il.ChatScopeNotificationItem.replaceByAsyncItem('$async_url',
                       {name: 'Demo User', message: 'Message that has been sent. Nr: '}
                    );
                }, 5000);
                

            ";
            });

        /**
         * Put the chat notifications into a group, that GS will transform into a Notification Slate
         * Note that this item is probably not closable (if I understand right, so do not provide a close callback here).
         *
         * There is really no news here, so set the new Amount to 0. If the amount is known already on server side,
         * it would be good to set them here, so now flickering appears by resetting the counter async.
         */
        $group = $factory->standardGroup($id('chat_bucket_group'))->withTitle('Chat')
            ->addNotification($factory->standard($id('chat_bucket'))->withNotificationItem($chat_notification_item)
                ->withNewAmount(0)
            );

        return [
            $group,
        ];
    }

    /**
     * Delivers async a new item defined by the data sent through GET
     */
    public function getAsyncItem(){
        $name = $_GET["name"];
        $message = $_GET["message"];

        //Define some Endpoint of the close action
        $async_url = $this->dic->ctrl()->getLinkTargetByClass('ilOnScreenChatGUI','asyncCloseItem');

        // Note, I am unsere about the state of the user avatars, therefore I would just use the
        // Chat Icon for the moment.
        $chat_icon = $this->dic->ui()->factory()->symbol()->icon()->standard("chtr","conversations");
        $chat_title = $this->dic->ui()->factory()->link()->standard("Conversations", '#');
        $chat_notification_item = $this->dic->ui()->factory()->item()->notification($chat_title,$chat_icon)
                                            ->withDescription("No Conversations available");

        if($_GET["no_aggregates"] !== "true"){
            //Create the aggregate (sub) items
            $aggregate_title = $this->dic->ui()->factory()->button()->shy($name, '#')
                                         ->withAdditionalOnLoadCode(function($id){
                                             //Do what needs to be done by clicking on the title probably reload the entries without the one here.
                                             return "$('#$id').click(function() {
                    return console.log('Notification Title has been clicked');
                });";
                                         });
            $aggregate_item = $this->dic->ui()->factory()->item()->notification($aggregate_title,$chat_icon)
                                        ->withDescription($message ."1")
                                        ->withCloseAction($async_url."&nr=1");

            $chat_notification_item = $chat_notification_item
                ->withAggregateNotifications([$aggregate_item])
                ->withDescription("You have 1 conversation");
        }

        echo $this->dic->ui()->renderer()->renderAsync([$chat_notification_item]);
        exit;
    }

    /**
     * Allows to perform work after closing the Icon on server and client.
     */
    public function asyncCloseItem(){
        $nr = $_GET['nr'];
        $async_url = $this->dic->ctrl()->getLinkTargetByClass('ilOnScreenChatGUI','getNewAsyncItem');
        $async_url .= "&no_aggregates=true";
        $js = $this->dic->ui()->factory()->legacy("")->withOnLoadCode(function($id) use ($nr,$async_url){
            //Do what neeeds to be done, if the icon is closed
            return "
               il.ChatScopeNotificationItem.replaceByAsyncItem('$async_url');
               console.log('Notification nr $nr has been closed');
            ";
        });
        echo $this->dic->ui()->renderer()->renderAsync($js);
        exit;
    }
}
