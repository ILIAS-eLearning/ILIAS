var provider = il.GS.Services.provider().getByProviderName('BTClientSideNotificationProvider');
var identification = il.GS.Services.identification(provider, 'my_first_notification');
var one_notification = il.GS.Services.factory().notifications().onScreen(identification);

one_notification.title = "My Title";
one_notification.summary = "My Summary";

var collector = il.GS.Services.collector().notifications();

collector.push(one_notification);

console.log(collector.getAll());