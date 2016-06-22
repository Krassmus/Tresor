<?= $this->render_partial("container/_user_key.php") ?>

<div id="encrypted_key" data-key="<?= htmlReady($userkey['encrypted_key']) ?>"></div>

<form action="<?= PluginEngine::getLink($plugin, array(), "container/store/".$container->getId()) ?>"
      method="post"
      class="default">

    <input type="hidden" name="encrypted_content" id="encrypted_content" value="<?= htmlReady($container['encrypted_content']) ?>">
    <textarea id="content" style="width: calc(100% - 20px); height: calc(100vh - 20px);" placeholder="<?= _("Text eingeben ...") ?>"></textarea>

    <div>
        <?= \Studip\LinkButton::create(_("Speichern"), "#", array('onClick' => "STUDIP.Tresor.storeContainer(); return false;")) ?>
    </div>
</form>

<div id="wheel" style="text-align: center;">
    <img src="<?= $plugin->getPluginURL() ?>/assets/settings.svg" width="50px" heigh="50px">
</div>

<style>
    #wheel {
        min-height: 100px;
    }
    #wheel img {
        opacity: 0.5;
        transition: opacity .25s ease-in-out;
        position: absolute;
        left: 50vw;
        -webkit-animation:spin 8s linear infinite;
        -moz-animation:spin 8s linear infinite;
        animation:spin 8s linear infinite;
    }
    #wheel img:hover {
        opacity: 0.25;
        transition: opacity .25s ease-in-out;
        -webkit-animation-play-state: paused;
        -moz-animation-play-state: paused;
        -o-animation-play-state: paused;
        animation-play-state: paused;
    }
    @-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
    @-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
    @keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
</style>