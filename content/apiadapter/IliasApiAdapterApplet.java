/*
 * SCORM-1.2 API-Adapter Java Applet for ILIAS
 * Copyright (c) Matthai Kurian, Jan Gellweiler, Alexander Killing
 *
 * Made for ILIAS, the same license terms as for ILIAS itself apply.
 *
 * This Applet handles communication between ILIAS and SCORM-1.2
 * Sharable Content Objects (SCOs). Most communication is via Liveconnect.
 * CMI (Computer Managed Instruction) data is sent to ILIAS through http POST
 * to an ILIAS server side PHP script. SCORM-1.2 runtime behavior and CMI
 * datamodel management is done by the PfPLMS SCORM-1.2 API-Adapter Core. 
 */

import java.util.Hashtable;
import java.util.Enumeration;
import java.net.*;
import java.io.*;

public	class IliasApiAdapterApplet
	extends java.applet.Applet
	implements ch.ethz.pfplms.scorm.api.ApiAdapterInterface
{
	private	ch.ethz.pfplms.scorm.api.ApiAdapter core;

	private Hashtable IliasScoCmi  = new Hashtable();

	private String  IliasStudentId;
	private String  IliasStudentName;
	private String  IliasRefId;
	private String  IliasScoId;
	private String  IliasNextScoId;

	private boolean isLaunched  = false;
	private boolean isLaunching = false;
	private boolean isVerbose   = false;
	private long	clickTime = 0;

	public IliasApiAdapterApplet () {
		core = new ch.ethz.pfplms.scorm.api.ApiAdapter ();
	}

	public	final void init () {
		//if (getParameter("verbose") != null)
		isVerbose = true;
		IliasRefId       = getParameter ("ref_id");
		IliasStudentId   = getParameter ("student_id");
		IliasStudentName = getParameter ("student_name");
		say ("cmi.core.student_id=" +IliasStudentId);
		say ("cmi.core.student_name=" +IliasStudentName);
	}

	private final void say (String s) {
		if (isVerbose) System.out.println (s);
	}

	private final void IliasLaunchContent (String s) {
		try {
			getAppletContext().showDocument (
				new URL(getCodeBase()+s),
				"scorm_content"
			);
		} catch (Exception e) {}
	}

	public	final void IliasLaunchSco (String sco_id) {
		if (System.currentTimeMillis() < clickTime + 1000) {
			say ("Overclicked.");
			return;
		}
		if (isLaunching) {
			say ("SCO " +IliasScoId +" is launching.");
			return;
		}
		if (isLaunched && sco_id.equals(IliasScoId)) {
			say ("SCO " +sco_id +" is already running.");
			return;
		}
		IliasScoCmi.clear();
		clickTime = System.currentTimeMillis();

		say ("Launching sco " +sco_id);

		if (isLaunched) {
			say ("Sco "+IliasScoId +" will be unloaded.");
			IliasNextScoId = sco_id;
			IliasLaunchContent (
				 "../scorm_presentation.php?cmd=unloadSco"
				  +"&sco_id="  + IliasScoId
			);
		} else {
			isLaunching = true;
			IliasNextScoId = null;
			IliasScoId     = sco_id;
			IliasLaunchContent (
				 "../scorm_presentation.php?cmd=launchSco"
				  +"&ref_id="  + IliasRefId
				  +"&sco_id="  + IliasScoId
			);
		}
	}

	public	final void IliasSetValue (String l, String r) {
		if (r == null) r = ""; // MSIE bug
		say ("IliasSetValue("+l+"="+r+")");
		if (l != null) IliasScoCmi.put (l, r);
	}

	public	final void IliasAbortSco (String sco_id) {
		if (!IliasScoId.equals (sco_id)) return;
		isLaunching = false;
		if (!isLaunched) return;
		say ("Warning: sco " +sco_id +" did not call LMSFinish()");
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

	private	final void IliasFinish (boolean commit) {
		if (!isLaunched) return;
		if (commit) IliasCommit(); // Stupid "implicit commit"
		isLaunched = false;
		IliasLaunchContent (
			"../scorm_presentation.php?cmd=finishSco"
			  +"&sco_id="  + IliasScoId
			  +"&ref_id="  + IliasRefId
			  +"&status="  + core.sysGet("cmi.core.lesson_status")
			  +"&totime="  + core.sysGet("cmi.core.total_time")
			  +"&launch="  + IliasNextScoId
		);
	}

	private final String IliasCommit () {
		if (IliasScoId == null) return "false";

		core.transEnd();
		StringBuffer P = new StringBuffer();
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

		HttpURLConnection po;

		try {
			po = (HttpURLConnection) ( new java.net.URL (
				getCodeBase().toString()
				+ "../scorm_server.php"
				+ "?cmd=store" 
				+ "&api=2" 
				+ "&user_id="+IliasStudentId
				+ "&sco_id=" +IliasScoId
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
			po.setRequestMethod ("POST");
			po.setAllowUserInteraction (false);

			OutputStream os = po.getOutputStream();
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
