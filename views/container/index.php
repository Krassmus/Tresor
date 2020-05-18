<? if ($donothing) {
    return;
} ?>
<?= $this->render_partial("container/_user_key.php") ?>
<? $my_key = TresorUserKey::findMine() ?>

<table class="default sortable">
    <thead>
        <tr>
            <th style="max-width: 20px;"></th>
            <th><?= _("Name") ?></th>
            <th><?= _("Letzte Änderung") ?></th>
            <th><?= _("Letzter Bearbeiter") ?></th>
            <th class="actions"></th>
        </tr>
    </thead>
    <tbody>
    <? if (count($coursecontainer)) : ?>
        <? foreach ($coursecontainer as $container) : ?>
            <? $new = ($container['chdate'] > Request::int("highlight")) && ($container['last_user_id'] !== $GLOBALS['user']->id) ?>
            <tr<?= $new ? ' class="new"' : "" ?>>
                <td>
                    <? if ($container->needsReencryption()) {
                        echo Icon::create("exclaim-circle", "info")->asImg(20, ['class' => "text-bottom", 'title' => _("Dieses Objekt muss noch einmal verschlüsselt werden, damit alle Teilnehmer*innen der Veranstaltung es sehen können.")]);
                        if ($my_key['chdate'] <= $container['chdate']) {
                            $todo = true;
                        }
                    } ?>
                </td>
                <td>
                    <? if (!$GLOBALS['perm']->have_perm("admin") && $my_key) : ?>
                        <a href="<?= PluginEngine::getLink($plugin, array(), "container/details/".$container->getId()) ?>" data-dialog>
                            <?= FileManager::getIconForMimeType($container['mime_type'])->asImg("20px", array('class' => "text-bottom")) ?>
                    <? else : ?>
                        <?= FileManager::getIconForMimeType($container['mime_type'])->asImg("20px", array('class' => "text-bottom")) ?>
                    <? endif ?>
                    <?= htmlReady($container['name'])  ?>
                    <? if (!$GLOBALS['perm']->have_perm("admin") && $my_key) : ?>
                        </a>
                    <? endif ?>
                </td>
                <td><?= date("j.n.Y G:i", $container['chdate']) ?></td>
                <td><a href="<?= URLHelper::getLink("dispatch.php/profile", ['username' => get_username($container['last_user_id'])]) ?>"><?= htmlReady(get_fullname($container['last_user_id'])) ?></td>
                <td class="actions">
                    <? if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id) || $container['last_user_id'] === $GLOBALS['user']->id) : ?>
                        <? if (!$GLOBALS['perm']->have_perm("admin")) : ?>
                            <a href="<?= PluginEngine::getLink($plugin, array(), "container/edit/".$container->getId()) ?>" data-dialog title="<?= _("Objekt bearbeiten") ?>">
                                <?= Icon::create("edit", "clickable")->asImg(20, ['class' => "text-bottom"]) ?>
                            </a>
                        <? endif ?>
                        <form action="<?= PluginEngine::getLink($plugin, array(), "container/delete/".$container->getId()) ?>"
                              method="post"
                              class="tresor_delete">
                            <button title="<?= _("Objekt löschen") ?>"
                                onClick="return window.confirm('<?= _("Wirklich löschen?") ?>');">
                                <?= Icon::create("trash", "clickable")->asImg("20px") ?>
                            </button>
                        </form>
                    <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
    <? else : ?>
        <tr>
            <td colspan="4" style="text-align: center;"><?= _("Noch keine verschlüsselten Dokumente vorhanden.") ?></td>
        </tr>
    <? endif ?>
    </tbody>
</table>

<script>
    jQuery(function () {
        //jQuery("table.sortable").tablesorter();
        STUDIP.Tresor.keyToEncryptFor = <?= json_encode(array_map(
            function ($key) { return $key['public_key']; },
            $foreign_user_public_keys
        )) ?>;
    });
</script>


<form action="<?= PluginEngine::getLink($plugin, array(), "container/store") ?>"
      method="post"
      id="uploadform"
      style="display: none;">
    <input type="text" name="name" value="">
    <input type="hidden" name="encrypted_content" value="">
    <input type="hidden" name="mime_type" value="text/plain">
</form>

<? if (Config::get()->TRESOR_ACCEPT_FILETYPES !== "none") : ?>
    <input type="file"
           id="fileupload"
           onChange="STUDIP.Tresor.uploadFile(event);"
           <?= Config::get()->TRESOR_ACCEPT_FILETYPES ? 'accept="'.htmlReady(Config::get()->TRESOR_ACCEPT_FILETYPES).'"' : "" ?>
           style="display: none;">
<? endif ?>

<? if ($todo && $GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) : ?>
    <div id="dialog_wait_renew_containers"
         data-title="<?= _("Daten werden verschlüsselt ...") ?>"
         style="display: none;">
        <div class="uploadbar" style="margin-top: 20px;"></div>
    </div>
<? endif ?>

<?
$actions = new ActionsWidget();
if ($my_key) {
    if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
        $actions->addLink(
            _("Bereich konfigurieren"),
            PluginEngine::getURL($plugin, array(), "container/settings"),
            Icon::create("admin", "clickable"),
            array('data-dialog' => "1")
        );
    }
    $actions->addLink(
        _("Text hinzufügen"),
        PluginEngine::getURL($plugin, array(), "container/create"),
        Icon::create("add", "clickable"),
        array('data-dialog' => 1)
    );
    if (Config::get()->TRESOR_ACCEPT_FILETYPES !== "none") {
        $actions->addLink(
            _("Datei hochladen"),
            PluginEngine::getURL($plugin, array(), "container/create_file"),
            Icon::create("file+add", "clickable"),
            array('onclick' => "jQuery('#fileupload').trigger('click'); return false;")
        );
    }
    if ($todo && $GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
        $actions->addLink(
            _("Dateien aktualisieren für neue Schlüssel"),
            PluginEngine::getURL($plugin, array(), "container/update_encryption"),
            Icon::create("refresh", "clickable"),
            array('onclick' => "STUDIP.Tresor.updateEncryption(); return false;")
        );
    }
} elseif(!$GLOBALS['perm']->have_perm("admin")) {
    $actions->addLink(
        _("Persönlichen Schlüssel erstellen"),
        "#",
        Icon::create("key+add", "clickable"),
        array('onClick' => "STUDIP.Tresor.createUserKeys(); return false;")
    );
}
Sidebar::Get()->addWidget($actions);
