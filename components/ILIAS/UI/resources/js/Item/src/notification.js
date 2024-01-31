import {notificationItemFactory} from "./notification.main.js";
import {counterFactory} from "../../Counter/src/counter.main.js";

il.UI = il.UI || {};
il.UI.item = il.UI.item || {};

/**
 * Scope for JS code for the Notification Items in the UI Components.
 *
 * Note that this scope provides a public interface through which Notification Items can
 * be accessed and manipulated by the client side. Note that this is the same pattern as is used by
 * counter.js
 *
 * This scope contains only the getNotificationItemObject through which a Notification Item object can
 * be accessed.
 *
 * See the public_object_interface below for a list of functions of this object offered
 * to the public. Also see the extended asyc Main Controls Meta Bar example for a detailed
 * show case of the provided functionality.
 *
 * Example Usage:
 *
 * //Step 1: Get the Notification Item Object
 * var il.MyScoope.myNotificationItem = il.UI.item.notification.getNotificationItemObject($('selector'));
 *
 * Note that it is probably best to grap the selector directly from the item itself like so:
 *
 * $async_item = $item->withAdditionalOnLoadCode(function($id) {
 *   return "il.MyScoope.myNotificationItem  = il.UI.item.notification.getNotificationItemObject($($id));";
 * });
 *
 * //Step 2: Do stuff with the Notification Item Object
 * il.MyScoope.myNotificationItem.replaceByAsyncItem('some_url',{some_data});
 *
 * //Step 3: Note that you can also get the counter if the object is placed in the Meta Bar like so:
 * il.MyScoope.myNotificationItem.getCounterObjectIfAny().incrementNoveltyCount(10);
 */
il.UI.item.notification = notificationItemFactory($,counterFactory);