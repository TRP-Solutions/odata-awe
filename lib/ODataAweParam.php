<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
trait ODataAweParamTrait {
	private $param = [];

	private function Param($input) {
		if(isset($input['$top'])) {
			$this->param['top'] = (int) $input['$top'];
		}
		else {
			$this->param['top'] = null;
		}
		if(isset($input['$skip'])) {
			$this->param['skip'] = (int) $input['$skip'];
		}
		else {
			$this->param['skip'] = 0;
		}

		return [];
	}

	public function getTop() {
		return $this->param['top'];
	}
	public function getSkip() {
		return $this->param['skip'];
	}
}
