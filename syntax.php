<?php
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_addnewpage extends DokuWiki_Syntax_Plugin {
    function getInfo(){
        return array(
            'author' => 'iDo',
            'email'  => 'ido@idotech.info',
            'date'   => '20/12/2006',
            'name'   => 'addnewpage',
            'desc'   => 'This add a "new page form" in your page. \\ Syntax : {{NEWPAGE[>namespace]}}  where [>namespace] is optional.',
            'url'    => 'http://wiki.splitbrain.org/plugin:addnewpage',
        );
    }
	

    function getType(){
        return 'substition';
    }

    function getSort(){
        return 199;
    }
 
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('\{\{NEWPAGE[^\}]*\}\}', $mode, 'plugin_addnewpage');  
    }
	
    function handle($match, $state, $pos, &$handler){
      $ns = substr($match, 10, -2);  // strip markup
      return array($ns); // use an array here
	}
    
 
	function render($mode, &$renderer, $data) {
    	global $lang;
		$renderer->info['cache'] = false;
		$data = $data[0]; // get data back from the array
    	
		if ($mode == 'xhtml') {
			$cmb=$this->_makecombo($data);
			if ($cmb==$this->getLang('nooption')) {
				$renderer->doc .=(!$this->getConf('addpage_hideACL'))?$cmb:'';
				return true;
			} 

		    $renderer->doc .= '<div class="addnewpage_form" id="addnewpage_form" align="left">';
		    $renderer->doc .= '<form name="editform" id="editform" method="post" action="" accept-charset="'.$lang['encoding'].'" onsubmit="setName();return true;">';
		    $renderer->doc .= $cmb;
		    $renderer->doc .= '<input class="edit" type="text" name="title" id="addnewpage_title" size="20" maxlength="255" tabindex="2" />';			
			$renderer->doc .= '<input type="hidden" name="do" id="do" value="edit" />';
		    $renderer->doc .= '<input class="button" type="submit" value="'.((@$this->getLang('okbutton'))?$this->getLang('okbutton'):'ok').'" tabindex="3" />';
		    $renderer->doc .= '</form>';
		    $renderer->doc .= '</div>';

			return true;
		}
		return false;
	}
	/** 
	* Parse namespace request
	*
	* @author  Samuele Tognini <samuele@cli.di.unipi.it>
	*/
	function _parse_ns ($ns) {
		global $ID;
		$ns=preg_replace("/^\.(:|$)/",dirname(str_replace(':','/',$ID))."$1",$ns);
		$ns=str_replace("/",":",$ns);
		$ns = cleanID($ns);
		return $ns;
	}
	function _makecombo($data) {
		global $ID;
		
		$hide=$this->getConf('addpage_hide');
		
		if (($data != "") && ($hide)) 
			return '<input type="hidden" name="np_cat" id="np_cat" value="'. $this->_parse_ns($data) .'"/>';
			
		$ns=explode(':',$ID);
		array_pop($ns);
		$ns=implode(':',$ns);
 
		$r=$this->_getnslist("");
		


		$ret='<select class="edit" id="np_cat" name="np_cat"  tabindex="1">';

		$someopt=false;

		if ($this->getConf('addpage_showroot')) {

			$root_disabled=(auth_quickaclcheck($data.":") < AUTH_CREATE) ?true:false;

			
			if ($data=='') {
				if (!$root_disabled) {
					$ret.='<option '.(($ns=='')?'selected="true"':'').' value="">'.((@$this->getLang('namespaceRoot'))?$this->getLang('namespaceRoot'):'top').'</option>';
					$someopt=true;
					}
			} else {
				if (!$root_disabled) {
					$ret.='<option '.(($ns==$data)?'selected="true"':'').' value="'.$data.'">'.$data.'</option>';
					$someopt=true;
				}
				
			}
		}
		foreach ($r as $k => $v) {
			if ($data != '')
				if (strpos(":".$v,":".$data.":")===false) continue;
			
			if(auth_quickaclcheck($v.":") < AUTH_CREATE)continue;
			$vv=explode(':',$v);
			$vv=str_repeat('&nbsp;&nbsp;',substr_count($v, ':')).$vv[count($vv)-1];
			$ret.='<option '.(($ns==$v)?'selected="true"':'').' value="'.$v.'">'.$vv.'</option>';
			$someopt=true;
		}
		$ret.='</select>';
		if (!$someopt) $ret = $this->getLang('nooption');

		return $ret;
	}
	function _getnslist ($tns='') {
		require_once(DOKU_INC.'inc/search.php');
		global $conf;
 
		if ($tns=='')
			$tns = $conf['datadir'];
 
		if (!is_dir($tns))
			$tns  = str_replace(':','/',$tns);
		
		$data = array();
		
		$exclude=$this->getConf('addpage_exclude');
		
		if ($exclude=="")
			$exclude=array();
		else 
			$exclude=@explode(';',strtolower($exclude));
		
		search($data,$tns,'search_index',array('ns' => ''));
  
		$data2 = array();
		foreach($data as $k => $v) {
			if ($v['type']=='d') {
				if (!in_array(strtolower($v['id']),$exclude)) {
					array_push($data2,$v['id']);
					$r=$this->_getnslist($tns.'/'.$v['id']);
					foreach ($r as $vv) {
						if (!in_array(strtolower($vv),$exclude))
							array_push($data2,$v['id'].':'.$vv);
					}
				}
			}
		}
		return $data2;
	} 
}
?>
