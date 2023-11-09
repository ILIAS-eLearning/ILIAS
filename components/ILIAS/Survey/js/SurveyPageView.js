var ilSurveyPageView = {		
	drop_id: 'il_droparea',	
	drag_id: 'il_editarea',		
	drag_active_css: 'il_editarea_active',
	drag_selected_css: 'il_editarea_active_selected',
	drag_process_css: 'ilSurveyPageEditAreaDragging',
	drop_active_css: 'ilSurveyPageEditDropArea',
	drop_selected_css: 'ilSurveyPageEditDropAreaSelected',
	drag_process_id: null,
	sel_edit_areas: Array(),
	init: function() {		
		$('div.' + this.drag_id).each(function() {			
			$(this).hover(ilSurveyPageView.dragHoverIn, ilSurveyPageView.dragHoverOut)
		});
		$('div.' + this.drag_id).each(function() {	
			$(this).draggable({  
				axis: 'y', 
				scroll: true,
				containment: 'parent', 
				cursor: 'move',				
				revert: 'invalid',
				opacity: 0.6, 
				/* helper: 'clone', */
				helper: function(event) {
					return '<div class="' + ilSurveyPageView.drag_process_css + 
						'" style="width:' + $(this).width() + 'px; height: 150px;">' +				
						$('#label_' + $(this).attr('id')).html()
						+ '</div>';
				},
				start: function(event, ui) {
					ilSurveyPageView.drag_process_id = $(this).attr('id');
					$(this).removeClass(ilSurveyPageView.drag_active_css);	
					$('#label_' + $(this).attr('id')).hide();
				},
				stop: function(event, ui) {
					ilSurveyPageView.drag_process_id = null;										
				}
				
			});			
			if($(this).hasClass('selectable'))
			{
				$(this).dblclick(ilSurveyPageView.handleSelection);				
			}			
		});		
		$('div.' + this.drop_id).each(function() {	
			$(this).droppable({				
				activeClass: ilSurveyPageView.drop_active_css, 				
				hoverClass: ilSurveyPageView.drop_selected_css, 				
				tolerance: 'touch', 
				accept: function(draggable) {
					var drop_id = $(this).attr('id');
					// directly neighbouring drop areas are no movement
					if($(draggable).prev().attr('id') !== drop_id &&
						$(draggable).next().attr('id') !== drop_id)
					{
						return true;
					}
					return false;
				},
				drop: function(event, ui) {							
					var drag_id = $(ui.draggable).attr('id');
					var drop_id = $(this).attr('id');
					
					// set and submit form
					$('#il_hform_cmd').attr('value', 1);
					$('#il_hform_cmd').attr('name', 'cmd[renderPage]');
					$('#il_hform_subcmd').attr('value', 'dnd');
					$('#il_hform_source_id').attr('value', drag_id);
					$('#il_hform_target_id').attr('value', drop_id);
					$('#form_hform').submit(); 
				}
			});
		});
	},
	dragHoverIn: function(e) {
		if(!ilSurveyPageView.drag_process_id)
		{
			$(this).addClass(ilSurveyPageView.drag_active_css);					
			$('#label_' + $(this).attr('id')).show();
		}
	},
	dragHoverOut: function(e) {
		if(!ilSurveyPageView.drag_process_id)		
		{
			$('#label_' + $(this).attr('id')).hide();
			$(this).removeClass(ilSurveyPageView.drag_active_css);
		}
	},
	handleSelection: function(e) {
		var id = $(this).attr('id');		
		if(ilSurveyPageView.sel_edit_areas[id]) {
			ilSurveyPageView.sel_edit_areas[id] = false;
			$(this).removeClass(ilSurveyPageView.drag_selected_css);
			$(this).addClass(ilSurveyPageView.drag_active_css);
		}
		else {
			ilSurveyPageView.sel_edit_areas[id] = true;
			$(this).removeClass(ilSurveyPageView.drag_active_css);
			$(this).addClass(ilSurveyPageView.drag_selected_css);
		}
	},
	selectAll: function(e) {
		$('div.' + ilSurveyPageView.drag_id).each(function() {			
			if($(this).hasClass('selectable'))
			{
				var id = $(this).attr('id');
				if(!ilSurveyPageView.sel_edit_areas[id]) {
					ilSurveyPageView.sel_edit_areas[id] = true;
					$(this).removeClass(ilSurveyPageView.drag_active_css);
					$(this).addClass(ilSurveyPageView.drag_selected_css);
				}
			}
		});		
	},
	multiAction: function(cmd, subcmd) {
		if(subcmd == "selectAll")
		{
			this.selectAll();
			return false;
		}	

		// set and submit form

		// selected questions		
		var sel_ids = "";
		var delim = "";
		for (var key in ilSurveyPageView.sel_edit_areas)
		{
			if (ilSurveyPageView.sel_edit_areas[key])
			{
				sel_ids = sel_ids + delim + key;
				delim = ";";
			}
		}		
		$('#il_hform_multi').attr('value', sel_ids);		

		$('#il_hform_cmd').attr('value', 1);
		$('#il_hform_cmd').attr('name', 'cmd[' + cmd + ']');
		$('#il_hform_subcmd').attr('value', subcmd);				
		$('#form_hform').submit();		

		return false;
	}
};