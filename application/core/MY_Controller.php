<?php
/**
 * @author Ariel Ayaviri
 * @version 1.1
 * 11/2015
 *
 * @property MX_Config config
 * @property CI_Benchmark benchmark
 * @property CI_Output output
 * @property CI_Profiler profiler
 * @property MY_Router router
 */
class Base_Controller extends CI_Controller {
    
    protected $currentSite;
    public $preferredLang;
    protected $defaultLang;
    
    public function __construct() {
        parent::__construct();
		$this->load->helper( "gnloader" );
		$this->load->library( 'benchmark' );
		$this->load->library( 'profiler' );

        gnLoadClass("MY_Model", "core");
        gnLoadModel("sec/Model_Site");
        gnLoadModel("sec/Model_TimeZone");

		$this->output->enable_profiler( $this->config->item( 'profiler' ) );
		$this->currentSite = Model_Site::getCurrentSite();
		$this->benchmark->mark( 'controller_execution_time_( ' . $this->router->class . ' / ' . $this->router->method . ' )_start' );
	}

	/**
	 * This method generates a response JSON setting the header to "application/json" and if the profiler is enabled add a property named "profiler" inside the response.
	 * @param array $data The data to set into response JSON.
	 * @param int $options The options for json_encode method.
	 */
	public function buildResponseJSON ( $data = [], $options = 0 )
	{
		if ( $this->config->item( 'profiler' ) )
		{
			$this->benchmark->mark( 'controller_execution_time_( ' . $this->router->class . ' / ' . $this->router->method . ' )_end' );
			$data[ 'profiler' ] = $this->profiler->run();
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $data, $options );
		exit;
	}

    protected function _initBackendLanguage() {
        /**
         * LANGUAGE SETUP
         */
        $langConfig = $this->config->item("gnLang");
        $this->defaultLang = $langConfig["default"];
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	{ 
		$deflang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	}
	else
	{
		$deflang = "en";
	}
	if ($langConfig["enabled"]) {
            $langs = array("en-us", "es-es");
            //var_dump($this->router->fetch_module());
            $this->load->helper("cookie");
            $this->preferredLang = get_cookie("gns-lang");
            if ($this->preferredLang === null || !in_array($this->preferredLang, $langs)) {
                $this->preferredLang = $this->getPreferredLanguage($langs, $deflang);
                if ($this->preferredLang === null) {
                    $this->preferredLang = $this->defaultLang;
                }
            }
        }else{
            $this->preferredLang = $this->defaultLang;
        }
        
        foreach($this->config->item("modules") as $module) {
            $languagePath = APPPATH."modules/".$module."/language/".$this->preferredLang."/default_lang.php";
            if (file_exists($languagePath)) {
                $this->lang->load($module."/default", $this->preferredLang);
            } else {
                $languagePath = APPPATH."modules/".$module."/language/".$this->defaultLang."/default_lang.php";
                if (file_exists($languagePath)) {
                    $this->lang->load($module."/default", $this->defaultLang);
                }
            }
        }
        $this->load->helper('language');
    }

    protected function getPreferredLanguage($available_languages, $http_accept_language) {
        $available_languages = array_flip($available_languages);
        $langs = array();
        preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($http_accept_language), $matches, PREG_SET_ORDER);
        foreach($matches as $match) {
            
            list($a, $b) = explode('-', $match[1]) + array('', '');
            $value = isset($match[2]) ? (float) $match[2] : 1.0;
            if(isset($available_languages[$match[1]])) {
                $langs[$match[1]] = $value;
                continue;
            }
            if(isset($available_languages[$a])) {
                $langs[$a] = $value - 0.1;
            }
        }
        if($langs) {
            arsort($langs);
            return key($langs); // We don't need the whole array of choices since we have a match
        }
    }

    protected function setTimeZone()
    {
        // if($this->currentSite->getTimezoneid() !== null)
        // {
            // $timezone = Model_TimeZone::getById( $this->currentSite->getTimezoneid() );
            // date_default_timezone_set( $timezone->getTimezone() );
        // }
    }
}

class MY_Controller extends Oauth2_Controller {
    protected $gnView;
    protected $sessUser;
    protected $gnViewConfig;
    
