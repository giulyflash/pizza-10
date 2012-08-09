<?php
/**
 * Interface básica para os objetos Model
 *
 *
*/

namespace Model;

interface BaseInterface {

	
	/**
	* Salvando o objeto em BD ou arquivo
	*/
	function save($local = null);


	/**
	* Carregando o objeto do BD ou arquivo
	*/
	function load($local = null);


	/**
	* Excluindo o objeto do BD ou arquivo
	*/
	function delete($local = null);


	/**
	* Pegando um elemento do objeto
	*/
	function get($e = null);


	/**
	* Inserindo, criando ou modificando um elemento do objeto
	*/
	function set(array $e = null);

}
