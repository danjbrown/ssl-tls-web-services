<?php
//dev
class SSLRequest {
    
    // Configuration variables: certificates, keys, passwords, etc.
    private $userAgent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
    private $certificate = '/certificates/client.crt.pem';
    private $key = '/certificates/client.key.pem';
    private $certificatePassword = 'password';
    private $keyPassword = 'password';
    
    // Request URL: specified by the user
    public $url = '';
    
    public function init() {
        $this->response = new stdClass;
        $this->response->error = '';
        $this->response->data = '';
    }
    
    public function request()
    {
        $ch = curl_init();

        $options = array( 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_VERBOSE => true,
            CURLOPT_URL => $this->url,
            CURLOPT_SSLCERT => getcwd().$this->certificate,
            CURLOPT_SSLCERTPASSWD => $this->certificatePassword,
            CURLOPT_SSLKEY => getcwd().$this->key,
            CURLOPT_SSLKEYPASSWD => $this->keyPassword,
        );
 
        curl_setopt_array($ch , $options);

        $output = curl_exec($ch);

        if(!$output) {
            $this->setError(curl_error($ch));
        } else {
            $this->setData($output);
        }
        
        return $this->response;
    }
    
    private function setData($data) {
        $this->response->data = $data;
    }
    
    private function setError($error) {
        $this->response->error = $error;
    }
}
