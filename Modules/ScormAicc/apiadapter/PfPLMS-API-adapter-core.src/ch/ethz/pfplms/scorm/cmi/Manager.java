package ch.ethz.pfplms.scorm.cmi;

/*
 * ch/ethz/pfplms/scorm/cmi/Manager.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class that handles SCORM-1.2 CMI Datamodel
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


import ch.ethz.pfplms.scorm.cmi.components.SyntaxChecker;
import ch.ethz.pfplms.scorm.cmi.components.AccessChecker;
import ch.ethz.pfplms.scorm.cmi.components.FormatChecker;
import ch.ethz.pfplms.scorm.cmi.components.RangeChecker;
import ch.ethz.pfplms.scorm.cmi.components.CMI;
import ch.ethz.pfplms.scorm.cmi.cmiTime;

import java.util.Vector;
import java.util.Hashtable;
import java.util.Enumeration;

public	class Manager {

	public	static final int ENONE   = 0;   // no error
	public	static final int ESYNTAX = 201; // syntax error
	public	static final int ENOCHLD = 202; // cannot have children
	public	static final int ENOCNT  = 203; // not an array
	public	static final int EISKEY  = 402; // is a keyword
	public	static final int ERDONLY = 403; // read only
	public	static final int EWRONLY = 404; // write only
	public	static final int ETYPE   = 405; // incorrect data type

	private static int error = ENONE;

	private static Hashtable   cntd = new Hashtable();
	private static Hashtable    cmi = new Hashtable();
	private static Hashtable  trans = new Hashtable();
	private static Hashtable  trNew = new Hashtable();
	private static Hashtable  trMod = new Hashtable();

	private static SyntaxChecker sc = new SyntaxChecker();
	private static AccessChecker ac = new AccessChecker();
	private static FormatChecker fc = new FormatChecker();
	private static RangeChecker  rc = new RangeChecker();

public	Manager () {
	CMI.init(cmi);
}

public final Hashtable getCmiHash () {

	Hashtable rv = new Hashtable();
	for (Enumeration e = cmi.keys(); e.hasMoreElements(); ) {
		String k = e.nextElement().toString();
		if (k.indexOf(".n.") != -1) continue;
		if (!k.startsWith ("cmi.")) continue;
		rv.put (k, cmi.get(k));
	}
	return rv;
}

public	final int getErrorCode () {
	return error;
}

public	final void put (String l, String r) {

	boolean R,W;

	error = ENONE;


	if (!sc.check(l)) {
		error = ESYNTAX;
		return;
	}

	String ename = sc.getElement();

	/*
	 * Access check
	 */

	W = ac.isWriteable(l);
	R = ac.isReadable(l);

	if (!R && !W) {
		error = ESYNTAX;
		return;
	}

	/*
	 * In some cases the several different errorcodes are possible,
	 * the SCORM does not clearly specify this. However, considering
	 * that the ADL TestSuite comes from the same place as the SCORM,
	 * we will consider the TestSuite as a part of the SCORM, and not
	 * ignore its warnings.  ADL testsuite 1.2.6 expects the following 
	 * errorcode precedence:
	 */
	if (l.endsWith ("._children")) error = R ? EISKEY : ENOCHLD;
	if (l.endsWith ("._count"))    error = R ? EISKEY : ENOCNT;
	if (error != ENONE) return;


	if (!W) {
		error = ERDONLY;
		return;
	}
	error = ac.getError();
	if (error != ENONE) return;



	/*
	 * At this point, in case of elements with counters, we
	 * have to check for the counters being in sequence, before
	 * checking the format and range.
	 */

	if (sc.numCounters() > 0) {
		checkSequence();
	}
	if (error != ENONE) {
		return;
	}


	/*
	 * CMIFeedback type values depend on the feedback type.
	 * So we have to configure the FormatChecker (fc) to the
	 * current feedback type.
	 */

	Object o = cmi.get(sc.getFeedbackType());
	fc.setFeedbackType ((o == null) ? "" : o.toString());

	/*
	 * Format check
	 */

	if (!fc.check (sc.getElement() , r)) {
		error = ETYPE;
		return;
	}

	/*
	 * Range checker, knows the range values based on element names.
	 * Returns true if there is no range defined for that element.
	 */
	 
	if (!rc.check (ename, r)) {
		error = ETYPE;
		return;
	}

	/*
	 * From here onwards we deal with some special cases.
	 * The actual put comes at the end of this method.
	 */



	if (l.equals ("cmi.core.session_time")) {

		/*
		 * Session time has to be added to total time. 
		 * Session time is not stored in the cmi datamodel.
		 * SCO can not ask for cmi.core.session_time
		 * it can ask for cmi.core.total_time
		 */

		r = new cmiTime(
				cmi.get("cmi.core.total_time").toString()
			).getTotal (r);

		l = "cmi.core.total_time";
		cmi.put (l, r);
		error = ENONE;
		return;
	}
	

	if (sc.numCounters() > 0) {

		/*
		 * In case the element is an array value, we have
		 * to perform additional checks and update the counters.
		 */

		handleLists(l, r);
		return;
	}


	if (l.equals("cmi.core.lesson_status") && 
	    r.equals("not attempted")) {

		/*
		 * The sco may not set its status to "not attempted", even
		 * though the vocabulary allows it. It would be lying anyway.
		 */

		error = ETYPE;
		return;
	}

	if (l.equals("cmi.comments")) {

		/*
		 * In case of the cmi.comments element, r has to
		 * be APPENDED to its current rvalue, not SET to r.
		 */

		o = cmi.get ("cmi.comments");
		if (o != null) {
			r = o.toString() + r;
			if (r.length() > 4096) {
				error = ESYNTAX;
				return;
			}
		}
		// FALLTHROUGH
	}
	
	/*
	 * Everything is "normal", just put it.
	 */
	
	cmi.put (l, r);
	error = ENONE;
}

