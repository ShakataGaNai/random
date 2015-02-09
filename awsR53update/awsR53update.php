<?php
require 'vendor/autoload.php';
require_once 'config.php';

use Aws\Route53\Route53Client;

$r53 = Route53Client::factory(array(
    'key'    => AWSACCESSKEY,
    'secret' => AWSSECRETKEY
));



// Example code from: https://github.com/aws/aws-sdk-php/blob/master/tests/Aws/Tests/Route53/Integration/BasicOperationsTest.php

$command="/sbin/ifconfig " . ETHFACE . " | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'";
$localIP = exec ($command);

// Pull a list of existing records, 100 should be MORE than enough
$set = $r53->listResourceRecordSets(array(
	'HostedZoneId' => R53ZONEID,
	'StartRecordType' => "A",
	'StartRecordName' => R53DOMAINNAME,
	'MaxItems' => "100",
));

// See if there is an existing record
$old = null;
foreach($set['ResourceRecordSets'] as $res){
	if($res['Name'] == R53DOMAINNAME . "." && $res['Type'] == "A"){
		$old = $res;
		break;
		print "found\n";
	}
}

if($old['ResourceRecords'][0]['Value'] != $localIP){
	//The IP Addresses are no longer the same - Update them

	// Delete the old record
	if(!is_null($old)){
		$r53->ChangeResourceRecordSets(array(
			'HostedZoneId' => R53ZONEID,
			'ChangeBatch' => array(
				'Changes' => array(
					array(
						'Action'=> 'DELETE',
						'ResourceRecordSet' => $old
					),
				),
			),
		));
	}

	// Create the new record
	$r53->ChangeResourceRecordSets(array(
		'HostedZoneId' => R53ZONEID,
		'ChangeBatch' => array(
			'Changes' => array(
				array(
					'Action'=> 'CREATE',
					'ResourceRecordSet' => array(
						'Name' => R53DOMAINNAME,
						'Type' => "A",
						'TTL' => 300,
						'ResourceRecords' => array(
							array('Value' => $localIP)
						),
					),
				),
			),
		),
	));

}else{
	//No Change - Do we need to do anything?

}
