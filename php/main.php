<?php

class Main
	extends Base {

	function __construct(){
		//Setando buffer de saída
		ob_start('ob_gzhandler');
		header('X-Powered-By: www.neosphp.org');

		//Constantes
		define('INITIME', microtime(true));
		define('DS',DIRECTORY_SEPARATOR);
		define('PATH', ((strpos(__DIR__,'phar://') === false) ? dirname(__DIR__).DS.'php'.DS : __DIR__.DS));
		define('RPATH', ((strpos(PATH,'phar://') === false) ? PATH : str_replace('phar://', '',dirname(PATH).DS)));
		define('CTRL', PATH.'controller'.DS);
		define('VIEW', PATH.'view'.DS);
		define('LIB', PATH.'lib'.DS);
		define('EXTVW', '.html'); //extensão de arquivo view
                //if(strpos(__DIR__,'phar://') !== false) exit(PATH.'<br>'.RPATH);

		//iniciando o carregador automático de classes (autoLoader)
		\Loader::initLoader();

		//alias para algumas classes
		class_alias('\Main', 'o');
		class_alias('\View\View', '_view');
		class_alias('\Loader', '_cfg');
		class_alias('\Base', 'NEOS');

		//finalmente cria a constante BASE
		define('BASE', $this->decodeUrl());

		//Tratamento de Erros
		self::initError();
	}

	static function run($config = ''){
		//carregando uma instância da classe
		$main = self::this();

		//Carregando a configuração
		if($config != '') \_cfg::this()->loadConfig(PATH.$config.'.ini');

		//Rodar o Controller/função
		$main->runController(\_cfg::this()->url);

		//Criando a visualização e enviando ao navegador do usuário
		\_view::produce();

		return $main;
	}

	/**
	 * Fazendo os 'settings' de erros.
	 * Fáz os ajustes iniciais dos reports de erros.
	 *
	 * @return bool
	*/
	final static function initError() {
		//modificando a tela de exibição de erros do PHP
		if(function_exists('ini_set')){
			ini_set('error_prepend_string', file_get_contents(LIB.'error/head.html') . '<p>');
			ini_set('error_append_string', '</p>' . file_get_contents(LIB.'error/footer.html'));
		}

		//Setando a classe de tratamento de erros
		set_error_handler('\Lib\Error\Error::error');
		set_exception_handler('\Lib\Error\Error::exception');
	}

	/**
	 * Redireciona para uma nova localização ('vai para...').
	 * Esta ação depende também da configuração do browser - browsers modernos redirecionam :P.
	 *
	 * @param string $uri Caminho interno (a partir da url base do site) ou completo (depende de '$externo') - default: página inicial.
	 * @param string $metodo Tipo de redirecionamento: 'reflesh' ou 'location'.
	 * @param numeric $cod Código do redirecionamento.
	 * @param bool $externo True habilita o redirecionamento para outro site (externo)
	 * @return void
	*/
	static function go($uri = '', $metodo = '', $cod = 302) {
		if(strpos($uri, 'http') === false) $uri = BASE . $uri; //se tiver 'http' na uri então será externo.
		if (strtolower($metodo) == 'refresh') {header('Refresh:0;url=' . $uri);}
		else {header('Location: ' . $uri, TRUE, $cod);}
		exit;
	}

	/**
	 * Imprime na tela como a função 'print_r' do PHP com tags '<pre>' do html.
	 * Isso garante um resultado gráfico mais elegante - principalmente para depuração de arrays/objetos.
	 *
	 * @param mixed $v (valor) Pode ser uma string, número, objeto ou array a serem mostrados.
	 * @param bool $ec (echo) True mostra imediatamente na tela; True retorna o conteúdo printavel (inversamente ao mesmo parâmetro da função print_r)
	 * @param bool $t (tabela) Mostra o resultado em uma tabela levemente estilizada.
	 *
	 * @return string|boll
	*/
	static function pt($v, $ec = true, $t = false) {
		if($t == false) $x = '<pre>' . print_r($v, true) . '</pre>';
		if($t == true){
			$x = '<table border="0" cellpadding="2" cellspacing="3"><tr><th>id</th><th>valor</th></tr>';
			if(is_array($v) || is_object($v)){
				foreach ($v as $k => $v) {
					if (is_array($v) || is_object($v)) $v = '<pre>' . print_r($v, true) . '</pre>';
					$x.='<tr><td>' . $k . '</td><td>' . $v . '</td></tr>';
				}
			}
		else {$x.='<tr><td> </td><td>' . $v . '</td></tr>';}
		$x.='</table>
	';
		}
		if($ec){echo $x;}
		else{return $x;}
	}

   /*
	* detecta se o acesso está sendo feito por SSL (https)
	*/
	private function _detectSSL(){
		if (!isset($_SERVER["HTTPS"]))		return false;
		if ($_SERVER["HTTPS"] == "on")		return true;
		if ($_SERVER["HTTPS"] == 1)			return true;
		if ($_SERVER['SERVER_PORT'] == 443) return true;
		return false;
	}

   /*
	* Decodifica a solicitação da URL (usuário) - se for um arquivo da pasta "public" envia e sai
	*/
	private function decodeUrl(){
		//procurando o arquivo/recurso solicitado
		$url = (isset($_SERVER['PATH_INFO'])) ?  ltrim($_SERVER['PATH_INFO'], ' /') : '';
		$url = (isset($_SERVER['ORIG_PATH_INFO'])) ?  ltrim($_SERVER['ORIG_PATH_INFO'], ' /') : $url;
		//endereço para para WEB
		$base = 'http'.(self::_detectSSL() ? 's' : '').'://'.trim($_SERVER['HTTP_HOST'],' /').
				((isset($_SERVER['PHAR_SCRIPT_NAME']))
				? $_SERVER['PHAR_SCRIPT_NAME'].'/'
				: '/' .trim(str_replace($url,'',urldecode($_SERVER['REQUEST_URI'])),' /').'/');

		//==========================================================

		//echo $url.'<br>';
                //exit('<pre>'.print_r($_SERVER, true).'</pre>'.$base.'<br>'.$url);
                //
                //
		//==========================================================

		//se for uma solicitação de arquivo - entrega e sai
		if(strpos($url,'public/') !== false) {
                    if(_cfg::this()->intPublic && file_exists(PATH.$url)) {
			//gerando header apropriado
			include LIB.'mimes.php';
			$ext = explode('.', $url);
			if(isset($_mimes[end($ext)]))
				header('Content-type: '.((is_array($_mimes[end($ext)])) ? $_mimes[end($ext)][0] : $_mimes[end($ext)]));
			//enviando o arquivo solicitado
			exit(file_get_contents(PATH.$url));
                    } else self::go(rtrim(str_replace('index.php', '', $base), ' /') . '/'.$url,'',301);
		}
		//atualizando a configuração
		\_cfg::this()->url = $url;
		return $base;
	}

   /*
	* Pega o controlador/método/argumentos da solicitação
	*/
	private function runController($url){
		$url = strtolower($url);
		//definindo os valores default
		$ctrl = \_cfg::this()->ctrl;
		$func = \_cfg::this()->func;
		$args = \_cfg::this()->args;
		//pegando os elementos (controller/function/args)
		if($url!= ''){
			$uri = explode('/', $url);
			if(isset($uri[0])) $ctrl = array_shift($uri);//pegando o controller
			if(isset($uri[0])) $func = array_shift($uri);//pegando o método
			if(isset($uri[0])) $args = $uri;//pegando os dados
		}
		//controller
		$fctrl = CTRL.$ctrl.'.php';
		//pegando os dados 'default' se o controller não existir
		if(!file_exists($fctrl)) {
			$ctrl = \_cfg::this()->ctrl;
			$func = \_cfg::this()->func;
			$args = \_cfg::this()->args;
		}
		//carregando o arquivo do controller
		include CTRL.$ctrl.'.php';

		//instanciando a classe
		$ctrl = 'Controller_' . ucfirst($ctrl);
		$class = new $ctrl;

		//definendo e chamando o método do controller
		if(is_callable(array($class, $func))) call_user_func_array(array($class, $func), $args);
		elseif(is_callable(array($class, \_cfg::this()->func))) {$func = \_cfg::this()->func; call_user_func_array(array($class, $func), $args);}
		else exit('FATAL ERROR :: Método "'.\_cfg::this()->func.'" não existe');

		//atualizando os dados no config
		\_cfg::this()->ctrl = $ctrl;
		\_cfg::this()->func = $func;
		\_cfg::this()->args = $args;

		//retornando a instância do controller
		return $class;
	}
}


