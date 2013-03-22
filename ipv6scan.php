<?php

// IPv6 Scanner. For command line use.
// Reads apache logs

if(is_null($argv[1])){
	print "\nUSE ipv6scan.php access.log\n";
	exit;
}else{
	$lines = file($argv[1]);
}

$ipv6 = 0; $ipv4 = 0; $ipv0 = 0; $l = 0; $page = 0;
foreach($lines as $line){
	$l++;

	if(preg_match("/mod_pagespeed/",$line,$bla)){
		$page++;
	}else{

		if(preg_match("/(.*) - - \[/", $line, $match)){
			$ip = $match[1];

			$v6 = preg_match("/^[0-9a-f]{1,4}:([0-9a-f]{0,4}:){1,6}[0-9a-f]{1,4}$/", $ip);
			$v4 = preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $ip);

			if ( $v6 != 0 ){
				$ipv6++;
			} elseif ( $v4 != 0 ) {
				$ipv4++;
			} else {
				$ipv0++;
			}
		}
	}
}

$sanspage = $l - $page;
print "----Stats for {$argv[1]}----\n";
print "Lines Total: $l\n";
print "Non-Pagespeed Lines: $sanspage\n";
print "IPv6: $ipv6 (".round(($ipv6/$sanspage)*100,2)."%)\n";
print "IPv4: $ipv4 (".round(($ipv4/$sanspage)*100,2)."%)\n";
if($ipv0>0){
	print "Other: $ipv0 (".round(($ipv0/$sanspage)*100,2)."%)\n";
}

