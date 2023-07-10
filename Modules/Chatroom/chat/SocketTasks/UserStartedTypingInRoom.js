const Container = require('../AppContainer'),
    UserStartedTyping = require('../Model/Messages/UserStartedTyping');

module.exports = function (roomId) {
    const serverRoomId = Container.createServerRoomId(roomId),
        namespace = Container.getNamespace(this.nsp.name),
        room = namespace.getRoom(serverRoomId);

    Container.getLogger().info('UserStartedTypingInRoom command send to room %s of namespace %s', serverRoomId, namespace.getName());
    if (typeof this.subscriber === "undefined") {
        Container.getLogger().error("Missing subscriber, don't process message");
        return;
    }

    if (!room.hasSubscriber(this.subscriber.getId())) {
        Container.getLogger().error("Subscriber is not in room, don't process message");
        return;
    }

    Container.getLogger().debug("Subscribed with id %s started typing", this.subscriber.getId());

    const message = UserStartedTyping.create(roomId, this.subscriber.toString()),
        subscriber = this.subscriber;

    room.forSubscribers(function(sub) {
        if (sub.getId() == subscriber.getId()) {
            return;
        }

        sub.getSocketIds().forEach(function(socketId) {
            namespace.getIO().to(socketId).emit('userStartedTyping', message);
        });
    });
};
