const panel = function () {
    const initExpandable = function () {
        document.querySelectorAll('div.panel-expandable').forEach(
            (p) => {
                // hide expand button when panel is expanded or collapse button when panel is collapsed
                if (p.querySelector('.panel-body-expandable.collapse:not(.in)')) {
                    p.querySelector('.panel-collapse-button').style.display = 'none';
                    p.querySelector('.panel-viewcontrols').style.display = 'none';
                } else {
                    p.querySelector('.panel-expand-button').style.display = 'none';
                }
            },
        );
    };

    const onCollapseCmd = function (event, id, action = null, signal = null) {
        const p = document.getElementById(id).closest('.panel-expandable');
        p.querySelector('.panel-collapse-button').style.display = 'none';
        p.querySelector('.panel-expand-button').style.display = 'block';
        p.querySelector('.panel-viewcontrols').style.display = 'none';

        performAsync(action);
        performSignal(id, signal);
    };

    /**
     *
     * @param event
     * @param id
     * @param cmd
     */
    const onExpandCmd = function (event, id, action = null, signal = null) {
        const p = document.getElementById(id).closest('.panel-expandable');
        p.querySelector('.panel-expand-button').style.display = 'none';
        p.querySelector('.panel-collapse-button').style.display = 'block';
        p.querySelector('.panel-viewcontrols').style.display = 'flex';

        performAsync(action);
        performSignal(id, signal);
    };

    const performAsync = function (action) {
        if (action !== null) {
            fetch(action, {
                method: 'GET',
            });
        }
    }

    const performSignal = function (id, signal) {
        const b = document.getElementById(id);
        if (signal !== null) {
            $(b).trigger(signal.signal_id, {
                'id' : signal.signal_id,
                'event' : signal.event,
                'triggerer' : b,
                'options' : signal.options});
        }
    }

    /**
     * Public interface
     */
    return {
        initExpandable,
        onCollapseCmd,
        onExpandCmd,
    };
};

il = il || {};
il.UI = il.UI || {};

il.UI.panel = panel();
