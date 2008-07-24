/*
 * ch/ethz/pfplms/scorm/cmi/components/SyntaxChecker.java
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class that deals with SCORM-1.2 CMI datamodel element names
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

public	class SyntaxChecker {

        private static RE idx, sep, tok, fbt;
	private static String counter;
	private static String element;
	private static String array;
	private static String fbtype;

	private static String[] sindex;
	private static String[] sarray;
	private static String[] scounter;

static {
	try {
		idx = new RE ("(\\d+)");
		sep = new RE ("\\.");
		tok = new RE ("(\\d+|[a-z_]+)");
		fbt = new RE ("^cmi\\.interactions\\.(\\d+)\\.", RE.REG_NOTEOL);

	} catch (Exception e) { }
}

public	SyntaxChecker () {
}

public	String getElement () {
	return element;
}

public	String getCounter () {
	return counter;
}

public	int numCounters () {
	return sindex == null ? 0 : sindex.length;
}

public	String getIndex (int i) {
	return (sindex == null || sindex.length <= i) ? "0" : sindex[i];
}

public	String getCounter (int i) {
	return (scounter == null || scounter.length <= i) ? null : scounter[i];
}

public	String getArray (int i) {
	return (sarray == null || sarray.length <= i) ? null : sarray[i];
}

public	String getFeedbackType () {
	return fbtype;
}

public	boolean check (String l) {

	element = ""; // The element's name with 'n' for array indices
	counter = ""; // The array element's *._count element name
	array   = ""; // The element's array name, ends with array index
	fbtype  = ""; // Feedback type, vocabulary of CMIFeedback

	sindex    = null;
	scounter  = null;

	if (l == null) {
		return false;
	}

	/*
	 * Determine element name, array name and counter element name
	 */

	REMatch[] m = idx.getAllMatches (l);
	REMatch[] s = sep.getAllMatches (l);
	REMatch[] t = tok.getAllMatches (l);

	if (s.length < 1) return false;

	String tail = l.substring (s[s.length-1].getStartIndex()+1, l.length());

	if (m.length == 0) {
		element = l;
		if (tail.equals ("_count")) { 
			counter = l;
		}
	}

	if (m.length > 0) {
		sindex = new String [m.length];
		scounter = new String [m.length];
		sarray = new String [m.length];
	}
	for (int i=0; i<m.length; i++) {
		sindex[i] = l.substring(
				m[i].getStartIndex(i),
				m[i].getEndIndex(i)
			    );
		scounter[i] = l.substring(0, m[i].getStartIndex(i)) + "_count";
		sarray[i] = l.substring(0, m[i].getEndIndex(i));
	}

	for (int i=0; i<=m.length; i++) {


		if (i==m.length) {

			if (i > 1) { 
				element += l.substring(
						m[m.length-1].getEndIndex(i-1),
						l.length()
					   );
				array   += l.substring(
						m[m.length-1].getStartIndex(i-1),
						m[m.length-1].getEndIndex(i-1)
					   );
			} 
			if (i == 1) {

				if (tail.equals ("_count")) {

					counter   += l.substring(
						m[0].getStartIndex(),
						l.length()
					);
				} else {
					counter += "_count";
				}

				element += l.substring(
						m[0].getEndIndex(i-1),
						l.length()
					   );
				array   += l.substring(
						m[0].getStartIndex(i),
						m[0].getEndIndex(i)
					   );
			}
			break;
		}

		if (i>0) {
			element += l.substring(
					m[i-1].getEndIndex(i-1),
					m[i].getStartIndex(i)
				   );
			if (i<m.length) {
				counter += l.substring(
						m[i-1].getEndIndex(i-1),
						m[i].getStartIndex(i)
					   );
				counter += "_count";

				array   += l.substring(
						m[i-1].getEndIndex(i-1),
						m[i].getStartIndex(i)
					   );

			}
		} else {
			element += l.substring(0,m[i].getStartIndex(i));
			if (i<m.length-1) {
				counter += l.substring(0,m[i].getEndIndex(i));
				array   += l.substring(0,m[i].getEndIndex(i));
			} else {
				counter += l.substring(0,m[i].getStartIndex(i));
				array   += l.substring(0,m[i].getStartIndex(i));
			}
		}
		element += "n";
	}
	

	if (s.length+1 == t.length) {

		/*
		 * Syntax of element name is ok. Determine feedback-type
		 * element name in case of cmi.interaction.n.* type
		 */

		REMatch fb = fbt.getMatch (l);

		if (fb != null) {
			fbtype = fb.toString() + "type";
		} else {
			fbtype = "";
		}

		return true;
	} 

	return false;
}


}
