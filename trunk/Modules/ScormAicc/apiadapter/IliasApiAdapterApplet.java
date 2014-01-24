/*
 * SCORM-1.2 API-Adapter Java Applet for ILIAS
 * Copyright (c) Matthai Kurian, Jan Gellweiler, Alexander Killing, Hendrik Holtmann
 *
 * Made for ILIAS, the same license terms as for ILIAS itself apply.
 *
 * This Applet handles communication between ILIAS and SCORM-1.2
 * Sharable Content Objects (SCOs). Most communication is via Liveconnect.
 * CMI (Computer Managed Instruction) data is sent to ILIAS through http POST
 * to an ILIAS server side PHP script. SCORM-1.2 runtime behavior and CMI
 * datamodel management is done by the PfPLMS SCORM-1.2 API-Adapter Core. 
 *
 *
 * This is adapted to reflect url parameter changes for Ilias version 3.7 onwards 
 *  
 */

import java.util.Hashtable;
import java.util.Enumeration;
import java.util.Timer;
import java.util.TimerTask;
import java.net.*;
import java.io.*;
import java.security.AccessController;
import java.security.PrivilegedAction;

public	class IliasApiAdapterApplet
	extends java.applet.Applet
	implements ch.ethz.pfplms.scorm.api.ApiAdapterInterface
{
	private	ch.ethz.pfplms.scorm.api.ApiAdapter core;

	private Hashtable IliasScoCmi  = new Hashtable();

	private String  IliasStudentId;
	private String  IliasStudentName;
	private String  IliasStudentLogin;
	private String  IliasStudentOu;
	
	private String  IliasRefId;

	private String  IliasSahsId;
	private String  IliasNextObjId;
	
	private int		IliasPingSession;

	private boolean isLaunched  = false;
	private boolean isLaunching = false;
	private boolean isVerbose   = false;
	//private long	clickTime = 0;

	public IliasApiAdapterApplet () {
		core = new ch.ethz.pfplms.scorm.api.ApiAdapter ();
	}

	public	final void init () {
		//if (getParameter("verbose") != null)
		isVerbose = true;
		IliasRefId       = getParameter ("ref_id");
		IliasStudentId   = getParameter ("student_id");
		IliasStudentName = getParameter ("student_name");
		IliasStudentLogin = getParameter ("student_login");
		IliasStudentOu    = getParameter ("student_ou");
		IliasPingSession    = Integer.parseInt(getParameter ("ping_session"));
		
		say ("cmi.core.student_id=" +IliasStudentId);
		say ("cmi.core.student_name=" +IliasStudentName);
		
		if (IliasPingSession>0)
		{
			SchedulePing();
		}
	}

	private final void SchedulePing()
	{
		say("Session-Ping will occur every: "+ IliasPingSession+ " seconds." );
		
		int delay = IliasPingSession * 1000;   
	    int period = IliasPingSession * 1000;  
	    Timer timer = new Timer();

	    timer.scheduleAtFixedRate(new TimerTask() {
	            public void run() {
					URLConnection po;
					try {
						po = (URLConnection) ( new java.net.URL (
							getCodeBase().toString()
						 	+"../../../ilias.php?baseClass=ilSAHSPresentationGUI&cmd=pingSession"
							+ "&ref_id=" +IliasRefId
						)).openConnection();
						BufferedReader in = new BufferedReader(
				                                new InputStreamReader(
				                                po.getInputStream()));
						in.close();
					} catch (Exception e) {
						say("Ping session failed");
					}
				}
	        }, delay, period);
	}
	
	private final void say (String s) {
		if (isVerbose) System.out.println (s);
	}

	private final void IliasLaunchContent (String s) {
		try {
			getAppletContext().showDocument (
				new URL(getCodeBase()+s),
				"sahs_content"
			);
		} catch (Exception e) {}
	}

	/*
	 * Methods for Ilias to call via Liveconnect
	 */

	public	final void IliasLaunchSahs (String sahs_id) {
		/*
		if (System.currentTimeMillis() < clickTime + 1000) {
			say ("Click ignored.");
			return;
		}
		*/
		if (isLaunching) {
			say ("SAHS " +IliasSahsId +" is launching.");
			return;
		}
		if (isLaunched && sahs_id.equals(IliasSahsId)) {
			say ("SAHS " +sahs_id +" is already running.");
			return;
		}
		IliasScoCmi.clear();

		say ("Launching sahs " +sahs_id);

		if (isLaunched) {
			say ("SAHS "+IliasSahsId +" will be unloaded.");
			IliasNextObjId = sahs_id;
			IliasLaunchContent (
				 "../../../ilias.php?baseClass=ilSAHSPresentationGUI&cmd=unloadSahs"
				  +"&sahs_id="  + IliasSahsId
				  +"&ref_id="  + IliasRefId
			);
		} else {
			isLaunching = true;
			//clickTime = System.currentTimeMillis();
			IliasNextObjId = null;
			IliasSahsId     = sahs_id;
			IliasLaunchContent (
				 "../../../ilias.php?baseClass=ilSAHSPresentationGUI&cmd=launchSahs"
				  +"&ref_id="  + IliasRefId
				  +"&sahs_id="  + IliasSahsId
			);
		}
	}

	public	final void IliasSetValue (String l, String r) {
		if (r == null) r = ""; // MSIE bug
		say ("IliasSetValue("+l+"="+r+")");
		if (l != null) IliasScoCmi.put (l, r);
	}

	public	final void IliasAbortSahs (String sahs_id) {
		if (!IliasSahsId.equals (sahs_id)) return;
		isLaunching = false;
		if (!isLaunched) return;
		say ("Warning: sahs " +sahs_id +" did not call LMSFinish()");
		IliasFinish (false);
		core.reset();
	}

	private	final void IliasInitialize () {
		isLaunching = false;
		core.sysPut ("cmi.core.student_id",   IliasStudentId);
		core.sysPut ("cmi.core.student_name", IliasStudentName);
		core.sysPut (IliasScoCmi);
		core.transBegin();
		isLaunched  = true;
	}

	private final void initEntry () {
		String l = "cmi.core.entry";
		if (core.sysGet("cmi.core.exit").equals("suspend")) { 
			core.sysPut(l, "resume");
		} else {
			core.sysPut(l, "");		
		}
		return;
	}
	
	 private final void modifyEntry () {
		 String l = "cmi.core.entry";
		 if (core.sysGet("cmi.core.exit").equals("suspend")) { 
		         core.sysPut(l, "resume");
		 } else {
		         core.sysPut(l, "");             
		 }
		 return;
     }

	
	private	final void IliasFinish (boolean commit) {
		if (!isLaunched) return;
		this.modifyEntry();
		if (commit) IliasCommit(); // Stupid "implicit commit"
		isLaunched = false;
		IliasLaunchContent (
			"../../../ilias.php?baseClass=ilSAHSPresentationGUI&cmd=finishSahs"
			  +"&sahs_id="  + IliasSahsId
			  +"&ref_id="  + IliasRefId
			  +"&status="  + core.sysGet("cmi.core.lesson_status")
			  +"&totime="  + core.sysGet("cmi.core.total_time")
			  +"&launch="  + IliasNextObjId
		);
	}

	public	final void IliasLaunchAsset (String id) {
		if (isLaunching) return;
		say ("Launching asset: " +id);
		if (isLaunched) {
			say ("SAHS "+IliasSahsId +" will be unloaded.");
			IliasNextObjId = id;
			IliasLaunchContent (
				 "../../../ilias.php?baseClass=ilSAHSPresentationGUI&cmd=unloadSahs"
				  +"&sahs_id="  + IliasSahsId
			);
		} else {
			//clickTime = System.currentTimeMillis();
			IliasNextObjId = null;
			IliasSahsId     = null;
			IliasLaunchContent (
				 "../../../ilias.php?baseClass=ilSAHSPresentationGUI&cmd=launchAsset"
				  +"&ref_id="  + IliasRefId
				  +"&asset_id="  + id
			);
		}
	}

	private String IliasCommit() {// </editor-fold>
		if (IliasSahsId == null) return "false";

		core.transEnd();
		StringBuilder P = new StringBuilder();
		Hashtable ins = core.getTransNew ();
		Hashtable mod = core.getTransMod ();
		core.transBegin();

		int i=0;
		for (Enumeration e = ins.keys(); e.hasMoreElements(); i++) {
			Object l = e.nextElement();
			Object r = ins.get(l);
			P.append("&iL["+i+"]="+l.toString());
			P.append("&iR["+i+"]="+URLEncoder.encode(r.toString()));
		}

		int u=0;
		for (Enumeration e = mod.keys(); e.hasMoreElements(); u++) {
			Object l = e.nextElement();
			Object r = mod.get(l);
			P.append("&uL["+u+"]="+l.toString());
			P.append("&uR["+u+"]="+URLEncoder.encode(r.toString()));
		}

		if (i == 0 && u == 0) {
			say ("Nothing to do.");
			return "true";
		}

		//HttpURLConnection po;
		final URLConnection po;

		try {
			//po = (HttpURLConnection) ( new java.net.URL (
			po = (URLConnection) ( new java.net.URL (
				getCodeBase().toString()
				+ "../sahs_server.php"
				+ "?cmd=store" 
				+ "&user_id="+IliasStudentId
				+ "&sahs_id=" +IliasSahsId
				+ "&ref_id=" +IliasRefId
			)).openConnection();

			po.setRequestProperty (
				"Content-Type",
				"application/x-www-form-urlencoded"
			);
			po.setRequestProperty (
				"Content-Length",
				Integer.toString (P.length())
			);
			po.setDoOutput (true);
			po.setUseCaches (false);
			//po.setRequestMethod ("POST");
			po.setAllowUserInteraction (false);

			// Do as privileged action as workaround for
			// http://forums.oracle.com/forums/thread.jspa?threadID=1772674
			OutputStream os = null;
			os = (OutputStream) AccessController.doPrivileged(new PrivilegedAction()
			{
				public Object run() {
					try {
						return po.getOutputStream();
					}
					catch(Exception e) {
						say(e.getMessage());
					}
					return null;
				}
			}
			);



			say ("post:" +P.toString());
			os.write (P.toString().getBytes());
			os.close ();

			DataInputStream r = new DataInputStream(
				po.getInputStream ()
			);
			try {
				say (r.readUTF());
				r.close ();
			} catch (EOFException ok) {}

			say (i +" inserted.");
			say (u +" updated.");
			return "true";

		} catch (Exception e) {
			say ("Ilias cmi storage failed.");
			say (e.toString());
			return "false";
		}
	}

	/*
	 * Liveconnect interface methods for SCO
	 */

	public	final String LMSInitialize (String s) { 
		String rv = core.LMSInitialize(s);
		say ("LMSInitialize("+s+")="+rv);
		if (rv.equals("false")) return rv;
		core.reset();
		rv = core.LMSInitialize(s);
		IliasInitialize ();
		return rv;
	}

	public	final String LMSCommit (String s) {
		String rv = core.LMSCommit(s);
		if (rv.equals("false")) return rv;
		rv = IliasCommit(); 
		say ("LMSCommit("+s+")="+rv);
		return rv;
	}

	public	final String LMSFinish (String s) {
		String rv = core.LMSFinish(s);
		say ("LMSFinish("+s+")="+rv);
		if (rv.equals("false")) return rv;
		IliasFinish(true);
		core.reset();
		return rv;
	}

	public	final String LMSGetDiagnostic (String e) {
		String rv = core.LMSGetDiagnostic (e);
		say ("LMSGetDiagnostic("+e+")="+rv);
		return rv;
	}

	public	final String LMSGetErrorString (String e) {
		String rv = core.LMSGetErrorString (e);
		say ("LMSGetErrorString("+e+")="+rv);
		return rv;
	}

	public	final String LMSGetLastError () {
		String rv = core.LMSGetLastError ();
		say ("LMSLastError()="+rv);
		return rv;
	}

	public	final String LMSGetValue (String l) {
		if (l.equals("cmi.core.student_login")) {say ("Special: cmi.core.student_login = " + IliasStudentLogin); return IliasStudentLogin;}
		if (l.equals("cmi.core.student_ou")) {say ("Special: cmi.core.student_ou = " + IliasStudentOu); return IliasStudentOu;}		
		String rv = core.LMSGetValue (l);
		say ("LMSGetValue("+l+")="+rv);
		return rv;
	}

	public	final String LMSSetValue (String l, String r) {
		String rv = core.LMSSetValue (l, r);
		say ("LMSSetValue("+l+"="+r+")="+rv);
		return rv;
	}
}
