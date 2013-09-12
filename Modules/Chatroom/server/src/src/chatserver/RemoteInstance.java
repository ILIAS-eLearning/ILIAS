package chatserver;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.Writer;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;
import java.util.Properties;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * This class stores information for communicating with remote instances (e.g.
 * an ILIAS installation).
 * 
 * Most of the information are read from the instance properties file.
 */
public class RemoteInstance {

	private String name;
	private String hash;
	/**
	 * base url for ilias
	 * 
	 * this is used to build the soap url and an url to push information directly
	 * to ilias without soap
	 */
	private String feedbackUrl;
	/**
	 * list of chat rooms
	 */
	private ChatScopeList scopeList = new ChatScopeList();
	/**
	 * current ilias session id
	 */
	private String iliasSession;
	/**
	 * ilias client name
	 */
	private String iliasClient;
	/**
	 * ilias soap user id
	 */
	private String iliasUser;
	/**
	 * ilias soap password
	 */
	private String iliasPassword;
	/**
	 * stores the timestamp of the last ilias soap login
	 */
	private long lastLoggedIn;

	public RemoteInstance(String name, String hash, String feedbackUrl) {
		this.name = name;
		this.hash = hash;
		this.feedbackUrl = feedbackUrl;

		if (this.feedbackUrl.charAt(this.feedbackUrl.length() - 1) != '/') {
			this.feedbackUrl += "/";
		}
	}

	/**
	 * factory method for building an instance from a property set
	 * 
	 * @param props
	 * @return 
	 */
	public static RemoteInstance fromProperties(Properties props) {
		RemoteInstance instance = new RemoteInstance(props.getProperty("name"), props.getProperty("hash"), props.getProperty("url"));
		instance.setIliasUser(props.getProperty("user"));
		instance.setIliasPassword(props.getProperty("password"));
		instance.setIliasClient(props.getProperty("client"));
		return instance;
	}

	/**
	 * builds a connection object that can be used to push information to ilias
	 * @param query
	 * @return
	 * @throws MalformedURLException
	 * @throws IOException 
	 */
	public URLConnection getFeedbackConnection(String query) throws MalformedURLException, IOException {
		// refresh soap login every 60 seconds
		// @todo could be more elegant :)
		synchronized (this) {
			if (System.currentTimeMillis() - lastLoggedIn > 1000 * 60) {
				login();
			}
		}
		
		if (!query.equals("")) {
			query = "&" + query;
		}

		URL url = new URL(this.feedbackUrl + "ilias.php?baseClass=ilObjChatroomGUI&serverInquiry=true" + query);
		URLConnection con = url.openConnection();

		Logger.getLogger("default").finer("[" + getIliasClient() + "] Calling ILIAS with session " + getIliasSession());

		con.setRequestProperty("Cookie", "ilClientId=" + getIliasClient() + ";" + "PHPSESSID=" + getIliasSession());
		return con;
	}

	/**
	 * performs an soap login to ilias
	 */
	public void login() {
		String soapURL = feedbackUrl + "webservice/soap/server.php";

		HttpURLConnection connection = null;

		try {
			URL u = new URL(soapURL);
			URLConnection uc = u.openConnection();
			connection = (HttpURLConnection) uc;

			connection.setDoOutput(true);
			connection.setDoInput(true);
			connection.setRequestMethod("POST");
			connection.setRequestProperty("SOAPAction", "urn:ilUserAdministration#login");
			connection.setRequestProperty("Content-Type", "text/xml;charset=UTF-8");

			OutputStream out = connection.getOutputStream();
			Writer wout = new OutputStreamWriter(out);

			// build the soap message
			wout.write("<SOAP-ENV:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:urn=\"urn:ilUserAdministration\">\n");
			wout.write("<SOAP-ENV:Header/>\n");
			wout.write("<SOAP-ENV:Body>\n");
			wout.write("<urn:login soapenv:encodingStyle=\"http://schemas.xmlsoap.org/soap/encoding/\">\n");
			wout.write("<client xsi:type=\"xsd:string\">" + getIliasClient() + "</client>\n");
			wout.write("<username xsi:type=\"xsd:string\">" + getIliasUser() + "</username>\n");
			wout.write("<password xsi:type=\"xsd:string\">" + getIliasPassword() + "</password>\n");
			wout.write("</urn:login>\n");
			wout.write("</SOAP-ENV:Body>\n");
			wout.write("</SOAP-ENV:Envelope>");

			wout.flush();
			wout.close();

			StringBuilder bfr = new StringBuilder();

			InputStream in = connection.getInputStream();
			int c;
			while ((c = in.read()) != -1) {
				bfr.append((char) c);
			}
			in.close();

			// parse the session id or throw an exception
			// if there is a session id, the login is assumed to be successful
			Pattern ptr = Pattern.compile("<sid xsi:type=\"xsd:string\">(.*?)::(.*?)</sid>", Pattern.DOTALL);
			Matcher m = ptr.matcher(bfr.toString());
			if (m.find()) {
				setIliasSession(m.group(1));

				Logger.getLogger("default").finer("[" + getIliasClient() + "] Logged in to ILIAS using user " + getIliasUser() + ", Session: " + getIliasSession());
			} else {
				Logger.getLogger("default").severe("[" + getIliasClient() + "] Could not find session, unable to login to ILIAS using user " + getIliasUser());
				throw new RuntimeException("Could not login");
			}

			lastLoggedIn = System.currentTimeMillis();

		} catch (IOException e) {
			// oops, can not connect to ilias
			// trying to get some information from the connection and dump them
			// to stdout
			e.printStackTrace();

			if (connection != null) {
				Logger.getLogger("default").log(Level.SEVERE, "[" + getIliasClient() + "] Unable to login to ILIAS using user " + getIliasUser(), e);

				InputStream in = connection.getErrorStream();
				int b;
				try {
					while ((b = in.read()) >= 0) {
						System.out.print((char) b);
					}
				} catch (IOException ex) {
					Logger.getLogger("default").log(Level.SEVERE, null, ex);
				}
				throw new RuntimeException("Could not login");
			}
		}
	}

	public String getHash() {
		return hash;
	}

	public void setHash(String hash) {
		this.hash = hash;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public ChatScopeList getScopeList() {
		return this.scopeList;
	}

	/**
	 * @return the ilias_session
	 */
	public String getIliasSession() {
		return iliasSession;
	}

	/**
	 * @param ilias_session the ilias_session to set
	 */
	public void setIliasSession(String ilias_session) {
		this.iliasSession = ilias_session;
	}

	/**
	 * @return the ilias_client
	 */
	public String getIliasClient() {
		return iliasClient;
	}

	/**
	 * @param ilias_client the ilias_client to set
	 */
	public void setIliasClient(String ilias_client) {
		this.iliasClient = ilias_client;
	}

	/**
	 * @return the iliasUser
	 */
	public String getIliasUser() {
		return iliasUser;
	}

	/**
	 * @param iliasUser the iliasUser to set
	 */
	public void setIliasUser(String iliasUser) {
		this.iliasUser = iliasUser;
	}

	/**
	 * @return the iliasPassword
	 */
	public String getIliasPassword() {
		return iliasPassword;
	}

	/**
	 * @param iliasPassword the iliasPassword to set
	 */
	public void setIliasPassword(String iliasPassword) {
		this.iliasPassword = iliasPassword;
	}
}
