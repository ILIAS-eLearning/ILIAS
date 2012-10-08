/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

package chatserver;

/**
 *
 * @author jposselt
 */
public class MissingArgumentException extends Exception{

    public MissingArgumentException(String string) {
	super("Missing argument \"" + string + "\"");
    }

}
