
function mkfragment(elements) {
    var frag = document.createDocumentFragment();

    for (var i=0 ; i<elements.length ; ++i) {
        frag.appendChild(elements[i]);
    }

    return frag;
};

module.exports.remove = function(el) {
    if (!el.parentNode) {
        return;
    }
    return el.parentNode.removeChild(el);
};

module.exports.replace = function(el, what) {
    if (!el.parentNode) {
        return;
    }
    return el.parentNode.replaceChild(mkfragment(what), el);
};

module.exports.prepend = function(el, what) {
    return el.insertBefore(mkfragment(what), el.firstChild);
};

module.exports.append = function(el, what) {
    var frag = document.createDocumentFragment();
    return el.appendChild(mkfragment(what));
};

// returns newly inserted element
module.exports.after = function(el, what) {
    if (!el.parentNode) {
        return;
    }

    // ie9 doesn't like null for insertBefore
    if (!el.nextSilbling) {
        return el.parentNode.appendChild(mkfragment(what));
    }

    return el.parentNode.insertBefore(mkfragment(what), el.nextSilbling);
};

module.exports.before = function(el, what) {
    if (!el.parentNode) {
        return;
    }
    return el.parentNode.insertBefore(mkfragment(what), el);
};

module.exports.empty = function(parent) {
    // cheap way to remove all children
    parent.innerHTML = '';
};

