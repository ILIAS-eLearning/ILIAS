il = il || {};
il.UI = il.UI || {};

(function($, UI) {
	UI.tree = (function($) {

		var init = function (component_id, highlight_nodes) {
			var tree = $('#' + component_id);
			initNodesForExpansion(tree);
			initNodesForAsyncLoading(tree);
			if(highlight_nodes) {
				initHighlightOnNodeClick(tree);
			}
		};

		var initNodesForExpansion = function (tree) {
			tree.find('.il-tree-node .node-line').click(
				function(e){
					$(this).parent('.il-tree-node').toggleClass('expanded');

					let link_in_list = $(this).find("a");
					if (link_in_list.length == 0) {
						return false;
					}
				}
			);

		};

		var initNodesForAsyncLoading = function (tree) {
			tree.find(".il-tree-node[data-async_url][data-async_loaded='false'] .node-line").click(
				function(e){
					var node = $(e.currentTarget).parent('.il-tree-node');

					if(node.attr('data-async_loaded') == 'false') {
						$.ajax({
							url: node.attr('data-async_url'),
							dataType: 'html'
						}).done(
							function(html) {
								node.attr('data-async_loaded', true);

								if(!html) {
									node.removeClass('expandable');
								}
								$(html).insertAfter($(e.currentTarget));
							}
						);
					}

					return false;
				}
			);
		};

		var resetNodeHighlights = function(tree) {
			tree.find('.il-tree-node').removeClass('highlighted');
		}

		var initHighlightOnNodeClick = function(tree) {
			tree.find('.il-tree-node .node-line').click(
				function(e){
					resetNodeHighlights(tree);
					$(this).parent('.il-tree-node').addClass('highlighted');
					let link_in_list = $(this).find("a");
					if (link_in_list.length == 0) {
						return false;
					}
				}
			);
		};

		var registerFurtherNodeSignals = function (id, signals) {
			$('#' + id + ' > span').click(
				function(e){
					var node = $('#' + id);
					for (var i = 0; i < signals.length; i++) {
						var s = signals[i];
						node.trigger(s.signal_id, s);
					}

					let link_in_list = $(this).find("a");
					if (link_in_list.length == 0) {
						return false;
					}
				}
			);
		}

		return {
			init: init,
			registerFurtherNodeSignals: registerFurtherNodeSignals
		}

	})($);
})($, il.UI);
