<?php
/*
ODataAwe is licensed under the Apache License 2.0 license
https://github.com/TRP-Solutions/odata-awe/blob/main/LICENSE
*/
trait ODataAweParamTrait {
	private $param = [];

	private function Param($input) {
		$this->param['top'] = isset($input['$top']) ? (int) $input['$top'] : null;
		$this->param['skip'] = isset($input['$skip']) ? (int) $input['$skip'] : 0;
		$this->param['count'] = empty($input['$count']) ? false : true;
		$this->param['select'] = empty($input['$select']) ? null : array_fill_keys(explode(',',$input['$select']),null);
		$this->param['filter'] = empty($input['$filter']) ? [] : explode(' and ',$input['$filter']);
	}

	private function Header($headers) {
		if(isset($headers['Prefer'])) {
			$prefer = explode(',',$headers['Prefer']);
			foreach($prefer as $item) {
				if(strpos($item,'odata.maxpagesize')===0) {
					$maxpagesize = (int) substr($item,strpos($item,'=')+1);
					if($maxpagesize<$this->options['maxpagesize']) {
						$this->options['maxpagesize'] = $maxpagesize;
					}
				}
			}
		}
	}

	public function getTop() {
		return $this->param['top'];
	}
	public function getSkip() {
		return $this->param['skip'];
	}
	public function getSelect() {
		return $this->param['select'];
	}
}
