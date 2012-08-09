<?php

class Controller_Inicial {
	
	
	function index(){
                //_cfg::$fileIni = 'magé';
		echo o::pt(_cfg::this());
                //_cfg::saveConfig(RPATH.'teste.ini');
		_view::set('inicial');
	}
}