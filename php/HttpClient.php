<?php
/**
 * HttpClient
 *
 * An HTTP client for making POST/GET requests, using curl for the
 * underlying implementation.
 *
 * // Example usage:
 * $request = new HttpClient();
 * $result = $request->withFollowRedirects(true)->withSslVerify(true)->get("http://www.google.com");
 * echo $result->content();
 *
 * Created by PhpStorm.
 * User: Bryan Mayor
 * Comapny: Blue Nest Digital LLC, Al l rights reserved
 * Date: 10/25/15
 * Time: 1:30 AM
 */

define("CONST_USER_AGENT", "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.86 Safari/537.36");

class HttpClient
{
    private $curl;
    private $verbose = false;
    const USER_AGENT = CONST_USER_AGENT;
    /**
     * HttpClient constructor.
     */
    public function __construct()
    {
        $this->curl = curl_init();
        $this->withTimeout(5);
        $this->withFollowRedirects(true);
    }

    /**
     * @return HttpClient
     */
    public static function create() {
        return new HttpClient();
    }

    /**
     * @param $url
     * @param array $queryStringParams
     * @return HttpResult
     * @throws Exception
     */
    public function get($url, $queryStringParams = []) {
        return $this->retrieveUrl($url, "GET", $queryStringParams);
    }

    private function execute() {
        return curl_exec($this->curl);
    }

    /**
     * Set to verbos mode
     * @return $this
     */
    public function verbose() {
        $this->verbose = true;
        return $this;
    }

    private function retrieveUrl($url, $httpMethod, $params = []) {

        if($this->verbose) {
            logger("Retrieve url: [$httpMethod] $url");
        }
        $this->setRequestType($httpMethod);

        $ch = $this->curl;

        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        // Instead of curl_exec returning true on success, return the actual content
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);

        if($httpMethod === "POST") {
            curl_setopt($ch,CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
        } else if($httpMethod === "GET") {
            $queryString = "";
            foreach($params as $queryParam=>$paramVal) {
                $queryString .= "$queryParam=$paramVal&";
            }
            $url = $url . "?" . $queryString;
            if($this->verbose) {
                logger("Retrieve url with query string: [$httpMethod] $url");
            }
        } else {
            throw new Exception("Unsupported HTTP method: " . $httpMethod);
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        // MAKE THE REQUEST!
        $response = $this->execute();

        if($response === false) {
            throw new Exception("Requested failed");
        }

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        $httpResult = new HttpResult($ch, $header, $body);

        if($this->verbose) {
            logger($httpResult);
        }

        curl_close($ch);
        if($this->verbose) {
            logger("Connection close for url: [$httpMethod] $url");
        }

        return $httpResult;
    }

    public function withHttpBasicAuthentication($user, $pass) {
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($this->curl, CURLOPT_USERPWD, "$user:$pass");
    }

    public function withSslVerify($shouldVerifyPeer) {
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $shouldVerifyPeer);
        return $this;
    }

    public function withTimeout($timeoutSeconds) {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $timeoutSeconds);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeoutSeconds);

        return $this;
    }

    public function withFollowRedirects($shouldFollow) {
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, $shouldFollow);
        return $this;
    }

    public function setRequestType($requestType) {
        $requestTypes = [ "GET", "POST", "PUT", "DELETE"];

        if(!in_array($requestType, $requestTypes))
            die("Invalid request type set");

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $requestType);
    }
}

/**
 * Class HttpResult
 * Holds the result of an HTTP request
 */
class HttpResult {
    public $result = [];
    private $headersParsed = null;
    private $headersRaw = null;
    private $skippedOutputHeaders = ["content-length", "x-frame-options", "connection",
        "keep-alive", "access-control-allow-origin", "content-security-policy",
        "keep-alive", "transfer-encoding" ];

    public function __construct($ch, $headerStringRaw, $result)
    {
        $this->headersRaw = $headerStringRaw;
        $this->headersParsed = $this->parseHeaders($headerStringRaw);
        $this->result = [
            "response_code" => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            "content" => $result,
            "headers-raw" => $headerStringRaw,
            "headers-parsed" => $this->headersParsed,
            "redirect" => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
        ];
    }

    /** Parse the header string into key->value array
     * @param $headerString
     * @return array
     */
    private function parseHeaders($headerString) {
        $exploded = explode("\r\n", $headerString);
        $responsePart = null;
        $responseHeaders = [];
        foreach($exploded as $line) {
            if(strpos($line, "HTTP/") !== False) {
                if($responsePart != null) {
                    $responseHeaders[] = $responsePart;
                }
                $responsePart = [];
            } else {
                if(strpos($line, ": ") != False) {
                    $headerKeyAndValue = explode(": ", $line);
                    $responsePart[$headerKeyAndValue[0]] = $headerKeyAndValue[1];
                }
            }
        }

        if($responsePart != null) {
            $responseHeaders[] = $responsePart;
        }

        return $responseHeaders;
    }

    public function getFinalResponseHeaders() {
        return $this->headersParsed[count($this->headersParsed) - 1];
    }

    /**
     * Check if HTTP response was successful. Throw if not.
     * @return $this
     * @throws Exception
     */
    public function check() {
        if($this->result["response_code"] != 200) {
            throw new Exception("Response code was: " . $this->result["response_code"]);
        }
        return $this;
    }

    public function content() {
        if(!isset($this->result["content"])) {
            throw new Exception("Content is missing");
        }
        return $this->result['content'];
    }

    /**
     * Output the resulting html to the page
     * @throws Exception
     */
    public function output() {
        header_remove();
        http_response_code(200);

        foreach($this->getFinalResponseHeaders() as $key=>$header) {
            if(in_array(strtolower($key), $this->skippedOutputHeaders)) {
                continue;
            }
            $headerOut = $key . ": " . $header;
            header($headerOut);
        }

        echo $this->content();

        header("Connection: close");
    }

    public function __toString()
    {
        $eol = "<br>";
        $str = "HttpResult:";
        $str .= "Response Code: " . $this->result["response_code"] . $eol;
        $str .= "HTML Length: " . strlen($this->result["content"]) . $eol;
        $str .= "Headers: " . $this->headersRaw . $eol;
        return $str;
    }
}