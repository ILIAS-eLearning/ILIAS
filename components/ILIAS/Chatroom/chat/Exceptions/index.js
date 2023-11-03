exports.FileNotFound = function(path) {
	this.path = path;
	this.message = "The file \""+ path +"\" does not exist!";
	this.name = "FileNotFound";
};
exports.FileNotFound.prototype = Error.prototype;

exports.RequiredPropertyMissing = function(property) {
	this.property = property;
	this.message = "The required property \"" + property + "\" is missing";
};
exports.RequiredPropertyMissing.prototype = Error.prototype;