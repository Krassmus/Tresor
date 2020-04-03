<? if ($donothing) {
    return;
} ?>
<div>
    <?= nl2br(htmlReady(TresorUserKey::find($GLOBALS['user']->id)->public_key)) ?>
</div>

<?
$actions = new ActionsWidget();
$actions->addLink(
    _("Passwort vergessen?"),
    PluginEngine::getURL($plugin, array(), "userdata/create_new_key"),
    Icon::create("key+decline", "info"),
    array('data-dialog' => 1)
);
Sidebar::Get()->addWidget($actions);
