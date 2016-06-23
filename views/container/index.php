<?= $this->render_partial("container/_user_key.php") ?>
<? $my_key = TresorUserKey::findMine() ?>

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
                <td>
                    <? if (!$GLOBALS['perm']->have_perm("admin") && $my_key) : ?>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "container/details/".$container->getId()) ?>">
                        <?= Icon::create("lock-unlocked", "clickable")->asImg("20px", array('class' => "text-bottom")) ?>
                    <? else : ?>
                        <?= Icon::create("lock-locked", "info")->asImg("20px", array('class' => "text-bottom")) ?>
                    <? endif ?>
                        <?= htmlReady($container['name'])  ?>
                    <? if (!$GLOBALS['perm']->have_perm("admin") && $my_key) : ?>
                    </a>
                    <? endif ?>
                </td>
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
if ($my_key) {
    $actions->addLink(_("Text hinzufügen"), PluginEngine::getURL($plugin, array(), "container/create"), Icon::create("add", "info"), array('data-dialog' => 1));
} else {
    $actions->addLink(_("Persönlichen Schlüssel erstellen"), "#", Icon::create("key+add", "info"), array('onClick' => "STUDIP.Tresor.createUserKeys(); return false;"));
}
Sidebar::Get()->addWidget($actions);