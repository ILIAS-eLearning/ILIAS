/*
 * ch/ethz/pfplms/scorm/cmi/CMI.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to handle SCORM-1.2 cmi value initialization etc.
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

public	class CMI {

public final static void init (java.util.Hashtable h) {

	/*
	 * Load _children elements
	 */

	h.put ("cmi.objectives.n.score._children",
	         "raw,max,min");
	h.put ("cmi.interactions._children",
	         "id,objectives,time,type,correct_responses,weighting,student_response,result,latency");
	h.put ("cmi.student_preference._children",
	         "audio,language,speed,text");
	h.put ("cmi.core.score._children",
	         "raw,max,min");
	h.put ("cmi.objectives._children",
	         "id,score,status");
	h.put ("cmi.student_data._children",
	         "mastery_score,max_time_allowed,time_limit_action");
	h.put ("cmi.core._children",
	         "student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time");

	/*
	 * Load default values
	 */

	h.put ("cmi.core.credit", "no-credit");
	h.put ("cmi.core.lesson_status", "not attempted");
	h.put ("cmi.core.entry", "ab-initio");
	h.put ("cmi.core.total_time", "0000:00:00.00");
	h.put ("cmi.core.lesson_mode", "browse");
	h.put ("cmi.student_data.time_limit_action", "continue,no message");
	h.put ("cmi.student_preference.audio", "0");
	h.put ("cmi.student_preference.speed", "0");
	h.put ("cmi.student_preference.text", "0");
}
public final static void cput (java.util.Hashtable h, String l, String r) {

	if (r.length()==0) return;
	h.put (l, r);
}

}
