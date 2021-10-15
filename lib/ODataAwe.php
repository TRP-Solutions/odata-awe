<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
require_once __DIR__.'/ODataAweMeta.php';
require_once __DIR__.'/ODataAweParam.php';

class ODataAwe {
	use ODataAweMetaTrait;
	use ODataAweParamTrait;

	private $options = null;
	private $functions = [];
	private $data = [];
	private $count = null;

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
		if(strpos($uri,'?')!==false) {
			$uri = substr($uri,0,strpos($uri,'?'));
		}
		$path = explode('/',$uri);

		foreach($path as $part) {
			if($part==='$metadata') {
				$this->Metadata();
				return;
			}
		}

		$this->Param($_GET);
		header('Content-type: application/json; odata.metadata=minimal');
		header('OData-Version: 4.0');

		$entityset = !empty($path[0]) ? $path[0] : null;

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
					call_user_func($this->functions[$entityset]['callback'],$this);
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

		$json = [];
		$json['@odata.context'] = $context;
		if($this->param['count'] && $this->count!==null) $json['@odata.count'] = $this->count;
		$json['value'] = $this->data;
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

	public function setCount($count) {
		$this->count = (int) $count;
	}
}
