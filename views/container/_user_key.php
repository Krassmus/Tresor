<? $my_key = TresorUserKey::findMine() ?>
<div id="my_key"
     data-private_key="<?= htmlReady($my_key['synchronously_encrypted_private_key']) ?>"
     data-public_key="<?= htmlReady($my_key['public_key']) ?>"></div>
<? if (!$my_key) : ?>
    <?= MessageBox::info(sprintf(_("Sie haben noch keinen Schlüssel. %sJetzt erstellen.%s"), '<a href="" onClick="STUDIP.Tresor.createUserKeys(); return false;">', '</a>')) ?>

    <form id="set_password" style="display: none; padding-top: 20px; padding-bottom: 20px;" class="default">

        <div id="wheel">
            <img src="<?= $plugin->getPluginURL() ?>/assets/settings.svg" width="40px" heigh="40px">
        </div>

        <label>
            <?= _("Passwort für Ihren Tresorschlüssel (nicht Stud.IP-Passwort)") ?>
            <input type="password" name="password">
        </label>
        <label>
            <?= _("Passwort wiederholen") ?>
            <input type="password" name="password_2">
        </label>

        <input type="hidden" name="user" value="<?= htmlReady(get_fullname()) ?>">
        <input type="hidden" name="mail" value="<?= htmlReady(User::findCurrent()->email) ?>">

        <?= \Studip\LinkButton::create(_("Passwort setzen"), "#", array('onClick' => "STUDIP.Tresor.setPassword(); return false;")) ?>
    </form>
<? endif ?>
<div style="display: none;" id="question_passphrase_title"><?= _("Passwort zum Entschlüsseln eingeben") ?></div>
<div style="display: none;" id="question_passphrase">
    <form class="default">
        <label>
            <?= _("Passwort zum Entschlüsseln") ?>
            <input type="password" name="passphrase">
        </label>

        <?= \Studip\LinkButton::create(_("Entschlüsseln"), "#", array('onclick' => "STUDIP.Tresor.extractPrivateKey(); return false;")) ?>
    </form>
</div>
