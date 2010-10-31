<?php
require_once 'restclient/RestClient.php';
require_once 'controllers/RestResponseInstrumenter.php';

class RestExplorerController {  
    private $BASE_REST_URL_PREFIX = '/services/data';
    public $errors; 
    public $url;
    public $requestBody;
    public $requestMethod;
    public $rawResponseHeaders;
    public $rawResponse;
    public $response;
    public $showResponse;
    public $autoExec;    
    private $insturmenter;
    
    public function __construct() {
        $this->requestMethod = 'GET';
        $this->insturmenter = new RestResponseInstrumenter($_SERVER['PHP_SELF']);
        $this->url = isset($_REQUEST['url']) ? $_REQUEST['url'] : $this->BASE_REST_URL_PREFIX;
    }
    
    public function onPageLoad() {
        $this->errors = null;
        $this->showResponse = false;
        $this->requestMethod = isset($_REQUEST['requestMethod']) ? $_REQUEST['requestMethod'] : $this->requestMethod;
        $this->url = isset($_REQUEST['url']) 
                                ? (get_magic_quotes_gpc() 
                                    ? stripslashes($_REQUEST['url']) 
                                    : $_REQUEST['url']) 
                                : $this->url;
        $this->requestBody = isset($_REQUEST['requestBody']) 
                                ? (get_magic_quotes_gpc() 
                                    ? stripslashes($_REQUEST['requestBody']) 
                                    : $_REQUEST['requestBody']) 
                                : $this->requestBody;
    	$this->autoExec = isset($_REQUEST['autoExec']) ? $_REQUEST['autoExec'] : $this->autoExec;
    	$doExecute = isset($_REQUEST['doExecute']) ? $_REQUEST['doExecute'] : null;
    	
    	if ($doExecute != null || $this->autoExec == '1') {
            $this->execute();
        }    	
    }

    private function execute() {
        try {
            // clear any old values, in case we don't populate them on this request
            $this->rawResponseHeaders = null;
            $this->rawResponse = null;
            $this->response = null;
            $this->autoExec = null;
            
            // clean up the URL
            $this->url = str_replace(' ', '+', trim($this->url));
            
            // validate URL
            if (strpos($this->url, $this->BASE_REST_URL_PREFIX) != 0) {
                throw new Exception('Invalid REST API Service URI. Must begin with \'' + $this->BASE_REST_URL_PREFIX + '\'.');
            }
            
            //TODO: remove mocking!
            $this->rawResponseHeaders = "some headers\n";
            
            $this->rawResponse = getRestApiConnection()->send($this->requestMethod, 
                                                              $this->url, "application/json",
                                                              $this->requestMethod == 'POST' ? $this->requestBody : null);
            
            $this->response = $this->rawResponse;
            
//            // process the headers
//            $this->rawResponseHeaders = '';                 
//            for ($headerKey : $httpResponse.getHeaderKeys()) {
//                if (headerKey == null) continue;
//                rawResponseHeaders += headerKey + ': ' + httpResponse.getHeader(headerKey) + '\n';
//            }
//            
//            // process the body
//            $this->rawResponse = httpResponse.getBody();
            $this->response = $this->insturmenter->instrument($this->rawResponse);
            $this->showResponse = true;
        } catch (Exception $e) {
            $this->errors = $e->getMessage();
        }
    }
}
?>