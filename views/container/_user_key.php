<? if ($donothing) {
    return;
} ?>
<? $my_key = TresorUserKey::findMine() ?>
<div id="my_key"
     data-private_key="<?= htmlReady($my_key['synchronously_encrypted_private_key']) ?>"
     data-public_key="<?= htmlReady($my_key['public_key']) ?>"></div>
<? if (!$my_key && !$GLOBALS['perm']->have_perm("admin")) : ?>
    <? PageLayout::postMessage(MessageBox::info(sprintf(dgettext("tresor","Sie haben noch keinen Schlüssel für %s. %sJetzt erstellen.%s"), Config::get()->TRESOR_GLOBALS_NAME, '<a href="" onClick="STUDIP.Tresor.createUserKeys(); return false;">', '</a>'))) ?>

    <div id="set_password_title" style="display: none;"><?= dgettext("tresor","Wählen Sie ein sicheres Passwort aus") ?></div>
    <div id="set_password" style="display: none;">
        <form class="default" action="?" method="post" onSubmit="STUDIP.Tresor.setPassword(); return false;">
            <div id="wheel">
                <img src="<?= $plugin->getPluginURL() ?>/assets/settings.svg" width="40px" heigh="40px">
            </div>

            <label>
                <?= sprintf(dgettext("tresor","Passwort für Ihren Schlüssel für %s (nicht Stud.IP-Passwort)"), Config::get()->TRESOR_GLOBALS_NAME) ?>
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

            <div style="text-align: center;">
                <?= \Studip\LinkButton::create(dgettext("tresor","Passwort setzen"), "#", array('onClick' => "STUDIP.Tresor.setPassword(); return false;")) ?>
            </div>
        </form>
    </div>
<? endif ?>
<div style="display: none;" id="question_passphrase_title"><?= dgettext("tresor","Passwort zum Entschlüsseln eingeben") ?></div>
<div style="display: none;" id="question_passphrase">
    <form class="default" onSubmit="STUDIP.Tresor.extractPrivateKey(); return false;" action="?" method="post">
        <div class="wrong"><?= MessageBox::error(dgettext("tresor","Falsches Passwort. Einfach nochmal probieren.")) ?></div>
        <label>
            <?= dgettext("tresor","Passwort zum Entschlüsseln") ?>
            <input type="password" name="passphrase" autocomplete="off">
        </label>

        <label>
            <?= dgettext("tresor","Passwort speichern ...") ?>
            <select name="save_password">
                <option value="save"<?= $GLOBALS['user']->cfg->TRESOR_SAVE_PASSWORD === "save" ? " selected": "" ?>>
                    <?= dgettext("tresor","bis zum Ausloggen") ?>
                </option>
                <option value="thispage"<?= $GLOBALS['user']->cfg->TRESOR_SAVE_PASSWORD === "thispage" ? " selected": "" ?>>
                    <?= dgettext("tresor","nur für diese Seite") ?>
                </option>
                <option value="never"<?= $GLOBALS['user']->cfg->TRESOR_SAVE_PASSWORD === "never" ? " selected": "" ?>>
                    <?= dgettext("tresor","gar nicht.") ?>
                </option>
            </select>
        </label>

        <div data-dialog-button>
            <?= \Studip\LinkButton::create(dgettext("tresor","Entschlüsseln"), "#", array('onclick' => "STUDIP.Tresor.extractPrivateKey(); return false;")) ?>
        </div>
    </form>
</div>
