<?php
	
/**
 * Appointment-Handler
 * The Appointment-Handler handles all actions with Appointments for the ILIAS3-Calendar.
 *
 * version 1.0
 * @author Christoph Schulz-Sacharov <sch-sa@gmx.de>
 * @author MArtin Schumacher <ilias@auchich.de>
 * @author Mark Ulbrich <Mark_Ulbrich@web.de>
 **/
	
require_once "./classes/Calendar/class.ilAppointment.php";
require_once "./classes/Calendar/class.ilCalDBHandler.php";
require_once "./classes/class.ilObjUser.php";
	
class ilAppointmentHandler
{	
	
	var $appointmentArrayList;
	var $arrayIndex; 
	var $startTimestamp;
	var $endTimestamp;
		
	function getAppointmentArrayList($userId, $startTimestamp, $endTimestamp) {
		$this->deleteOldAppointments();
		$appointmentArrayList = $this->setAppointmentArrayList ($userId, $startTimestamp, $endTimestamp);
		return $appointmentArrayList;
	}
		
	function getSecretAppointmentArrayList($usersArray, $startTimestamp, $endTimestamp) {
		$appointmentArrayList = null;
		$finalAppointmentArrayList = null;
		for ($i=0;$i<count($usersArray);$i++) {
			if ($usersArray[$i] != "" && $usersArray[$i] != null) {
				$appointmentArrayList = array_merge($appointmentArrayList, 
														$this->setAppointmentArrayList($usersArray[$i], $startTimestamp, $endTimestamp));
			}
		}
		for($ii=0;$ii<count($appointmentArrayList);$ii++) {
			
			$resultAppointment = $appointmentArrayList[$ii];
			
			if($resultAppointment->getAccess() == "Public")
			{
				$user = new ilObjUser($resultAppointment->getUserId());
				$resultAppointment->setDescription("-");
				$resultAppointment->setTerm($user->buildFullName()); //$user->data["FirstName"]." ".$user->data["SurName"]);
				$resultAppointment->setLocation("-");
				$finalAppointmentArrayList[] = $resultAppointment;
			}
		} 
		
		if(count($finalAppointmentArrayList) > 0) {
			usort($finalAppointmentArrayList, array("AppointmentHandler", "cmp"));
		}
		return $finalAppointmentArrayList;
	}

	function insertAppointment($user, $appointment) {
		if ($appointment != null) {
			$dbHandler = new ilCalDBHandler();

			$appointmentUnionId = time() + mt_rand(1,1000000) + $appointment->getOwnerId();
//echo "AppHandler:insertApp()<br>";
			for($ii=0;$ii<count($user);$ii++) {
				if ($user[$ii] != "" && $user[$ii] != null) {
//echo "user:".$user[$ii]."<br>";
					$placeHolder = $appointment->getStartTimestamp() + mt_rand(1, 99);
					$currentUser = $user[$ii];
					$dbtable = "cal_appointment";
					$fields = "term, appointmentUnionId, categoryId, priorityId, access, duration, startTimestamp, serial, userId";
					$values = "'".$placeHolder."', 1, 1, 1, 'Private', 0, 0, 0, ".$currentUser;
					$number_of_values = 3;
					$dbHandler->insert($number_of_values, $dbtable, $fields, $values);

					$where = "cal_appointment.term = '".$placeHolder."' AND cal_appointment.userId=".$currentUser;
					$appointmentResultSet = $dbHandler->select($dbtable, "", $where);

					if (count($appointmentResultSet) != 1)
						die("AppointmentHandler::insertAppointment: got not exact one new appointment from DB");
					else {
						$row = $appointmentResultSet->fetchRow();
						$currentAppointmentId = $row[0];
					}
					unset($appointmentResultSet);

					$values =  "cal_appointment.access = '".$appointment->getAccess()."', ".
								  "cal_appointment.categoryId = ".$appointment->getCategoryId().", ".
								  "cal_appointment.description = '".$appointment->getDescription()."', ".
								  "cal_appointment.duration = ".$appointment->getDuration().", ".
								  "cal_appointment.priorityId = ".$appointment->getPriorityId().", ".
								  "cal_appointment.startTimestamp = ".$appointment->getStartTimestamp().", ".
								  "cal_appointment.term = '".$appointment->getTerm()."', ".
								  "cal_appointment.appointmentUnionId = ".$appointmentUnionId.", ".
								  "cal_appointment.location = '".$appointment->getLocation()."', ".
								  "cal_appointment.serial = ".$appointment->getSerial().", ".
								  "cal_appointment.ownerId = ".$appointment->getOwnerId();
								  "cal_appointment.userId = ".$currentUser;
					$whereCondition = "cal_appointment.appointmentId = ".$currentAppointmentId;

					$dbHandler->update($dbtable, $values, $whereCondition);

					if ($appointment->getSerial() == 1) {

						$dbtable = "cal_appointmentrepeats";
						$fields = "appointmentId, endTimestamp, type, weekdays";
						if ($appointment->getSer_stop() == "")
							$ets = 0;
						else
							$ets = $appointment->getSer_stop();
						$values = $currentAppointmentId.", ".$ets.", '".
									 $appointment->getSer_type()."', '".$appointment->getSer_days()."'";
						$number_of_values = 3;
						$dbHandler-> insert($number_of_values, $dbtable, $fields, $values);
					}
				}
			}
		}
	}

