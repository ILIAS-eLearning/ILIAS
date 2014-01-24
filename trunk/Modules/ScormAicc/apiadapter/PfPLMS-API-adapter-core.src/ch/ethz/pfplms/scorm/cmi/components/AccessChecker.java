/*
 * ch/ethz/pfplms/scorm/cmi/AccessChecker.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to handle SCORM-1.2 cmi R/W access restrictions
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

package ch.ethz.pfplms.scorm.cmi.components;

import java.util.Hashtable;
import gnu.regexp.*;

public	class	AccessChecker {

public  static final int ENONE   = 0;
public  static final int ENOCNT  = 203;
public  static final int ENOIMP  = 401;
public  static final int EISKEY  = 402;
public  static final int ERDONLY = 403;
public  static final int EWRONLY = 404;

private static int error = ENONE;

public static int getError () {
	return error;
}

private static Hashtable tabu      = new Hashtable (); // Non accessible elements
private static Hashtable readable  = new Hashtable (); // Readable elements
private static Hashtable writeable = new Hashtable (); // Writeable elements

private static RE tabuRegexp0; // Regexps of non accessible elements

/*
 * Regexp handlers for numbered readable elements
 */

private static RE readable0; // cmi.objectives.n.id
private static RE readable1; // cmi.objectives.n.score._children
private static RE readable2; // cmi.objectives.n.score.raw
private static RE readable3; // cmi.objectives.n.score.max
private static RE readable4; // cmi.objectives.n.score.min
private static RE readable5; // cmi.objectives.n.status
private static RE readable6; // cmi.interactions.n.objectives
private static RE readable7; // cmi.interactions.n.objectives._count
private static RE readable8; // cmi.interactions.n.correct_responses
private static RE readable9; // cmi.interactions.n.correct_responses._count

/*
 * Regexp handlers for numbered writeable elements
 */

private static RE writeable0; // cmi.objectives.n.id
private static RE writeable1; // cmi.objectives.n.score.raw
private static RE writeable2; // cmi.objectives.n.score.max
private static RE writeable3; // cmi.objectives.n.score.min
private static RE writeable4; // cmi.objectives.n.status
private static RE writeable5; // cmi.interactions.n.id
private static RE writeable6; // cmi.interactions.n.objectives.n.id
private static RE writeable7; // cmi.interactions.n.time
private static RE writeable8; // cmi.interactions.n.type
private static RE writeable9; // cmi.interactions.n.correct_responses.n.pattern
private static RE writeable10; // cmi.interactions.n.weighting
private static RE writeable11; // cmi.interactions.n.student_response
private static RE writeable12; // cmi.interactions.n.result
private static RE writeable13; // cmi.interactions.n.latency


