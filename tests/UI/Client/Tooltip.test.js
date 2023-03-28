import { expect } from 'chai';

import Tooltip from "../../../src/UI/templates/js/Core/src/core.Tooltip.js";

describe("tooltip class exists", function() {
    it("Tooltip", function() {
        expect(Tooltip).not.to.be.undefined; 
    });
});

describe("tooltip initializes", function() {
    var addEventListenerElement = [];
    var addEventListenerContainer = [];
    var getAttribute = [];
    var getElementById = [];

    var window = {};

    var document = {
        getElementById: function(which) {
            getElementById.push(which);
            return tooltip;
        },
        parentWindow: window
    };

    var container = {
        addEventListener: function(ev, handler) {
            addEventListenerContainer.push({e : ev, h: handler});
        },
    };

    var element = {
        addEventListener: function(ev, handler) {
            addEventListenerElement.push({e : ev, h: handler});
        },
        getAttribute: function(which) {
            getAttribute.push(which);
            return "attribute-" + which;
        },
        ownerDocument: document,
        parentElement: container
    };

    var tooltip = {
        style: {
            transform: null
        }
    };

    var object = new Tooltip(element);
    object.checkVerticalBounds = function() {};
    object.checkHorizontalBounds = function() {};

    it("searches tooltip element", function() {
        expect(getAttribute).to.include("aria-describedby");
        expect(getElementById).to.include("attribute-aria-describedby");
    });

    it("binds events on element", function() {
        expect(addEventListenerElement).to.deep.include({e: "focus", h: object.showTooltip});
        expect(addEventListenerElement).to.deep.include({e: "blur", h: object.hideTooltip});
    });


    it("binds events on container", function() {
        expect(addEventListenerContainer).to.deep.include({e: "mouseenter", h: object.showTooltip});
        expect(addEventListenerContainer).to.deep.include({e: "touchstart", h: object.showTooltip});
        expect(addEventListenerContainer).to.deep.include({e: "mouseleave", h: object.hideTooltip});
    });
});


describe("tooltip show works", function() {
    var addEventListenerDocument = [];
    var classListAdd = [];

    var window = {};

    var document = {
        addEventListener: function(ev, handler) {
            addEventListenerDocument.push({e: ev, h: handler});
        },
        getElementById: function(which) {
            return tooltip;
        },
        parentWindow: window
    };

    var container = {
        addEventListener: function(ev, handler) {
        },
        classList: {
            add: function(which) {
                classListAdd.push(which);
            },
            remove: function(which) {
                classListRemove.push(which);
            }
        }
    };

    var element = {
        addEventListener: function(ev, handler) {
        },
        getAttribute: function(which) {
            return "attribute-" + which;
        },
        ownerDocument: document,
        parentElement: container
    };

    var tooltip = {
        style: {
            transform: null
        }
    };

    var object = new Tooltip(element);
    object.checkVerticalBounds = function() {};
    object.checkHorizontalBounds = function() {};

    it("binds events on document", function () {
        classListAdd = [];

        expect(addEventListenerDocument).not.to.deep.include({e: "keydown", h: object.onKeyDown});
        expect(addEventListenerDocument).not.to.deep.include({e: "pointerdown", h: object.onPointerDown});
    
        object.showTooltip();

        expect(addEventListenerDocument).to.deep.include({e: "keydown", h: object.onKeyDown});
        expect(addEventListenerDocument).to.deep.include({e: "pointerdown", h: object.onPointerDown});
    });

    it("adds visibility classes from tooltip", function () {
        classListAdd = [];

        object.showTooltip();

        expect(classListAdd).to.deep.equal(["c-tooltip--visible"]);
    });
});

