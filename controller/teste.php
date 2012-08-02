<?php

class Teste {
	
	function index(){
		//o::go('inicialize');	
		
		_cfg::loadConfig('app');
		exit(o::pt(_cfg::this()));
		
	}
	
	function udu(){
		_pt(func_get_args());	
		
	}	
	
	
	
	
}