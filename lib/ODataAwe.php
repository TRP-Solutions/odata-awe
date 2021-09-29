<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
require_once __DIR__.'/ODataAweMeta.php';

class ODataAwe {
	use ODataAweMetaTrait;

	private $options = null;
	private $functions = [];
	private $data = [];

	public function __construct($options = []) {
		$this->options = $options;
		if(!isset($this->options['rewritebase'])) {
			$this->options['rewritebase'] = '';
		}
		if(!isset($this->options['namespace'])) {
			$this->options['namespace'] = 'ODataAwe';
		}
	}

	public function handle() {
		$uri = $_SERVER['REQUEST_URI'];

		if($this->options['rewritebase']) {
			if(strpos($uri,$this->options['rewritebase'])===0) {
				$uri = substr($uri,strlen($this->options['rewritebase']));
			}
		}
		if(strpos($uri,'/')===0) {
			$uri = substr($uri,1);
		}
		$param = explode('/',$uri);

		foreach($param as $part) {
			if($part==='$metadata') {
				$this->Metadata();
				return;
			}
		}

		header('Content-type: application/json; odata.metadata=minimal');
		header('OData-Version: 4.0');

		$entityset = !empty($param[0]) ? $param[0] : null;

		if($entityset===null) {
			foreach($this->functions as $key => $function) {
				$this->AddData([
					'name' => $key,
					'kind' => 'EntitySet',
					'url' => $key,
				]);
			}
		}
		else {
			if(isset($this->functions[$entityset])) {
				if(is_callable($this->functions[$entityset]['callback'])) {
					call_user_func($this->functions[$entityset]['callback'],$this,[]);
				}
				else {
					throw new Exception('Function: '.$this->functions[$entityset]['callback'].' is not callable');
				}
			}
			else {
				echo 'EntitySet: '.$entityset.' is not fould';
				http_response_code(404);
				exit;
			}
		}

		$context = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'].$this->options['rewritebase'];
		if($entityset) $context .= '/'.$entityset;
		$context .= $entityset ? '/$metadata#'.$entityset : '/$metadata';

		$json = [
			'@odata.context' => $context,
			'value' => $this->data,
		];
		echo json_encode($json,JSON_UNESCAPED_SLASHES);
	}

	public function addFunction($name,$fields,$callback = null) {
		if($callback===null) $callback = $name;
		$this->functions[$name] = [
			'callback' => $callback,
			'field' => $fields,
		];
	}

	public function addData($data) {
		$this->data[] = $data;
	}
}
