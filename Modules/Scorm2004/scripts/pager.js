/*
Requires JQuery
*/

var pager =  
{
	currentPage : null,
	
	Init : function()
	{
		jQuery('table.ilc_page_cont_PageContainer').each(function(ix, el)
		{
			if(pager.currentPage==null)
				pager.currentPage = el;
			else
			{
				jQuery(el).hide();
			}
		}
		);
		jQuery('a.ilc_page_rnavlink_RightNavigationLink').bind("click", function(){pager.NextPage();});
		jQuery('a.ilc_page_lnavlink_LeftNavigationLink').each(function(ix)
			{
				jQuery( this ).bind("click", function(){pager.PrevPage();});
				jQuery( this ).hide();
			});
			
	},
	NextPage : function()
	{
		var newPage = jQuery(pager.currentPage).nextAll('table.ilc_page_cont_PageContainer');
		if(newPage.length>0)
		{
			jQuery(pager.currentPage).hide();
			pager.currentPage = newPage[0];
		
			//fix for IE Bug...imagemap-highlights have to be restored when hidden
			jQuery(pager.currentPage).show("fast",function(){ jQuery('.imagemap').maphilight({fade:true});});
		
			jQuery('a.ilc_page_lnavlink_LeftNavigationLink').show();
			if(newPage.length==1){
				jQuery('a.ilc_page_rnavlink_RightNavigationLink').hide();
				finishSCO();
			}	
		}
	},
	
	PrevPage : function()
	{
		var newPage = jQuery(pager.currentPage).prevAll('table.ilc_page_cont_PageContainer');
		if(newPage.length>0)
		{
			jQuery(pager.currentPage).hide();
			pager.currentPage = newPage[0];
			jQuery(pager.currentPage).show("fast",function(){ jQuery('.imagemap').maphilight({fade:true});});
		
			jQuery('a.ilc_page_rnavlink_RightNavigationLink').show();
			if(newPage.length==1)
			jQuery('a.ilc_page_lnavlink_LeftNavigationLink').hide();
		}
	}
};

	

 jQuery(document).ready(function(){
   pager.Init();
 });

