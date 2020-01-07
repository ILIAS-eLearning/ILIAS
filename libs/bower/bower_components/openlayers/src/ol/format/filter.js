goog.provide('ol.format.filter');

goog.require('ol.format.filter.And');
goog.require('ol.format.filter.Bbox');
goog.require('ol.format.filter.Contains');
goog.require('ol.format.filter.During');
goog.require('ol.format.filter.EqualTo');
goog.require('ol.format.filter.GreaterThan');
goog.require('ol.format.filter.GreaterThanOrEqualTo');
goog.require('ol.format.filter.Intersects');
goog.require('ol.format.filter.IsBetween');
goog.require('ol.format.filter.IsLike');
goog.require('ol.format.filter.IsNull');
goog.require('ol.format.filter.LessThan');
goog.require('ol.format.filter.LessThanOrEqualTo');
goog.require('ol.format.filter.Not');
goog.require('ol.format.filter.NotEqualTo');
goog.require('ol.format.filter.Or');
goog.require('ol.format.filter.Within');


/**
 * Create a logical `<And>` operator between two or more filter conditions.
 *
 * @param {...ol.format.filter.Filter} conditions Filter conditions.
 * @returns {!ol.format.filter.And} `<And>` operator.
 * @api
 */
ol.format.filter.and = function(conditions) {
  var params = [null].concat(Array.prototype.slice.call(arguments));
  return new (Function.prototype.bind.apply(ol.format.filter.And, params));
};


/**
 * Create a logical `<Or>` operator between two or more filter conditions.
 *
 * @param {...ol.format.filter.Filter} conditions Filter conditions.
 * @returns {!ol.format.filter.Or} `<Or>` operator.
 * @api
 */
ol.format.filter.or = function(conditions) {
  var params = [null].concat(Array.prototype.slice.call(arguments));
  return new (Function.prototype.bind.apply(ol.format.filter.Or, params));
};


/**
 * Represents a logical `<Not>` operator for a filter condition.
 *
 * @param {!ol.format.filter.Filter} condition Filter condition.
 * @returns {!ol.format.filter.Not} `<Not>` operator.
 * @api
 */
ol.format.filter.not = function(condition) {
  return new ol.format.filter.Not(condition);
};


/**
 * Create a `<BBOX>` operator to test whether a geometry-valued property
 * intersects a fixed bounding box
 *
 * @param {!string} geometryName Geometry name to use.
 * @param {!ol.Extent} extent Extent.
 * @param {string=} opt_srsName SRS name. No srsName attribute will be
 *    set on geometries when this is not provided.
 * @returns {!ol.format.filter.Bbox} `<BBOX>` operator.
 * @api
 */
ol.format.filter.bbox = function(geometryName, extent, opt_srsName) {
  return new ol.format.filter.Bbox(geometryName, extent, opt_srsName);
};

/**
 * Create a `<Contains>` operator to test whether a geometry-valued property
 * contains a given geometry.
 *
 * @param {!string} geometryName Geometry name to use.
 * @param {!ol.geom.Geometry} geometry Geometry.
 * @param {string=} opt_srsName SRS name. No srsName attribute will be
 *    set on geometries when this is not provided.
 * @returns {!ol.format.filter.Contains} `<Contains>` operator.
 * @api
 */
ol.format.filter.contains = function(geometryName, geometry, opt_srsName) {
  return new ol.format.filter.Contains(geometryName, geometry, opt_srsName);
};

/**
 * Create a `<Intersects>` operator to test whether a geometry-valued property
 * intersects a given geometry.
 *
 * @param {!string} geometryName Geometry name to use.
 * @param {!ol.geom.Geometry} geometry Geometry.
 * @param {string=} opt_srsName SRS name. No srsName attribute will be
 *    set on geometries when this is not provided.
 * @returns {!ol.format.filter.Intersects} `<Intersects>` operator.
 * @api
 */
ol.format.filter.intersects = function(geometryName, geometry, opt_srsName) {
  return new ol.format.filter.Intersects(geometryName, geometry, opt_srsName);
};

/**
 * Create a `<Within>` operator to test whether a geometry-valued property
 * is within a given geometry.
 *
 * @param {!string} geometryName Geometry name to use.
 * @param {!ol.geom.Geometry} geometry Geometry.
 * @param {string=} opt_srsName SRS name. No srsName attribute will be
 *    set on geometries when this is not provided.
 * @returns {!ol.format.filter.Within} `<Within>` operator.
 * @api
 */
ol.format.filter.within = function(geometryName, geometry, opt_srsName) {
  return new ol.format.filter.Within(geometryName, geometry, opt_srsName);
};


