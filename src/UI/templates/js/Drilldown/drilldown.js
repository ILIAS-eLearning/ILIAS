il = il || {};
il.UI = il.UI || {};

(function($, UI) {
	UI.drilldown = (function($) {

		var init = function (component_id) {
			var dd = $('#' + component_id),
				structure = dd.children('.il-drilldown-structure'),
				firstlevel = structure.children('.il-drilldown-entry').children('.il-drilldown-level'),
				visible = dd.children('.il-drilldown-visible');

				window.top.struct = structure;
				window.top.firstlevel = firstlevel;

			visible.html(
				//structure.children('.il-drilldown-entry').clone()
				firstlevel.children('.il-drilldown-entry').clone()
			);
			initEntries(dd);
		};

		var initEntries= function (drilldown) {
			var entries = drilldown.children('.il-drilldown-visible').children('.il-drilldown-entry'),
				backlinks = drilldown.children('.il-drilldown-backlink').children('.il-drilldown-entry');

			$.merge(entries, backlinks);

			entries.children('.entry').click( function() {
				var entry = $(this).parent('.il-drilldown-entry');
				setActive(entry);
			});
		};

		var setActive = function(entry) {
			var dd = entry.parents('.il-drilldown'),
				structure = dd.children('.il-drilldown-structure'),
				all_entries = structure.children('.il-drilldown-entry');
				struct_entry = structure.find('#' + entry.attr('id')),
				backlink = dd.children('.il-drilldown-backlink'),
				visible = dd.children('.il-drilldown-visible')
				back_entry = struct_entry.parents('.il-drilldown-entry');

			if(back_entry.length > 1) {
				back_entry = $(back_entry[0]);
			}

			all_entries.attr('data-active', false);
			struct_entry.attr('data-active', true);

			visible.html(
				struct_entry.children('.il-drilldown-level').children('.il-drilldown-entry').clone(true)
			);
			backlink.html(back_entry.clone());


			initEntries(dd);
		};

		return {
			init: init
		}

	})($);
})($, il.UI);
