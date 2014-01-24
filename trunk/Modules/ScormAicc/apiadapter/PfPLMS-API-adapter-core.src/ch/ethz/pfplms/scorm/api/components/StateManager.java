/*
 * ch/ethz/pfplms/scorm/api/components/StateManager.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to manage SCORM-1.2 RTE state
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

public	class StateManager {

	public	static	final	int ENONE    = 0;
	public	static	final	int EGENERAL = 101;
	public	static	final	int ENOINIT  = 301;

	public	static	final	int LMSInitialize     = 0;
	public	static	final	int LMSSetValue       = 1;
	public	static	final	int LMSGetValue       = 2;
	public	static	final	int LMSGetLastError   = 3;
	public	static	final	int LMSGetErrorString = 4;
	public	static	final	int LMSGetDiagnostic  = 5;
	public	static	final	int LMSCommit         = 6;
	public	static	final	int LMSFinish         = 7;


	private	static	final	int ISNOTINITIALIZED = 0;
	private	static	final	int ISINITIALIZED    = 1;
	private	static	final	int ISFINISHED       = 2;
	private	static	final	int ISERROR          = 3;

	private	static	int state = ISNOTINITIALIZED; 
	private	static	int ecode = ENONE;

public	StateManager	() {
	init();
}

private	static void init () {
	state = ISNOTINITIALIZED;
}

public	void reset () {
	state = ISNOTINITIALIZED;
	ecode = ENONE;
}

public	static	boolean isInitialized () {
	return (state == ISINITIALIZED);
}

public	static int getErrorCode (){
	return ecode;
}

public	static boolean processEvent (int ns) {

	/*
	 * SCORM-1.2 runtime API state machine
	 */

	switch (ns) {
		case LMSInitialize: 
			switch (state) {
				case ISNOTINITIALIZED: {
					state = ISINITIALIZED;
					ecode = ENONE;
					break;
				}
				case ISINITIALIZED: {
					state = ISERROR;
					ecode = EGENERAL;
					break;
				}
				case ISFINISHED: {
					state = ISINITIALIZED;
					ecode = ENONE;
					break;
				}
				default: {
					state = ISERROR;
					ecode = EGENERAL;
					return false;
				}
			}
			break;

		case LMSSetValue:
		case LMSGetValue:
		case LMSCommit: 
			switch (state) {
				case ISNOTINITIALIZED: {
					ecode = ENOINIT;
					return false;
				}
				case ISINITIALIZED: {
					break;
				}
				default: {
					state = ISERROR;
					return false;
				}
			}
			break;

		case LMSGetLastError:
		case LMSGetErrorString:
		case LMSGetDiagnostic: 
			switch (state) {
				case ISNOTINITIALIZED: {
					ecode = ENOINIT;
					state = ISERROR;
					return false;
				}
				case ISINITIALIZED:
				case ISERROR: {
					break;
				}
				default: {
					state = ISERROR;
					ecode = EGENERAL;
					return false;
				}
			}
			break;

		case LMSFinish: 
			switch (state) {
				case ISNOTINITIALIZED: {
					ecode = ENOINIT;
					return false;
				}
				case ISINITIALIZED: {
					state = ISFINISHED;
					ecode = EGENERAL;
					return true;
				}
				default: {
					state = ISERROR;
					ecode = EGENERAL;
					return false;
				}
			}
			// FALLTHROUGH

		default: { 
			return false;
		}
	}
	return true;
}

}
