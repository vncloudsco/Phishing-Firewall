<?php


class SafeBrowsingAPI {

    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function checkUrls($urlArray) {
        $parsedUrls = array();
        foreach ($urlArray as $url) {
            $parsedUrls[] = array("url" => $url);
        }
        $data = array(
            'client' => array(
                "clientId" => 'Evolved',
                "clientVersion" => '1.0.0'
            ),
            'threatInfo' => array(
                'threatTypes' => array("MALWARE", "SOCIAL_ENGINEERING"),
                'platformTypes' => array("ANY_PLATFORM"),
                'threatEntryTypes' => array("URL"),
                'threatEntries' => $parsedUrls
            )
        );

        $encoded = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key='.$this->apiKey);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

}