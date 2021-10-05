<?php
require_once __DIR__.'/../lib/ODataAwe.php';

$options = [
	'rewritebase' => '/odata-awe/sample',
];
$odata = new ODataAwe($options);

$field = [
	'UserID' => ['type' => 'int','key' => true],
	'UserName' => ['type' => 'string'],
	'Cost' => ['type' => 'float'],
	'Account' => ['type' => 'string'],
	'Length' => ['type' => 'int','null' => true],
	'Active' => ['type' => 'bool'],
	'LastLogin' => ['type' => 'datetime'],
	'Birthday' => ['type' => 'date'],
	'Break' => ['type' => 'time'],
];
$odata->addFunction('Staff',$field);

function Staff($odata,$param) {
	function line($id,$name) {
		return [
			'UserID' => $id,
			'UserName' => $name,
			'Cost' => round($id*31.4159265358979,6),
			'Account' => strtolower(str_pad('',3,substr($name,0,1))),
			'Length' => (int) mb_strlen($name),
			'Active' => $id%2 ? true : false,
			'LastLogin' => date('c',strtotime('-'.$id.' hours')),
			'Birthday' => date('Y-m-d',strtotime('2007-01-01 +'.($id*100).' days')),
			'Break' => date('H:i:s',strtotime('10:00:00 +'.pow(3,$id).' minutes')),
		];
	}

	$odata->addData(line(1,'Jacob'));
	$odata->addData(line(2,'Jesper'));
	$odata->addData(line(3,'Jonas'));
	$odata->addData(line(6,'Thorbjørn'));
}

$odata->handle();
