<?php

namespace idrsolutions;

if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'r'));
if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'w'));
if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'w'));

class IDRCloudClient {

    const POLL_INTERVAL = 500; //ms
    const TIMEOUT = 10;  // seconds

    const KEY_ENDPOINT = 'endpoint';
    const KEY_PARAMETERS = 'parameters';
    const KEY_INPUT = 'input';
    const KEY_FILE_PATH = 'file';
    const KEY_CONVERSION_URL = 'url';
    
    const INPUT_UPLOAD = 'upload';
    const INPUT_DOWNLOAD = 'download';
    const INPUT_JPEDAL = 'jpedal';
    const INPUT_BUILDVU = 'buildvu';

    private static function progress($r) {
        fwrite(STDOUT, json_encode($r, JSON_PRETTY_PRINT) . "\r\n");
    }

    private static function handleProgress($r) {

        if ($r['state'] === 'error') {
            self::progress(array(
                'state' => $r['state'],
                'error' => $r['error'])
            );
        } elseif ($r['state'] === 'processed') {
            self::progress(array(
                'state' => $r['state'],
                'previewUrl' => $r['previewUrl'],
                'downloadUrl' => $r['downloadUrl'])
            );
        } else {
            self::progress(array(
                'state' => $r['state'])
            );
        }
    }

    private static function validateInput($opt) {

        if (!array_key_exists(self::KEY_ENDPOINT, $opt) || !isset($opt[self::KEY_ENDPOINT])) {
            self::exitWithError('Missing endpoint.');
        }
        if (!array_key_exists(self::KEY_PARAMETERS, $opt) || !isset($opt[self::KEY_PARAMETERS])) {
            self::exitWithError('Missing parameters.');
        } else {
            $params = $opt[self::KEY_PARAMETERS];
        }
        if (array_key_exists(self::KEY_INPUT, $params) && $params[self::KEY_INPUT] === self::INPUT_UPLOAD && !array_key_exists(self::KEY_FILE_PATH, $params)) {
            self::exitWithError('Missing file.');
        }
        if (array_key_exists(self::KEY_INPUT, $params) && $params[self::KEY_INPUT] === self::INPUT_DOWNLOAD && !array_key_exists(self::KEY_CONVERSION_URL, $params)) {
            self::exitWithError('Missing url.');
        }
    }

    private static function createContext($opt) {
        
        $parameters = $opt[self::KEY_PARAMETERS];

        if ($parameters[self::KEY_INPUT] === self::INPUT_UPLOAD) {
            if(array_key_exists(self::KEY_FILE_PATH, $parameters)) {
                $filePath = $parameters[self::KEY_FILE_PATH];
            }
            $multipart_Boundary = '--------------------------'.microtime(true);
            $header = 'Content-Type: multipart/form-data; boundary=' .$multipart_Boundary;
            $content = self::generateMultipartContent($parameters, $filePath, $multipart_Boundary);
        }
        else {
            $content = http_build_query($parameters);
            $header = "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($content);
        }

        $options = array(
            'http' => array(
                'method' => 'POST',
                'TIMEOUT' => self::TIMEOUT,
                'ignore_errors' => TRUE,
                'header' => $header,
                'content' => $content
            )
        );
        return stream_context_create($options);
    }
    
    private static function generateMultipartContent($parameters, $filePath, $multipartBoundary) {
        
        $form_field = "file";

        $file = file_get_contents($filePath);
        if (!$file) {
            self::exitWithError("File not found.");
        }
        
        $content = '--'.$multipartBoundary."\r\n".
            'Content-Disposition: form-data; name="' .$form_field. '"; filename="'.basename($filePath)."\"\r\n".
            "Content-Type: application/zip\r\n\r\n".$file."\r\n--".$multipartBoundary;

        foreach ($parameters as $name => $value) {
            $content .= "\r\nContent-Disposition: form-data; name=\"" . $name . "\"\r\n" .
                "Content-Type: text/plain\r\n\r\n" . $value . "\r\n--" . $multipartBoundary;
        }

        return $content . "--\r\n";
    }

    private static function poll($endpoint, $result) {

        $json = json_decode($result, true);
        $retries = 0;
        $data = array('state' => '');

        while ($data['state'] !== 'processed') {
            $result = file_get_contents($endpoint . '?uuid=' . $json['uuid']);
            if (!$result) {    // ERROR
                if ($retries > 3) {
                    self::exitWithError('Failed to convert.');
                }
                $retries++;
            } else {
                $data = json_decode($result, true);
                if ($data['state'] === 'processed') {
                    self::handleProgress($data);
                    return $data;  // SUCCESS
                }

                self::handleProgress($data);
                usleep(self::POLL_INTERVAL * 1000);
            }
        }
    }

    private static function download($downloadUrl, $outputDir, $filename) {
        $fullOutputPath = $outputDir . $filename;
        file_put_contents($fullOutputPath, fopen($downloadUrl, 'r'));
    }

    private static function exitWithError($printStr, $errCode = 0) {
        fwrite(STDERR, $printStr);
        throw new \Exception($printStr, $errCode);
    }
    
    /**
     * Use the server response to download a zip file of the converted output
     * 
     * @param type $results The server response generated from the convert method
     * @param type $outputDir The directory where the output will be saved
     * @param type $filename (optional) A filename for the downloaded zip file
     */
    public static function downloadOutput($results, $outputDir, $filename = null) {
        
        $downloadUrl = $results['downloadUrl'];
        
        if ($filename == null) {
            $filename = pathinfo($downloadUrl)['basename'];
        }
        
        self::download($downloadUrl, $outputDir, $filename);
    }

    /**
     * Start a conversion of a file for a MicroService server
     * 
     * @param array $opt An associative array of the conversion options desired
     * @return array The response from the server after the conversion completes
     */
    public static function convert($opt) {

        self::validateInput($opt);
        $endpoint = $opt[self::KEY_ENDPOINT];
        $context = self::createContext($opt);

        $result = file_get_contents($endpoint, false, $context);
        $http_response = substr($http_response_header[0], 9, 3);
        if ($http_response !== '200') { //Check http response code for if the request failed
            if ($result !== false) { //If a text response was given
                $decoded = json_decode($result, true);//Decode the json
                if(array_key_exists('error',$decoded)) {
                    self::exitWithError("http error code " . $http_response . ": " . $decoded['error'], $http_response ); //Exit with the error provided
                } else {
                    self::exitWithError('Failed to upload.');
                }
            } else {
                self::exitWithError('Failed to upload.');
            }
        }
        
        if (array_key_exists('callbackUrl', $opt[self::KEY_PARAMETERS])) {
            return array('state'=>'queued');
        }
        return self::poll($endpoint, $result);
    }
}