public	AccessChecker () {


	/*
	 * Create regexps of non accessible keyword elements
	 */

	try {
		tabuRegexp0 = new RE ("^cmi.objectives.\\d+.score$");

	} catch (Exception e) {}

	/*
	 * Set up list of non accessible keyword elements
	 */

	tabu.put ("cmi.core", "1");
	tabu.put ("cmi.core.score", "1");
	tabu.put ("cmi.objectives", "1");
	tabu.put ("cmi.student_data", "1");
	tabu.put ("cmi.student_preference", "1");
	tabu.put ("cmi.interactions", "1");

	/*
	 * Create regexps of readable elements
	 */

	try {
		readable0 = new RE ("^cmi.objectives.\\d+.id$", RE.REG_NOTEOL);
		readable1 = new RE ("^cmi.objectives.\\d+.score._children$", RE.REG_NOTEOL);
		readable2 = new RE ("^cmi.objectives.\\d+.score.raw$", RE.REG_NOTEOL);
		readable3 = new RE ("^cmi.objectives.\\d+.score.max$", RE.REG_NOTEOL);
		readable4 = new RE ("^cmi.objectives.\\d+.score.min$", RE.REG_NOTEOL);
		readable5 = new RE ("^cmi.objectives.\\d+.status$", RE.REG_NOTEOL);
		readable6 = new RE ("^cmi.interactions.\\d+.objectives$", RE.REG_NOTEOL);
		readable7 = new RE ("^cmi.interactions.\\d+.objectives._count$", RE.REG_NOTEOL);
		readable8 = new RE ("^cmi.interactions.\\d+.correct_responses$", RE.REG_NOTEOL);
		readable9 = new RE ("^cmi.interactions.\\d+.correct_responses._count$", RE.REG_NOTEOL);

	} catch (Exception e) {}

	/*
	 * Set up list of readable elements
	 */

	readable.put ("cmi.core._children", "1");
	readable.put ("cmi.core.student_id", "1");
	readable.put ("cmi.core.student_name", "1");
	readable.put ("cmi.core.lesson_location", "1");
	readable.put ("cmi.core.credit", "1");
	readable.put ("cmi.core.lesson_status", "1");
	readable.put ("cmi.core.entry", "1");
	readable.put ("cmi.core.score._children", "1");
	readable.put ("cmi.core.score.raw", "1");
	readable.put ("cmi.core.score.max", "1");
	readable.put ("cmi.core.score.min", "1");
	readable.put ("cmi.core.total_time", "1");
	readable.put ("cmi.core.lesson_mode", "1");
	readable.put ("cmi.suspend_data", "1");
	readable.put ("cmi.launch_data", "1");
	readable.put ("cmi.comments", "1");
	readable.put ("cmi.comments_from_lms", "1");
	readable.put ("cmi.objectives._children", "1");
	readable.put ("cmi.objectives._count", "1");
	readable.put ("cmi.student_data._children", "1");
	readable.put ("cmi.student_data.mastery_score", "1");
	readable.put ("cmi.student_data.max_time_allowed", "1");
	readable.put ("cmi.student_data.time_limit_action", "1");
	readable.put ("cmi.student_preference._children", "1");
	readable.put ("cmi.student_preference.audio", "1");
	readable.put ("cmi.student_preference.language", "1");
	readable.put ("cmi.student_preference.speed", "1");
	readable.put ("cmi.student_preference.text", "1");
	readable.put ("cmi.interactions._children", "1");
	readable.put ("cmi.interactions._count", "1");

	/*
	 * Create regexps of numbered writable elements
	 */

	try {
		writeable0 = new RE ("^cmi.objectives.\\d+.id$", RE.REG_NOTEOL);
		writeable1 = new RE ("^cmi.objectives.\\d+.score.raw$", RE.REG_NOTEOL);
		writeable2 = new RE ("^cmi.objectives.\\d+.score.max$", RE.REG_NOTEOL);
		writeable3 = new RE ("^cmi.objectives.\\d+.score.min$", RE.REG_NOTEOL);
		writeable4 = new RE ("^cmi.objectives.\\d+.status$", RE.REG_NOTEOL);
		writeable5 = new RE ("^cmi.interactions.\\d+.id$", RE.REG_NOTEOL);
		writeable6 = new RE ("^cmi.interactions.\\d+.objectives.\\d+.id$", RE.REG_NOTEOL);
		writeable7 = new RE ("^cmi.interactions.\\d+.time$", RE.REG_NOTEOL);
		writeable8 = new RE ("^cmi.interactions.\\d+.type$", RE.REG_NOTEOL);
		writeable9 = new RE ("^cmi.interactions.\\d+.correct_responses.\\d+.pattern$", RE.REG_NOTEOL);
		writeable10 = new RE ("^cmi.interactions.\\d+.weighting$", RE.REG_NOTEOL);
		writeable11 = new RE ("^cmi.interactions.\\d+.student_response$", RE.REG_NOTEOL);
		writeable12 = new RE ("^cmi.interactions.\\d+.result$", RE.REG_NOTEOL);
		writeable13 = new RE ("^cmi.interactions.\\d+.latency$", RE.REG_NOTEOL);

	} catch (Exception e) {}

	/*
	 * Set up list of writeable elements
	 */

	writeable.put ("cmi.core.lesson_location", "1");
	writeable.put ("cmi.core.lesson_status", "1");
	writeable.put ("cmi.core.score.raw", "1");
	writeable.put ("cmi.core.score.max", "1");
	writeable.put ("cmi.core.score.min", "1");
	writeable.put ("cmi.core.exit", "1");
	writeable.put ("cmi.core.session_time", "1");
	writeable.put ("cmi.suspend_data", "1");
	writeable.put ("cmi.comments", "1");
	writeable.put ("cmi.student_preference.audio", "1");
	writeable.put ("cmi.student_preference.language", "1");
	writeable.put ("cmi.student_preference.speed", "1");
	writeable.put ("cmi.student_preference.text", "1");
}

public  boolean isReadable (String l) {

	error = ENONE;

	if (readable.containsKey (l))  return true;

	if (tabuRegexp0.getMatch (l) != null) {
		error = EISKEY;
		return false;
	}

	if (tabu.containsKey (l)) {
		error = EISKEY;
		return false;
	}

	if (readable0.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable1.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable2.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable3.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable4.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable5.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable6.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable7.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable8.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	if (readable9.getMatch (l) != null) {
		readable.put (l, "1");
		return true;
	}
	return false;
}

public  boolean isWriteable (String l) {

	error = ENONE;

	if (writeable.containsKey (l))  return true;

	if (tabuRegexp0.getMatch (l) != null) {
		error = EISKEY;
		return false;
	}

	if (tabu.containsKey (l)) {
		error = EISKEY;
		return false;
	}

	if (writeable0.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable1.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable2.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable3.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable4.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable5.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable6.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable7.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable8.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable9.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable10.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable11.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable12.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	if (writeable13.getMatch (l) != null) {
		writeable.put (l, "1");
		return true;
	}
	return false;
}
}

