<?php

class Controller_Phar {

	function index(){
		//Setando os recursos listados.
		$recursos = array(trim(str_replace(dirname(RPATH), '', RPATH), ' '.DS));
		$msg = '';

		//Checando se o comando "run" foi solicitado
		if(!isset($_POST['run'])) {
			//criando as variáveis iniciais
			foreach($recursos as $v){
				$dt[$v] = array('o'=>RPATH,
						'd'=>dirname(RPATH).DS.$v.'.phar',
						'i'=>RPATH.'index.php',
						'k'=>dirname(RPATH).DS.$v.'.phar.pubkey',
						'r'=>'checked',
						'z'=>'checked');
			}
		}else{
			//RODANDO CONVERSOR -------------

			//formatando os dados que vieram do post
			foreach($_POST as $k=>$v) {if (is_array($v)) $dt[$k] = $v;}
			//rodando
			$phar = Lib\Util\File::makePhar($dt);
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
		$noRun =(!$executar)
			? 'disabled title="Não é possível converter sem solucionar o problema indicado acima!"'
			: 'title="Executar a conversão em PHAR!"';

		//Criando as colunas da tabela de configuração
		$listagem = $this->ajaxForm($dt);


//		$kt = '';
		//pegando as assinaturas válidas
//		foreach(\Phar::getSupportedSignatures() as $k){
//			$kt .= '<option value="'.$k.'">'.$k.'</option>';
//		}
//
//		foreach($dt as $k=>$v){
//			$listagem .= '
//			<div class="listagem">
//				<h3>Conversor PHAR <input name="del" class="bt_del" type="button" value="" title="Excluir este recurso." /></h3>
//				<div>
//					<label>Origem</label>
//					<input name="'.$k.'[o]" type="text" value="'.$v['o'].'">
//
//					<label>Destino</label>
//					<input name="'.$k.'[d]" type="text" value="'.$v['d'].'">
//
//					<label>Chave de Segurança : </label>
//					<select name="'.$k.'[t]">'.$kt.'</select>
//					<input name="'.$k.'[k]" type="text" value="'.$v['k'].'">
//
//					<label>Stub [executar como default]</label>
//					<input name="'.$k.'[i]" type="text" value="'.$v['i'].'">
//
//                                        <label><input name="'.$k.'[z]" type="checkbox" value="checked" '.@$v['z'].' title="On/Off - Compacta o arquivo PHAR no final da conversão."> Compactar</label>
//				</div>
//			</div>';
//		}

		//setando as variáveis de VIEWS
		_view::val('msg',$msg);
		_view::val('listagem',$listagem);
		_view::val('noRun',$noRun);

		//carregando a view
		_view::set('phar');
	}



        function ajaxForm($dt = ''){
            $return = true;
            //se for informado apenas o nome da nova seção - vindo do ajax
            if(!is_array($dt)){
                $return = false;
                $v = $dt;
                $dt = array();
                $dt[$v] = array('o'=>RPATH,
				'd'=>dirname(RPATH).DS.$v.'.phar',
				'i'=>RPATH.'index.php',
				'k'=>dirname(RPATH).DS.$v.'.phar.pubkey',
				'r'=>'checked',
				'z'=>'checked');

            }
            $kt = $o = '';
            //pegando as assinaturas válidas
            foreach(\Phar::getSupportedSignatures() as $k){
                    $kt .= '<option value="'.$k.'">'.$k.'</option>';
            }
            foreach($dt as $k=>$v){
                $o .= '<div class="listagem">
                        <h3>Conversor PHAR <input name="del" class="bt_del" type="button" value="" title="Excluir este recurso." /></h3>
                        <div>
                                <label>Origem</label>
                                <input name="'.$k.'[o]" type="text" value="'.$v['o'].'">

                                <label>Destino</label>
                                <input name="'.$k.'[d]" type="text" value="'.$v['d'].'">

                                <label>Chave de Segurança : </label>
                                <select name="'.$k.'[t]">'.$kt.'</select>
                                <input name="'.$k.'[k]" type="text" value="'.$v['k'].'">

                                <label>Stub [executar como default]</label>
                                <input name="'.$k.'[i]" type="text" value="'.$v['i'].'">

                                <label><input name="'.$k.'[z]" type="checkbox" value="checked" '.@$v['z'].' title="On/Off - Compacta o arquivo PHAR no final da conversão."> Compactar</label>
                        </div>
                </div>';
            }
            if($return) return $o;
            else echo $o;
        }

}