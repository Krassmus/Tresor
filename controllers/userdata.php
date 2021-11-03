<?php

class UserdataController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/Tresor.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/openpgp.js");
        PageLayout::addScript("jquery/jquery.tablesorter-2.22.5.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL()."/assets/Tresor.css");
        if (\Studip\ENV === "production" && $_SERVER['HTTPS'] !== 'on') {
            PageLayout::postError(sprintf(dgettext("tresor","Diese Seite ist nicht mit HTTPS abgesichert. %s ist so nicht sicher."), Config::get()->TRESOR_GLOBALS_NAME));
            $this->donothing = true;
        }
    }

    public function set_keys_action()
    {
        if (Request::isPost()) {
            $userkey = new TresorUserKey(User::findCurrent()->id);
            $userkey['synchronously_encrypted_private_key'] = preg_replace("/\r/", "", Request::get("private_key"));
            $userkey['public_key'] = preg_replace("/\r/", "", Request::get("public_key"));
            $userkey->store();
            PageLayout::postMessage(MessageBox::success(dgettext("tresor","Schlüssel erfolgreich erstellt. Vergessen Sie Ihr Passwort nicht!")));
        }
        $this->render_text(MessageBox::success(dgettext("tresor","Schlüssel erfolgreich erstellt. Vergessen Sie Ihr Passwort nicht!")));
    }

    public function settings_action() {
        Navigation::activateItem("/profile/settings/tresor");
    }

    public function create_new_key_action() {

    }

    public function set_save_password_action()
    {
        if (Request::isPost()) {
            $GLOBALS['user']->cfg->store('TRESOR_SAVE_PASSWORD', Request::get("save_password"));
        }
        $this->render_nothing();
    }
}
