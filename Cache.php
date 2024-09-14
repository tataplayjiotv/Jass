<?php

date_default_timezone_set('Asia/Kolkata');

$apikey = ""; // get your own apiKey from " https://babel-in.xyz "
$cacheFolder = "_cache_"; // set cache folder
$cacheTime = 691200; // 8 days in seconds

if (!is_dir($cacheFolder)) {
    mkdir($cacheFolder, 0755, true);
}

// get data
$url = "https://babel-in.xyz/$apikey/tata/channels";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_USERAGENT, 'Babel-In');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);

if ($data !== null) {
    
    foreach ($data["data"] as $channel) {
        $channelId = $channel['id'];
        $channelKey = $channel['channel_key'] ?? null;

        if ($channelKey !== null) {

            $keys = $channelKey['keys'] ?? [];
            if (!empty($keys)) {
                $k = $keys[0]['k'] ?? null;
                $kid = $keys[0]['kid'] ?? null;
            } else {
                $k = null;
                $kid = null;
            }

            $cacheFile = "$cacheFolder/$channelId.json";

            if (file_exists($cacheFile)) {
                $existingData = json_decode(file_get_contents($cacheFile), true);

                $keyExists = false;
                foreach ($existingData as $value) {
                    foreach ($value['keys'] as $key) {
                        if ($key['k'] == $k && $key['kid'] == $kid) {
                            $keyExists = true;
                            break 2; // Exit both loops
                        }
                    }
                }

                if (!$keyExists) {
                    $newData = [];
                    foreach ($existingData as $value) {
                        $timeAdded = strtotime($value['time_added']);
                        $currentTime = strtotime(date('Y-m-d H:i:s'));
                        $timeDiff = $currentTime - $timeAdded;

                        if ($timeDiff <= $cacheTime) {
                            $newData[] = $value;
                        }
                    }

                    $newData[] = [
                        'keys' => [
                            [
                                'kty' => 'oct',
                                'k' => $k,
                                'kid' => $kid
                            ]
                        ],
                        'type' => 'temporary',
                        'time_added' => date('Y-m-d H:i:s')
                    ];

                    file_put_contents($cacheFile, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                }
            } else {
                $newData = [
                    [
                        'keys' => [
                            [
                                'kty' => 'oct',
                                'k' => $k,
                                'kid' => $kid
                            ]
                        ],
                        'type' => 'temporary',
                        'time_added' => date('Y-m-d H:i:s')
                    ]
                ];

                file_put_contents($cacheFile, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
        } else {
            echo "Channel key is null for channel ID $channelId\n";
        }
    }
} else {
    echo 'Failed to retrieve or decode data.';
}
