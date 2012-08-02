<?php

namespace Library\Util;

 /*
	MÉTODOS DISPONÍVEIS
	
	toIniFile()
  

 */

class File
	extends \NEOS {		
	
    /**
	* Cria um arquivo ".ini"
	* 
	* @param Array $ini		Array contendo os dados a serem convertidos
	* @param String $file	Caminho e nome do arquivo ".ini"
	* 
	* @return Bool|String	Se $file for indicado retorna o status da criação/grvação do arquivo
							Se $file não for indicado retorna uma string com os dados convertidos
	*/	
    static function toIniFile(array $ini, $file = null){	
		$o = '';
		foreach($ini as $k=>$v){
			$o .= '['.$k."]\r\n";		
			//segundo nó
			if(is_array($v)){
				foreach($v as $_k=>$_v){
					//terceiro nó
					if(is_array($_v)){
						foreach($_v as $__k=>$__v){
							if(is_array($__v)) $__v = print_r($__v, true);
							$o .= "\t".$_k.'['.$__k.'] = '.(is_numeric($__v)? $__v : '"'.$__v.'"')."\r\n";
						}
					}else $o .= "\t".$_k.' = '.(is_numeric($_v)? $_v : '"'.$_v.'"')."\r\n";				
				}
			}
		}
		if($file != null && file_exists($file)) return file_put_contents($file, $o);
		else return $o;	
	}		
}