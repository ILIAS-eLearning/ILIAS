const Container = require('../AppContainer'),
    UserStoppedTyping = require('../Model/Messages/UserStoppedTyping');

module.exports = function (roomId) {
    const serverRoomId = Container.createServerRoomId(roomId),
        namespace = Container.getNamespace(this.nsp.name),
        room = namespace.getRoom(serverRoomId);

    Container.getLogger().info('UserStoppedTypingInRoom command send to room %s of namespace %s', serverRoomId, namespace.getName());
    if (typeof this.subscriber === "undefined") {
        Container.getLogger().error("Missing subscriber, don't process message");
        return;
    }

    if (!room.hasSubscriber(this.subscriber.getId())) {
        Container.getLogger().error("Subscriber is not in room, don't process message");
        return;
    }

    Container.getLogger().debug("Subscribed with id %s stopped typing", this.subscriber.getId());

    const subscriber = this.subscriber,
        message = UserStoppedTyping.create(roomId, this.subscriber.toString());

    room.forSubscribers(function(sub) {
        if (sub.getId() == subscriber.getId()) {
            return;
        }

        sub.getSocketIds().forEach(function(socketId) {
            namespace.getIO().to(socketId).emit('userStoppedTyping', message);
        });
    });
};
