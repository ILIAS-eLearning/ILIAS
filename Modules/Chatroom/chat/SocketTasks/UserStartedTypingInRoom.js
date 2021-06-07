const Container = require('../AppContainer'),
    UserStartedTyping = require('../Model/Messages/UserStartedTyping');

module.exports = function (roomId, subRoomId) {
    const serverRoomId = Container.createServerRoomId(roomId, subRoomId),
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

    namespace.getIO().to(serverRoomId).emit(
        'userStartedTyping',
        UserStartedTyping.create(roomId, subRoomId, this.subscriber.getId())
    );
};
