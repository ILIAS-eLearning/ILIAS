<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote 
 */

$metadata['https://adfstest.office.databay.de/adfs/services/trust'] = array (
	'entityid' => 'https://adfstest.office.databay.de/adfs/services/trust',
	
	'sign.logout' => TRUE, // Important
	'contacts' =>
		array (
			0 =>
				array (
					'contactType' => 'support',
				),
		),
	'metadata-set' => 'saml20-idp-remote',
	'SingleSignOnService' =>
		array (
			0 =>
				array (
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
					'Location' => 'https://adfstest.office.databay.de/adfs/ls/',
				),
			1 =>
				array (
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
					'Location' => 'https://adfstest.office.databay.de/adfs/ls/',
				),
		),
	'SingleLogoutService' =>
		array (
			0 =>
				array (
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
					'Location' => 'https://adfstest.office.databay.de/adfs/ls/',
				),
			1 =>
				array (
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
					'Location' => 'https://adfstest.office.databay.de/adfs/ls/',
				),
		),
	'ArtifactResolutionService' =>
		array (
			0 =>
				array (
					'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
					'Location' => 'https://adfstest.office.databay.de/adfs/services/trust/artifactresolution',
					'index' => 0,
				),
		),
	'keys' =>
		array (
			0 =>
				array (
					'encryption' => true,
					'signing' => false,
					'type' => 'X509Certificate',
					'X509Certificate' => 'MIIC9jCCAd6gAwIBAgIQKHAhQehXRZpB3uGHrSu4dDANBgkqhkiG9w0BAQsFADA3MTUwMwYDVQQDEyxBREZTIEVuY3J5cHRpb24gLSBhZGZzdGVzdC5vZmZpY2UuZGF0YWJheS5kZTAeFw0xNTExMjgyMDAxNDJaFw0xNjExMjcyMDAxNDJaMDcxNTAzBgNVBAMTLEFERlMgRW5jcnlwdGlvbiAtIGFkZnN0ZXN0Lm9mZmljZS5kYXRhYmF5LmRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqbXvjacSKQbRECYDvncdDNdphA4VuMa/oTPxV3ciy8AfQaVh+hDvijHWgj4dzk6Xouk5Y3S6+H1iQ3xEIzx9Aj1BTMF9tkuAGQ5xrLBozRdSzP3xM5WbM+bUhRJXsz6zCn6szeyqNBTtoFA/PwNaFO9TLK9dqAwiG9ar5SLJP80hldzNFfP1yc3y8j5odvxqZVWUp/84MRaCkPlPw+jip2XiOU06/ZU845gOjnbF7u915YhHxUZ8o25bPURXgyeE5Pn8k9YrrPBKy1qm2l3UTnZATFJ2KrXQvhoCJEptlOMkbsHs0ATZN6Cns8b8JncXZqOVfO5oQz4O2/bF9p5bKQIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQB9sbiYyGzMD26P8fwpqQAFJ66axzv3m6DtlIj9H5vwuifUJEJm2ei0YJHlD26tH2eUc9o4Mh5DmoSEWVl0NmgJtWE6mrudDaGZ5+eNCxNeAFNl/rmTNNpN6QGr7sNv0+XVuCb527moiRhbw4efe5L1+XloKAz4UIQuYVFGtbbdbHAJZDBIbB9EiMMIrf7tiSTlMeBH7BPxO+XH+1inidSuf6Nth+eUUu1HaAfwOQ/7v0K3+PnEQkuDuD9Xdp+Tqgw2TGAjAPvaBSkNSMXBAleZj/GlSx1E+Fc80B3t2p2cWakjFM3L/cER3kSaPzstbU1HSpC5cvxQ5VnAggdvwJEL',
				),
			1 =>
				array (
					'encryption' => false,
					'signing' => true,
					'type' => 'X509Certificate',
					'X509Certificate' => 'MIIC8DCCAdigAwIBAgIQSQsrXHfrKZdOqFQPj84I7TANBgkqhkiG9w0BAQsFADA0MTIwMAYDVQQDEylBREZTIFNpZ25pbmcgLSBhZGZzdGVzdC5vZmZpY2UuZGF0YWJheS5kZTAeFw0xNTExMjgyMDAxNDNaFw0xNjExMjcyMDAxNDNaMDQxMjAwBgNVBAMTKUFERlMgU2lnbmluZyAtIGFkZnN0ZXN0Lm9mZmljZS5kYXRhYmF5LmRlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArv/oCPa//riM99Yn4DItRjQBU+21vX908Ltg6TziFNwV7/AOYl9iDChCq1RJBe7KEFgjnHUKjN6OegqkjUve1ktCfR3J6w4JyMPnzBrJylmmticKqaD4d1J5UERGIHEpWiqQcj6N6ev9qa40C/wGos618ud0o7l9n2PmfgWjpLqOfeR3GCpCYARxxMKbZkivkxBJauIS7qEFIu7JghI4b5UGS57G8OKUp7v8MVwrBNb7pa7hhnXZIHm6tsAKPF6CpBuxWKu6qV0JhB9B+p1w1ZRFI3Lk5XRGOnI/SOO3fLFohFhmoxEfxrlz2MEfN/ZZUMb+OJe/s4rPBpynNIuubwIDAQABMA0GCSqGSIb3DQEBCwUAA4IBAQAXyzLEYa1wqIDRADy9B6bYQr9jsC7jo3jiQ+MPxwk4GI/c361L1m5QFkexfcrxmPRroyxcRHEz+TRLXaOahLDdYT5/3MH0rXOrn+pFjuex8yoT6Y2k8VQwCouMBwgOgMsF/P5mFko3gvqHctZVspbnRXx6Pjl6A/5z0vxUx+KcNJfYbK1tDlnsF9GubVY/dfQlxSzW+y7AbHXIcxEh3WxetFiCY+3JAm1ZdfciIKip0zir3GMfzLWY0V7lQCyF7AuQegpRDk0HP82+3hiBKKqKS0ZQF0BxvM4kYA1KcgRuc5oOMa7GLxzN1a028hlUH/uizzNwQ4P6p9crqlfQS/5f',
				),
		),
);