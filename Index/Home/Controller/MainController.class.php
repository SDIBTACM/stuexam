<?php
namespace Home\Controller;
use Think\Controller;

class MainController extends Controller {
	public function _initialize(){
		if(!session('user_id'))
			$this->error('Please Login First!');
	}
}
?>
