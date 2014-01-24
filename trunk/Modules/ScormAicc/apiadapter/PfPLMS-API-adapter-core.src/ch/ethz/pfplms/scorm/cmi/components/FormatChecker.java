/*
 * ch/ethz/pfplms/scorm/cmi/FormatChecker.java 
 * This file is part of the PfPLMS SCORM-1.2 API-adapter core
 * A class to handle SCORM-1.2 cmi value formats
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

public	class FormatChecker {

	private static Hashtable regexp = new Hashtable();

	private static RE RE_Feedback = null;
	private static Hashtable regexpFeedback = new Hashtable();

	private static RE RE_CMIBlank;
	private static RE RE_CMIBoolean;
	private static RE RE_CMIDecimal;
	private static RE RE_CMIIdentifier;
	private static RE RE_CMIInteger;
	private static RE RE_CMISInteger;
	private static RE RE_CMIString255;
	private static RE RE_CMIString4096;
	private static RE RE_CMITime;
	private static RE RE_CMITimespan;

	private static RE RE_choice;
	private static RE RE_fill_in;
	private static RE RE_likert;
	private static RE RE_matching;
	private static RE RE_numeric;
	private static RE RE_performance;
	private static RE RE_sequencing;
	private static RE RE_true_false;

	private static RE RE_Credit;
	private static RE RE_Entry;
	private static RE RE_Exit;
	private static RE RE_Interaction;
	private static RE RE_Mode;
	private static RE RE_Result;
	private static RE RE_Status;
	private static RE RE_TimeLimitAction;

public	FormatChecker () {

	try {
		RE_CMIBlank = new RE ("", RE.REG_NOTEOL);
		RE_CMIBoolean = new RE ("^(true|false)$", RE.REG_NOTEOL);
		RE_CMIDecimal = new RE ("^[-+]??(\\d+)??(\\.)??(\\d)+$", RE.REG_NOTEOL);
		RE_CMIIdentifier = new RE ("^\\S{1,255}$", RE.REG_NOTEOL);
		RE_CMIInteger = new RE ("^\\d{1,5}$", RE.REG_NOTEOL);
		RE_CMISInteger = new RE ("^[-+]??\\d{1,5}$", RE.REG_NOTEOL);
		RE_CMIString255 = new RE ("^[\\S\\s]{0,255}$", RE.REG_NOTEOL);
		RE_CMIString4096 = new RE ("^[\\S\\s]{0,4096}$", RE.REG_NOTEOL);
		RE_CMITime = new RE ("^(0[0-9]|1[0-9]|2[1-3]):[0-5]\\d:[0-5]\\d(\\.((\\d)|(\\d\\d)))*$", RE.REG_NOTEOL);
		RE_CMITimespan = new RE ("^(\\d){2,4}:(\\d\\d)\\:(\\d\\d)((\\.)((\\d)|(\\d\\d)))*$", RE.REG_NOTEOL);

		RE_choice = new RE ("^([a-z\\d])?((,)([a-z\\d]))*$|^{([a-z\\d])??((,)([a-z\\d]))*}$", RE.REG_NOTEOL);
		RE_fill_in = new RE ("^[\\S\\s]{0,255}$", RE.REG_NOTEOL);
		RE_likert = new RE ("^[a-z0-9]?$", RE.REG_NOTEOL);
		RE_matching = new RE ("^([a-z\\d]\\.[a-z\\d])??(,([a-z\\d]\\.[a-z\\d]))*$|^{([a-z\\d]\\.[a-z\\d])??(,([a-z\\d]\\.[a-z\\d]))*}$", RE.REG_NOTEOL);
		RE_numeric = new RE ("^[-+]??(\\d+)??(\\.)??(\\d)+$", RE.REG_NOTEOL);
		RE_performance = new RE ("^[\\S\\s]{0,255}$", RE.REG_NOTEOL);
		RE_sequencing = new RE ("^([a-z\\d])??(,[a-z\\d])*$", RE.REG_NOTEOL);
		RE_true_false = new RE ("^(t|f|0|1)$", RE.REG_NOTEOL);

		RE_Credit = new RE ("^(credit|no-credit)$", RE.REG_NOTEOL);
		RE_Entry = new RE ("^(ab-initio|resume)??$", RE.REG_NOTEOL);
		RE_Exit = new RE ("^(time-out|suspend|logout)??$", RE.REG_NOTEOL);
		RE_Interaction = new RE ("^(true-false|choice|fill-in|matching|performance|likert|sequencing|numeric)$", RE.REG_NOTEOL);
		RE_Mode = new RE ("^(normal|review|browse)$", RE.REG_NOTEOL);
		RE_Result = new RE ("^(correct|wrong|unanticipated|neutral|([+-]?\\d+)\\.?\\d*)$", RE.REG_NOTEOL);
		RE_Status = new RE ("^(passed|completed|failed|incomplete|browsed|not attempted)$", RE.REG_NOTEOL);
		RE_TimeLimitAction = new RE ("^(exit,message|exit,no message|continue,message|continue,no message)$", RE.REG_NOTEOL);

	} catch (Exception e) {}

	regexpFeedback.put ("choice", RE_choice);
	regexpFeedback.put ("fill-in", RE_fill_in);
	regexpFeedback.put ("likert", RE_likert);
	regexpFeedback.put ("matching", RE_matching);
	regexpFeedback.put ("numeric", RE_numeric);
	regexpFeedback.put ("performance", RE_performance);
	regexpFeedback.put ("sequencing", RE_sequencing);
	regexpFeedback.put ("true-false", RE_true_false);

	regexp.put ("cmi.core.student_id", RE_CMIIdentifier);
	regexp.put ("cmi.core.student_name", RE_CMIString255);
	regexp.put ("cmi.core.lesson_location", RE_CMIString255);
	regexp.put ("cmi.core.credit", RE_Credit);
	regexp.put ("cmi.core.lesson_status", RE_Status);
	regexp.put ("cmi.core.entry", RE_Entry);
	regexp.put ("cmi.core.score.raw", RE_CMIDecimal);
	regexp.put ("cmi.core.score.max", RE_CMIDecimal);
	regexp.put ("cmi.core.score.min", RE_CMIDecimal);
	regexp.put ("cmi.core.total_time", RE_CMITimespan);
	regexp.put ("cmi.core.lesson_mode", RE_Mode);
	regexp.put ("cmi.core.exit", RE_Exit);
	regexp.put ("cmi.core.session_time", RE_CMITimespan);
	regexp.put ("cmi.suspend_data", RE_CMIString4096);
	regexp.put ("cmi.launch_data", RE_CMIString4096);
	regexp.put ("cmi.comments", RE_CMIString4096);
	regexp.put ("cmi.comments_from_lms", RE_CMIString4096);
	regexp.put ("cmi.objectives.n.id", RE_CMIIdentifier);
	regexp.put ("cmi.objectives.n.score.raw", RE_CMIDecimal);
	regexp.put ("cmi.objectives.n.score.max", RE_CMIDecimal);
	regexp.put ("cmi.objectives.n.score.min", RE_CMIDecimal);
	regexp.put ("cmi.objectives.n.status", RE_Status);
	regexp.put ("cmi.student_data.mastery_score", RE_CMIDecimal);
	regexp.put ("cmi.student_data.max_time_allowed", RE_CMITimespan);
	regexp.put ("cmi.student_data.time_limit_action", RE_TimeLimitAction);
	regexp.put ("cmi.student_preference.audio", RE_CMISInteger);
	regexp.put ("cmi.student_preference.language", RE_CMIString255);
	regexp.put ("cmi.student_preference.speed", RE_CMISInteger);
	regexp.put ("cmi.student_preference.text", RE_CMISInteger);
	regexp.put ("cmi.interactions.n.id", RE_CMIIdentifier);
	regexp.put ("cmi.interactions.n.objectives.n.id", RE_CMIIdentifier);
	regexp.put ("cmi.interactions.n.time", RE_CMITime);
	regexp.put ("cmi.interactions.n.type", RE_Interaction);
	regexp.put ("cmi.interactions.n.weighting", RE_CMIDecimal);
	regexp.put ("cmi.interactions.n.result", RE_Result);
	regexp.put ("cmi.interactions.n.latency", RE_CMITimespan);
}

public	void setFeedbackType (String fb) {

	RE_Feedback = null;
	if (!fb.equals("")) {
		Object o = regexpFeedback.get (fb);
		if (o != null) {
			RE_Feedback = (RE) o;
		}
	}
}

public	boolean check (String el, String val) {

	RE tr;
	if (el.equals("cmi.interactions.n.correct_responses.n.pattern") ||
	    el.equals("cmi.interactions.n.student_response") ){
		tr = RE_Feedback;
	} else {
		tr = (RE) regexp.get (el);
	}

	if (tr == null) return false;

	if (tr.getMatch (val) != null) return true;

	return false;
}
}
