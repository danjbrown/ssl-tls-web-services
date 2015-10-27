## SSL/TLS authentication for web services on a LAMP stack

In many cases using SSL/TLS is the most secure way to implement web service authentication: IP addresses can be forged and session based authentication potentially intercepted during the transfer. The information below assumes that you have already created and partly configured a new Apache SSL virtual host file, but explains how to generate and use a self-signed certificate in the context of this type of authentication.

## Installation

Firstly, follow these steps to generate the certificate and setup the Apache server

Install OpenSSL
```
sudo apt-get install openssl
```
Generate the self-signed certificate and private key on the server using openssl and store these in the directory /etc/apache2/ssl/
```
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.crt
```
Now setup the configuration in your virtual host file, enable SSL and reference the files created above
```
SSLEngine on
SSLCertificateFile /etc/apache2/ssl/apache.crt
SSLCertificateKeyFile /etc/apache2/ssl/apache.key
```
Next export the certificate and private key as a PKCS #12 file to be used by the client
```
openssl pkcs12 -export -clcerts -in apache.crt -inkey apache.key -out client.p12
```
You will be prompted to enter a password to protect the PKCS file during the export process.

## Implementation

Here are examples of two ways in which the could make SSL/TLS authenticated HTTP requests to a web service.

Through a **web broswer**, for example to create a user on the fly. In this case the client.p12 file should be imported into the client browser and the following settings enabled in the virtual host configuration to force client verification for every request
```
SSLVerifyClient require
SSLVerifyDepth 1
SSLCACertificateFile /etc/apache2/ssl/apache.crt
```
Using a tool such as **cURL** for client side data transfer, particularly useful if data from the web service is required within the code base of another project. The PKCS file can be converted to certificate and key PEM files which are then copied to the server- ensuring this is outside a publicly accessible web path- and used as part of the cURL request. Convert the PKCS file using these commands
```
openssl pkcs12 -in client.p12 -out client.key.pem -nocerts -nodes
openssl pkcs12 -in client.p12 -out client.crt.pem -clcerts -nokeys
```

Using cURL, the request is made like this
```
  $url = "https://your-secure-url";
  $ch = curl_init();
  $options = array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
    CURLOPT_VERBOSE => true,
    CURLOPT_URL => $url ,
    CURLOPT_SSLCERT => getcwd().'/path_to_certificate/client.crt.pem',
    CURLOPT_SSLCERTPASSWD => password,
    CURLOPT_SSLKEY => getcwd().'/path_to_key/client.key.pem',
    CURLOPT_SSLKEYPASSWD => password,
  );
  curl_setopt_array($ch , $options);
  $output = curl_exec($ch);
  echo $output;
```

## Code Example

$oSSLRequest = new SSLRequest();
$oSSLRequest->url = "https://your-secure-url";
$oResponse = $oSSLRequest->request();

$data = $oResponse->data;
$error = $oResponse->error;