private	final void handleLists (String l, String r) {

	/*
	 * This loop updates the involved *_count elements
	 * It also checks for the involved counter value(s) being in sequence
	 */

	for (int i = 0; i < sc.numCounters(); i++) {
		String cnt = sc.getCounter(i);
		String ary = sc.getArray(i);
		Object o = cmi.get (cnt);
		int mx = o == null ? 0 : Integer.parseInt(o.toString());
		int now = Integer.parseInt(sc.getIndex(i));
		if (!cntd.containsKey(ary)) {
			o = cmi.get (cnt);
			int c = o == null ? 0 : Integer.parseInt (o.toString());
			cntd.put (ary, "1");
			cmi.put (cnt, Integer.toString(++c));
		}

		if (now > mx) { // out of sequence
			error = ESYNTAX;
			return;
		}
	}

	cmi.put (l, r);
	error = ENONE;
}

private	final void checkSequence () {

	/*
	 * This loop checks all counter values to be in
	 * sequence, starting with 0, as specified in the
	 * SCORM-1.2 RTE spec section 3.4.3 "Handling Lists"
	 */

	for (int i = 0; i < sc.numCounters(); i++) {
		String cnt = sc.getCounter(i);
		Object o = cmi.get (cnt);
		int mx = o == null ? 0 : Integer.parseInt(o.toString());
		int now = Integer.parseInt(sc.getIndex(i));
		if (now > mx) {
			error = ESYNTAX;
			return;
		}
	}
}

public	final void sysput (String l, String r) {

	sc.check(l);
	if (!sc.getCounter().equals (l)) {
		if (sc.numCounters()>0) {
			cntd.put (sc.getArray(sc.numCounters()-1), "1");
		}
	} 

	cmi.put (l, r);
}

public	final void sysput (Hashtable h) {
	for (java.util.Enumeration e = h.keys(); e.hasMoreElements(); ) {
		String l = e.nextElement().toString();
		String r = (String) h.get (l);
		sysput (l, r);
	}
}

public	final String sysget (String l) {
	sc.check (l);
	Object o = cmi.get(l);
	if (l.equals (sc.getCounter()) && o == null) {
		return "0";
	}


	return (o == null) ? "" : o.toString();
}

public	final String get (String l) {

	boolean R,W;

	String ename;

	error = ENONE;

	if (!sc.check (l)) {
		error = ESYNTAX;
		return "";
	}

	ename = sc.getElement();

	R = ac.isReadable(l);
	error = ac.getError();

	/*
	 * In some cases the several different errorcodes are possible,
	 * the SCORM does not clearly specify this. However, considering
	 * that the ADL TestSuite comes from the same place as the SCORM,
	 * we will consider the TestSuite as a part of the SCORM, and not
	 * ignore its warnings.  ADL testsuite 1.2.6 expects the following 
	 * errorcode precedence:
	 */
	if (!R) {
		if (l.endsWith ("._children")) error = ENOCHLD;
		if (l.endsWith ("._count"))    error = ENOCNT;
	}

	if (error != ENONE) return "";

	W = ac.isWriteable(l);

	if (W && !R) {
		error = EWRONLY;
		return "";
	}

	/*
	 * If it is not readable and not writeable it does not exist.
	 * The element name is syntactically correct, so we set the
	 * error to "not an array" if it contains a ".n" field.
	 */

	if (!R && !W) {
		if (!l.equals(ename)) {
			error = ENOCNT;
		} else {
			error = ESYNTAX;
		}
		return "";
	}

	/*
	 * Return "0" for uninitialized counter elements. We initialize
	 * the counters like cmi.core.interactions.count to "0", but we
	 * can't guess counters inside arrays. For these we return "0".
	 */

	Object o = cmi.get(l);

	if (l.equals (sc.getCounter())) {
		return (o == null) ? "0" : o.toString();
	}

	/*
	 * Check for *._children elements
	 */

	Object cho = cmi.get (ename);
	if (cho != null) {
		return cho.toString();
	}

	return (o == null) ? "" : o.toString();
}

public	final void reset () {
	cmi.clear();
	CMI.init (cmi);
	cntd.clear();
	error = ENONE;
}

/*
 * Methods to provide differential commit capability
 */

public	final void transBegin () {
	synchronized (cmi) {
		trans.clear();	
		trNew.clear();	
		trMod.clear();	
		for (Enumeration e = cmi.keys(); e.hasMoreElements(); ) {
			Object k = e.nextElement();
			trans.put (k, cmi.get(k));
		}
	}
}

public	final void transEnd () {
	synchronized (cmi) {
		for (Enumeration e = cmi.keys(); e.hasMoreElements(); ) {
			Object k = e.nextElement();
			Object o = cmi.get(k);
			Object t = trans.get(k);
			if (t == null) {
				trNew.put (k, o);
				continue;
			} else {
				if (!o.toString().equals(t.toString())) {
					trMod.put (k, o);
				}
			}
		}
	}
}

public	final Hashtable getTransNew () {
	Hashtable rv = new Hashtable();
	for (Enumeration e = trNew.keys(); e.hasMoreElements(); ) {
		Object k = e.nextElement();
		rv.put (k, cmi.get(k));
	}
	return rv;
}

public	final Hashtable getTransMod () {
	Hashtable rv = new Hashtable();
	for (Enumeration e = trMod.keys(); e.hasMoreElements(); ) {
		Object k = e.nextElement();
		rv.put (k, cmi.get(k));
	}
	return rv;
}


}
