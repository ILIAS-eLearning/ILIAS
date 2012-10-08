package chatserver;

/**
 *
 */
public class ActionHandlerException extends Exception{

    public ActionHandlerException(Exception missingArgumentException) {
	super(missingArgumentException);
    }

}
