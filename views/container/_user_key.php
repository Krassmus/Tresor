<? $my_key = TresorUserKey::findMine() ?>
<div id="my_key" data-private_key="<?= htmlReady($my_key['synchronously_encrypted_private_key']) ?>"></div>
<? if (!$my_key) : ?>
    <?= MessageBox::info(sprintf(_("Sie haben noch keinen Schlüssel. %sJetzt erstellen.%s"), '<a href="" onClick="STUDIP.Tresor.createUserKeys(); return false;">', '</a>')) ?>
<? endif ?>
<form id="set_password" style="display: none; padding-top: 20px; padding-bottom: 20px;" class="default">
    <label>
        <?= _("Passwort für Ihren Tresorschlüssel (nicht Stud.IP-Passwort)") ?>
        <input type="password" name="password">
    </label>
    <label>
        <?= _("Passwort wiederholen") ?>
        <input type="password" name="password_2">
    </label>
    <?= \Studip\LinkButton::create(_("Passwort setzen"), "#", array('onClick' => "STUDIP.Tresor.setPassword(); return false;")) ?>
</form>