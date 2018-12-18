<?php

// Get cURL resource
$ch = curl_init();

// Set url
curl_setopt($ch, CURLOPT_URL, 'https://api.ean.com/1/regions/2114?language=en-US&include=DETAILS');

// Set method
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

// Set options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Set headers
curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Forwarded-For: 128.0.0.1",
        "Cookie: JSESSIONID=70188B5A4940AC1FD8DE99C976C056CD.chhxappean091",
        "Authorization: EAN APIKey=5ji84kjgss19qcerldo0lhp93g,Signature=4df7f7d14a365ed734f8517435cd9ceaeacdb532b5832ab1b32367b0362fd38a7cc49cfa0150187ef34ff682fc6539d015fc7f99b5475b001d478d676113d3cd,timestamp=1497349860",
        "Customer-Ip: 128.0.0.1",
    ]
);
curl_setopt($ch,CURLOPT_ENCODING , "gzip");


// Send the request & save response to $resp
$resp = curl_exec($ch);

if(!$resp) {
    die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
} else {
    echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "\nResponse HTTP Body : " . $resp;
}

// Close request to clear up some resources
curl_close($ch);


