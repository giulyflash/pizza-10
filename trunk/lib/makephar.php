<?php

/*	Converte diretórios inteiros para arquivos PHAR
 *	$dt é um array com três dados importantes:
 
 *	$dt[n]    -> "n" pode ser um nome para a compilação ou apenas um índice numérico.
 *	$dt[n][o] -> diretório de origem
 *	$dt[n][d] -> arquivo Phar de destino (caminho + nome do arquivo + extensão)
 *	$dt[n][i] -> arquivo que será carregado na inicialização do Phar resultante
 *	$dt[n][z] -> true/false - indica se o arquivo Phar deve ser do tipo compactado
 *	$dt[n][r] -> true/false - executar/ignorar o processamento
 *  constante "PATH" é requerida e indica o path raíz deste sistema

 */

function makePhar(array $dt){
	
	//retornando com erro se o array estiver vazio
	if(count($dt) <= 0) return false;
	
	//aumentando a memoria e o tempo de execução - pode ser muito significante em sistemas lentos e diretórios muito grandes
	ini_set('memory_limit', '30M');
	ini_set('max_execution_time', 180);
	
	//Array com os dados da execução
	$ok = array();
	
	//lendo e executando as conversões indicadas
	foreach($dt as $k=>$lote){
		
		//checando se deve converter indice['r']
		if(!isset($lote['r'])) continue;
	
		$dir = trim($lote['o'], ' \\/');
		$stub = '<?php 
			Phar::interceptFileFuncs();
			Phar::mungServer(array(\'REQUEST_URI\', \'PHP_SELF\', \'SCRIPT_NAME\', \'SCRIPT_FILENAME\'));
			Phar::webPhar(\'\', \'\', \'404.php\');
			__HALT_COMPILER();';
			
			// include(\'phar://\' . __FILE__ . \'/' . $lote['i'] . '\');
		
		if(is_dir($dir)){ 		
			//criando arquivo PHAR
			$phar = new Phar(trim($lote['d'], ' \\/'));	
			
			//pegando o diretório (e sub-diretórios) e arquivos contidos
			$phar->buildFromIterator(
				new RecursiveIteratorIterator(
				 new RecursiveDirectoryIterator($dir)), $dir);
			
			//criando o cabeçalho Stub
			$phar->setStub($stub);
			
			//carregando a assinatura
			if(file_exists(LIB.'key.md5'))
				$phar->setSignatureAlgorithm(Phar::MD5, file_get_contents(LIB.'key.md5'));
			
			//comprimindo os dados (exceto o Stub)
			$compactar = false;
			if(isset($lote['z'])){
				$compactar = true;
				if(Phar::canCompress(Phar::GZ)) 		$phar->compressFiles(Phar::GZ);
				elseif (Phar::canCompress(Phar::BZ2))	$phar->compressFiles(Phar::BZ2); 
			}
			//adicionando os dados de saída
			$ok[$k] = array('o'=>$dir, 'd'=>$lote['d'], 'z'=>$compactar, 'i'=>$lote['i']);
			
		} else $ok[$k] = array('e'=>'O diretório "' . $dir . '" não existe!');		
	}
	if(count($ok) == 0) return false;
	return $ok;
}