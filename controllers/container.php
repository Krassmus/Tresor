<?php

require_once 'app/controllers/plugin_controller.php';

class ContainerController extends PluginController {

    public function index_action() {
        Navigation::activateItem("/course/tresor");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/Tresor.js");
        $this->coursecontainer = TresorContainer::findBySQL("seminar_id = ? ORDER BY name", array($_SESSION['SessionSeminar']));
    }

    public function details_action($tresor_id) {
        Navigation::activateItem("/course/tresor");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/Tresor.js");
        $this->container = new TresorContainer($tresor_id);
        if (!$GLOBALS['perm']->have_studip_perm("autor", $this->container['seminar_id'])) {
            throw new AccessDeniedException();
        }
        $this->userkey = TresorGroupKey::findOneBySQL("user_id = ? AND seminar_id = ?", array(
            $GLOBALS['user']->id,
            $this->container['seminar_id']
        ));
    }

    public function create_action()
    {
        if (Request::isPost()) {
            $this->container = new TresorContainer();
            $this->container['seminar_id'] = $_SESSION['SessionSeminar'];
            $this->container['name'] = Request::get("name");
            $this->container['last_user_id'] = $GLOBALS['user']->id;
            $this->container['encrypted_content'] = "";
            $this->container->store();
            PageLayout::postSuccess(_("Neuen Text initialisiert"));
            $this->redirect("container/details/".$this->container->getId());
        }
    }

}