describe("tooltip hide works", function() {
    var classListRemove = [];
    var removeEventListener = [];

    var window = {};

    var document = {
        addEventListener: function(ev, handler) {
        },
        removeEventListener: function(ev, handler) {
            removeEventListener.push({e: ev, h: handler});
        },
        getElementById: function(which) {
            return tooltip;
        },
        parentWindow: window
    };

    var container = {
        addEventListener: function(ev, handler) {
        },
        classList: {
            add: function(which) {
            },
            remove: function(which) {
                classListRemove.push(which);
            }
        }
    };

    var element = {
        addEventListener: function(ev, handler) {
        },
        getAttribute: function(which) {
            return "attribute-" + which;
        },
        ownerDocument: document,
        parentElement: container
    };

    var tooltip = {
        style: {
            transform: null
        }
    };

    var object = new Tooltip(element);
    object.checkVerticalBounds = function() {};
    object.checkHorizontalBounds = function() {};

    it("unbinds events on document when tooltip hides", function () {
        expect(removeEventListener).not.to.deep.include({e: "keydown", h: object.onKeyDown});
        expect(removeEventListener).not.to.deep.include({e: "pointerdown", h: object.onPointerDown});
    
        object.hideTooltip();

        expect(removeEventListener).to.deep.include({e: "keydown", h: object.onKeyDown});
        expect(removeEventListener).to.deep.include({e: "pointerdown", h: object.onPointerDown});
    });

    it("removes visibility classes from tooltip", function () {
        classListRemove = [];

        object.hideTooltip();

        expect(classListRemove).to.deep.equal(["c-tooltip--visible", "c-tooltip--top"]);
    }); 

    it("hides on escape key", function () {
        var hideTooltipCalled = false;
        var keep = object.hideTooltip;
        object.hideTooltip = function () {
            hideTooltipCalled = true;
        };

        object.onKeyDown({key : "Escape"});

        expect(hideTooltipCalled).to.equal(true);
        object.hideTooltip = keep;
    });

    it("hides on esc key", function () {
        var hideTooltipCalled = false;
        var keep = object.hideTooltip;
        object.hideTooltip = function () {
            hideTooltipCalled = true;
        };

        object.onKeyDown({key : "Esc"});

        expect(hideTooltipCalled).to.equal(true);
        object.hideTooltip = keep;
    });

    it("ignores other key", function () {
        var hideTooltipCalled = false;
        var keep = object.hideTooltip;
        object.hideTooltip = function () {
            hideTooltipCalled = true;
        };

        object.onKeyDown({key : "Strg"});

        expect(hideTooltipCalled).to.equal(false);
        object.hideTooltip = keep;
    });

    it("hides and calls blur on click somewhere", function() {
        var hideTooltipCalled = false;
        var blurCalled = false;
        var keep = object.hideTooltip;
        object.hideTooltip = function () {
            hideTooltipCalled = true;
        };
        object.element.blur = function () {
            blurCalled = true;
        };

        object.onPointerDown({});

        expect(hideTooltipCalled).to.equal(true);
        expect(blurCalled).to.equal(true);
        object.hideTooltip = keep;
    });

    it("does not hide on click on tooltip and prevents default", function() {
        var hideTooltipCalled = false;
        var preventDefaultCalled= false;
        var keep = object.hideTooltip;
        object.hideTooltip = function () {
            hideTooltipCalled = true;
        };
        var preventDefault = function () {
            preventDefaultCalled= true;
        };

        object.onPointerDown({target: object.tooltip, preventDefault: preventDefault});

        expect(hideTooltipCalled).to.equal(false);
        expect(preventDefaultCalled).to.equal(true);
        object.hideTooltip = keep;
    });

    it("does not hide on click on element and prevents default", function() {
        var hideTooltipCalled = false;
        var preventDefaultCalled = false;
        var keep = object.hideTooltip;
        object.hideTooltip = function () {
            hideTooltipCalled = true;
        };
        var preventDefault = function () {
            preventDefaultCalled= true;
        };

        object.onPointerDown({target: object.element, preventDefault: preventDefault});

        expect(hideTooltipCalled).to.equal(false);
        expect(preventDefaultCalled).to.equal(true);
        object.hideTooltip = keep;
    });
});