	function setAppointmentArrayList($userId, $st, $et) {
		global $appointmentArrayList, $arrayIndex, $startTimestamp, $endTimestamp;
		$startTimestamp = $st;
	   $endTimestamp = $et;
	   $appointmentArrayList = null;
	   $appointmentRepeatsNotTS = null;
		// All appointments of the last 105 days, 'cause the duration can be 99 days, 99 hours and 99 minutes
		$startTimestamp105 = strtotime("-105 days", $startTimestamp); 
		
		$dbtable = "cal_appointment, cal_priority, cal_category";
		
		$where =   "((cal_appointment.userId  = $userId AND ".
					  "cal_appointment.serial = 0 AND ".
					  "((cal_appointment.StartTimestamp BETWEEN $startTimestamp105 AND $endTimestamp) AND ( startTimestamp+(duration*60)>$startTimestamp ))  ) ".
					  "OR (cal_appointment.userId  = $userId AND ".
					  "cal_appointment.serial = 1)) ".
					  "AND cal_priority.priorityId = cal_appointment.priorityId ".
					  "AND cal_category.categoryId = cal_appointment.categoryId";
		$orderBy = "cal_appointment.startTimestamp ASC";
					
		$dbHandler = new ilCalDBHandler();
		$appointmentResultset = $dbHandler->select($dbtable, "cal_appointment.*, cal_priority.term as priTerm, cal_category.term as calTerm", $where, $orderBy);
		if ($appointmentResultset->numRows() > 0) {
			$arrayIndex = 0;
			while($resultAppointment = $appointmentResultset->fetchRow(DB_FETCHMODE_ASSOC)){					
				
				if ($resultAppointment["serial"] == 1) {
					$dbtable = "cal_appointmentrepeats";
					$where = "cal_appointmentrepeats.appointmentId=".$resultAppointment["appointmentId"];
					$appointmentRepeatsResultset = $dbHandler->select($dbtable, "", $where);
					
					if ($appointmentRepeatsResultset->numRows() == 1) {
						$resultAppointmentRepeats = $appointmentRepeatsResultset->fetchRow(DB_FETCHMODE_ASSOC);
						$dbtable = "cal_appointmentrepeatsnot";
						$where = "cal_appointmentrepeatsnot.appointmentRepeatsId=".$resultAppointmentRepeats["appointmentId"];
						$appointmentRepeatsNotResultset = $dbHandler->select($dbtable, "*", $where);
						while($resultAppointmentRepeatsNot = $appointmentRepeatsNotResultset->fetchRow(DB_FETCHMODE_ASSOC)) {
							$appointmentRepeatsNotTS[] = $resultAppointmentRepeatsNot["leaveOutTimestamp"];
						}
					}
					else {
						$resultAppointmentRepeats = null;
						$resultAppointmentRepeatsNot = null;
					}
					
					if ($resultAppointmentRepeats["endTimestamp"] >= $startTimestamp || 
							($resultAppointmentRepeats["endTimestamp"] == "NULL" || 
							 $resultAppointmentRepeats["endTimestamp"] == null || 
							 $resultAppointmentRepeats["endTimestamp"] == "" ||
							 $resultAppointmentRepeats["endTimestamp"] == 0
							)
						) {
					
						$currentTimestamp = $resultAppointment["startTimestamp"];
						$timer = 0;
						/*
							!! Situation hier !!
							$startTimestamp = Periodenanfang
							$endTimestamp = Periodenende
							$currentTimestamp = startTimestamp des Appointments
							$resultAppointment = Aktueller Recordset der cal_appointment
							$resultAppointmentRepeats = Aktueller Recordset der cal_appointmentRepeats
							$appointmentRepeatsNotResultset = Resultset auf die cal_appointmentRepeatsNot
						*/
						if ($resultAppointmentRepeats["type"] == "ser_week") {
							$weekdays = $resultAppointmentRepeats["weekdays"];
							for ($i=0;$i<7;$i++)
							{
								$weekdaysArray[$i] = substr($weekdays, $i, 1);
							}
							$startTimestampZ = $startTimestamp - ($resultAppointment["duration"]*60);
							$splitStartTimestamp = getdate($startTimestampZ);
						   $splitCurrentTimestamp = getdate($currentTimestamp);
						   $sts = $startTimestampZ < ($currentTimestamp+($resultAppointment["duration"]*60))
						   									 ? $currentTimestamp : mktime($splitCurrentTimestamp["hours"],
	   																								$splitCurrentTimestamp["minutes"],
	   																								$splitCurrentTimestamp["seconds"],
	   																								$splitStartTimestamp["mon"],
	   																								$splitStartTimestamp["mday"],
	   																								$splitStartTimestamp["year"]);
							$diffDays = ($endTimestamp - $startTimestampZ)/(24*60*60);
							
							for($i=0;$i<=$diffDays;$i++) {
								$calcTimestamp = strtotime("+".$i." days", $sts);
								$weekdayCalcTS = date("w", $calcTimestamp);
								if ($weekdaysArray[$weekdayCalcTS] == "y" || $weekdaysArray[$weekdayCalcTS] == "Y") {
									$exists = false;
									
									for($ii=0;$ii<count($appointmentRepeatsNotTS);$ii++) {
										if ($appointmentRepeatsNotTS[$ii] == $calcTimestamp)
						    				$exists = True;
									}
									
									$daEndTimestamp = $resultAppointmentRepeats["endTimestamp"] == 0 ? strtotime("+1 year", $startTimestamp) : $resultAppointmentRepeats["endTimestamp"];
									
									if ($exists == false && $calcTimestamp <= $daEndTimestamp) {
										$this->createAppointmentObjectsForDuration($resultAppointment, $calcTimestamp, $resultAppointmentRepeats);
									}
								}
							}
						}
					    
						if ($resultAppointmentRepeats["type"] == "ser_month") {
						   
						   $startTimestampZ = $startTimestamp - ($resultAppointment["duration"]*60);
						   $splitStartTimestamp = getdate($startTimestampZ);
						   $splitCurrentTimestamp = getdate($currentTimestamp);
						   $sts = $startTimestampZ < ($currentTimestamp+($resultAppointment["duration"]*60)) 
						   										 ? $currentTimestamp : mktime($splitCurrentTimestamp["hours"],
						   																				$splitCurrentTimestamp["minutes"],
						   																				$splitCurrentTimestamp["seconds"],
						   																				$splitStartTimestamp["mon"],
						   																				$splitCurrentTimestamp["mday"],
						   																				$splitStartTimestamp["year"]);
						   																								
							$diffMonths = ((date("Y", $endTimestamp) - date("Y", $sts) - 1) * 12) + ((12 - date("m", ($sts>$endTimestamp?$endTimestamp:$sts))) + date("m", $endTimestamp));
							
							unset($i);
							for($i=0;$i<=$diffMonths;$i++) {
								$calcTimestamp = strtotime("+".$i." month", $sts);
								$calcDay = date("d", $calcTimestamp);
								$startDay = date("d", $resultAppointment["startTimestamp"]);
								if($calcDay<$startDay) {
									$tempTimestamp = strtotime("-1 month", $calcTimestamp);
									$numOfDays = date("t", $tempTimestamp);
									$temp = getdate($tempTimestamp);
									$calcTimestamp = mktime($temp["hours"],$temp["minutes"],$temp["seconds"],$temp["mon"],$numOfDays,$temp["year"]);
								}
								
								$exists = false;
								for($ii=0;$ii<count($appointmentRepeatsNotTS);$ii++) {
									if ($appointmentRepeatsNotTS[$ii] == $calcTimestamp)
					    				$exists = True;
								}
								
								$daEndTimestamp = $resultAppointmentRepeats["endTimestamp"] == 0 ? strtotime("+1 year", $startTimestamp) : $resultAppointmentRepeats["endTimestamp"];
								if ($exists == false && $calcTimestamp <= $daEndTimestamp && $calcTimestamp <= $endTimestamp) {
									$this->createAppointmentObjectsForDuration($resultAppointment, $calcTimestamp, $resultAppointmentRepeats);
								}
							}
						}
							
						if ($resultAppointmentRepeats["type"] == "ser_halfayear") {
							$notFound = true;
							// We took a while statement, because of the small count of iterations
							while($notFound && $currentTimestamp <= $endTimestamp) {						   	
						   	if ($currentTimestamp >= ($startTimestamp - ($resultAppointment["duration"]*60)) && $currentTimestamp <= $endTimestamp) {
						   		$notFound = false;
						   		
						   		$exists = False;
									
									$currentDay = date("d", $currentTimestamp);
									$startDay = date("d", $resultAppointment["startTimestamp"]);
									if($currentDay<$startDay) {
										$tempTimestamp = strtotime("-1 month", $currentTimestamp);
										$numOfDays = date("t", $tempTimestamp);
										$temp = getdate($tempTimestamp);
										$currentTimestamp = mktime($temp["hours"],$temp["minutes"],$temp["seconds"],$temp["mon"],$numOfDays,$temp["year"]);
									}
									
									for($ii=0;$ii<count($appointmentRepeatsNotTS);$ii++) {
										if ($appointmentRepeatsNotTS[$ii] == $currentTimestamp)
						    				$exists = True;
									}
									
									$daEndTimestamp = $resultAppointmentRepeats["endTimestamp"] == 0 ? strtotime("+1 year", $startTimestamp) : $resultAppointmentRepeats["endTimestamp"];
									if ($exists == false && $currentTimestamp <= $daEndTimestamp) {
										
										$this->createAppointmentObjectsForDuration($resultAppointment, $currentTimestamp, $resultAppointmentRepeats);
									}	
						   	}
						   	$currentTimestamp = strtotime("+ 6 month", $currentTimestamp);
							}
						}
						if ($resultAppointmentRepeats["type"] == "ser_year") {
							$notFound = true;
							while($notFound && $currentTimestamp <= $endTimestamp) {						   	
						   	if ($currentTimestamp >= ($startTimestamp - ($resultAppointment["duration"]*60))&& $currentTimestamp <= $endTimestamp) {
						   		$notFound = false;
						   		
						   		$exists = False;
									
									for($ii=0;$ii<count($appointmentRepeatsNotTS);$ii++) {
										if ($appointmentRepeatsNotTS[$ii] == $currentTimestamp)
						    				$exists = True;
									}
									
									$daEndTimestamp = $resultAppointmentRepeats["endTimestamp"] == 0 ? strtotime("+1 year", $startTimestamp) : $resultAppointmentRepeats["endTimestamp"];
	
									if ($exists == false && $calcTimestamp <= $daEndTimestamp) {
										
										$this->createAppointmentObjectsForDuration($resultAppointment, $currentTimestamp, $resultAppointmentRepeats);
									}	
						   	}
						   	$currentTimestamp = strtotime("+ 1 year", $currentTimestamp);
							}
						}
					}
				}
				
				else {
					$this->createAppointmentObjectsForDuration($resultAppointment);
				}
			}
		}
		else {
			$appointmentArrayList = null;
		}
		if(count($appointmentArrayList) > 0) {
			usort($appointmentArrayList, array("AppointmentHandler", "cmp"));
		}
		$arrayIndex = 0;
		return $appointmentArrayList;
	}

