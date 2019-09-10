var Container = require('../AppContainer');
var UUID	  = require('node-uuid');
var Conversation = require('../Model/Conversation');

    /**
 * @param {Array} participants
 */
module.exports = function(participants) {
    Container.getLogger().info('Conversation Requested');
    var namespace = Container.getNamespace(this.nsp.name);
    var conversations = namespace.getConversations();
    var conversation = conversations.getForParticipants(participants);
    var socket = this;

    if(conversation == null) {
        Container.getLogger().info('No Conversation found. Creating new one');
        conversation = new Conversation(UUID.v4());
        conversations.add(conversation);

        namespace.getDatabase().persistConversation(conversation);
    }

    for(var key in participants) {
        var participant = namespace.getSubscriberWithOfflines(participants[key].id, participants[key].name);
        conversation.addParticipant(participant);
        participant.join(conversation.id);
    }

    namespace.getDatabase().updateConversation(conversation);

    function onLastConversationMessageResult(row) {
        row.userId = row.user_id;
        row.conversationId = row.conversation_id;
        conversation.setLatestMessage(row);
    }

    function onLastConversationMessageEnd() {
        socket.participant.emit('conversation-init', conversation.json());
    }

    namespace.getDatabase().getLatestMessage(conversation, onLastConversationMessageResult, onLastConversationMessageEnd);
};
