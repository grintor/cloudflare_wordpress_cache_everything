<?php

$source_domain = 'cms.example.com'; // change to the real domain name

$request_headers = [];
$response_headers = [];

if ($_SERVER['REQUEST_URI'] == '/robots.txt' ){
	header("Content-Type: text/plain");
	print("User-agent: *\r\nAllow: /");
	die();
}

$ch = curl_init( 'https://' . $source_domain . $_SERVER['REQUEST_URI']);


foreach (getallheaders() as $key => $value) {
	if (!in_array(strtolower($key), ['user-agent', 'cookie', 'host', 'connection', 'content-length', 'accept-encoding', 'location'])) {
		$request_headers[] = $key . ': ' . $value;
	}
}

curl_setopt( $ch, CURLOPT_HTTPHEADER, $request_headers);
curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
curl_setopt( $ch, CURLOPT_ENCODING, '' );

// this function is called by curl for each header received
curl_setopt($ch, CURLOPT_HEADERFUNCTION,
	function($curl, $header) use (&$response_headers) {
		$len = strlen($header);
		$header = explode(':', trim($header), 2);
		if (count($header) < 2) // ignore invalid headers
			return $len;

		$response_headers[trim($header[0])] = trim($header[1]);

		return $len;
	}
);

$response = curl_exec( $ch );
$response_info = curl_getinfo( $ch );

curl_close( $ch );

if ($response_info['http_code'] == 200)
	http_response_code (203);
else
	http_response_code ($response_info['http_code']);

foreach ($response_headers as $key => $value) {
	if (!in_array(strtolower($key), ['x-robots-tag', 'transfer-encoding', 'set-cookie', 'content-security-policy', 'x-content-security-policy', 'connection', 'content-length', 'content-encoding'])) {
		if (strtolower($key) == 'location' && in_array($response_info['http_code'], [200, 301, 302, 303, 307, 308])){
			$value = str_replace('//' . $source_domain . '/', '//' . $_SERVER['HTTP_HOST'] . '/', $value);		# replace "//cms.example.com/" with "//www.example.com/"
		}
		header ($key . ': ' . $value);
	}
}

if (substr($response_info['content_type'], 0, 4) == 'text') {
	$response = str_replace('//' . $source_domain . '/', '//' . $_SERVER['HTTP_HOST'] . '/', $response); 			# replace "//cms.example.com/" with "//www.example.com/"
	$response = str_replace('\\/\\/' . $source_domain . '\\/', '\\/\\/' . $_SERVER['HTTP_HOST'] . '\\/', $response);	# replace "\/\/cms.example.com\/" with "\/\/www.example.com\/"
}


print_r($response);


