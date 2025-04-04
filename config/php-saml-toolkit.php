<?php

$sp_base_url = env('SAML_BASEURL', env('APP_URL', 'https://localhost'));

/*
 | The IDP base url has been tested against CIT shibboleth. It does not
 | support a discovery service at this time (e.g. CIT idselect).
 */
$idp_base_url = env('SAML_IDP_BASEURL', (env('APP_ENV') == 'production')
    ? 'https://shibidp.cit.cornell.edu/idp'
    : 'https://shibidp-test.cit.cornell.edu/idp');

/*
 | The default path of storage/app/keys is ignored by git in a standard
 | Laravel installation, so typically this does not need to be changed.
 */
$cert_path = storage_path(env('SAML_CERT_PATH', 'app/keys'));

return [
    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | If 'strict' is true, then the PHP Toolkit will reject unsigned
    | or unencrypted messages if it expects them to be signed or encrypted.
    | It will also reject messages that do not strictly follow the SAML
    | standard (e.g. Destination, NameId, Conditions, etc.).
    |
    */
    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable debug mode to print errors. Disable in production.
    |
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Set a BaseURL to be used instead of trying to guess the BaseURL of the view
    | that processes the SAML message.
    | For example: http://sp.example.com/ or http://example.com/sp/
    |
    */
    'baseurl' => $sp_base_url.'/sso',

    /*
    |--------------------------------------------------------------------------
    | Service Provider (SP) Data
    |--------------------------------------------------------------------------
    |
    | Data for the Service Provider that you are deploying.
    |
    */
    'sp' => [
        /*
        |----------------------------------------------------------------------
        | SP Entity ID
        |----------------------------------------------------------------------
        |
        | Identifier of the SP entity (must be a URI).
        |
        */
        'entityId' => $sp_base_url.'/sso',

        /*
        |----------------------------------------------------------------------
        | Assertion Consumer Service (ACS)
        |----------------------------------------------------------------------
        |
        | Specifies where and how the <AuthnResponse> message MUST be returned
        | to the requester (our SP).
        |
        */
        'assertionConsumerService' => [
            // URL where the <Response> from the IdP will be returned.
            'url' => $sp_base_url.'/sso/acs',
            // SAML protocol binding to be used. Only HTTP-POST is supported.
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
        ],

        /*
        |--------------------------------------------------------------------------
        | Attribute Consuming Service
        |--------------------------------------------------------------------------
        |
        | If you need to specify requested attributes, define this section.
        | Note: nameFormat, attributeValue, and friendlyName can be omitted.
        | Remove this section if not needed.
        |
        */
        /*
        'attributeConsumingService' => [
            'serviceName' => 'SP test',
            'serviceDescription' => 'Test Service',
            'requestedAttributes' => [
                [
                    // Name of the attribute.
                    'name' => '',
                    // Whether the attribute is required.
                    'isRequired' => false,
                    // Format of the attribute name.
                    'nameFormat' => '',
                    // Friendly name of the attribute.
                    'friendlyName' => '',
                    // Default attribute value.
                    'attributeValue' => '',
                ],
            ],
        ],
        */

        /*
        |--------------------------------------------------------------------------
        | Single Logout Service (SLO)
        |--------------------------------------------------------------------------
        |
        | Specifies where and how the <Logout Response> message MUST be returned
        | to the requester (our SP).
        |
        */
        /*
        'singleLogoutService' => [
            // URL where the <Response> from the IdP will be returned.
            'url' => '',
            // SAML protocol binding to be used. Only HTTP-Redirect is supported.
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        */

        /*
        |--------------------------------------------------------------------------
        | NameID Format
        |--------------------------------------------------------------------------
        |
        | Specifies constraints on the Name Identifier to be used to represent
        | the requested subject.
        | Refer to lib/Saml2/Constants.php for supported formats.
        |
        */
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',

        /*
        |--------------------------------------------------------------------------
        | SP x509 Certificate and Private Key
        |--------------------------------------------------------------------------
        |
        | Typically, the x509 certificate and private key of the SP are provided
        | via files in the certs folder. Alternatively, they can be provided
        | directly here.
        |
        */
        'x509cert' => trim(
            file_exists($cert_path.'/sp_cert.pem') ? file_get_contents($cert_path.'/sp_cert.pem') : ''
        ),
        'privateKey' => trim(
            file_exists($cert_path.'/sp_key.pem') ? file_get_contents($cert_path.'/sp_key.pem') : ''
        ),

        /*
        |--------------------------------------------------------------------------
        | Key Rollover (Optional)
        |--------------------------------------------------------------------------
        |
        | If you plan to update the SP x509 certificate and private key, you can
        | define the new x509 certificate here. It will be published in the SP
        | metadata so that Identity Providers can prepare for rollover.
        |
        */
        // 'x509certNew' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity Provider (IdP) Data
    |--------------------------------------------------------------------------
    |
    | Data for the Identity Provider that you want to connect with your SP.
    |
    */
    'idp' => [
        /*
        |----------------------------------------------------------------------
        | IdP Entity ID
        |----------------------------------------------------------------------
        |
        | Identifier of the IdP entity (must be a URI).
        |
        */
        'entityId' => env('SAML_IDP_ENTITYID', $idp_base_url.'/shibboleth'),

        /*
        |----------------------------------------------------------------------
        | Single Sign-On Service (SSO)
        |----------------------------------------------------------------------
        |
        | Endpoint of the IdP where the SP will send the Authentication Request.
        |
        */
        'singleSignOnService' => [
            // URL target of the IdP where the Authentication Request will be sent.
            'url' => env('SAML_IDP_SSO_URL', $idp_base_url.'/profile/SAML2/Redirect/SSO'),
            // SAML protocol binding to be used. Only HTTP-Redirect is supported.
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],

        /*
        |----------------------------------------------------------------------
        | Single Logout Service (SLO)
        |----------------------------------------------------------------------
        |
        | Specifies endpoints for logout.
        |
        */
        /*
        'singleLogoutService' => [
            // URL of the IdP where the SP will send the SLO Request.
            'url' => env('SAML_IDP_SLO_URL', $idp_entity_id.'/profile/SAML2/Redirect/SLO'),
            // URL of the IdP where the SP SLO Response will be sent.
            // If not set, the URL for the SLO Request will be used.
            'responseUrl' => '',
            // SAML protocol binding to be used. Only HTTP-Redirect is supported.
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        */

        /*
        |--------------------------------------------------------------------------
        | IdP x509 Certificate
        |--------------------------------------------------------------------------
        |
        | Public x509 certificate of the IdP.
        |
        */
        'x509cert' => trim(
            file_exists($cert_path.'/idp_cert.pem') ? file_get_contents($cert_path.'/idp_cert.pem') : ''
        ),

        /*
        |--------------------------------------------------------------------------
        | Optional: Certificate Fingerprint
        |--------------------------------------------------------------------------
        |
        | Instead of using the full x509 certificate, you can use a fingerprint to
        | validate the SAMLResponse. This method is not recommended in production
        | due to potential collision attacks.
        |
        */
        // 'certFingerprint' => '',
        // 'certFingerprintAlgorithm' => 'sha1',

        /*
        |--------------------------------------------------------------------------
        | Optional: Multiple Certificates
        |--------------------------------------------------------------------------
        |
        | In scenarios where the IdP uses different certificates for signing and
        | encryption, or is undergoing key rollover, you can provide multiple
        | certificates here.
        |
        */
        // 'x509certMulti' => [
        //     'signing' => [
        //         0 => '<cert1-string>',
        //     ],
        //     'encryption' => [
        //         0 => '<cert2-string>',
        //     ],
        // ],
    ],

];
