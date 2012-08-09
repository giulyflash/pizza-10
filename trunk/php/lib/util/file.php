<?php

namespace Lib\Util;

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


	/*	Converte diretórios inteiros para arquivos PHAR
	 *	$dt é um array com três dados importantes:

	 *	$dt[n]    -> "n" pode ser um nome para a compilação ou apenas um índice numérico.
	 *	$dt[n][o] -> diretório de origem
	 *	$dt[n][d] -> arquivo Phar de destino (caminho + nome do arquivo + extensão(deve ser .phar))
	 *	$dt[n][i] -> arquivo que será carregado na inicialização do Phar resultante
	 *	$dt[n][z] -> true/false - indica se o arquivo Phar deve ser do tipo compactado
	 *	$dt[n][k] -> arquivo contendo a chave pública - deve estar no mesmo diretório de destino da conversão, no formato file.phar.pubkey)
	 *  constante "PATH" é requerida e indica o path raíz deste sistema

	 */
	static function makePhar(array $dt){

		//retornando com erro se o array estiver vazio
		if(count($dt) <= 0) return false;

		//aumentando a memoria e o tempo de execução - pode ser muito significante em sistemas lentos e diretórios muito grandes
		ini_set('memory_limit', '30M');
		ini_set('max_execution_time', 180);

		//Array com os dados da execução
		$ok = array();

		//lendo e executando as conversões indicadas
		foreach($dt as $k=>$lote){
			$stub = '<?php
				Phar::interceptFileFuncs();
				Phar::mungServer(array(\'REQUEST_URI\', \'PHP_SELF\', \'SCRIPT_NAME\', \'SCRIPT_FILENAME\'));
				Phar::webPhar(\'\', \'\', \'404.php\');
				__HALT_COMPILER();';

			if(is_dir($lote['o'])){
				//criando arquivo PHAR
				$phar = new \Phar($lote['d']);

                                //pegando o diretório (e sub-diretórios) e arquivos contidos
                                $phar->buildFromDirectory($lote['o']);

				//criando o cabeçalho Stub
				$phar->setStub($stub);

				//carregando a assinatura
				if(is_file($lote['o']))
					$phar->setSignatureAlgorithm(\Phar::MD5, file_get_contents($lote['o']));

				//comprimindo os dados (exceto o Stub)
				$compactar = false;
				if(isset($lote['z'])){
					$compactar = true;
					if(\Phar::canCompress(\Phar::GZ)) 		$phar->compressFiles(\Phar::GZ);
					elseif (\Phar::canCompress(\Phar::BZ2))	$phar->compressFiles(\Phar::BZ2);
				}
				//adicionando os dados de saída
				$ok[$k] = array('o'=>$lote['o'], 'd'=>$lote['d'], 'z'=>$compactar, 'i'=>$lote['i']);

			} else $ok[$k] = array('e'=>'O diretório "' . $lote['o'] . '" não existe!');
		}
		if(count($ok) == 0) return false;
		return $ok;
	}
}