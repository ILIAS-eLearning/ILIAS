package chatserver;

import java.util.HashMap;

/**
 * Simple wrapper for retreiving typed parameters which are stored as string.
 */
public class Parameters extends HashMap<String, String> {

	public int getInt(String name) {
		try {
			return Integer.parseInt(this.get(name));
		} catch (Exception e) {
			return 0;
		}
	}

	public String getString(String name) {
		try {
			return (String) this.get(name);
		} catch (Exception e) {
			return null;
		}
	}
}
