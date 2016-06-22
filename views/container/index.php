<?= $this->render_partial("container/_user_key.php") ?>

<table class="default">
    <head>
        <tr>
            <th><?= _("Name") ?></th>
            <th><?= _("Gruppe") ?></th>
            <th><?= _("Letzte Änderung") ?></th>
            <th><?= _("Letzter Editor") ?></th>
        </tr>
    </head>
    <tbody>
    <? if (count($coursecontainer)) : ?>
        <? foreach ($coursecontainer as $container) : ?>
            <tr>
                <td><?= htmlReady($container['name'])  ?></td>
                <td><?= _("keine") ?></td>
                <td><?= date("j.n.Y", $container['chdate']) ?></td>
                <td><?= htmlReady(get_fullname($container['last_user_id'])) ?></td>
            </tr>
        <? endforeach ?>
    <? else : ?>
        <tr>
            <td colspan="4"><?= _("Noch keine verschlüsselten Texte vorhanden.") ?></td>
        </tr>
    <? endif ?>
    </tbody>
</table>

<?

$actions = new ActionsWidget();
$actions->addLink(_("Text hinzufügen"), PluginEngine::getURL($plugin, array(), "container/create"), Icon::create("add", "info"), array('data-dialog' => 1));
Sidebar::Get()->addWidget($actions);