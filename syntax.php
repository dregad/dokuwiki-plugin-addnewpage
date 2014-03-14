<?php

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

/**
 * Add-New-Page Plugin: a simple form for adding new pages.
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   iDO <ido@idotech.info>
 * @author   Sam Wilson <sam@samwilson.id.au>
 */
class syntax_plugin_addnewpage extends DokuWiki_Syntax_Plugin {

    /**
     * Syntax Type
     */
    function getType() { return 'substition'; }

    /**
     * Paragraph Type
     */
    function getPType() { return 'block'; }

    /**
     * @return int
     */
    function getSort() { return 199; }

    /**
     * @param string $mode
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{NEWPAGE[^\}]*\}\}', $mode, 'plugin_addnewpage');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $ns = substr($match, 10, -2); // strip markup
        return array($ns);
    }

    /**
     * Create the new-page form.
     *
     * @param   $mode   string        output format being rendered
     * @param   $renderer Doku_Renderer the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        global $lang;
        $renderer->info['cache'] = false;
        $data = $data[0]; // get data back from the array

        if ($mode == 'xhtml') {
            $ns_select = $this->_makecombo($data);
            if ($ns_select == $this->getLang('nooption')) {
                $renderer->doc .= (!$this->getConf('addpage_hideACL')) ? $ns_select : '';
                return true;
            }

            $button_val = ((@$this->getLang('okbutton')) ? $this->getLang('okbutton') : 'ok');
            $form = '<div class="addnewpage">'.DOKU_LF
                .DOKU_TAB.'<form name="addnewpage" method="get" action="'.DOKU_BASE.DOKU_SCRIPT.'" accept-charset="'.$lang['encoding'].'">'.DOKU_LF
                .DOKU_TAB.DOKU_TAB.$ns_select.DOKU_LF
                .DOKU_TAB.DOKU_TAB.'<input class="edit" type="text" name="title" size="20" maxlength="255" tabindex="2" />'.DOKU_LF
                .DOKU_TAB.DOKU_TAB.'<input type="hidden" name="do" value="edit" />'.DOKU_LF
                .DOKU_TAB.DOKU_TAB.'<input type="hidden" name="id" />'.DOKU_LF
                .DOKU_TAB.DOKU_TAB.'<input class="button" type="submit" value="'.$button_val.'" tabindex="3" />'.DOKU_LF
                .DOKU_TAB.'</form>'.DOKU_LF
                .'</div>';
            $renderer->doc .= $form;

            return true;
        }
        return false;
    }

    /**
     * Parse namespace request
     *
     * @author Samuele Tognini <samuele@cli.di.unipi.it>
     * @author Michael Braun <michael-dev@fami-braun.de>
     */
    function _parse_ns($ns) {
        global $ID;
        if ($ns == "@PAGE@") return $ID;
        if ($ns == "@NS@") return getNS($ID);
        $ns = preg_replace("/^\.(:|$)/", dirname(str_replace(':', '/', $ID)) . "$1", $ns);
        $ns = str_replace("/", ":", $ns);
        $ns = cleanID($ns);
        return $ns;
    }

    /**
     * Create the HTML Select element for namespace selection.
     * 
     * @global string $ID The page ID
     * @param string|false $dest_ns The destination namespace, or false if none provided.
     * @return string Select element with appropriate NS selected.
     */
    function _makecombo($dest_ns) {
        global $ID;

        // If a NS has been provided:
        // Whether to hide the NS selection (otherwise, show only subnamespaces).
        $hide = $this->getConf('addpage_hide');

        // Whether the user can create pages in the provided NS (or root, if no
        // destination NS has been set.
        $can_create = (auth_quickaclcheck($dest_ns.":") >= AUTH_CREATE);

        if (!empty($dest_ns) && $hide) {
            if ($can_create) {
                return '<input type="hidden" name="np_cat" id="np_cat" value="'.$this->_parse_ns($dest_ns).'"/>';
            } else {
                return $this->getLang('nooption');
            }
        }

        $ns = explode(':', $ID);
        array_pop($ns);
        $ns = implode(':', $ns);

        $subnamespaces = $this->_getnslist("");
        $ret = '<select class="edit" id="np_cat" name="np_cat" tabindex="1">';

        // Whether the NS select element has any options
        $someopt=false;

        // Show root namespace if requested and allowed
        if ($this->getConf('addpage_showroot') && $can_create) {
            if (empty($dest_ns)) {
                // If no namespace has been provided, add an option for the root NS.
                $option_text = ((@$this->getLang('namespaceRoot'))?$this->getLang('namespaceRoot'):'top');
                $ret.='<option '.(($ns=='')?'selected ':'').'value="">'.$option_text.'</option>';
                $someopt=true;
            } else {
                // If a namespace has been provided, add an option for it.
                $ret.='<option '.(($ns==$dest_ns)?'selected ':'').'value="'.$dest_ns.'">'.$dest_ns.'</option>';
                $someopt=true;
            }
        }

        foreach ($subnamespaces as $v) {
            if ($dest_ns != '') {
                if (strpos(":" . $v, ":" . $dest_ns . ":") === false) {
                    continue;
                }
            }
            if (auth_quickaclcheck($v . ":") < AUTH_CREATE) continue;
            $vv = explode(':', $v);
            $vv = str_repeat('&nbsp;&nbsp;', substr_count($v, ':')) . $vv[count($vv) - 1];
            $ret.='<option '.(($ns == $v) ? 'selected ' : '').'value="'.$v.'">'.$vv.'</option>';
            $someopt = true;
        }
        $ret.='</select>';
        if (!$someopt) $ret = $this->getLang('nooption');

        return $ret;
    }

    /**
     * Get a list of namespaces below the given namespace.
     * Recursively fetches subnamespaces.
     * 
     * @param string $tns The top namespace
     * @return array Multi-dimensional array of all namespaces below $tns
     */
    function _getnslist($tns = '') {
        global $conf;

        if ($tns == '') $tns = $conf['datadir'];
        if (!is_dir($tns)) $tns = utf8_encodeFN(str_replace(':', '/', $tns));
        $data = array();
        $exclude = $this->getConf('addpage_exclude');

        if ($exclude == "") $exclude = array();
        else $exclude = @explode(';', strtolower($exclude));

        search($data, $tns, 'search_index', array('ns' => ''));

        $data2 = array();
        foreach ($data as $v) {
            if ($v['type'] == 'd') {
                if (!in_array(strtolower($v['id']), $exclude)) {
                    array_push($data2, $v['id']);
                    $r = $this->_getnslist($tns . '/' . $v['id']);
                    foreach ($r as $vv) {
                        if (!in_array(strtolower($vv), $exclude)) {
                            array_push($data2, $v['id'] . ':' . $vv);
                        }
                    }
                }
            }
        }
        return $data2;
    }

}
