/**
 * This represents one tooltip on the page.
 */
class Tooltip {
    /**
     * @type {HTMLElement}
     */
    #tooltip;

    /**
     * The tooltip element itself.
     * @type {Element}
     */
    #element;

    /**
     * The container of the tooltip and the trigger element.
     * @type {Element}
     */
    #container;

    /**
     * The HTMLDocument this all exists inside.
     * @type {HTMLDocument}
     */
    #document;

    /**
     * The Window through which we see that stuff.
     * @type {Window}
     */
    #window;

    /**
     * This will be the "main"-container if the tooltip is inside one.
     * @type {?Element}
     */
    #main = null;

    constructor(element) {
        this.#container = element.parentElement;
        this.#element = element;
        this.#document = element.ownerDocument;
        this.#window = this.#document.defaultView || this.#document.parentWindow;

        var tooltip_id = this.#element.getAttribute("aria-describedby");
        if (tooltip_id === null) {
            throw new Error("Could not find expected attribute aria-describedby for element with tooltip.");
        }

        this.#tooltip = this.#document.getElementById(tooltip_id);
        if (this.#tooltip === null) {
            throw new Error("Tooltip " + tooltip_id + " not found.", {cause: this.#element});
        }

        let main = getVisibleMainElement(this.#document);
        if (null !== main && main.contains(this.#tooltip)) {
            this.#main = main;
        }

        this.showTooltip = this.showTooltip.bind(this);
        this.hideTooltip = this.hideTooltip.bind(this);
        this.onKeyDown = this.onKeyDown.bind(this);
        this.onPointerDown = this.onPointerDown.bind(this);

        this.bindElementEvents();
        this.bindContainerEvents();
    }

    /**
     * @returns {HTMLElement}
     */
    get tooltip() {
        return this.#tooltip;
    }

    /**
     * @returns {undefined}
     */
    showTooltip() {
        this.#container.classList.add("c-tooltip--visible");
        this.bindDocumentEvents();

        this.checkVerticalBounds();
        this.checkHorizontalBounds();
    }

    /**
     * @returns {undefined}
     */
    hideTooltip() {
        this.#container.classList.remove("c-tooltip--visible");
        this.unbindDocumentEvents();

        this.#container.classList.remove("c-tooltip--top");
        this.#tooltip.style.transform = null;
    }

    /**
     * @returns {undefined}
     */
    bindElementEvents() {
        this.#element.addEventListener("focus", this.showTooltip);
        this.#element.addEventListener("blur", this.hideTooltip);
    }

    /**
     * @returns {undefined}
     */
    bindContainerEvents() {
        this.#container.addEventListener("mouseenter", this.showTooltip);
        this.#container.addEventListener("touchstart", this.showTooltip);
        this.#container.addEventListener("mouseleave", this.hideTooltip);
    }

    /**
     * @returns {undefined}
     */
    bindDocumentEvents() {
        this.#document.addEventListener("keydown", this.onKeyDown)
        this.#document.addEventListener("pointerdown", this.onPointerDown)
    }

    /**
     * @returns {undefined}
     */
    unbindDocumentEvents() {
        this.#document.removeEventListener("keydown", this.onKeyDown)
        this.#document.removeEventListener("pointerdown", this.onPointerDown)
    }

    /**
     * @returns {undefined}
     */
    onKeyDown(event) {
        if (event.key === "Esc" || event.key === "Escape") {
            this.hideTooltip();
        }
    }

    /**
     * @returns {undefined}
     */
    onPointerDown(event) {
        if(event.target === this.#element || event.target === this.#tooltip) {
            event.preventDefault();
        }
        else {
            this.hideTooltip();
            this.#element.blur();
        }
    }

    /**
     * @returns {undefined}
     */
    checkVerticalBounds() {
        var ttRect = this.#tooltip.getBoundingClientRect();
        var dpRect = this.getDisplayRect();

        if (ttRect.bottom > (dpRect.top + dpRect.height)) {
            this.#container.classList.add("c-tooltip--top");
        }
    }

    /**
     * @returns {undefined}
     */
    checkHorizontalBounds() {
        var ttRect = this.#tooltip.getBoundingClientRect();
        var dpRect = this.getDisplayRect();

        if ((dpRect.width - dpRect.left) < ttRect.right) {
            this.#tooltip.style.transform = "translateX(" + ((dpRect.width - dpRect.left) - ttRect.right) + "px)";
        }
        if (ttRect.left < dpRect.left) {
            this.#tooltip.style.transform = "translateX(" + ((dpRect.left - ttRect.left) - ttRect.width/2) + "px)";
        }
    }

    /**
     * @returns {{left: number, top: number, width: number, height: number}}
     */
    getDisplayRect() {
        if (this.#main !== null) {
            return this.#main.getBoundingClientRect();
        }

        return {
            left: 0,
            top: 0,
            width: this.#window.innerWidth,
            height: this.#window.innerHeight
        }
    }
}

/**
 * Returns the visible main-element of the given document.
 *
 * A document may contain multiple main-elemets, only one must be visible
 * (not have a hidden-attribute).
 *
 * @param {HTMLDocument} document
 * @returns {HTMLElement|null}
 * @see https://html.spec.whatwg.org/multipage/grouping-content.html#the-main-element
 */
function getVisibleMainElement(document) {
    const main_elements = document.getElementsByTagName("main");
    const visible_main = main_elements.find((element) => !element.hasOwnProperty('hidden'));

    return (undefined !== visible_main) ? visible_main : null;
}

export default Tooltip;
