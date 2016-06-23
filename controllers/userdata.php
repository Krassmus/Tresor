<?php

class UserdataController extends PluginController {

    public function set_keys_action()
    {
        if (Request::isPost()) {
            $userkey = new TresorUserKey(User::findCurrent()->id);
            $userkey['synchronously_encrypted_private_key'] = preg_replace("/\r/", "", Request::get("private_key"));
            $userkey['public_key'] = preg_replace("/\r/", "", Request::get("public_key"));
            $userkey->store();
            PageLayout::postMessage(MessageBox::success(_("Schlüssel erfolgreich erstellt. Vergessen Sie Ihr Passwort nicht!")));
        }
        $this->render_text(MessageBox::success(_("Schlüssel erfolgreich erstellt. Vergessen Sie Ihr Passwort nicht!")));
    }
}