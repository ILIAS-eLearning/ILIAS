
import java.util.Hashtable;
import java.net.*;

public	class IliasApiAdapterApplet
	extends java.applet.Applet
	implements ch.ethz.pfplms.scorm.api.ApiAdapterInterface
{
	private	ch.ethz.pfplms.scorm.api.ApiAdapter api;

	private String user_id;
	private String ref_id;
	private String student_id;
	private String student_name;

	public IliasApiAdapterApplet () {
		api = new ch.ethz.pfplms.scorm.api.ApiAdapter ();
	}

	public void init () {
		ref_id  = getParameter("ref_id");
		student_id = getParameter("student_id");
		student_name = getParameter("student_name");

		System.out.println ("IliasApiAdapterApplet ready.");

		System.out.println ("ref_id="+ref_id);
		System.out.println ("student_id="+student_id);
		System.out.println ("student_name="+student_name);

	}

	/*
	 * Liveconnect interface methods for SCO
	 */
	public	String LMSInitialize (String s) {

		String rv = api.LMSInitialize(s);

		/*
		 * Override or modify this method to load learners stored cmi
		 * data from your RTE backend
		 */
		System.out.println ("LMSInitialize("+s+")="+rv);

		return rv;
	}

	public	String LMSCommit (String s) {

		String rv = api.LMSCommit(s);
		/*
		 * Override or modify this method to store/update learners
		 * cmi data in your RTE backend
		 */
		System.out.println ("LMSCommit("+s+")="+rv);

		return rv;
	}

	public	String LMSFinish (String s) {

		String rv = api.LMSFinish(s);
		/*
		 * Override or modify this method to store/update learners
		 * cmi data in your RTE backend
		 */
		System.out.println ("LMSFinish("+s+")="+rv);

		return rv;
	}

	/*
	 * The following methods can be left as they are, they are 
	 * related only to the internal state of the API adapter.
	 */
	public	String LMSGetDiagnostic (String e) {
		// return LMSGetDiagnostic (e);
		
		String rv = api.LMSGetDiagnostic (e);
		System.out.println ("LMSGetDiagnostic("+e+")="+rv);
		return rv;
	}

	public	String LMSGetErrorString (String e) {
		//return api.LMSGetErrorString(e);
		
		String rv = api.LMSGetErrorString (e);
		System.out.println ("LMSGetErrorString("+e+")="+rv);
		return rv;
	}

	public	String LMSGetLastError () {
		// return api.LMSGetLastError();
		
		String rv = api.LMSGetLastError ();
		System.out.println ("LMSLastError()="+rv);
		return rv;
	}

	public	String LMSGetValue (String l) {
		//return api.LMSGetValue(l);

		String rv = api.LMSGetValue (l);
		System.out.println ("LMSGetValue("+l+")="+rv);
		return rv;
	}

	public	String LMSSetValue (String l, String r) {
		//return (api.LMSSetValue(l,r));

		String rv = api.LMSSetValue (l, r);
		System.out.println ("LMSSetValue("+l+"="+r+")="+rv);
		return rv;
	}


	/*
	 * Methods to integrate into Ilias
	 */

	public	void IliasLaunchSco (String sco_id) {

		reset ();

		api.sysPut ("cmi.core.student_id",   student_id);
		api.sysPut ("cmi.core.student_name", student_name);

		try {
			getAppletContext().showDocument(
				new URL(
					getCodeBase()
					+"../scorm_presentation.php?cmd=launchSco"
					+"&sco_id=" + sco_id
					+"&ref_id=" + ref_id
				),
				"scorm_content"
			);
		} catch (Exception e) {
		}
		
	}

	public	void IliasSetValue (String l, String r) {
		System.out.println ("IliasSetValue("+l+"="+r+")");
		sysPut (l, r);
	}

	private void IliasStoreCmi () {

		HttpURLConnection http;
		try {
			http = (HttpURLConnection) (
					new java.net.URL (
						getCodeBase().toString() + "../scorm_server.php"
					)
				).openConnection();

			http.setRequestMethod("POST");
			http.setRequestProperty("Content-Type","application/x-www-form-urlencoded");
			http.setDoOutput(true);
			http.setUseCaches (false);

		} catch (Exception e) {
			System.out.println (e);
			return;
		}

		/*
		URL url = new URL (getCodeBase().toString() + "YourFormhandler.php");
		HttpURLConnection http = (HttpURLConnection)url.openConnection();

		byte [] data = "var0=Name".getBytes();
		http.setRequestMethod("POST");
		http.setRequestProperty("Content-Type","application/x-www-form-urlencoded");
		http.setDoOutput(true);
		http.setUseCaches (false);

		OutputStream os = http.getOutputStream();
		os.write(data);
		os.close();

		StringBuffer contentBuffer = new StringBuffer();
		PrintWriter out = new PrintWriter(http.getOutputStream ());
		contentBuffer.append("your1DataParam=" + URLEncoder.encode(your1Data));
		contentBuffer.append("&your2DataParam=" + URLEncoder.encode(your2Data));
		String content = new String(contentBuffer.toString());
		out.println(content);
		out.flush ();
		out.close ();
		*/

	}

	/*
	 * Extra methods needed for integration into RTE
	 */
	private	void	reset () {
		api.reset();
	}

	private void sysPut (Hashtable cmi) {
		api.sysPut (cmi);
	}

	private void sysPut (String l,  String r) {
		api.sysPut (l, r);
	}

	private  String sysGet (String l) {
		return api.sysGet (l);
	}

	private  Hashtable getCmiHash () {
		return api.getCmiHash ();
	}

	/*
	 * Incremental RTE backend storage support methods, use these methods
	 * to minimize traffic and load to your RTE backend.
	 */

	private	void transBegin () {
		// marks beginning of changes to current internal cmi data state
		api.transBegin ();
	}

	private	void transEnd () {
		// marks end of changes to internal cmi data state
		api.transEnd ();
	}

	private	Hashtable getTransMod () {
		// delivers modified cmi elements
		return api.getTransMod ();
	}

	private	Hashtable getTransNew () {
		// delivers inserted cmi elements
		return api.getTransNew ();
	}

}
