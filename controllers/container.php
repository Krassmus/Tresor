<?php

require_once 'app/controllers/plugin_controller.php';

class ContainerController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/tresor");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/Tresor.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/openpgp.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL()."/assets/Tresor.css");
    }

    public function index_action() {
        $this->coursecontainer = TresorContainer::findBySQL("seminar_id = ? ORDER BY name", array($_SESSION['SessionSeminar']));
    }

    public function details_action($tresor_id) {
        $this->container = new TresorContainer($tresor_id);
        if (!$GLOBALS['perm']->have_studip_perm("autor", $this->container['seminar_id'])) {
            throw new AccessDeniedException();
        }
        $this->userkey = TresorGroupKey::findOneBySQL("user_id = ? AND seminar_id = ?", array(
            $GLOBALS['user']->id,
            $this->container['seminar_id']
        ));
        $this->foreign_user_public_keys = TresorUserKey::findForSeminar($this->container['seminar_id']);
    }

    public function store_action($tresor_id) {
        $this->container = new TresorContainer($tresor_id);
        if (!$GLOBALS['perm']->have_studip_perm("autor", $this->container['seminar_id'])) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $this->container['encrypted_content'] = Request::get("encrypted_content");
            $this->container['last_user_id'] = User::findCurrent()->id;
            $this->container->store();
            PageLayout::postMessage(MessageBox::success(_("Daten wurden verschlüsselt und gespeichert.")));
            $this->redirect("container/details/".$tresor_id);
        }
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