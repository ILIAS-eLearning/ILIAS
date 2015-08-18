<?php

/* conditions for training categories inside the hist_course table */

$workload_training_conditions = array(	'trainingtype1' => " hc.edu_program = 'foo' AND hc.type != 'bar' " 
										);


/* cats will be shown in table. subcats are counted to cats. this relates only to non-training categories in hist_tep */

$workload_tep_cats = array(	'cat1' => array(
								'subcat11'
								,'subcat12'),
							'cat2' => array(
								'subcat21'
								,'subcat22')
							);

/* cats that are counted as full day indifferently */

$workload_fullday 	= array('vacation');

/* titles of cats */

$workload_label 	= array('cat'=>'cat_title'
							);

/* meta_cats that group cats inside the table  */


$workload_meta = array('meta_cat1'=>array(
								'cat1'
								,'cat2'));

/* reference days for meta_cats, metacats without reference will not calculate ration  */

$workload_days_per_yead_norm = array(	'meta_cat1'=>60,
										'meta_cat2'=>20);

?>