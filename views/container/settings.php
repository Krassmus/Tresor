<? if ($donothing) {
    return;
} ?>
<form action="<?= PluginEngine::getLink($plugin, array(), "container/settings") ?>" method="post" class="default">

    <label>
        <?= dgettext("tresor","Name des Reiters") ?>
        <input type="text" name="tabname" value="<?= htmlReady($setting['tabname']) ?>" placeholder="<?= htmlReady(Config::get()->TRESOR_GLOBALS_NAME) ?>">
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("tresor","Speichern")) ?>
    </div>
</form>
