/*
 * ch/ethz/pfplms/scorm/api/components/ErrorHandler
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to handle SCORM-1.2 RTE Errors
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

package ch.ethz.pfplms.scorm.api.components;

import java.util.Hashtable;

public	class ErrorHandler {

	private	static Hashtable en = new Hashtable();
	private static int error = 0;
	private static String diag = "";

public	ErrorHandler () {
	en.put ("0",   "No Error");
	en.put ("101", "General Exception");
	en.put ("201", "Invalid argument error");
	en.put ("202", "Element cannot have children");
	en.put ("203", "Element not an array - Cannot have count");
	en.put ("301", "Not initialized");
	en.put ("401", "Not implemented error");
	en.put ("402", "Invalid set value, element is a keyword");
	en.put ("403", "Element is read only");
	en.put ("404", "Element is write only");
	en.put ("405", "Incorrect Data Type");
}

public	String getErrorString (String ec) {
	if (ec == null) return "";
	Object rv = en.get (ec);
	return (rv == null) ? "" : rv.toString();
}

public	String getErrorString (int ec) {
	Object rv = en.get (Integer.toString(ec));
	return (rv == null) ? "" : rv.toString();
}

public	String getErrorString () {
	return "";
}

public	void setErrorCode (int ec) {
	error = ec;
}

public	String getErrorCode () {
	return Integer.toString(error);
}

public	String getDiagnostic () {
	return diag;
}

public	void setDiagnostic (String d) {
	diag = d;
}

public	boolean isError () {
	return (error != 0);
}

public	void	reset () {
	error = 0;
	diag = "";
}

}