    public function __construct() {
        parent::__construct();
        //$this->load->driver('session');
        $this->gnViewConfig = $this->config->item("gnView");
        gnLoadModel("sec/Model_User");
        gnLoadModel("sec/Model_MenuItem");
        $this->sessUser = Model_User::getLoggedUser();
        if ($this->sessUser === null) {
            $this->sessUser = Model_User::autoLogin();
        }
        
        $this->gnView = new GN_View($this->gnViewConfig["public-template"], $this->gnViewConfig["public-template-assets"], $this->gnViewConfig["app-assets"]);
        $this->gnView->addGeneralJs("sec/assets/js/jquery.jsrender/jsrender.min.js");
        $this->gnView->addGeneralJs("sec/assets/js/jquery.parsley/dist/parsley.min.js");
        $this->gnView->addGeneralJs('sec/assets/js/general.js');
        
        $this->gnView->addGeneralCss('sec/assets/fonts/font-awesome-4.3.0/css/font-awesome.min.css');
        
        //Required components
        $this->gnView->addSnippet("sec/panel/components/page-message");
        if ($this->sessUser === null) {
            $this->gnView->addGeneralJs('sec/assets/js/app/login.js');
            $this->gnView->addSnippet("sec/panel/components/login-modal");
            $this->gnView->addSnippet("sec/panel/components/pass-restore-modal");
        } else {
            $this->gnView->addGeneralJs('sec/assets/js/app/logout.js');
            $this->gnView->addJsData(array("sessUserId" => $this->sessUser->getId()));
            $this->gnView->addSnippet("sec/panel/components/change-password");
            if ($this->sessUser->getRequiredPasswordChange()) {
                $this->gnView->addJsData(array("launchPasswordChange" => true));
            }
        }
        
        $this->gnView->addData(array(
            "sessUser" => $this->sessUser,
            "currentSite" => $this->currentSite
        ));
        
        /**
         * LANGUAGE SETUP for javascript
         */
        foreach($this->config->item("modules") as $module) {
            $langJs = $module."/assets/language/".$this->preferredLang."/default.js";
            $viewLanguagePath = APPPATH."modules/".$langJs;
            
            if (file_exists($viewLanguagePath)) {
                $this->gnView->addGeneralJs($langJs);
            } else {
                $langJs = $module."/assets/language/".$this->defaultLang."/default.js";
                $viewLanguagePath = APPPATH."modules/".$langJs;
                if (file_exists($viewLanguagePath)) {
                    $this->gnView->addGeneralJs($langJs);
                }
            }
        }
        $this->gnView->addGeneralJs("sec/assets/js/jquery.parsley/src/i18n/".substr($this->preferredLang, 0, 2).".js");
    }    
}

class Panel_Controller extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        if ($this->sessUser === null) {
            $loginConfig = $this->config->item("login");
            redirect( base_url($loginConfig["controller"])."?r=".uri_string() );
        }
        $this->gnView->setTemplate($this->gnViewConfig["panel-template"]);
        $this->gnView->setAssetsDir($this->gnViewConfig["panel-template-assets"]);
    }
}

class Oauth2_Controller extends Base_Controller {
    
    protected $_oauth;
    protected $_oauthRequest;
    protected $_oauthResponse;
    protected $_tokenData;
    public function __construct() {
        parent::__construct();
        
        $this->_initBackendLanguage();
        $this->setTimeZone();
        
        $modules = $this->config->item("modules");
        if (in_array("oauth2", $modules)) {
            $gnApi = $this->config->item("gnApi");
            
            require_once (APPPATH . 'libraries/OAuth2/Autoloader.php');
            OAuth2\Autoloader::register();
            
            gnLoadModel("oauth2/Model_OauthStorage");
            $storage = new Model_OauthStorage();
            // Pass a storage object or array of storage objects to the OAuth2 server
            // class
            $this->_oauth = new OAuth2\Server($storage, array('allow_implicit' => true));
    
            // Add the "Client Credentials" grant type (it is the simplest of the
            // grant types)
            $this->_oauth->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));
    
            // Add the "Authorization Code" grant type (this is where the oauth magic
            // happens) 
            $this->_oauth->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));
    
            // Add the "Refresh token" grant type 
            $this->_oauth->addGrantType(new OAuth2\GrantType\RefreshToken($storage,  array(
                'always_issue_new_refresh_token' => true
            )));
            
            // Add the "User Credentials" grant type 
            $this->_oauth->addGrantType(new OAuth2\GrantType\UserCredentials($storage));
            
            //Set the Request object and the token object
            $this->_oauthRequest = OAuth2\Request::createFromGlobals();
            $this->_tokenData = $this->_oauth->getAccessTokenData($this->_oauthRequest);
            $this->_oauthResponse = new OAuth2\Response();
        }
    }
}
