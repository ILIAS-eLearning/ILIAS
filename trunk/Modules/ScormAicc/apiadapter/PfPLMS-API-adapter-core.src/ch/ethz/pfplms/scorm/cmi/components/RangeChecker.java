/*
 * ch/ethz/pfplms/scorm/cmi/RangeChecker.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to handle SCORM-1.2 cmi value range limits
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

import gnu.regexp.*;
import java.util.Hashtable;

public	class RangeChecker {

	private static Hashtable min = new Hashtable();
	private static Hashtable max = new Hashtable();

public	RangeChecker () {

	min.put ("cmi.core.score.raw", new Float ("0"));
	max.put ("cmi.core.score.raw", new Float ("100"));

	min.put ("cmi.core.score.max", new Float ("0"));
	max.put ("cmi.core.score.max", new Float ("100"));

	min.put ("cmi.core.score.min", new Float ("0"));
	max.put ("cmi.core.score.min", new Float ("100"));

	min.put ("cmi.objectives.n.score.raw", new Float ("0"));
	max.put ("cmi.objectives.n.score.raw", new Float ("100"));

	min.put ("cmi.objectives.n.score.max", new Float ("0"));
	max.put ("cmi.objectives.n.score.max", new Float ("100"));

	min.put ("cmi.objectives.n.score.min", new Float ("0"));
	max.put ("cmi.objectives.n.score.min", new Float ("100"));

	min.put ("cmi.student_preference.audio", new Float ("-1"));
	max.put ("cmi.student_preference.audio", new Float ("100"));

	min.put ("cmi.student_preference.speed", new Float ("-100"));
	max.put ("cmi.student_preference.speed", new Float ("100"));

	min.put ("cmi.student_preference.text", new Float ("-1"));
	max.put ("cmi.student_preference.text", new Float ("1"));

}

public	boolean check (String el, String val) {

	Float nval;
	Float nmin;
	Float nmax;

	Object omin = min.get(el);
	Object omax = max.get(el);

	if (omin == null || omax == null) {
		return true;
	}

	try {
		nval = new Float (val);
		nmin = (Float) omin;
		nmax = (Float) omax;
	} catch (Exception e) {
		return false;
	}

	return (
		nval.floatValue() >= nmin.floatValue() &&
		nval.floatValue() <= nmax.floatValue()
	);
}

}
