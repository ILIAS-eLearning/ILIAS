let Container  = require('../AppContainer');

module.exports = function(conversationId, userId) {
    if (conversationId !== null && userId !== null) {
        const namespace = Container.getNamespace(this.nsp.name),
            conversation = namespace.getConversations().getById(conversationId),
            participant = namespace.getSubscriber(userId);

        if (conversation !== null && conversation.isParticipant(participant)) {
            Container.getLogger().debug('User %s started typing in conversation "%s"', userId, conversationId);

            const participants = conversation.getParticipants();
            for (let key in participants) {
                if (participants.hasOwnProperty(key)) {
                    if (participants[key].getId() != participant.getId()) {
                        participants[key].emit('userStartedTyping', {
                            'conversation': conversation.json(),
                            'participant': participant.json()
                        });
                    }
                }
            }
        }
    }
};
