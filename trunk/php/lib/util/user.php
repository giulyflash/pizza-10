<?php

namespace Lib\Util;

 /*
	MÉTODOS DISPONÍVEIS
	
	getIp()
	getBrowser()
    getOs()
    getRobot()
    getMobile()
    getLang($l='')    -> $l entrando com o valor retorna true/false se compatível
    getCharset($c='') -> $c entrando com o valor retorna true/false se compatível
    getMethod()
    

 */

class User
	extends \NEOS {		
	
	
    function getIp() {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return false;
        }
    }

    function _includeAgent() {
        if (!isset($this->_user_agents)) {
            $this->_user_agents = include (PATH_NEOS . '/neos/config/user_agents.php');
        }
    }

    function _searcUserAgent($type, $in='') {
        if ($in == '') $in = 'HTTP_USER_AGENT';
		if (isset($_SERVER[$in])) {
            $this->_includeAgent();
			$uag = $_SERVER[$in];
			$ver = '';
            foreach ($this->_user_agents[$type] as $k => $v) {
				$t = strpos(strtoupper($uag), strtoupper($k));
                if ($t !== false) {
					if($type == 'browsers'){
						$t += strlen($k);
						if($uag[$t] == '/' || $uag[$t] == ' '){ 
							for($i = $t +1; $i < strlen($uag) ; $i++){
								if($uag[$i] == ' ' || $uag == '/') break;
								$ver .= $uag[$i];							
							}
						}
						$b['browser'] = $v;
						$b['version'] = $ver;
						return $b;
					} else { return $v;}
                    break;
                }
            }
        }return false;
    }

    function getBrowser() {
        return $this->_searcUserAgent('browsers');
    }

    function getOs() {
        return $this->_searcUserAgent('platforms');
    }

    function getRobot() {
        return $this->_searcUserAgent('robots');
    }

    function getMobile() {
        return $this->_searcUserAgent('mobiles');
    }

    /**
	* checa se a linguagem é suportada pelo usuário/browser
	*/
    function getLang($l='') {
        if ($l == '') {
            return $this->_searcUserAgent('lang', 'HTTP_ACCEPT_LANGUAGE');
        }if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strrpos(strtoupper($_SERVER['HTTP_ACCEPT_LANGUAGE']), strtoupper($l)) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
	* verifica se o charset é compatível com o usuário/browser
	*/
    function getCharset($c='') {
        if ($c == '') $c = \_cfg::this()->charset;
		
		if (isset($_SERVER['HTTP_ACCEPT_CHARSET']) && strrpos(strtoupper($_SERVER['HTTP_ACCEPT_CHARSET']), strtoupper($c)) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
	* retorna o método da requisição (POST/GET)
	*/
    function getMethod() {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return $_SERVER['REQUEST_METHOD'];
        } else {
            return false;
        }
    }
		
		
		
}