<?php
namespace Model;


class Usuario
	extends		\Library\User 
	implements	BaseInterface {
	
	
	//Definindo propriedades
	public $id = null;
	public $email = '';
	public $pass = '';
	public $name = 'Visitante';
	public $access = array();
	public $status = 'N';
	
	
	//Excluindo o usuário (BD)
	function delete(){
		if(is_null($this->id)) return false;
		
		//....
		\_db::query('');	
		
	}
	
	function save(){}

}