//====================================== BASE ============================================

abstract class Base {

	/**
	 * referencia estática a própria classe!
	 * Todas as classes que "extends" essa BASE armazenam sua instância singleton neste array.
	 */
	static $THIS = array();


	/**
	 * Construtor singleton da própria classe.
	 * Acessa o método estático para criar uma instância da classe automáticamente.
	 *
	 * @param string $class Classe invocada.
	 * @return object this instance
	*/
	final public static function this(){
		$class = get_called_class();
		if (!isset(static::$THIS[$class])) static::$THIS[$class] = new static;
		return static::$THIS[$class];
	}

	/*
	 * Dispara o sistema de ERRORs
	 *
	 * @param $msg String Mensagem de erro a ser exibida
	 * @param $cod Number (se existir) Código da ajuda para o erro
	 *
	 * @return void 	Gera um erro no sistema!
	 */
	 static function _error($msg, $cod = 0, $class = null){
		\Error\Error::this()->codigo = $cod;
		\Error\Error::this()->classPath = ($class != null) ? $class : get_called_class();
		trigger_error($msg);
	 }
}

//====================================== LOADER ============================================

class Loader
	extends Base {

	public $fileIni = '';

	//On/off status bar
	public $statusBar = true;

	//variáveis da solicitação do usuário (URI)
	public $url = '';
	public $ctrl = 'inicial';
	public $func = 'index';
	public $args = array();
        //controle de ERROS
	public $error = Array(  'action'    =>'display', //ação em caso de erro - opções: 'displayfileroutemail'
                                'active'    => true,  //ativa/desativa a classe de erro.
				'level'     => E_ALL, //nivel dos erros reportados/ignorados
				'route'     => 'error', //ir para este controller se houver erros (se 'action' contiver 'route')
				'logFile'   => 'log_error.html', //pode ser configurado um arquivo de logs de erros nos arquivos 'config.php'
				'helpPath'  => ''); //localização dos arquivos de help das classes do NEOS ('' -> interno!)
        //modo da pasta "public" - interno = false (default)
        public $intPublic = false;


	/**
	* Inicialização do carregador automático
	*/
	final static function initLoader(){
		//setando o include_path
		$incpath = trim(get_include_path(), '.');
		$incpath = explode(PATH_SEPARATOR, $incpath);
		array_shift($incpath);
		set_include_path(implode(PATH_SEPARATOR, array_merge(array(str_replace('phar:', 'phar|', PATH)), $incpath)));
		//setando a classe de carregamento automático
		if(!function_exists('spl_autoload_register')) exit("spl_autoload não foi instalado neste sistema (PHP)");
		spl_autoload_register( function ($class){
									$class = ltrim(DS . strtolower(trim(strtr($class, '_\\', DS . DS), DS . '/ ')),' \\');
									$pth = explode(PATH_SEPARATOR, get_include_path());
									//procurando o arquivo da classe
									foreach($pth as $f){
										$f = str_replace('phar|', 'phar:', $f);
										if(file_exists($f . $class . '.php')) return require $f . $class . '.php';
									}
								});
	}

	/**
	* Carregando arquivo ".ini"
	*/
	final static function loadConfig($file){
		if(file_exists($file)){
			//atualiza o arquivo ini da classe
			self::this()->fileIni = $file;
			//pega os dados do arquivo
			$a = parse_ini_file($file, true);
			//carrega os dados na classe Loader
			foreach($a as $k=>$v){self::this()->$k = $v;}
		}else return false;
	}

	/**
	* Salvando arquivo ".ini"
	*/
	final static function saveConfig($file, $var = null){
		if(is_null($var)) $var = self::this();
		return \Lib\Util\File::toIniFile($var, $file);
	}

	/**
	* Pegando dados do Config
	*/
	static function __callStatic($name,$var){
	exit(\o::pt($name).\o::pt($var));
	}

}