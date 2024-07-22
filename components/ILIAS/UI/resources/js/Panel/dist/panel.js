/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

const panel = function () {
    const performAsync = function (action) {
        if (action !== null) {
            fetch(action, {
                method: 'GET',
            });
        }
    };

    const performSignal = function (id, signal) {
        const b = document.getElementById(id);
        if (signal !== null) {
            // eslint-disable-next-line no-undef
            $(b).trigger(signal.signal_id, {
                id: signal.signal_id,
                event: signal.event,
                triggerer: b,
                options: signal.options,
            });
        }
    };

    const showAndHideElementsForCollapse = function (id, type) {
        const p = document.getElementById(id).closest('.panel-expandable');
        p.querySelector('[data-collapse-button-visibility]').dataset.collapseButtonVisibility = '0';
        p.querySelector('[data-expand-button-visibility]').dataset.expandButtonVisibility = '1';
        p.querySelector('.panel-viewcontrols').dataset.vcExpanded = '0';
        if (type === 'standard') {
            p.querySelector('.panel-body').dataset.bodyExpanded = '0';
        } else if (type === 'listing') {
            p.querySelector('.panel-listing-body').dataset.bodyExpanded = '0';
        }
    };

    const onCollapseCmdAction = function (event, id, type, action) {
        showAndHideElementsForCollapse(id, type);
        performAsync(action);
    };

    const onCollapseCmdSignal = function (event, id, type, signal) {
        showAndHideElementsForCollapse(id, type);
        performSignal(id, signal);
    };

    const showAndHideElementsForExpand = function (id, type) {
        const p = document.getElementById(id).closest('.panel-expandable');
        p.querySelector('[data-expand-button-visibility]').dataset.expandButtonVisibility = '0';
        p.querySelector('[data-collapse-button-visibility]').dataset.collapseButtonVisibility = '1';
        p.querySelector('.panel-viewcontrols').dataset.vcExpanded = '1';
        if (type === 'standard') {
            p.querySelector('.panel-body').dataset.bodyExpanded = '1';
        } else if (type === 'listing') {
            p.querySelector('.panel-listing-body').dataset.bodyExpanded = '1';
        }
    };

    const onExpandCmdAction = function (event, id, type, action) {
        showAndHideElementsForExpand(id, type);
        performAsync(action);
    };

    const onExpandCmdSignal = function (event, id, type, signal) {
        showAndHideElementsForExpand(id, type);
        performSignal(id, signal);
    };

    /**
     * Public interface
     */
    return {
        onCollapseCmdAction,
        onCollapseCmdSignal,
        onExpandCmdAction,
        onExpandCmdSignal,
    };
};

il = il || {};
il.UI = il.UI || {};

il.UI.panel = panel();