/**
 * Creates a `<PropertyIsEqualTo>` comparison operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!(string|number)} expression The value to compare.
 * @param {boolean=} opt_matchCase Case-sensitive?
 * @returns {!ol.format.filter.EqualTo} `<PropertyIsEqualTo>` operator.
 * @api
 */
ol.format.filter.equalTo = function(propertyName, expression, opt_matchCase) {
  return new ol.format.filter.EqualTo(propertyName, expression, opt_matchCase);
};


/**
 * Creates a `<PropertyIsNotEqualTo>` comparison operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!(string|number)} expression The value to compare.
 * @param {boolean=} opt_matchCase Case-sensitive?
 * @returns {!ol.format.filter.NotEqualTo} `<PropertyIsNotEqualTo>` operator.
 * @api
 */
ol.format.filter.notEqualTo = function(propertyName, expression, opt_matchCase) {
  return new ol.format.filter.NotEqualTo(propertyName, expression, opt_matchCase);
};


/**
 * Creates a `<PropertyIsLessThan>` comparison operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!number} expression The value to compare.
 * @returns {!ol.format.filter.LessThan} `<PropertyIsLessThan>` operator.
 * @api
 */
ol.format.filter.lessThan = function(propertyName, expression) {
  return new ol.format.filter.LessThan(propertyName, expression);
};


/**
 * Creates a `<PropertyIsLessThanOrEqualTo>` comparison operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!number} expression The value to compare.
 * @returns {!ol.format.filter.LessThanOrEqualTo} `<PropertyIsLessThanOrEqualTo>` operator.
 * @api
 */
ol.format.filter.lessThanOrEqualTo = function(propertyName, expression) {
  return new ol.format.filter.LessThanOrEqualTo(propertyName, expression);
};


/**
 * Creates a `<PropertyIsGreaterThan>` comparison operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!number} expression The value to compare.
 * @returns {!ol.format.filter.GreaterThan} `<PropertyIsGreaterThan>` operator.
 * @api
 */
ol.format.filter.greaterThan = function(propertyName, expression) {
  return new ol.format.filter.GreaterThan(propertyName, expression);
};


/**
 * Creates a `<PropertyIsGreaterThanOrEqualTo>` comparison operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!number} expression The value to compare.
 * @returns {!ol.format.filter.GreaterThanOrEqualTo} `<PropertyIsGreaterThanOrEqualTo>` operator.
 * @api
 */
ol.format.filter.greaterThanOrEqualTo = function(propertyName, expression) {
  return new ol.format.filter.GreaterThanOrEqualTo(propertyName, expression);
};


/**
 * Creates a `<PropertyIsNull>` comparison operator to test whether a property value
 * is null.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @returns {!ol.format.filter.IsNull} `<PropertyIsNull>` operator.
 * @api
 */
ol.format.filter.isNull = function(propertyName) {
  return new ol.format.filter.IsNull(propertyName);
};


/**
 * Creates a `<PropertyIsBetween>` comparison operator to test whether an expression
 * value lies within a range given by a lower and upper bound (inclusive).
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!number} lowerBoundary The lower bound of the range.
 * @param {!number} upperBoundary The upper bound of the range.
 * @returns {!ol.format.filter.IsBetween} `<PropertyIsBetween>` operator.
 * @api
 */
ol.format.filter.between = function(propertyName, lowerBoundary, upperBoundary) {
  return new ol.format.filter.IsBetween(propertyName, lowerBoundary, upperBoundary);
};


/**
 * Represents a `<PropertyIsLike>` comparison operator that matches a string property
 * value against a text pattern.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!string} pattern Text pattern.
 * @param {string=} opt_wildCard Pattern character which matches any sequence of
 *    zero or more string characters. Default is '*'.
 * @param {string=} opt_singleChar pattern character which matches any single
 *    string character. Default is '.'.
 * @param {string=} opt_escapeChar Escape character which can be used to escape
 *    the pattern characters. Default is '!'.
 * @param {boolean=} opt_matchCase Case-sensitive?
 * @returns {!ol.format.filter.IsLike} `<PropertyIsLike>` operator.
 * @api
 */
ol.format.filter.like = function(propertyName, pattern,
    opt_wildCard, opt_singleChar, opt_escapeChar, opt_matchCase) {
  return new ol.format.filter.IsLike(propertyName, pattern,
      opt_wildCard, opt_singleChar, opt_escapeChar, opt_matchCase);
};


/**
 * Create a `<During>` temporal operator.
 *
 * @param {!string} propertyName Name of the context property to compare.
 * @param {!string} begin The begin date in ISO-8601 format.
 * @param {!string} end The end date in ISO-8601 format.
 * @returns {!ol.format.filter.During} `<During>` operator.
 * @api
 */
ol.format.filter.during = function(propertyName, begin, end) {
  return new ol.format.filter.During(propertyName, begin, end);
};
