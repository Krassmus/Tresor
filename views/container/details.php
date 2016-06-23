<?= $this->render_partial("container/_user_key.php") ?>

<div id="encrypted_key" data-key="<?= htmlReady($userkey['encrypted_key']) ?>"></div>

<form action="<?= PluginEngine::getLink($plugin, array(), "container/store/".$container->getId()) ?>"
      method="post"
      class="default">

    <input type="hidden" name="encrypted_content" id="encrypted_content" value="<?= htmlReady($container['encrypted_content']) ?>">
    <textarea id="content"
              style="width: calc(100% - 20px); height: calc(100vh - 90px);"
              placeholder="<?= $container['encrypted_content'] ? _("Es wird entschlüsselt ...") : _("Text eingeben ...") ?>"></textarea>

    <script>
        STUDIP.Tresor.keyToEncryptFor = <?= json_encode(studip_utf8encode(array_map(
            function ($key) { return $key['public_key']; },
            $foreign_user_public_keys
        ))) ?>;
        jQuery(STUDIP.Tresor.decryptContainer);
    </script>

    <div>
        <?= \Studip\LinkButton::create(_("Speichern"), "#", array('onClick' => "STUDIP.Tresor.storeContainer(); return false;")) ?>
    </div>
</form>

