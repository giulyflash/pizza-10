<?php

namespace View;

class View {

	/**
	 * referencia estática a própria classe!
	 */
	public static $THIS = null;

	/**
	 * Buffer contendo views a serem renderizadas
	 */
	public $varViews = array();

	/**
	 * Buffer contendo variáveis para as views
	 */
	public $varViewVar = array(0=>array());

	/**
	 * Construtor singleton da própria classe
	 * acesso ao método estático para criar uma instância da classe automáticamente
	 *
	 * @return this instance
	*/
	public static function this(){
		return (!isset(static::$THIS)) ? static::$THIS = new static : static::$THIS;
	}


	/**
	 * Setando uma View
	 *
	 * @param string $view nome do arquivo contendo a view
	 * @param array $data variáveis para a view
	 * @param string $nome nome de referencia para a view
	 *
	 * @return void adiciona a view (e variáveis) para a renderização
	*/
	static function set($view='index', $nome='', $local = VIEW){
		self::this()->varViews[(($nome == '')? count(self::this()->varViews): $nome)]
			= array(
					'file' => str_ireplace(array('.html', '.htm', '.php', '.neos'), '', trim($view)),
					'local' => rtrim($local, '/\\ ')
					);
	}

	/**
	 * Carregando uma variável para as views
	 *
	 * @param string $var nome da variável
	 * @param mixed $var valor da variável
	 * @param string $view nome da view a que pertence
	 * @return void conteudo da variável será armazenada para a renderização da view
	*/

	//alias para o método 'value'
	static function val($var, $val='', $view = null){self::value($var, $val, $view);}

	static function value($var, $val='', $view = null){
		if($view == null) $view = 0;
		//se for enviado um array de variáveis...
		if(is_array($var)){
			foreach ($var as $k=>$v){
				if(is_numeric($k)) $k = 'default';
				self::this()->varViewVar[$view][$k] = $v;
			}
		} else {
		//caso seja um para $var->$val
			self::this()->varViewVar[$view][$var] = $val;
		}
	}

	/**
	 * Renderiza a saída
	 *
	 * @return void
	*/
	static function produce(){
		//pegando as variáveis - TODO: criar sistema que pega somente as variáveis da view atual
		extract(self::this()->varViewVar[0]);

		//incluinda as views solicitadas no controller
		foreach(self::this()->varViews as $nomeView=>$valView){
			//arquivo da view
			$varq = $valView['local'] . DS . $valView['file'] . EXTVW;
			$neosarquivo = file_exists($varq)
							? $varq
							: exit('View "' . $valView['file'] . '" não encontrada!');
			//incluindo o arquivo
			include $neosarquivo;
		}

		//mostrando a barra de status
		if(\_cfg::this()->statusBar) echo self::statusBar();

		//Enviando a saída ao navegador
		ob_end_flush();
	}


	private static function statusBar(){
		//mostrando o tempo

//		$sb = '<table id="neostatustable" title="click para esconder!"><tr><th colspan="2">NEOS PHP Framework - ver</th></tr><tr><th colspan="2">Arquivos Incluidos</th></tr>';
//		$ct = $cf = 0;
//		foreach(get_included_files() as $f){
//			$fz = filesize($f);
//			$sb .= '<tr><td>'.$f.'</td><td class="neostatusright">'.number_format($fz/1000,2,',','.').'&nbsp;kb</td></tr>';
//			$ct += $fz;$cf++;
//		}
//		$sb .= '</table>';



		return  '<div style="position:fixed;right:10px;bottom:0;color:#977">'.
			//$sb.
			number_format(round(((memory_get_usage()+memory_get_peak_usage())/2000),0),0,',','.').' kb | '.
			number_format((microtime(true)-INITIME)*1000,1,',','.').' ms'.
			' | Base: '.BASE.
			' | Url: '.\_cfg::this()->url.
			' | Ctrl: '.\_cfg::this()->ctrl.
			' | Func: '.\_cfg::this()->func.
			' | Args: '.implode(' + ',\_cfg::this()->args).'</div>';

	}




}