/*
 * ch/ethz/pfplms/scorm/api/ApiAdapter.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * Class providing LiveConnect API for SCORM-1.2 scos
 * Extend this class to build into your API-Adapter applet
 * 
 * Copyright (C) 2004  Matthai Kurian
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


package ch.ethz.pfplms.scorm.api;

import java.util.Hashtable;

public	class ApiAdapter implements ApiAdapterInterface {

	protected ch.ethz.pfplms.scorm.cmi.Manager
		  cm = new ch.ethz.pfplms.scorm.cmi.Manager ();

	protected ch.ethz.pfplms.scorm.api.components.ErrorHandler
		  eh = new ch.ethz.pfplms.scorm.api.components.ErrorHandler ();

	protected ch.ethz.pfplms.scorm.api.components.StateManager
		  sm = new ch.ethz.pfplms.scorm.api.components.StateManager ();

public	ApiAdapter () {
}

public	final Hashtable getCmiHash () {
	return cm.getCmiHash();
}

public	final String isError () {
	return (eh.isError()) ? "false" : "true";
}

public	void reset () {
	sm.reset();
	eh.reset();
	cm.reset();
}

public  String LMSInitialize (String s) {
	/*
	 * We treat null as the empty string "" because MSIE?? converts
	 * "" sent by the SCO to null.
	 */
        if (s == null) s = "";

	if (s.length()>0) {
		eh.setDiagnostic ("LMSInitialize with bad argument");
		eh.setErrorCode (201);
		return isError();
	}

	if (sm.isInitialized()) {
		eh.setDiagnostic (
			"LMSInitialize called in initialized state"
		);
		eh.setErrorCode (101);
		return isError();
	}

	sm.reset();
	if (sm.processEvent (sm.LMSInitialize)) {
		eh.reset();
		return isError();
	}

	return "false";
}



public  String LMSSetValue (String l, String r) {

        if (l == null) {
		eh.setErrorCode (101);
		eh.setDiagnostic ("lvalue is null");
		return isError();
	}

	/*
	 * We treat null as the empty string "" because MSIE?? converts
	 * "" sent by the SCO to null.
	 */
	if (r == null)  r = "";

	if (sm.processEvent (sm.LMSSetValue)) {
		cm.put (l, r);
		eh.setErrorCode (cm.getErrorCode());
	} else {
		eh.setErrorCode (sm.getErrorCode());
	}

	return isError();
}

public  String LMSGetValue (String l) {
        if (l == null) {
		eh.setErrorCode (101);
		return isError();
	}
	String rv = "";
	if (sm.processEvent (sm.LMSGetValue)) {
		rv = cm.get (l);
		eh.setErrorCode (cm.getErrorCode());
	} else {
		eh.setErrorCode (sm.getErrorCode());
	}
	return rv;
}

public  String LMSFinish (String s) {
	/*
	 * We treat null as the empty string "" because MSIE?? converts
	 * "" sent by the SCO to null.
	 */
	if (s == null) s = "";

	if (s.length()>0) {
		eh.setDiagnostic ("LMSFinish with bad argument");
		eh.setErrorCode (201);
		return isError();
	}
        if (sm.processEvent (sm.LMSFinish)) {
		eh.reset();
	} else {
		eh.setDiagnostic (
			"ERROR "+eh.getErrorCode()
			+" "    +eh.getErrorString()
		);
	}
	return isError();
}

public  String LMSCommit (String s) {
	/*
	 * We treat null as the empty string "" because MSIE?? converts
	 * "" sent by the SCO to null.
	 */
        if (s == null) s = "";

	if (s.length()>0) {
		eh.setDiagnostic("LMSCommit with bad argument");
		eh.setErrorCode (201);
		return isError();
	}

	sm.processEvent (sm.LMSCommit);
	eh.setErrorCode (sm.getErrorCode());
	return isError();

}

public  String LMSGetLastError () {
	sm.processEvent (sm.LMSGetLastError);
	return eh.getErrorCode();
}

public  String LMSGetErrorString (String ec) {
	sm.processEvent (sm.LMSGetErrorString);
	return eh.getErrorString (ec);
}

public  String LMSGetDiagnostic (String ec) {
	if (ec == null) {
		return eh.getErrorString ("0");
	}
	sm.processEvent (sm.LMSGetDiagnostic);
	return eh.getErrorString (ec);
}

public	String sysGet (String l) {
	return cm.sysget (l);
}

public	void sysPut (String l, String r) {
	cm.sysput (l, r);
}

public	void sysPut (Hashtable h) {
	cm.sysput (h);
}

public final void transBegin () { cm.transBegin(); }
public final void transEnd () { cm.transEnd(); }
public final Hashtable getTransNew () { return cm.getTransNew(); }
public final Hashtable getTransMod () { return cm.getTransMod(); }

}

