<form action="<?= PluginEngine::getLink($plugin, array(), "container/create") ?>" method="post" class="default">
    <label>
        <?= _("Name des Textes") ?>
        <input type="text" name="name" placeholder="Name ...">
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(_("Erstellen")) ?>
    </div>
</form>