	function cmp ($a, $b) {
	    if ($a->getStartTimestamp() == $b->getStartTimestamp()) return 0;
	    return ($a->getStartTimestamp() < $b->getStartTimestamp()) ? -1 : 1;
	}
	
	function createAppointmentObjectsForDuration($resultAppointment, $currentTimestamp=null, $resultAppointmentRepeats=null) {
		global $appointmentArrayList, $arrayIndex, $startTimestamp, $endTimestamp;
		if ($currentTimestamp == null)
			$currentTimestamp = $resultAppointment["startTimestamp"];
		
		if ($resultAppointment["duration"] == 0) {
			$appointment = new ilAppointment;
			
			$appointment->setAccess($resultAppointment["access"]);
			$appointment->setAppointmentId($resultAppointment["appointmentId"]);
			$appointment->setCategory($resultAppointment["catTerm"]);
			$appointment->setCategoryId($resultAppointment["categoryId"]);
			$appointment->setDescription($resultAppointment["description"]);
			$appointment->setDuration($resultAppointment["duration"]);
			$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
			$appointment->setOwnerId($resultAppointment["ownerId"]);
			$appointment->setUserId($resultAppointment["userId"]);
			$appointment->setPriority($resultAppointment["priTerm"]);
			$appointment->setPriorityId($resultAppointment["prioityId"]);
			$appointment->setStartTimestamp($currentTimestamp);
			$appointment->setTerm($resultAppointment["term"]);
			$appointment->setLocation($resultAppointment["location"]);
			$appointment->setSerial($resultAppointment["serial"]);
			if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
				$appointment->setSer_type($resultAppointmentRepeats["type"]);
				$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
				$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
			}
			
			$appointmentArrayList[$arrayIndex] = $appointment;
			$arrayIndex++;
		}
		elseif($currentTimestamp >= $startTimestamp) {
			$appointment = new ilAppointment;
			
			$appointment->setAccess($resultAppointment["access"]);
			$appointment->setAppointmentId($resultAppointment["appointmentId"]);
			$appointment->setCategory($resultAppointment["catTerm"]);
			$appointment->setCategoryId($resultAppointment["categoryId"]);
			$appointment->setDescription($resultAppointment["description"]);
			$appointment->setDuration($resultAppointment["duration"]);
			$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
			$appointment->setOwnerId($resultAppointment["ownerId"]);
			$appointment->setUserId($resultAppointment["userId"]);
			$appointment->setPriority($resultAppointment["priTerm"]);
			$appointment->setPriorityId($resultAppointment["priorityId"]);
			$appointment->setStartTimestamp($currentTimestamp);
			$appointment->setTerm("<i>[S]</i> ".$resultAppointment["term"]);
			$appointment->setLocation($resultAppointment["location"]);
			$appointment->setSerial($resultAppointment["serial"]);
			if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
				$appointment->setSer_type($resultAppointmentRepeats["type"]);
				$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
				$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
			}
			
