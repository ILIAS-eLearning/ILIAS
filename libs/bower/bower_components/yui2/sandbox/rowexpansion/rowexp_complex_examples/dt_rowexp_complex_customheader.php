<style type="text/css">
/* custom styles for this example */
#expandable_table{width:500px;}
		
/** 
*
* Style the yui-dt-expandablerow-trigger column 
*
**/
.yui-dt-expandablerow-trigger{
	width:18px;
	height:22px;
	cursor:pointer;
}
.yui-dt-expanded .yui-dt-expandablerow-trigger{
	background:url(includes/rowexpansion/arrow_open.png) 4px 4px no-repeat;
}
.yui-dt-expandablerow-trigger, .yui-dt-collapsed .yui-dt-expandablerow-trigger{
	background:url(includes/rowexpansion/arrow_closed.png) 4px 4px no-repeat;
}
.yui-dt-expanded .yui-dt-expandablerow-trigger.spinner{
	background:url(includes/rowexpansion/spinner.gif) 1px 4px no-repeat;
}

/** 
*
* Style the expansion row
*
**/
.yui-dt-expansion .yui-dt-liner{
	padding:0;
	border:solid 0 #bbb;
	border-width: 0 0 2px 0;
}
.yui-dt-expansion .yui-dt-liner th, .yui-dt-expansion .yui-dt-liner table{
	border:none;
	background-color:#fff;
}
.yui-dt-expansion .yui-dt-liner th, .yui-dt-expansion .yui-dt-liner table th{
	background-image:none;
	background-color:#eee;
}
.yui-dt-expansion .yui-dt-liner th, .yui-dt-expansion .yui-dt-liner table td{
	border:solid 0 #eee;
	border-width: 0 0 1px 1px;
}
.yui-dt-expansion .yui-dt-liner th, .yui-dt-expansion .yui-dt-liner table td div{
	padding:3px;
	overflow:hidden;
	width:100px;
}
.yui-dt-expansion .yui-dt-liner th, .yui-dt-expansion .yui-dt-liner table td.big div{
	width:300px;
}
.yui-dt-expansion .yui-dt-liner th, .yui-dt-expansion .yui-dt-liner table td ul{ padding:0;margin:0; }
</style>
