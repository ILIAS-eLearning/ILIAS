<?php
/* titles of the hoghest orgus to consider. it will not matter, if one of the orgus is below some other. */

$top_orgus = array('foo','bar');

/* category=>subcategory (from hist_tep) relations. all subcategories will be grouped within the category during summation */

$meta_categories = array(	
							'cat1' => array('subcat11','subcat12'),
							'cat2' => array('subcat21','subcat22'),
							);

/* category titles */
$meta_category_names = array(	
							'cat1' => 
								'title1',
							'cat2'=>
								'title2'
							);

?>