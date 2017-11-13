il = il || {};
il.UI = il.UI || {};
il.UI.viewcontrol = il.UI.viewcontrol || {};

(function($, viewcontrol) {
	viewcontrol.sortation = (function($) {
		var onInternalSelect = function(event, signalData, signal, component_id) {
			var triggerer = signalData.triggerer[0], 		//the shy-button
			param = triggerer.getAttribute('data-action'), 	//the actual value
			sortation = $('#' + component_id),				//the component itself
			sigdata = {
				'id' : signal,
				'event' : 'sort',
				'triggerer' : sortation,
				'options' : {
					'sortation': param
				}
			}
			dd = sortation.find('.dropdown-toggle');		//the dropdown

			//close dropdown and set current value
			dd.dropdown('toggle');
			dd.contents()[0].data = signalData.triggerer.contents()[0].data  + ' ';

			sortation.trigger(signal, sigdata);
		};

		return {
			onInternalSelect: onInternalSelect
		}

	})($);
})($, il.UI.viewcontrol);
