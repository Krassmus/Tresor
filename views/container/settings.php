<form action="<?= PluginEngine::getLink($plugin, array(), "container/settings") ?>" method="post" class="default">

    <label>
        <?= _("Name des Reiters") ?>
        <input type="text" name="tabname" value="<?= htmlReady($setting['tabname']) ?>" placeholder="<?= _("Tresor") ?>">
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>