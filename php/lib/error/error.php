<?php
namespace Lib\Error;
/**
 * Classe para tratamento de erros e exceções.
 * @copyright	NEOS PHP Framework - http://neosphp.org
 * @license		http://neosphp.org/license
 * @author		Paulo R. B. Rocha - prbr@ymail.com
 * @version		CAN : B4BC
 * @package		Neos\Error
 * @access 		public
 * @return		mixed Error/Exception display
 * @since		CAN : B4BC
 */

class Error
	extends \Exception {

	/**
	 * referencia estática a própria classe!
	 */
	public static $THIS = null;

	/**
	 * Código do erro atual e referência para HELP!
	 */
	public $codigo = 0;

	/**
	 * Path para a classe que originou o erro
	 */
	public $classPath = null;




	/**
	 * Construtor da classe Exception (parent)
	 *
	 * @return void
	*/

	public function __construct($m = '',$c = 0){exit('asfdasfd');
		parent::__construct($m, $c);
	}

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
	 * Controle de erros do Framework
	 *
	 * @param $n 	código do erro
	 * @param $m	mensagem de erro
	 * @param $f	arquivo onde ocorreu o erro
	 * @param $l	número da linha onde ocorreu o erro
	 * @param $v	array com variáveis disponíveis no contexto
	 *
	 * @return html|void	mostra uma mensagem de erro; toma uma decisão programada ou retorna sem ação.
	*/
	public static function error($n=0, $m='', $f='', $l='', $v=''){ exit($m.'<br />'.$f.' ['.$l.']');
		if(!\_cfg::this()->error['active'])return false;
		//limpando o buffer de saída para exibir SOMENTE a mensagem de erro
		ob_clean();
		//pegando os dados do TRACE
		$trace = self::this()->_errorGetTrace();
		//pegando os dados do erro
		$d = ($trace['dt'] == '') ? '<p>' . $f . ' [' . $l . ']</p>' : $trace['dt'];

		//gravando o log
		if(strpos(\_cfg::this()->error['action'], 'file') !== false) self::this()->toLog($m, $d, $trace['table']);
		//enviando por email
		if(strpos(\_cfg::this()->error['action'], 'mail') !== false) self::this()->toEmail($m, $d, $trace['table']);
		//desviando o usuário para a página de tratamento de erros do site
		if(strpos(\_cfg::this()->error['action'], 'route') !== false) _goto(\_cfg::this()->error['route']);
		//mostrando o erro ou uma tela vazia - prefira criar um "route"
		if(strpos(\_cfg::this()->error['action'], 'display') === false) exit();
		//mostrando o erro na tela
		exit(	self::head().
				'<h2>' . $m . '</h2><div id="msg">'.
				$d . '</div>' .
				self::this()->_errorGetHelp() .
				$trace['table'] .
				self::footer()
				);
	}

	/**
	 * Controle de exceção do Framework
	 *
	 * @param $e objeto Exception
	 * @return void O retorno depende da função 'self::error' (acima).
	*/
	public static function exception($e){
		$m = $e->getMessage();
		$f = $e->getFile();
		$l = $e->getLine();
		$n = $e->getCode();
		if($n == 0 && method_exists($e, 'getSeverity')) $n = $e->getSeverity();
		self::error($n, $m, $f, $l);
	}


	/**
	 * Gera os dados e envia por email
	 *
	 * @param $m 		string Mensagem de erro
	 * @param $d 		string Dados sobre o erro
	 * @param $trace 	string Tabela com os dados do trace
	 * @return html		Envia os dados imediatamente por email
	*/

	protected function toEmail($m, $d, $trace){
		$mail = new \Library\Mail;
		$cfg = \_cfg::this();
		//carregando as configurações de email do sistema
		$cfg->loadConfig(array('mail', 'app\mail'));

		//configurando a classe Mail
		$mail->Host = $cfg->mail['host'];

		$mail->Body = '<div>
		<h1>NEOS PHP Framework - erro na aplicação</h1>
		<p>'. date('d/m/Y - H:i:s') .'</p>
		<p>Url: ' . URL . '</p>
		<h2>' . $m . '</h2>
		<h3>' . $d . '</h3>
		'. $trace .'</div>';

		$mail->Subject = $cfg->mail['subject'];
		$mail->From = $cfg->mail['from'];
		$mail->Fromname = $cfg->mail['fromname'];
		$mail->AddAddress = $cfg->mail['to'];

		//Enviando o email
		return $mail->Send();
	}


	/**
	 * Gera os dados para o arquivo (log) de erros
	 *
	 * @param $m 		string Mensagem de erro
	 * @param $d 		string Dados sobre o erro
	 * @param $trace 	string Tabela com os dados do trace
	 * @return html		Grava os dados no arquivo de log do NEOS
	*/

	protected function toLog($m, $d, $trace){
		\NEOS::pushToLog('<div><p>'. date('d/m/Y - H:i:s') .'</p>
		<p>Url: ' . URL . '</p>
		<p>Request: '. $_SERVER['REQUEST_URI'] .'</p>
		<h2>' . $m . '</h2>
		<h3>' . $d . '</h3>
		'. $trace .'</div>', PATH_APP . DS . \_cfg::this()->error['logFile']);
	}

	/**
	 * Procura por uma ajuda sobre o erro
	 *
	 * @param $nHelpCod number	Código de erro interno
	 * @return html		Retorna uma ajuda sobre o erro atual ou uma string vazia se não existir
	*/

	protected function _errorGetHelp(){
		if($this->classPath == null) return '';
		//Definindo os caminhos
		$neos	= (strpos(PATH_NEOS, 'phar:') === false)? PATH_NEOS : dirname(str_replace('phar://','',PATH_NEOS));
		$app	= (strpos(PATH_APP , 'phar:') === false)? PATH_APP  : dirname(str_replace('phar://','',PATH_APP ));
		//Procurando os arquivos de help
		if(!\_cfg::this()->error['helpPath'] == ''
				&& file_exists(\_cfg::this()->error['helpPath']))
 												  $dir = 'phar://' . trim(\_cfg::$error['helpPath'], ' /\\') . '/';
		elseif(file_exists(PATH  . '/help.phar')) $dir = 'phar://' . PATH . '/help.phar/';
		elseif(file_exists($neos . '/help.phar')) $dir = 'phar://' . $neos . '/help.phar/';
		elseif(file_exists($app  . '/help.phar')) $dir = 'phar://' . $app . '/help.phar/';
		else return '';

		$o = false;
		$x = explode('/', str_replace(array('\\', '/'), '/', strtolower($this->classPath)));

		for($i = count($x); $i > 0; $i--){
		 	$path = $dir.implode('/', $x).'/error_'.$this->codigo.'.php';
			if (file_exists($path)) {
				ob_start();
				eval('?>'.file_get_contents($path));
				$o = ob_get_clean();
				break;
			}
			array_pop($x);
		 }
		//checando se o arquivo foi encontrado - [não -> carrega o default]
		if(isset($o)) {
			return '<h2>Informações</h2>
			<div id="ajuda">
			' . $o . '</div>';
		}
		return '';
	}

	/**
	 * Controle de exceção do Framework
	 *
	 * @param $e
	 * @return html
	*/
	protected function _errorGetTrace($e=''){
		$isErro = true;
		if(!is_object($e)) {
			$e = $this;
			$isErro = false;
		}
		$tp = '
		<h2>Trace</h2>
        <table id="trace" width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><th>File</th><th>Line</th><th>Action</th></tr>
            ';

		$x = $e->getTrace();

		//diferente em Error/Exception
		if(!$isErro){
			foreach($x as $v){
				if(isset($v['class']) && ($v['class'] == 'Error\Error') && ($v['function'] == 'this') ) {
				array_shift($x);
				continue;
				}else break;
			}
		}

		//invertendo a ordem cronológica dos eventos
		$x = array_reverse($x);

		//lendo o registro (trace)
		foreach($x as $k=>$tc){
				$tp .= '
			<tr><td>'.((isset($tc['file'])) ? $tc['file'] : ' ... ').
				'</td><td align="center">'.((isset($tc['line'])) ? $tc['line'] : ' ... ').
				'</td><td>'.((isset($tc['class'])) ? $tc['class'] : '').
				((isset($tc['type'])) ? $tc['type'] : '').
				((isset($tc['function'])) ? $tc['function'].'()' : '').
				'</td>
			</tr>';
			}

		$tp .= '
		</table>';

		if(isset($tc['file']) && $tc['file'] != ''){
			$dt = (($this->classPath != null) ? '<p><b>Recurso: </b>' . $this->classPath . '</p>' : '') .'
				<p><b>Arquivo: </b>' . ((isset($tc['file'])) ? $tc['file'] : '&nbsp;').((isset($tc['line'])) ? ' [' .$tc['line'] . ']' : '') . '</p>';
		}else{$dt = '';}
		return array('table' => $tp, 'dt'=>$dt);
	}

	/**
	 * Pega o head html
	 *
	 * @return html
	*/
	public static function head(){
		return file_get_contents( dirname(__FILE__) . '/head.html' );
	}

	/**
	 * Pega o footer html
	 *
	 * @return html
	*/
	public static function footer(){
		return file_get_contents( dirname(__FILE__) . '/footer.html' );
	}

}