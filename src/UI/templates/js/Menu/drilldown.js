il = il || {};
il.UI = il.UI || {};
il.UI.menu = il.UI.menu || {};

(function($, UI) {
	UI.menu.drilldown = (function($) {

		var init = function (component_id) {
			var dd = $('#' + component_id),
				structure = dd.children('.il-drilldown-structure'),
				visible = dd.children('.il-drilldown-visible'),
				current = dd.children('.il-drilldown-current'),

				firstentry = structure.children('.il-menu-item'),
				firstlevel = firstentry.children('.il-menu-level');

			current.html(firstentry.clone());
			visible.html(
				firstlevel.children('.il-menu-item').clone()
			);
			initEntries(dd);
			initInitiallyActive(dd);
		};

		var initEntries= function (drilldown) {
			var entries = drilldown.children('.il-drilldown-visible').children('.il-menu-item'),
				backlinks = drilldown.children('.il-drilldown-backlink').children('.il-menu-item');

			$.merge(entries, backlinks);

			entries.children('.il-menu-item-label').click(
				function() {
					var entry = $(this).parent('.il-menu-item');
					if(entry.attr('id')) {
						setActive(entry);
					}
				}
			);
		};

		var setActive = function(entry) {

			var dd = entry.parents('.il-drilldown'),
				structure = dd.children('.il-drilldown-structure'),
				all_entries = structure.children('.il-menu-item');
				struct_entry = structure.find('#' + entry.attr('id')),
				backlink = dd.children('.il-drilldown-backlink'),
				visible = dd.children('.il-drilldown-visible')
				current = dd.children('.il-drilldown-current')
				back_entry = struct_entry.parents('.il-menu-item');

			if(back_entry.length > 1) {
				back_entry = $(back_entry[0]);
			}

			all_entries.attr('data-active', false);
			struct_entry.attr('data-active', true);

			visible.html(
				struct_entry.children('.il-menu-level').children('.il-menu-item').clone(true)
			);
			backlink.html(back_entry.clone());
			current.html(struct_entry.clone());

			initEntries(dd);
		};

		var initInitiallyActive = function(dd) {
			var node = dd.find(".il-menu-item[data-active='true']").first();
			setActive(node);
		}

		return {
			init: init
		}

	})($);
})($, il.UI);
