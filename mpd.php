<?php

error_reporting(0);
date_default_timezone_set('Asia/Kolkata');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
 
if (empty($id)) {
    http_response_code(400);
    echo json_encode(["error" => "Missing or invalid 'id' parameter"]);
    exit;
}

function fetchContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return null;
    }
    curl_close($ch);
    return $response;
}

$id = basename($id);

$cache_dir = "_cache_/";
$cacheTime = 60;
$cacheFile = $cache_dir . "TP-$id.mpd";

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    header('Content-Type: application/dash+xml');
    readfile($cacheFile);
    exit;
}

$data = json_decode(fetchContent("https://fox.toxic-gang.xyz/tata/key/$id"), true);
$initialUrl = $data[0]['data']['initialUrl'];
$psshSet = $data[0]['data']['psshSet'];
$kid = $data[0]['data']['kid'];
$bssh = preg_replace('/bpweb(\d+)\.akamaized\.net/', 'bpprod$1catchup.akamaized.net', $initialUrl);
$bssh = preg_replace('/bpweb(\d+)\.akamaized-staging\.net/', 'bpprod$1catchup.akamaized.net', $bssh);
$pattern = '/<ContentProtection\s+schemeIdUri="(urn:[^"]+)"\s+value="Widevine"\/>/';


$manifestContent = fetchContent($bssh);
$manifestContent = str_replace('<BaseURL>dash/</BaseURL>', '<BaseURL>' . str_replace("toxicify.mpd","dash/",$bssh) . '</BaseURL>', $manifestContent);
$manifestContent = preg_replace('/\b(init.*?\.dash|media.*?\.m4s)(\?idt=[^"&]*)?("|\b)(\?decryption_key=[^"&]*)?("|\b)(&idt=[^&"]*(&|$))?/', "$1$3$5$6$7", $manifestContent);
$manifestContent = preg_replace_callback($pattern, function ($matches) use ($psshSet) {
    return '<ContentProtection schemeIdUri="' . $matches[1] . '"> <cenc:pssh>' . $psshSet . '</cenc:pssh></ContentProtection>';
}, $manifestContent);
$manifestContent = preg_replace('/xmlns="urn:mpeg:dash:schema:mpd:2011"/', '$0 xmlns:cenc="urn:mpeg:cenc:2013"', $manifestContent);
$new_content = "<ContentProtection schemeIdUri=\"urn:mpeg:dash:mp4protection:2011\" value=\"cenc\" cenc:default_KID=\"$kid\"/>"; 
$manifestContent = str_replace('<ContentProtection value="cenc" schemeIdUri="urn:mpeg:dash:mp4protection:2011"/>', $new_content, $manifestContent);


header('Content-Type: application/dash+xml');
file_put_contents($cacheFile, $manifestContent);
echo $manifestContent;