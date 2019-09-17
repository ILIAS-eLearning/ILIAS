var provider = il.GS.Provider.getClientSideProvider('BTClientSideNotificationProvider')


var identification = il.GS.Identification.getFromClientSideString('my_first_notification', provider);
var one_notification = il.GS.DTO.notifications().onScreen(identification);

one_notification.title = "My Title";
one_notification.summary = "My Summary";

var collector = il.GS.Collector.NotificationCollector.notifications();

collector.push(one_notification);

console.log(collector.getAll());