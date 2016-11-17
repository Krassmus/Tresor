<?= $this->render_partial("container/_user_key.php") ?>
<? $my_key = TresorUserKey::findMine() ?>

<table class="default sortable">
    <head>
        <tr>
            <th><?= _("Name") ?></th>
            <th><?= _("Letzte Änderung") ?></th>
            <th><?= _("Letzter Editor") ?></th>
            <th></th>
        </tr>
    </head>
    <tbody>
    <? if (count($coursecontainer)) : ?>
        <? foreach ($coursecontainer as $container) : ?>
            <? $new = ($container['chdate'] > Request::int("highlight")) && ($container['last_user_id'] !== $GLOBALS['user']->id) ?>
            <tr<?= $new ? ' class="new"' : "" ?>>
                <td>
                    <? if ($new) : ?>
                        <?= Icon::create("star", "new")->asImg("20px", array('class' => "text-bottom", 'title' => _("Neu oder verändert."))) ?>
                    <? endif ?>
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
                <td><?= date("j.n.Y", $container['chdate']) ?></td>
                <td><?= htmlReady(get_fullname($container['last_user_id'])) ?></td>
                <td>
                    <? if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) : ?>
                        <form action="<?= PluginEngine::getLink($plugin, array(), "container/delete/".$container->getId()) ?>" method="post">
                            <button style="border: none; background: none; cursor: pointer;" onClick="return window.confirm('<?= _("Wirklich löschen?") ?>');">
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
        jQuery("table.sortable").tablesorter();
    });
</script>

<?

$actions = new ActionsWidget();
if ($my_key) {
    $actions->addLink(
        _("Dokument hinzufügen"),
        PluginEngine::getURL($plugin, array(), "container/create"),
        Icon::create("add", "info"),
        array('data-dialog' => 1)
    );
} else {
    $actions->addLink(
        _("Persönlichen Schlüssel erstellen"),
        "#",
        Icon::create("key+add", "info"),
        array('onClick' => "STUDIP.Tresor.createUserKeys(); return false;")
    );
}
Sidebar::Get()->addWidget($actions);