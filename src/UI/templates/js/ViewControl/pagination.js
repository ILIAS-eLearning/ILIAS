il = il || {};
il.UI = il.UI || {};
il.UI.viewcontrol = il.UI.viewcontrol || {};

(function($, viewcontrol) {
	viewcontrol.pagination = (function($) {
		var onInternalSelect = function(event, signalData, signal, component_id) {
			var triggerer = signalData.triggerer[0], 		//the shy-button
			param = triggerer.getAttribute('data-action'), 	//the pagination-value
			pagination = $('#' + component_id),				//the component itself
			sigdata = {
				'id' : signal,
				'event' : 'select',
				'triggerer' : pagination,
				'options' : {
					'page': param
				}
			},
			dd = pagination.find('.dropdown-toggle');		//the (potential) dropdown

			if(dd.length > 0) {
				//close dropdown and set current value
				dd.dropdown('toggle');
				dd.contents()[0].data = (parseInt(param) + 1).toString() + ' ';
			}
			pagination.trigger(signal, sigdata);
		};

		return {
			onInternalSelect: onInternalSelect
		}

	})($);
})($, il.UI.viewcontrol);