			$appointmentArrayList[$arrayIndex] = $appointment;
			$arrayIndex++;
			
			$appointmentStartDay = getdate($currentTimestamp);
			$startIterateTS = mktime(0,0,0,$appointmentStartDay["mon"],$appointmentStartDay["mday"],$appointmentStartDay["year"]);
			$endIterateTS = strtotime("-23 hours 59 minutes", ($currentTimestamp+($resultAppointment["duration"]*60)));
			if ($endIterateTS > $endTimestamp) 
				$endIterateTS = $endTimestamp;
				
			//mittendrin
			$break = false;
			while($break == false) {
				$startIterateTS = strtotime("+ 1 day", $startIterateTS);
				if ($startIterateTS > $endIterateTS) {
					$break = true;
				}
				else {
					////echo "drin3<br>";
					$appointment = new ilAppointment;
					
					$appointment->setAccess($resultAppointment["access"]);
					$appointment->setAppointmentId($resultAppointment["appointmentId"]);
					$appointment->setCategory($resultAppointment["catTerm"]);
					$appointment->setCategoryId($resultAppointment["categoryId"]);
					$appointment->setDescription($resultAppointment["description"]);
					$appointment->setDuration(0);
					$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
					$appointment->setOwnerId($resultAppointment["ownerId"]);
					$appointment->setUserId($resultAppointment["userId"]);
					$appointment->setPriority($resultAppointment["priTerm"]);
					$appointment->setPriorityId($resultAppointment["priorityId"]);
					$appointment->setStartTimestamp($startIterateTS);
					$appointment->setTerm("<i>[M]</i> ".$resultAppointment["term"]);
					$appointment->setLocation($resultAppointment["location"]);
					$appointment->setSerial($resultAppointment["serial"]);
					if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
						$appointment->setSer_type($resultAppointmentRepeats["type"]);
						$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
						$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
					}
					
					$appointmentArrayList[$arrayIndex] = $appointment;
					$arrayIndex++;
				}
			}
			
			//Ende
			if (($currentTimestamp+($resultAppointment["duration"]*60))<=$endTimestamp) {
				$appointment = new ilAppointment;
			
				$appointment->setAccess($resultAppointment["access"]);
				$appointment->setAppointmentId($resultAppointment["appointmentId"]);
				$appointment->setCategory($resultAppointment["catTerm"]);
				$appointment->setCategoryId($resultAppointment["categoryId"]);
				$appointment->setDescription($resultAppointment["description"]);
				$appointment->setDuration(0);
				$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
				$appointment->setOwnerId($resultAppointment["ownerId"]);
				$appointment->setUserId($resultAppointment["userId"]);
				$appointment->setPriority($resultAppointment["priTerm"]);
				$appointment->setPriorityId($resultAppointment["priorityId"]);
				$appointment->setStartTimestamp($currentTimestamp+($resultAppointment["duration"]*60));
				$appointment->setTerm("<i>[E]</i> ".$resultAppointment["term"]);
				$appointment->setLocation($resultAppointment["location"]);
				$appointment->setSerial($resultAppointment["serial"]);
				if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
					$appointment->setSer_type($resultAppointmentRepeats["type"]);
					$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
					$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
				}
				
				$appointmentArrayList[$arrayIndex] = $appointment;
				$arrayIndex++;
			}
		}
		elseif(($currentTimestamp < $startTimestamp) 
			 && (($currentTimestamp+($resultAppointment["duration"]*60)) > $startTimestamp) 
			 && (($currentTimestamp+($resultAppointment["duration"]*60)) < $endTimestamp) ) {
			 
			$appointmentStartDay = getdate($startTimestamp);
			$startIterateTS = mktime(0,0,0,$appointmentStartDay["mon"],$appointmentStartDay["mday"],$appointmentStartDay["year"]);
			$endIterateTS = strtotime("-23 hours 59 minutes", ($currentTimestamp+($resultAppointment["duration"]*60)));
			if ($endIterateTS > $endTimestamp) 
				$endIterateTS = $endTimestamp;
				
			//mittendrin
			$break = false;
			unset($i);
			$i=0;
			while($break == false) {
				$sITS = strtotime("+ ".$i." day", $startIterateTS);
				$i++;
				if ($sITS > $endIterateTS) {
					$break = true;
				}
				else {
					$appointment = new ilAppointment;
					
					$appointment->setAccess($resultAppointment["access"]);
					$appointment->setAppointmentId($resultAppointment["appointmentId"]);
					$appointment->setCategory($resultAppointment["catTerm"]);
					$appointment->setCategoryId($resultAppointment["categoryId"]);
					$appointment->setDescription($resultAppointment["description"]);
					$appointment->setDuration(0);
					$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
					$appointment->setOwnerId($resultAppointment["ownerId"]);
					$appointment->setUserId($resultAppointment["userId"]);
					$appointment->setPriority($resultAppointment["priTerm"]);
					$appointment->setPriorityId($resultAppointment["priorityId"]);
					$appointment->setStartTimestamp($sITS);
					$appointment->setTerm("<i>[M]</i> ".$resultAppointment["term"]);
					$appointment->setLocation($resultAppointment["location"]);
					$appointment->setSerial($resultAppointment["serial"]);
					if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
						$appointment->setSer_type($resultAppointmentRepeats["type"]);
						$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
						$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
					}
					
					$appointmentArrayList[$arrayIndex] = $appointment;
					$arrayIndex++;
				}
			} 
			 	
			$appointment = new ilAppointment;
			
			$appointment->setAccess($resultAppointment["access"]);
			$appointment->setAppointmentId($resultAppointment["appointmentId"]);
			$appointment->setCategory($resultAppointment["catTerm"]);
			$appointment->setCategoryId($resultAppointment["categoryId"]);
			$appointment->setDescription($resultAppointment["description"]);
			$appointment->setDuration(0);
			$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
			$appointment->setOwnerId($resultAppointment["ownerId"]);
			$appointment->setUserId($resultAppointment["userId"]);
			$appointment->setPriority($resultAppointment["priTerm"]);
			$appointment->setPriorityId($resultAppointment["priorityId"]);
			$appointment->setStartTimestamp($currentTimestamp+($resultAppointment["duration"]*60));
			$appointment->setTerm("<i>[E]</i> ".$resultAppointment["term"]);
			$appointment->setLocation($resultAppointment["location"]);
			$appointment->setSerial($resultAppointment["serial"]);
			if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
				$appointment->setSer_type($resultAppointmentRepeats["type"]);
				$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
				$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
			}
			
			$appointmentArrayList[$arrayIndex] = $appointment;
			$arrayIndex++;
		}
		elseif ( ($currentTimestamp < $startTimestamp) 
			 && (($currentTimestamp+($resultAppointment["duration"]*60)) > $endTimestamp) ) {
			
			
			$numOfDays = ($endTimestamp - $startTimestamp)/(24*60*60);
			for ($i=0;$i<$numOfDays;$i++) {
				$day = getdate($startTimestamp);
				$newts = mktime(0,0,1,$day["mon"],$day["mday"]+$i,$day["year"]);
				$appointment = new ilAppointment;
				
				$appointment->setAccess($resultAppointment["access"]);
				$appointment->setAppointmentId($resultAppointment["appointmentId"]);
				$appointment->setCategory($resultAppointment["catTerm"]);
				$appointment->setCategoryId($resultAppointment["categoryId"]);
				$appointment->setDescription($resultAppointment["description"]);
				$appointment->setDuration(0);
				$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
				$appointment->setOwnerId($resultAppointment["ownerId"]);
				$appointment->setUserId($resultAppointment["userId"]);
				$appointment->setPriority($resultAppointment["priTerm"]);
				$appointment->setPriorityId($resultAppointment["priorityId"]);
				$appointment->setStartTimestamp($newts);
				$appointment->setTerm("<i>[M]</i> ".$resultAppointment["term"]);
				$appointment->setLocation($resultAppointment["location"]);
				$appointment->setSerial($resultAppointment["serial"]);
				if($resultAppointmentRepeats != null && count($resultAppointmentRepeats) > 0) {
					$appointment->setSer_type($resultAppointmentRepeats["type"]);
					$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
					$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
				}
				
				$appointmentArrayList[$arrayIndex] = $appointment;
				$arrayIndex++;
			}
		}
	}
		
	function getAppointmentsPD($userId) {
		$today = getdate();
		$startTimestamp = mktime(0,0,0,$today["mon"],$today["mday"],$today["year"]);
		$endTimestamp = mktime(0,0,0,$today["mon"],$today["mday"]+1,$today["year"]);
		
		$appointmentArrayList = $this->setAppointmentArrayList($userId, $startTimestamp, $endTimestamp);
		return $appointmentArrayList;
	}

	function deleteAppointment($userId, $appointmentId, $appointmentUnionId) {
		$dbHandler = new ilCalDBHandler;
		$appointment = $this->getSingleAppointment($appointmentId);

		if ($appointmentUnionId == Null || $appointmentUnionId == "" || $appointmentUnionId == 0) {

			$dbtable = "cal_appointmentrepeats";
			$where = "cal_appointmentrepeats.appointmentId = $appointmentId";
			$appointmentRepeatsResultset = $dbHandler->select($dbtable, $where);

			$dbtable = "cal_appointment";
			$where = "cal_appointment.appointmentId = $appointmentId";
			$dbHandler-> delete($dbtable, $where);

			$dbtable = "cal_appointmentrepeats";
			$where = "cal_appointmentrepeats.appointmentId = $appointmentId";
			$dbHandler-> delete($dbtable, $where);
		
			$dbtable = "cal_appointmentrepeatsnot";
			$where = "cal_appointmentrepeatsnot.appointmentRepeatsId = ".$appointmentRepeatsResultset["appointmentId"];
			$dbHandler-> delete($dbtable, $where);
			return true;
		}
		else {
			if ($appointment->getOwnerId() == $userId ) {
							
				$dbtable= "cal_appointment";
				$where = "cal_appointment.appointmentUnionId = $appointmentUnionId";
				$appointmentResultset = $dbHandler->select($dbtable, "", $where);
				
				$dbtable = "cal_appointment";
				$where = "cal_appointment.appointmentUnionId = $appointmentUnionId";
				$dbHandler->delete($dbtable, $where);
					
				while($resultAppointment = $appointmentResultset->fetchRow(DB_FETCHMODE_ASSOC))	
				{
					$dbtable = "cal_appointmentrepeats";
					$where = "cal_appointmentrepeats.appointmentId =".$appointmentId;
					$appointmentRepeatsResultset = $dbHandler->select($dbtable, "", $where);
					$resultAppointmentRepeats = $appointmentRepeatsResultset->fetchRow(DB_FETCHMODE_ASSOC);
					
					$dbtable = "cal_appointmentrepeats";
					$where = "cal_appointmentrepeats.appointmentId = ".$appointmentId;
					$dbHandler->delete($dbtable, $where);
					
					if ($resultAppointmentRepeats["appointmentRepeatsId"] != "" && 
						 $resultAppointmentRepeats["appointmentRepeatsId"] != Null &&
						 $resultAppointmentRepeats["appointmentRepeatsId"] != 0)
					{
						$dbtable = "cal_appointmentrepeatsnot";
						$where = "cal_appointmentrepeatsnot.appointmentRepeatsId = ".$resultAppointmentRepeats["appointmentRepeatsId"];
						$dbHandler-> delete($dbtable, $where);	
					}
				}
				return true;
			}
		}
		return false;
	}
			
	function appointmentRepeatsNot ($appointmentId, $leaveOutTimestamp) {
		$dbHandler = new ilCalDBHandler;
		
		$dbtable = "cal_appointmentrepeats";
		$where = "cal_appointmentrepeats.appointmentId = $appointmentId";
		$appointmentRepeatsResultset = $dbHandler->select($dbtable, $where);	
		
		$appointmentRepeatsId = $appointmentRepeatsResultset["appointmentRepeatsId"];
		
		$dbtable = "cal_appointmentrepeatsnot";
		$number_of_values = 2;
		$fields = "appointmentRepeatsId, leaveOutTimestamp";
		$values = "'$appointmentId', '$leaveOutTimestamp'";
		$dbHandler->insert($number_of_values, $dbtable, $fields, $values);
		return true;
	}
		
	function appointmentUpdate ($ownerId, $appointment) {
		$dbHandler = new ilCalDBHandler;
			
		$dbtable = "cal_appointment";
		$column = "appointmentID";
		$where = "ownerID=".$ownerId." AND appointmentID=".$appointment->getAppointmentId();
		$res = $dbHandler->select($dbtable, $column, $where);
		
		if($res->numRows() > 0) {						
			$dbtable = "cal_appointment";
			$values =  "cal_appointment.access = '".$appointment->getAccess()."', ".
						  "cal_appointment.categoryId = ".$appointment->getCategoryId().", ".
						  "cal_appointment.description = '".$appointment->getDescription()."', ".
						  "cal_appointment.duration = ".$appointment->getDuration().", ".
						  "cal_appointment.priorityId = ".$appointment->getPriorityId().", ".
						  "cal_appointment.StartTimestamp = ".$appointment->getStartTimestamp().", ".
						  "cal_appointment.term = '".$appointment->getTerm()."', ".
						  "cal_appointment.location = '".$appointment->getLocation()."', ".
						  "cal_appointment.serial = ".$appointment->getSerial();
			$where =   "cal_appointment.appointmentUnionId = ".$appointment->getAppointmentUnionId();			
			$dbHandler->update($dbtable, $values, $where);
			
			$appointmentResultSet = $dbHandler->select($dbtable, "appointmentId", "appointmentUnionId=".$appointment->getAppointmentUnionId());
			if ($appointmentResultSet->numRows() >= 1) {
				while($resultAppointment = $appointmentResultSet->fetchRow(DB_FETCHMODE_ASSOC)) {
					$appointmentRepeatsResultSet = $dbHandler->select("cal_appointmentrepeats", "appointmentRepeatsId", "appointmentId=".$resultAppointment["appointmentId"]);
					if ($appointmentRepeatsResultSet->numRows() >= 1) {
						$dbtable = "cal_appointmentrepeats";
						$values =  "cal_appointmentrepeats.weekdays = '".$appointment->getSer_days()."', ".
									  "cal_appointmentrepeats.endTimestamp = ".$appointment->getSer_stop().", ".
									  "cal_appointmentrepeats.type = '".$appointment->getSer_type()."'";
						$where =   "cal_appointmentrepeats.appointmentId = ".$resultAppointment["appointmentId"];	
						$dbHandler->update($dbtable, $values, $where);
					}
					else {
						$dbTable = "cal_appointmentrepeats";
						$fields  = "weekdays, endTimestamp, type, appointmentId";
						$values  = "'".$appointment->getSer_days()."', ".$appointment->getSer_stop().", '".$appointment->getSer_type()."', ".$resultAppointment["appointmentId"];
						$dbHandler->insert(2, $dbTable, $fields, $values);
					}
				}
			}
			return true;
		}
		else {
			return false;
		}
	}
	
	
	function deleteOldAppointments() {
		$lastDelete = strtotime("-400 days");
		$dbh = new ilCalDBHandler();

		$dbh->delete("cal_appointment", "startTimestamp<".$lastDelete." AND serial=0");
	}

	function getSingleAppointment($aid) {
		if ($aid == "" || $aid == null || $aid < 0) {
			return new ilAppointment();
		}
		else {
			$dbHandler = new ilCalDBHandler();
			$dbtable = "cal_appointment";
			$where = "cal_appointment.appointmentId = ".$aid;
			$appointmentResultSet = $dbHandler->select($dbtable, "", $where);
			if ($appointmentResultSet->numRows() > 0) {
				$resultAppointment = $appointmentResultSet->fetchRow(DB_FETCHMODE_ASSOC);
				
				$dbtable = "cal_appointmentrepeats";
				$where = "cal_appointmentrepeats.appointmentId=".$resultAppointment["appointmentId"];
				$appointmentRepeatsResultset = $dbHandler->select($dbtable, "", $where);
				
				if ($appointmentRepeatsResultset->numRows() == 1) 
					$resultAppointmentRepeats = $appointmentRepeatsResultset->fetchRow(DB_FETCHMODE_ASSOC);
				else
					$resultAppointmentRepeats = null;
				
				$appointment = new ilAppointment();
						
				$appointment->setAccess($resultAppointment["access"]);
				$appointment->setAppointmentId($resultAppointment["appointmentId"]);
				$appointment->setCategory($resultAppointment["catTerm"]);
				$appointment->setCategoryId($resultAppointment["categoryId"]);
				$appointment->setDescription($resultAppointment["description"]);
				$appointment->setDuration($resultAppointment["duration"]);
				$appointment->setAppointmentUnionId($resultAppointment["appointmentUnionId"]);
				$appointment->setOwnerId($resultAppointment["ownerId"]);
				$appointment->setPriority($resultAppointment["priTerm"]);
				$appointment->setPriorityId($resultAppointment["priorityId"]);
				$appointment->setStartTimestamp($resultAppointment["startTimestamp"]);
				$appointment->setTerm($resultAppointment["term"]);
				$appointment->setLocation($resultAppointment["location"]);
				$appointment->setSerial($resultAppointment["serial"]);
				$appointment->setSer_type($resultAppointmentRepeats["type"]);
				$appointment->setSer_days($resultAppointmentRepeats["weekdays"]);
				$appointment->setSer_stop($resultAppointmentRepeats["endTimestamp"]);
				
				return $appointment;
			}
			else
				return new ilAppointment();
		}
	}
}
?>
