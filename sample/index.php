<?php
require_once __DIR__.'/../lib/ODataAwe.php';
syslog(LOG_INFO,$_SERVER['REQUEST_URI']);

$options = [
	'rewritebase' => '/odata-awe/sample',
	'maxpagesize' => 200,
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

function Staff($odata) {
	$data = [
		1 => 'Jacob',
		2 => 'Jesper',
		3 => 'Jonas',
		6 => 'Thorbjørn',
	];
	$odata->setCount(4);

	function line($id,$name) {
		$return = [];
		$return['UserID'] = $id;
		$return['UserName'] = $name;
		$return['Cost'] = round($id*31.4159265358979,6);
		$return['Account'] = strtolower(str_pad('',3,substr($name,0,1)));
		$return['Length'] = (int) mb_strlen($name);
		$return['Active'] = $id%2 ? true : false;
		$return['LastLogin'] = date('c',strtotime('-'.$id.' hours'));
		$return['Birthday'] = date('Y-m-d',strtotime('2007-01-01 +'.($id*100).' days'));
		$return['Break'] = date('H:i:s',strtotime('10:00:00 +'.pow(3,$id).' minutes'));
		return $return;
	}

	$data = array_slice($data,$odata->getSkip(),$odata->getTop(),true);
	$select = $odata->getSelect();

	foreach($data as $key => $value) {
		$line = line($key,$value);
		if($select) {
			$line = array_intersect_key($line,$select);
		}
		$odata->addData($line);
	}
}

$odata->handle();
