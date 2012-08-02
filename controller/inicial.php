<?php

class Inicial {
	
	
	function index(){ 
		//Setando os recursos listados. 
		//TODO: fazer configurador na página para setar os recursos pelo próprio usuário (criar/editar/apagar).
		$recursos = array('file1');
		$msg = '';
		
		//Checando se o comando "run" foi solicitado
		if(!isset($_POST['run'])) {	
			//criando as variáveis iniciais	
			foreach($recursos as $v){
				$dt[$v] = array('o'=>RPATH,
								'd'=>RPATH.$v.'.phar',
								'i'=>'index.php',
								'r'=>'checked',
								'z'=>'checked');
			}
		}else{ o::pt($_POST);
			//RODANDO o conversor
			include LIB.'makephar.php';
			//formatando os dados que vieram do post
			foreach($recursos as $v){if(isset($_POST[$v])) $dt[$v] = $_POST[$v];}
			//rodando
			$phar = makePhar($dt);
			//mensagem de sucesso
			$msg = '<p class="success msg xmsg">';	
			if(!is_array($phar)) $msg .= 'Dados insuficientes para a conversão - <b>nenhuma</b> conversão foi realizada!';
			else {
				foreach($phar as $k=>$v){
					if(isset($v['e'])) $msg = $v['e'] . '<br>';
					elseif(isset($v['d'])) $msg .= $v['d'] . ' -- <b>Ok</b>!<br>';
					else $msg .= 'Erro desconhecido em "<b>'. $k . '</b>"';		
				}
			}
			$msg .= '</p>';
		}
		//habilitando o botão "Run"
		$executar = true;
		
		//verificando a verão do PHP
		if(version_compare(PHP_VERSION, '5.3.0', '<')){
			$msg .= '<p class="error msg"><b>Versão do PHP incompatível!</b> - sua versão: '.phpversion().' -- versão requerida: 5.3.0 (ou mais).</p>';
			$executar = false;}
		
		//checando phar.readonly
		if(function_exists('ini_get')){
			if(ini_get('phar.readonly')!='' || ini_get('phar.readonly') == 'On'){
				$msg .= '<p class="error msg">A diretiva <b>"phar.readonly"</b> no arquivo "php.ini" deve ser " = <b>Off</b>"</p>';
				$executar = false;
			}
		}else 
			$msg .= '<p class="error msg"><b>A diretiva "phar.readonly" no arquivo "php.ini" não pode ser checada</b>.<br >
			O valor deve ser <b>" = Off "</b> para usar arquivos PHAR.<br /><b class="red">A conversão pode não funcionar!</b></p>';
		
		//Habilitando o botão "RUN"
		$noRun =(!$executar) ? 'disabled title="Não é possível converter sem solucionar o problema indicado acima!"' : 'title="Executar a conversão em PHAR!"';
		
		
		//Criando as colunas da tabela de configuração
		$table = '';
		foreach($dt as $k=>$v){
			$table .= '
				<tr>                   
					<td><input name="'.$k.'[o]" type="text" value="'.$v['o'].'"></td>
					<td><input name="'.$k.'[d]" type="text" value="'.$v['d'].'"></td>
					<td><input name="'.$k.'[i]" type="text" value="'.$v['i'].'"></td>
					<td title="Compactar o arquivo: On/Off"><input name="'.$k.'[z]" type="checkbox" value="checked" '.@$v['z'].'></td>
					<td><input name="del" class="bt_del" type="button" value="" title="Excluir este recurso." /></td>					
				</tr>';		
		}
		
		//<td title="Converter em PHAR: On/Off"><input name="'.$k.'[r]" type="checkbox" value="checked" '.@$v['r'].'></td>
		_view::val('msg',$msg);
		_view::val('table',$table);
		_view::val('noRun',$noRun);
		
		_view::set('inicial');
	}
}