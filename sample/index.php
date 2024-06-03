<?php
require_once __DIR__.'/../lib/ODataAwe.php';
syslog(LOG_INFO,$_SERVER['REQUEST_URI']);

$options = [
	'rewritebase' => '/git_odata-awe/sample',
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
$odata->addFunction('Staff',$field,'StaffFunction','Mr./Mrs.');

function StaffFunction($odata,$prefix) {
	// Test data creation
	$data = [
		1 => 'Jacob',
		2 => 'Jonas',
		3 => 'Jesper',
		6 => 'Thorbjørn',
	];

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

	foreach($data as $key => $value) {
		$data[$key] = line($key,$prefix.' '.$value);
	}
	// Test data end

	// Filter
	$filter = $odata->getFilter();
	foreach($filter as $value) {
		$data = array_filter($data, function($elem) use($value){
			switch($value[1]) {
				case 'gt': return $elem[$value[0]] > $value[2];
				case 'ge': return $elem[$value[0]] >= $value[2];
				case 'le': return $elem[$value[0]] <= $value[2];
				case 'lt': return $elem[$value[0]] < $value[2];
				case 'eq': return $elem[$value[0]] == $value[2];
				case 'ne': return $elem[$value[0]] != $value[2];
				default: return false;
			}
		});
	}

	// Set Count
	$odata->setCount(sizeof($data));

	// Sorting
	$orderby = $odata->getOrderby();
	$multisort = [];
	foreach($orderby as $value) {
		$column = array_column($data,$value[0]);
		$multisort[] = $column;
		$multisort[] = $value[1];
	}
	$multisort[] = &$data;
	array_multisort(...$multisort);

	// Slice result
	$data = array_slice($data,$odata->getSkip(),$odata->getTop(),true);

	// Select columns
	$select = $odata->getSelect();
	foreach($data as $line) {
		if($select) {
			$line = array_intersect_key($line,$select);
		}

		// Add data
		$result = $odata->addData($line);
		if($result===false) {
			$odata->nextLink();
			return;
		}
	}
}

$odata->handle();
