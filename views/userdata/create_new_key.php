<? if ($donothing) {
    return;
} ?>
<form action="<?= PluginEngine::getLink($plugin, array(), "userdata/create_new_key") ?>"
      method="post"
      class="default"
      onSubmit="STUDIP.Tresor.setPassword(); return false;">

    <?= MessageBox::info(dgettext("tresor","Wenn Sie einen neuen Schlüssel erzeugen, müssen alle schon verschlüsselten Texte noch einmal für Sie verschlüsselt werden. damit Sie diese lesen können.")) ?>

    <div id="wheel">
        <img src="<?= $plugin->getPluginURL() ?>/assets/settings.svg" width="40px" heigh="40px">
    </div>

    <label>
        <?= dgettext("tresor","Passwort für Ihren neuen Schlüssel (nicht Stud.IP-Passwort)") ?>
        <input type="password" id="tresor_password" minlength="10">
    </label>
    <label>
        <?= dgettext("tresor","Passwort wiederholen") ?>
        <input type="password" id="tresor_password_2">
    </label>

    <div>
        <strong><?= dgettext("tresor","Zur Erinnerung") ?>:</strong>
        <?= dgettext("tresor","Sichere Passwörter sind in erster Regel sehr lang. Benutzen Sie auf keinen Fall Ihr Stud.IP-Passwort!") ?>
    </div>

    <input type="hidden" name="user" value="<?= htmlReady(get_fullname()) ?>">
    <input type="hidden" name="mail" value="<?= htmlReady(User::findCurrent()->email) ?>">

    <input type="hidden" name="synchronously_encrypted_private_key">
    <input type="hidden" name="public_key">

    <div style="display: none;"><input type="submit"></div>

    <div data-dialog-button>
        <?= \Studip\LinkButton::create(dgettext("tresor","Passwort setzen"), "#", array('onClick' => "STUDIP.Tresor.setPassword(); return false;")) ?>
    </div>

</form>
