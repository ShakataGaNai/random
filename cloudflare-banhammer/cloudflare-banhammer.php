<?php
/* To use, update:
* "/var/log/apache2/mysite-access.log"
* CLOUDFLARETOKEN & CLOUDFLAREEMAIL
* To your own values
*/

// Crazy bash one liner to scan the log file and get back only hit count & IP
ob_start();
passthru("grep -iF 'POST /wp-login.php' /var/log/apache2/mysite-access.log |uniq -c | sort -rn|sed 's/^ *//'|cut -f1-2 -d' '");
$in = ob_get_clean();
$lines = explode("\n", $in);
$die = array();
foreach($lines as $line){
	$ray = explode(" ", $line);

	// Store the data only if 3 or more hits per second
	if($ray[0] >= 3 && !in_array($ray[1],$die)){
		$die[] = $ray[1];
		print "Found: {$ray[1]}\n";
	}
}

// Will throw warning of non-existen file on first run, no issue
$banned = array();
$filedata = file_get_contents('/tmp/cloudflare.json') or print "cloudflare.json doesn't exist\n";
$banned = json_decode($filedata);

foreach($die as $eyepee){

	// Do the ban on Cloudflare
	if(!in_array($eyepee,$banned)){
		print "Banning $eyepee\n";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://www.cloudflare.com/api_json.html");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"a=ban&tkn=CLOUDFLARETOKEN&email=CLOUDFLAREEMAIL&key=$eyepee");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
		$banned[] = $eyepee;
	}else{
		print "Prebanned $eyepee\n";
	}

}

// Write out all banned IPs so we don't hammer cloudflare with the same requests over and over
$fp = fopen('/tmp/cloudflare.json', 'w');
fwrite($fp, json_encode($banned));
fclose($fp);

