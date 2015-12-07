<?php
/**
*	relevant users may only be found below this orgunits
*/
$this->top_orgus = array('only_look_below_this_orgu_title','and_below_this_orgu_ritle');
/**
*	ignore users having this role
*/
$this->ignore_roles = array('Administrator');
/**
*	categories 
*		=> condition: datbase definition of this category
*		=> how are the days of this category calculated per day
*	Tables:
*	ht = hist_tep
*	hc = hist_course
*	htid = hist_tep_individ_days
*/
$this->cats = array(
					'presence' => 
							array(	'condition'	=> 	" ht.category  = 'Training' AND hc.type = 'Päsenztraining' " 
									,'weight' 	=>	" TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time )) /3600 " )
					,'virtual' =>
							array(	'condition'	=> 	" ht.category  = 'Training' AND hc.type = 'Virtuelles Training' "
									,'weight'	=>	" 0.5 ")
					);
/**
*	meta categories which group a set of categories to sum over columns
*/
$this->meta_cats = array(	
					'training' =>
						array(	'presence'
								,'virtual')
					);