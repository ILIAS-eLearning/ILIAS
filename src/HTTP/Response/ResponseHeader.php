<?php

namespace ILIAS\HTTP\Response;

/**
 * Interface ResponseHeader
 *
 * Describes the most common response headers
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */

interface ResponseHeader
{

    /**
     * Specifying which web sites can participate in cross-origin resource sharing.
     *
     * @example Access-Control-Allow-Origin: *
     */
    const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    /**
     * Specifies which patch document formats this server supports.
     *
     * @example Accept-Patch: text/example;charset=utf-8
     */
    const ACCEPT_PATCH = 'Accept-Patch';
    /**
     * What partial content range types this server supports via byte serving.
     *
     * @example Accept-Ranges: bytes
     */
    const ACCEPT_RANGES = 'Accept-Ranges';
    /**
     * Valid actions for a specified resource. To be used for a 405 Method not allowed.
     *
     * @example Allow: GET, HEAD
     */
    const ALLOW = 'Allow';
    /**
     * Tells all caching mechanisms from server to client whether they may cache this object. It is
     * measured in seconds.
     *
     * @example Cache-Control: max-age=3600
     */
    const CACHE_CONTROL = 'Cache-Control';
    /**
     * Control options for the current connection.
     *
     * @example Connection: close
     */
    const CONNECTION = 'Connection';
    /**
     * An opportunity to raise a "File Download" dialogue box for a known MIME type with binary
     * format or suggest a filename for dynamic content. Quotes are necessary with special
     * characters.
     *
     * @example Content-Disposition: attachment; filename="fname.ext"
     */
    const CONTENT_DISPOSITION = 'Content-Disposition';
    /**
     * The type of encoding used on the data.
     *
     * @example Content-Encoding: gzip
     */
    const CONTENT_ENCODING = 'Content-Encoding';
    /**
     * The natural language or languages of the intended audience for the enclosed content.
     *
     * @example Content-Language: de
     */
    const CONTENT_LANGUAGE = 'Content-Language';
    /**
     * The length of the response body in octets (8-bit bytes).
     *
     * @example Content-Length: 348
     */
    const CONTENT_LENGTH = 'Content-Length';
    /**
     * An alternate location for the returned data.
     *
     * @example Content-Location: /index.htm
     */
    const CONTENT_LOCATION = 'Content-Location';
    /**
     * Where in a full body message this partial message belongs.
     *
     * @example Content-Range: bytes 21010-47021/47022
     */
    const CONTENT_RANGE = 'Content-Range';
    /**
     * The MIME type of this content.
     *
     * @example Content-Type: text/html; charset=utf-8
     */
    const CONTENT_TYPE = 'Content-Type';
    /**
     * The date and time that the message was sent (in "HTTP-date" format as defined by RFC 7231)
     *
     * @example Date: Tue, 15 Nov 1994 08:12:31 GMT
     */
    const DATE = 'Date';
    /**
     * An identifier for a specific version of a resource, often a message digest.
     *
     * @example ETag: "737060cd8c284d8af7ad3082f209582d"
     */
    const ETAG = 'ETag';
    /**
     * Gives the date/time after which the response is considered stale (in "HTTP-date" format as
     * defined by RFC 7231).
     *
     * @example Expires: Thu, 01 Dec 1994 16:00:00 GMT
     */
    const EXPIRES = 'Expires';
    /**
     * The last modified date for the requested object (in "HTTP-date" format as defined by RFC
     * 7231).
     *
     * @example Last-Modified: Tue, 15 Nov 1994 12:45:26 GMT
     */
    const LAST_MODIFIED = 'Last-Modified';
    /**
     * Used to express a typed relationship with another resource, where the relation type is
     * defined by RFC 5988.
     *
     * @example Link: </feed>; rel="alternate"
     */
    const LINK = 'Link';
    /**
     * Used in redirection, or when a new resource has been created.
     *
     * @example Location: http://www.w3.org/pub/WWW/People.html
     */
    const LOCATION = 'Location';
    /**
     * This field is supposed to set P3P policy.
     *
     * @example P3P: CP="This is not a P3P policy! See
     *          http://www.google.com/support/accounts/bin/answer.py?hl=en&answer=151657 for more
     *          info."
     */
    const P3P = 'P3P';
    /**
     * Implementation-specific fields that may have various effects anywhere along the
     * request-response chain.
     *
     * @example Pragma: no-cache
     */
    const PRAGMA = 'Pragma';
    /**
     * HTTP Public Key Pinning, announces hash of website's authentic TLS certificate.
     *
     * @example Public-Key-Pins: max-age=2592000;
     *          pin-sha256="E9CZ9INDbd+2eRQozYqqbQ2yXLVKB9+xcprMF+44U1g=";
     */
    const PUBLIC_KEY_PINS = 'Public-Key-Pins';
    /**
     * If an entity is temporarily unavailable, this instructs the client to try again later.
     * Value could be a specified period of time (in seconds) or a HTTP-date.
     *
     * @example Retry-After: 120
     * @example Retry-After: Fri, 07 Nov 2014 23:59:59 GMT
     */
    const RETRY_AFTER = 'Retry-After';
    /**
     * A name for the server.
     *
     * @example Server: Apache/2.4.1 (Unix)
     */
    const SERVER = 'Server';
    /**
     * A HSTS Policy informing the HTTP client how long to cache
     * the HTTPS only policy and whether this applies to subdomains.
     *
     * @example Strict-Transport-Security: max-age=16070400; includeSubDomains
     */
    const STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    /**
     * The Trailer general field value indicates that the given set of header fields
     * is present in the trailer of a message encoded with chunked transfer coding.
     *
     * @example Trailer: Max-Forwards
     */
    const TRAILER = 'Trailer';
    /**
     * The form of encoding used to safely transfer the entity to the user.
     * Currently defined methods are: chunked, compress, deflate, gzip, identity.
     *
     * @example Transfer-Encoding: chunked
     */
    const TRANSFER_ENCODING = 'Transfer-Encoding';
    /**
     * Tracking Status Value, value suggested to be sent in response to a DNT(do-not-track),
     * possible values:
     * "!" — under construction
     * "?" — dynamic
     * "G" — gateway to multiple parties
     * "N" — not tracking
     * "T" — tracking
     * "C" — tracking with consent
     * "P" — tracking only if consented
     * "D" — disregarding DNT
     * "U" — updated
     *
     * @example TSV: ?
     */
    const TSV = 'TSV';
    /**
     * Tells downstream proxies how to match future request headers to decide whether
     * the cached response can be used rather than requesting a fresh one from the origin server.
     *
     * @example Vary: *
     * @example Vary: Accept-Language
     */
    const VARY = 'Vary';
    /**
     * A general warning about possible problems with the entity body.
     *
     * @example Warning: 199 Miscellaneous warning
     */
    const WARNING = 'Warning';
    /**
     * Indicates the authentication scheme that should be used to access the requested entity.
     *
     * @example WWW-Authenticate: Basic
     */
    const WWW_AUTHENTICATE = 'WWW-Authenticate';
    /**
     * Cross-site scripting (XSS) filter.
     *
     * @example X-XSS-Protection: 1; mode=block
     */
    const X_XSS_PROTECTION = 'X-XSS-Protection';
    /**
     * The only defined value, "nosniff", prevents Internet Explorer from MIME-sniffing a response
     * away from the declared content-type. This also applies to Google Chrome, when downloading
     * extensions.
     *
     * @example X-Content-Type-Options: nosniff
     */
    const X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    /**
     * Tells a client to prefer HTTPS.
     *
     * @example Upgrade-Insecure-Requests: 1
     */
    const UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
}
