<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Add-New-Page Plugin: a simple form for adding new pages.
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   iDO <ido@idotech.info>
 * @author   Sam Wilson <sam@samwilson.id.au>
 */
class action_plugin_addnewpage extends DokuWiki_Action_Plugin {

    /**
     * Register the events
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'addbutton', array());
    }

    /**
     * Add 'new page'-button to pagetools
     *
     * @param Doku_Event $event
     */
    public function addbutton(Doku_Event $event) {
        global $ID, $INFO;
        $can_create = $INFO['perm'];

        if($this->getConf('addpage_path') != '' && $can_create >= AUTH_CREATE && $event->data['view'] == 'main') {
            $params = array('id' => $this->getConf('addpage_path'));

            // insert button at position before last (up to top)
            $event->data['items'] = array_slice($event->data['items'], 0, -1, true) +
                array('addpage_path' =>
                          '<li>'
                          . '<a href="' . wl($ID, $params) . '"  class="action addnewpage" rel="nofollow" title="' . $this->getLang('okbutton') . '">'
                          . '<span>' . $this->getLang('okbutton') . '</span>'
                          . '</a>'
                          . '</li>'
                ) +
                array_slice($event->data['items'], -1, 1, true);
        }
    }
}