describe("tooltip is on top if there is not enough space below", function() {
    var classListAdd = [];
    var classListRemove = [];

    var window = {};

    var document = {
        addEventListener: function(ev, handler) {
        },
        removeEventListener: function(ev, handler) {
        },
        getElementById: function(which) {
            return tooltip;
        },
        parentWindow: window
    };

    var container = {
        addEventListener: function(ev, handler) {
        },
        classList: {
            add: function(which) {
                classListAdd.push(which);
            },
            remove: function(which) {
                classListRemove.push(which);
            }
        }
    };

    var element = {
        addEventListener: function(ev, handler) {
        },
        getAttribute: function(which) {
            return "attribute-" + which;
        },
        ownerDocument: document,
        parentElement: container
    };

    var clientRect = null;
    var tooltip = {
        getBoundingClientRect: function () {
            return clientRect;
        },
        style: {
            transform: null
        }
    };


    it("does not add top-class if there is enough space", function () {
        clientRect = {bottom: 90};
        window.innerHeight = 100;

        classListAdd = [];
        var object = new Tooltip(element);
        object.main = null;
        object.showTooltip();

        expect(classListAdd).to.deep.equal(["c-tooltip--visible"]);
    });

    it("does add top-class if there is not enough space", function () {
        clientRect = {bottom: 110};
        window.innerHeight = 100;

        classListAdd = [];
        var object = new Tooltip(element);
        object.main = null;
        object.showTooltip();

        expect(classListAdd).to.deep.equal(["c-tooltip--visible", "c-tooltip--top"]);
    });

    it("removes top-class when it hides", function() {
        var object = new Tooltip(element);
        object.main = null;

        classListRemove = [];
        object.hideTooltip();

        expect(classListRemove).to.deep.equal(["c-tooltip--visible", "c-tooltip--top"]);
    });

    it("removes transform when it hides", function() {
        var object = new Tooltip(element);
        object.main = null;

        object.tooltip.style.transform = "foo";
        object.hideTooltip();

        expect(object.tooltip.style.transform).to.equal(null);
    });
});

describe("tooltip moves to left or right if there is not enough space", function() {
    var window = {};

    var document = {
        addEventListener: function(ev, handler) {
        },
        removeEventListener: function(ev, handler) {
        },
        getElementById: function(which) {
            return tooltip;
        },
        parentWindow: window
    };

    var container = {
        addEventListener: function(ev, handler) {
        },
        classList: {
            add: function(which) {
            },
            remove: function(which) {
            }
        }
    };

    var element = {
        addEventListener: function(ev, handler) {
        },
        getAttribute: function(which) {
            return "attribute-" + which;
        },
        ownerDocument: document,
        parentElement: container
    };

    var clientRect = null;
    var tooltip = {
        getBoundingClientRect: function () {
            return clientRect;
        },
        style: {
            transform: null
        }
    };


    it("does not move if there is enough space", function () {
        clientRect = {left: 10, right: 20};
        window.innerWidth= 100;

        var object = new Tooltip(element);
        object.main = null;
        object.showTooltip();

        expect(tooltip.style.transform).to.equal(null);
    });

    it("does move to left if there is enough space", function () {
        clientRect = {left: 20, right: 110};
        window.innerWidth = 100;

        var object = new Tooltip(element);
        object.main = null;
        object.showTooltip();

        expect(tooltip.style.transform).to.equal("translateX(-10px)");
    });

    it("does move to right if there is enough space", function () {
        clientRect = {left: -10, right: 20, width: 40};
        window.innerWidth= 100;

        var object = new Tooltip(element);
        object.main = null;
        object.showTooltip();

        expect(tooltip.style.transform).to.equal("translateX(-10px)");
    });
});

describe("get display rect", function() {
    var window = {};

    var document = {
        addEventListener: function(ev, handler) {
        },
        removeEventListener: function(ev, handler) {
        },
        getElementById: function(which) {
            return tooltip;
        },
        parentWindow: window
    };

    var container = {
        addEventListener: function(ev, handler) {
        },
        classList: {
            add: function(which) {
            },
            remove: function(which) {
            }
        }
    };

    var element = {
        addEventListener: function(ev, handler) {
        },
        getAttribute: function(which) {
            return "attribute-" + which;
        },
        ownerDocument: document,
        parentElement: container
    };

    var clientRect = null;
    var tooltip = {
        getBoundingClientRect: function () {
            return clientRect;
        },
        style: {
            transform: null
        }
    };

    it("returns window coordinates if tooltip is not in main", function () {
        var object = new Tooltip(element);
        var which = null;
        document.getElementsByTagName = function (w) {
            which = w;
            return [];
        }

        window.innerWidth = 100;
        window.innerHeight= 150;

        var rect = object.getDisplayRect();

        expect(which).to.equal("main");
        expect(rect).to.deep.equal({left: 0, top: 0, width: 100, height: 150});
    });

    it("returns main coordinates if tooltip is in main", function () {
        var object = new Tooltip(element);
        var which = null;
        var main = {
            getBoundingClientRect: function () {
                return {
                    left: 10,
                    top: 20,
                    width: 110,
                    height: 120
                };
            }
        }
        document.getElementsByTagName = function (w) {
            which = w;
            return [main];
        }

        var rect = object.getDisplayRect();

        expect(which).to.equal("main");
        expect(rect).to.deep.equal({left: 10, top: 20, width: 110, height: 120});
    });
});
