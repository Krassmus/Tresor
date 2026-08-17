<? if ($donothing) {
    return;
} ?>
<form action="<?= PluginEngine::getLink($plugin, array(), "container/create") ?>" method="post" class="default">
    <label>
        <?= dgettext("tresor","Name des Textes") ?>
        <input type="text" name="name" placeholder="Name ...">
    </label>
    <div data-dialog-button>
        <?= \Studip\Button::create(dgettext("tresor","Erstellen")) ?>
    </div>
</form>
