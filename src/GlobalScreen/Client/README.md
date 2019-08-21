Client Side GlobalScreen
========================

> Attention: This is not a general approach how to do client-side rendering in ILIAS or even bypass it. This mechanism is implemented due to the missing client-side rendering for the requirements of chat notifications. The discussion about client-side rendering will be conducted separately and should provide a general approach.

> This part of the GlobalScreen service also contains TypeScript code. Work is already underway on this in a wide variety of places. This suggestion here is not to bypass this by any means or to deliver final concepts for the use of TypeScript in ILIAS. TypeScript was only used here to have a more understandable and secure structure in JS. We are already looking forward to the discussions about TypeScript.



There are different scenarios in which besides the `Items` provided by the GlobalScreen providers on the server side, `Items` on the client side can also arise.

Such a case are the `Notifications`: Beside all the notifications, which are offered on the server side by the NotificationProvider, the chat client side must have the possibility to communicate own notifications to the `MainNotificationCollector` as well.

For the moment, this requirement is solved as described below. 

> At the moment some projects are taking place around ILIAS to find more central ways for such problems. As soon as an ILIAS-wide approach exists, the GlobalScreen service will adhere to it.

## ClientSideProvider
A `ClientSideProvider` allows you to generate your own items of a scope in a similar way as on the server side and communicate them to a collector. At the moment there is only the possibility to create `ClientSideNotificationProvider` within a POC. More about this under the description of the `Notifications`: Documentation](../Scope/Notification/README.md)

All `ClientSideProviders` are collected on the server side by a collector and registered on the client side using the `ClientSideProviderRegistrar`.

 On the client side, similar services are available as on the server side:

 ```typescript
 export class Services {
 
             ...
 
             public static provider(): ProviderFactory {
                 ...
             }
 
             public static identification(provider: isProvider, internal_identifier: string) {
                 ...
             }
 
             public static collector(): CollectorFactory {
                 ...
             }
 
             public static factory(): DTOFactory {
                 ...
             }
         }
 ```
 
With the help of these services you can now compile NotificationDTOs on the client side and log on to the collector: 

```javascript
// Get My Provider
var provider = il.GS.Services.provider().getByProviderName('BTClientSideNotificationProvider');

// Create a new Identification
var identification = il.GS.Services.identification(provider, 'my_first_notification');

// Create a new NotificationDTO and fill it with some stuff
var one_notification = il.GS.Services.factory().notifications().onScreen(identification);

one_notification.title = "My Title";
one_notification.summary = "My Summary";

// get the Notifications Collector and push my notification 
var collector = il.GS.Services.collector().notifications();

collector.push(one_notification);

```

> From here on, a conceptual description of how the client side can communicate with the server side follows. This in connection with notifications. None of this is implemented yet.


In the case of the Notification Center, all NotificationDTS could now be sent with the asynchronous retrieval of the HTML content of the Notification Center. NotificationDTS can be serialized and converted to effective notifications, e.g. `StandardNotification`, during server-side readout. Since these do not have all the information that a `StandardNotification` should have, they can be supplemented by the corresponding `ClientSideNotificationProvider` with `public function enrichItem(isItem $notification) : isItem;` to full server-side notifications. These, together with all other server-side notifications, can then be rendered and displayed in the NotificationCenter.