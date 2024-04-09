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

    const onCollapseCmd = function (event, id, action) {
        const p = document.getElementById(id).closest('.panel-expandable');
        p.querySelector('.panel-collapse-button').style.display = 'none';
        p.querySelector('.panel-expand-button').style.display = 'block';
        p.querySelector('.panel-viewcontrols').style.display = 'none';

        // Perform async request for collapse action
        fetch(action, {
            method: 'GET',
        });
    };

    /**
     *
     * @param event
     * @param id
     * @param cmd
     */
    const onExpandCmd = function (event, id, action) {
        const p = document.getElementById(id).closest('.panel-expandable');
        p.querySelector('.panel-expand-button').style.display = 'none';
        p.querySelector('.panel-collapse-button').style.display = 'block';
        p.querySelector('.panel-viewcontrols').style.display = 'flex';

        // Perform async request for expand action
        fetch(action, {
            method: 'GET',
        });
    };

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
