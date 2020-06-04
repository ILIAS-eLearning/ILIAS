il = il || {};
il.UI = il.UI || {};

(function($, UI) {
	UI.tree = (function($) {

		const toggleNodeState = function (event, action, state_param, was_expanded) {
			action += (action.indexOf("?") !== -1 ? "&" : "?") + encodeURIComponent(state_param) + "=" + (was_expanded ? 0 : 1);

			$.ajax({
				type: 'POST',
				url: action
			});
		}

		const init = function (component_id, highlight_nodes) {
			var tree = $('#' + component_id);
			initNodesForExpansion(tree);
			initNodesForActions(tree);
			initNodesForAsyncLoading(tree);
			if(highlight_nodes) {
				initHighlightOnNodeClick(tree);
			}
		}

		const initNodesForExpansion = function (tree) {
			tree.find('.il-tree-node .node-line').click(
				function(e) {
					$(this).parent('.il-tree-node').toggleClass('expanded').attr(
						'aria-expanded',
						function(index, attr) {
							return attr == 'false' ? 'true' : 'false';
						});
					e.preventDefault();
				}
			);
		}

		const initNodesForActions = function (tree) {
			tree.find('.il-tree-node .node-line .node-label a').click(
				function(e) {
					let href = $(this).attr('href');

					if (typeof href === typeof undefined || href === false || href === "#") {
						return false;
					}

					// Don't propagate event to prevent expanding the node on click
					e.stopPropagation();
				}
			);
		}

		const initNodesForAsyncLoading = function (tree) {
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

		const resetNodeHighlights = function(tree) {
			tree.find('.il-tree-node').removeClass('highlighted');
		}

		const initHighlightOnNodeClick = function(tree) {
			tree.find('.il-tree-node .node-line').click(
				function(e){
					resetNodeHighlights(tree);
					$(this).parent('.il-tree-node').addClass('highlighted');
						e.preventDefault();
					}
			);
		};

		const registerFurtherNodeSignals = function (id, signals) {
			$('#' + id + ' > span').click(
				function(e){
					var node = $('#' + id);
					for (var i = 0; i < signals.length; i++) {
						var s = signals[i];
						node.trigger(s.signal_id, s);
					}
						return false;
					}
			);
		}

		return {
			init: init,
			registerFurtherNodeSignals: registerFurtherNodeSignals,
			toggleNodeState: toggleNodeState
		}

	})($);
})($, il.UI);
