package chatserver;

/**
 * A wrapper for the messages to deliver for a user.
 * 
 * The messages are stored in a ring buffer with a fixed size of 100 elements.
 */
class MessageBuffer {

	private int maxSize = 100;
	private int listEnd = 0;
	private Message[] buffer = new Message[maxSize];

	/**
	 * adds a new message the the buffer
	 * 
	 * @param message 
	 */
	public void enqueue(Message message) {
		buffer[listEnd] = message;
		listEnd = (listEnd + 1) % maxSize;
	}

	/**
	 * returns the message at the current position or null if there is none
	 * 
	 * @param position the position from where to deque
	 * 
	 * @return 
	 */
	public BufferMessage dequeue(int position) {
		// set the buffer pointer behind the last element
		if (position == -1) {
			position = listEnd;
		}

		// checks if a message is available
		if (hasEntries(position) && buffer[position % maxSize] != null) {
			Message m = buffer[position % maxSize];
			position = (position + 1) % maxSize;
			return new BufferMessage(m, position);
		}
		return null;
	}

	public boolean hasEntries(int position) {
		return position != listEnd;
	}

	public int getListEndPosition() {
		return listEnd;
	}
}

/**
 * Wrapper for a message returned by the message buffer.
 * 
 * Contains the message and its position which can be used to fetch the next message
 */
class BufferMessage {

	private Message message;
	private int position;

	public BufferMessage(Message message, int position) {
		this.message = message;
		this.position = position;
	}

	public Message getMessage() {
		return message;
	}

	public int getPosition() {
		return position;
	}
}
