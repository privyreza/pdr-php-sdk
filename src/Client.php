<?php
namespace Pdr;

use GuzzleHttp\Psr7\Request;

class Client
{
    /**
     * The client for making http calls to api
     */
    protected $client;

    /**
     *  The API host URL
     * 
     */
    protected $host;

    /**
     * Api Url 
     */
    protected $apiUrl;

    /**
     * The API version to connect to
     */
    protected $apiVersion;

    /**
     * token
     */
    protected $token;

    /**
     * Request headers
     */
    protected $headers;

    /**
     * Set env
     */
    protected $debug;

    /**
     * Initialize the client
     */
    public function __construct(){
        $this->host = getenv('HOST');
        $this->apiVersion = getenv('API_VERSION');
        $this->apiUrl = $this->host . '/api/' . $this->apiVersion;
        $this->debug = getenv('DEBUG');

        $this->client = new \GuzzleHttp\Client(
            [
                "base_uri" => $this->host
            ]
        );

        // Set Headers
        $this->headers = [
            "Accept" => "application/vnd.api+json",
            "Content-Type" => "application/vnd.api+json"
        ];

        // Get Token
        $this->authorize();
    }

    /**
     * Search domain
     * @return Object
     */
    public function searchDomain($domain){
        $domain = [
            "domain" => $domain
        ];

        return $this->_post('domain-searches', $domain);
    }

     /**
     * Create new Contact
     */
    public function createContact($contact = []){
        return $this->_post('contacts', $contact);
    }

    /**
     * Create new NS
     */
    public function createNS($ns){
        return $this->_post('nameservers', $ns);
    }

    /**
     * Create Domain
     */
    public function createDomain($domain){
        $data = [
            "domain" => $domain,
            "status" => "pending"
        ];

        return $this->_post('domains', $data);
    }

    /**
     * Register Domain
     */
    public function registerDomain($data){
        // Create Domain
        $domain = $this->createDomain($data['domain']);
        $domain_id = $domain->data->id;

        // Create Contact
        $contact_data = array_merge($data['contacts']['registrant'], ['domain_id' => $domain_id]);
        $contact = $this->createContact($contact_data);

        // Create NS
        $ns_data = array_merge($data['nameservers'], ['domain_id' => $domain_id]);
        $ns = $this->createNS($ns_data);

        // Submit the domain for registration
        $data = [
            "action" => 'register'
        ];

        return $this->_patch('domains', $domain_id, $data);
    }
    
    public function getNameservers($domain){
        $nameserversFilters = [
            "domain_id" => $this->getDomainId($domain)
        ];
        
        
        $ns =  $this->_get('nameservers', "", $nameserversFilters)->data[0]->attributes;
       
        return $ns;
    }
    
    protected function getDomainId($domain){
        return $domain_id = $this->_get("domains", "", $domain)->data[0]->id;
    }
    
    public function setNameservers($postData){
        $domain_id = $this->getDomainId($postData['domain']);
        
        $nameserversFilters = [
            "domain_id" => $this->getDomainId($domain)
        ];
        
        $nsId = $this->_get("nameservers", "", $nameserversFilters)->data[0]->id;
        
        return $this->_patch('nameservers', $nsId, $postData['nameservers']);
    }

    public function updateContact($contact){
        $id = $contact['id'];
        
        return $this->_patch('contacts', $id, $contact['data']);
    }

    /**
     * Login to API
     */
    public function authorize(){
        // $password = getenv('PASSWORD');
        // $email = getenv('EMAIL');
        $client_id = getenv('CLIENT_ID');
        $secret = getenv('SECRET');

        $options = [
            'form_params' =>  [
                "grant_type" => "client_credentials",
                "client_id" => $client_id,
                "client_secret" => $secret
            ]
        ];

        try
        {
            $response = $this->client->post('/oauth/token', $options);

            $responseString = (string) $response->getBody();

            $responseArray = json_decode($responseString);

            $this->token = $responseArray->access_token;
        } 
        catch(\Exception $ex){
            // echo $ex->getMessage();
            $error = [
                "errors" => [
                    [
                        "status" => "401",
                        "title" => "Failed to login"
                    ]
                ]
            ];
            
            return json_encode($error);
        }
    }

    protected function _get($resource, $record = "", $filters = []){
        $url = $this->apiUrl . '/' . $resource;
        
        if( ! empty($record)){
            $url .= "/" . $record;
        } elseif ( ! empty ($filters) ) {
            $i = 0;
            $url .= "?";
            
            foreach ( $filters as $key => $filter ) {
                $url .= "filter[$key]=$filter&";
            }
        }
        
        $response = $this->client->get($url);
        
        return json_decode( ( string ) $response->getBody());
    }

    protected function _post($resource, $data){
        $url = $this->apiUrl . '/' . $resource;

        $options = [
            "json" => [
                "data" => [
                    "type" => $resource,
                    "attributes" => $data
                ]
            ],
            "headers" => $this->headers
        ];

        try{
            $response = $this->client->post(
            $url, 
            $options
            );

            return json_decode((string) $response->getBody());
        } catch(\Exception $ex) {
            $error = [
                "errors" => [
                    [
                        "status" => "500",
                        "title" => "Recheck ns1, ns2 or domain_id"
                    ]
                ]
            ];
            
            return json_encode($error);
        }
    }

    protected function _patch($resource, $record, $data){
        $url = $this->apiUrl . '/' . $resource . '/' . $record;

        $options = [
            "json" => [
                "data" => [
                    "type" => $resource,
                    "id" => "$record",
                    "attributes" => $data
                ]
            ],
            "headers" => $this->headers
        ];

        try{
            $response = $this->client->patch(
            $url, 
            $options
            );
            

            return (string) $response->getBody();
        } catch(\Exception $ex) {
            $error = [
                "errors" => [
                    [
                        "status" => "500",
                        "title" => "Error while registering domain"
                    ]
                ]
            ];
            
            return json_encode($error);
        }
    }
}
