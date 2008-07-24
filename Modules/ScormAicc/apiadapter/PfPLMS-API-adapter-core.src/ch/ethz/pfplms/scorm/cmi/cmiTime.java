package ch.ethz.pfplms.scorm.cmi;

/*
 * ch/ethz/pfplms/scorm/cmi/cmiTime.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to handle SCORM-1.2 CMITime and CMITimespan values
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


import java.util.StringTokenizer;

public	class cmiTime {

	private int h;
	private int m;
	private int s;
	private int ms;

public	cmiTime (String t) {

	try {
		StringTokenizer st = new StringTokenizer (t, ":");
		h = Integer.parseInt (st.nextToken());
		m = Integer.parseInt (st.nextToken());
		String r = st.nextToken();
		st = new StringTokenizer (r, ".");
		s = Integer.parseInt (st.nextToken());
		ms = st.hasMoreTokens() ? Integer.parseInt (st.nextToken()) : 0;

	} catch (Exception e) {
		h = 0; m = 0; s = 0; ms = 0;
	}
}

public	String getTotal (String t) {

	cmiTime tt = new cmiTime (t);

	for (ms += tt.ms; ms >= 100; ms -= 100, s++);
	for (s += tt.s; s >= 60; s -= 60, m++);
	for (m += tt.m; m >= 60; m -= 60, h++);

	h += tt.h;

	if (h > 9999) {
		h  = 9999;
		m  = 59;
		s  = 59;
		ms = 99;
	}
	
	String rv = new String();

	if (h < 10) rv += "0";
	if (h < 100) rv += "0";
	if (h < 1000) rv += "0";
	rv += h + ":";

	if (m < 10) rv += "0";
	rv += m + ":";

	if (s < 10) rv += "0";
	rv += s;

	if (ms > 0) {
		rv += ".";
		if (ms < 10) rv += "0";
		rv +=ms;
	}

	return rv;
}

}
