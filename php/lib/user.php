<?php

/**
 * Gerencimento de Usuário.
 * Também pode ser entendido como gerenciamento de acesso ao site. Representa/retorna os parâmetros do browser (ou robot) usado para acessar o site.
 * @copyright	NEOS PHP Framework - http://neosphp.org
 * @license		http://neosphp.org/license 
 * @author		Paulo R. B. Rocha - prbr@ymail.com
 * @version		CAN : B4BC
 * @package		Neos\Library
 * @access 		public
 * @since		CAN : B4BC
 */
 
 
/*
USER OBJECT

	Esta classe implementa um objeto USER que contem as definições do usuário do site/aplicação corrente.
	Para a preservação dos dados entre sessões se fáz necessário o uso de um banco de dados escolhido pelo desenvolvedor ou um bd em Sqlite.
	Os seguintes parâmetros podem ser configurados: identificação, autenticação, permissão, rastro (logs), ciclo vital, categoria, bagagens, etc.

PARA LOGIN/OUT

	Você precisa criar uma tabela no banco de dados com, pelo menos, os seguintes campos:

	CREATE TABLE 'USUARIO' (
	USER_ID integer primary key,
	USER_IDKEY varchar(100),
	USER_LOGIN varchar(10),
	USER_PASS varchar(100),
	USER_ACTIVE varchar(100) default 'N',
	
	-- crie outros campos a seu critério
	);
  
	Atenção: USER_ID não é usado por essa classe, portanto, é opcional usar um identificador único (ID). Lembre-se que o campo USER_LOGIN também pode ser considerado como um identificador, pois DEVE ser único.
	Estes são os nomes padrões (USUARIO, USER_ID, etc). Para usar seus próprios nomes de campos e tabela (aproveitando uma tabela já existente) você deve indicar os nomes de tabela, banco de dados (alias) e campos no arquivo de configuração do NEOS.
	
	Assim, no arquivo app/app.ini:

	[user]
		initialise		= "1"			;carregamento automático da classe (opcional)
		table			= "usuarios"
		col_id			= "id"
		col_login		= "login"
		col_pass		= "pass"
		col_name		= "name"
		col_active		= "status"
		life			= 9				;tempo de vida da sessão do usuário (em segundos)
		default_name	= "Visitante"

PARA USAR

	Use a função '_user()' do helper 'functions' para acessar as funções e parâmetros desta classe.
	Ex.: if(_user()->login()) echo 'Você está logado no sistema';
	Ex.: if(_user()->login()) echo 'Olá, '._user()->get('name').'!<br/>Você está logado no sistema.';
	
	É possivel chamar a classe diretamente:	
	Ex.: if(\Library\User::this()->login()) echo 'Você está logado no sistema';

 */
 
namespace Lib;

class User
	extends \NEOS {
	
	//Default Setup - será modificado com os dados do app.ini - se existir
	private $setup = array(	'db'=>'',
							'table'=>'usuario',
							'col_id'=>'user_id',
							'col_login'=>'user_login',
							'col_pass'=>'user_pass',
							'col_active'=>'user_active',
							'life'=>900,
							'login'=>false);
							
	public $id = null;
	public $email = '';
	public $pass = '';
	public $name = 'Visitante';
	public $access = array(0);
	public $status = 'N';
	

    function __construct($id = null) {
        if (!session_id()) session_start();
		//carregando as configurações...
		$this->_config();
		//pegando o usuário indicado em $id
		if($id != null){
			$q = \_db::query('SELECT *	FROM '.$this->setup['table'].'
										WHERE '.$this->setup['col_id'].'="'.$id.'"
										AND '.$this->setup['col_active'].'="S"',
										$this->setup['db']);
										
			if($q){	foreach($q[0] as $k=>$v){ $this->{$k} = $v; }}		
		} else $this->_timeLife();//checando o TimeLife
    }

    /**
	* carrega os parametros de configuração
	*/
    private function _config() {
        $this->setup['db'] = '';
        if (isset(\_cfg::this()->user)) {
            foreach (\_cfg::this()->user as $k => $v) { $this->setup[$k] = $v; }
            return true;
        } else return false;
    }

    /**
	* Login
	*/
	function login($login = '', $senha = '') {
		//retornando o status; logado/não logado
		if($login == '') return $this->setup['login'];
		//tratando login/senha
        $login = strtoupper($this->_escape($login));
        $senha = md5(trim($senha));

        //buscando no BD
        $q = \_db::query('SELECT *	FROM '.$this->setup['table'].'
									WHERE UPPER('.$this->setup['col_login'].')="'.$login.'"
									AND '.$this->setup['col_pass'].'="'.$senha.'"
									AND '.$this->setup['col_active'].'="S"',
									$this->setup['db']);
        if($q){
            //carregando TODOS os dados para acesso rápido
            foreach ($q[0] as $k => $v) {
                $this->{$k} = $v;
                $_SESSION['DB'][$k] = $v;
            }
            $this->setup['login'] = true;
            $_SESSION['login'] = true;
            $_SESSION['life_time'] = $this->setup['life'] + time();
            return true;
        }else return false;
    }

    /**
	* Logout/logoff
	*/
    function logout(){$this->logoff();}
    function logoff(){
        if (!session_id())session_start();
        //matando todas as variáveis da sessão
        $_SESSION = array();
        //destruindo o cookie da sessão (no navegador)
        if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time() - 42000, '/');
        // Finally, destroy the session.
        session_destroy();
        //redefine (envia cookie tambem) a sessão atual
        session_regenerate_id();
        //resetando o login e id ...
        $this->setup['login'] = false;
        $this->id = null;
        $_SESSION['login'] = false;
    }

	/**
	 * Pegando dados de um parâmetro do obj.
	 *
	 * @param string $name Nome da propriedade a ser retornada.
	 * @return mixed Retorna o que está armazenado na propriedade.
	 */	
	function get($name){
		return (isset($this->{$name})) ? $this->{$name} : false;		
	}
	
	/**
	 * Modificando uma propriedade do obj.
	
	 * @param string $name Nome da propriedade a ser modificada.
	 * @param mixed $value Valor a ser atribuído a propriedade.
	 * @return bool Sucesso true/false
	 */	
	function set($name, $value){
		if(!isset($this->{$name})) return false;		
		return $this->{$name} = $value;		
	}
	

    /**
	* ajustando o tempo de vida 
	*/
    function _timeLife() { 
        //checando se existe...
        if (isset($_SESSION['login']) && isset($_SESSION['life_time'])) {
            $this->setup['login'] = true;
			
			//atualizando o objeto com os dados da SESSION
			foreach($_SESSION['DB'] as $k=>$v){ $this->{$k} = $v;}
			
            //checando o lifeTime -> extendendo o tempo
            if ($_SESSION['life_time'] >= time()) $_SESSION['life_time'] = (time() + $this->setup['life']);
            else $this->logoff();			
		}
    }

}