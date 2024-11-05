<?php

defined( 'ABSPATH' ) || die();

// Load Feed Add-On Framework.
GFForms::include_feed_addon_framework();

class GFAPITrap extends GFFeedAddOn {
 
    protected $_version = GF_API_TRAP_VERSION;
    protected $_min_gravityforms_version = '2.8';
    protected $_slug = 'gravity-api-trap';
    protected $_path = 'gravity-api-trap/gravity-api-trap.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms Enquire/Aline Integration';
    protected $_short_title = 'Enquire/Aline API';
 
    private static $_instance = null;
 
    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new GFAPITrap();
        }
 
        return self::$_instance;
    }

    public function plugin_page() {
    }

    public function feed_settings_fields() {
        return array(
            array(
                'title'  => esc_html__( 'Enquire/Aline Settings', 'gravity-api-trap' ),
                'fields' => array(
                    array(
                        'name'                => 'feedName',
                        'label'               => '<h3>Feed Label</h3>',
                        'type'                => 'text',
                    ),
                    array(
                        'name'                => 'formFieldMap',
                        'label'               => '<h3>' . esc_html__( 'Map API Fields to Form Fields', 'gravity-api-trap' ) . '</h3>',
                        'type'                => 'generic_map',
                        'key_field'           => array(
                            'title'             => 'API Field',
                            'allow_custom'      => FALSE,
                            'choices'           => array(
                                array(
                                    'label'         => 'Email',
                                    'value'         => 'email',
                                ),
                                array(
                                    'label'         => 'First Name',
                                    'value'         => 'firstname',
                                ),
                                array(
                                    'label'         => 'Last Name',
                                    'value'         => 'lastname',
                                ),
                                array(
                                    'label'         => 'HomePhone ',
                                    'value'         => 'HomePhone ',
                                ),
                                array(
                                    'label'         => 'AddressLine1',
                                    'value'         => 'AddressLine1',
                                ),
                                array(
                                    'label'         => 'City',
                                    'value'         => 'City',
                                ),
                                array(
                                    'label'         => 'State',
                                    'value'         => 'State',
                                ),
                                array(
                                    'label'         => 'Zip Code',
                                    'value'         => 'PostalCode',
                                ),
                                array(
                                    'label'         => 'Comments',
                                    'value'         => 'Message',
                                ),
                                array(
                                    'label'         => 'CommunityUnique',
                                    'value'         => 'communityunique',
                                ),
                            ),
                        ),
                    ),
                ),
            )
        );
    }

    public function feed_list_columns() {
        return array(
            'feedName' => __( 'Name', 'gravity-api-trap' ),
        );
    }

    public function process_feed( $feed, $entry, $form ) {
        var_dump($feed);
        error_log('this is the feed:');
        error_log(print_r($feed, true));
        $metaData = $this->get_generic_map_fields( $feed, 'formFieldMap' );
        $communityunique = isset($metaData['communityunique']) ? $this->get_field_value($form, $entry, $metaData['communityunique']) : null;
        $email = isset($metaData['email']) ? $this->get_field_value($form, $entry, $metaData['email']) : null;
        $firstName = isset($metaData['firstname']) ? $this->get_field_value($form, $entry, $metaData['firstname']) : null;
        $lastName = isset($metaData['lastname']) ? $this->get_field_value($form, $entry, $metaData['lastname']) : null;
        $HomePhone  = isset($metaData['HomePhone ']) ? $this->get_field_value($form, $entry, $metaData['HomePhone ']) : null;
        $addressLine1 = isset($metaData['AddressLine1']) ? $this->get_field_value($form, $entry, $metaData['AddressLine1']) : null;
        $city = isset($metaData['City']) ? $this->get_field_value($form, $entry, $metaData['City']) : null;
        $state = isset($metaData['State']) ? $this->get_field_value($form, $entry, $metaData['State']) : null;
        $zip = isset($metaData['PostalCode']) ? $this->get_field_value($form, $entry, $metaData['PostalCode']) : null;
        $comments = isset($metaData['Message']) ? GFCommon::replace_variables($metaData['Message'], $form, $entry) : null;
    
        $data = array(
            'communityunique' => $communityunique,
            'email' => $email,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'HomePhone ' => $HomePhone ,
            'AddressLine1' => $addressLine1,
            'City' => $city,
            'State' => $state,
            'PostalCode' => $zip,
            'Message' => $comments,
        );
    
        error_log('this is the data: ' . print_r($data, true));
        $response = $this->sendApiRequest($data);
        error_log('this is the response: ' . print_r($response, true));
    }

    public function sendApiRequest(array $data) {

        $sendData = array(
            "individuals" => [
                "communities" => [
                    "NameUnique" => $data['communityunique'],
                ],
                "properties" => [
                    [
                        "property" => "firstname",
                        "value" => $data['firstname']
                    ], [
                        "property" => "lastname",
                        "value" => $data['lastname']
                    ], [
                        "property" => "email",
                        "value" => $data['email']
                    ], [
                        "property" => "HomePhone ",
                        "value" => $data['HomePhone ']
                    ],[
                        "property" => "type",
                        "value" => "prospect"
                    ],
                ],
                "addresses" => [
                    "addressLine1" => $data['AddressLine1'],
                    "city" => $data['City'],
                    "state" => $data['State'],
                    "zip" => $data['PostalCode'],
                ],
                "notes"  => [
                    "Message" => $data['Message'],
                ]
            ]
        );

        $primaryApiKey = get_option('gravity_api_trap_primary_api_key');
        $secondaryApiKey = get_option('gravity_api_trap_secondary_api_key');
        $url = get_option('gravity_api_trap_endpoint_url');

        $args = [
            'method' => 'POST',
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $primaryApiKey,
                'Content-Type' => 'application/json',
                'PortalId'     => get_option('gravity_api_trap_portal_id'),
            ],
            'body' => json_encode($sendData)
        ];

        error_log('args: ' . print_r($args, true));

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $args['headers']['Ocp-Apim-Subscription-Key'] = $secondaryApiKey;
            $response = wp_remote_post($url, $args);
        }

        if (is_wp_error($response)) {
            // Display an error message to the user
            add_settings_error('gravity-api-trap', 'api_request_error', 'API request failed: ' . $response->get_error_message(), 'error');
        } elseif ($response['response']['code'] !== 200) {
            // Display an error message to the user
            add_settings_error('gravity-api-trap', 'api_request_error', 'API request failed with status code ' . $response['response']['code'], 'error');
        } else {
            // Log the entire output
            error_log('API request successful: ' . print_r($response, true));
        }

        return $response;
    }

}

