<?php

require_once 'app/controllers/plugin_controller.php';

class ContainerController extends PluginController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/tresor");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/Tresor.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/openpgp.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL()."/assets/Tresor.css");
        $setting = TresorSetting::find(Context::get()->id);
        $name = $setting && $setting['tabname'] ? $setting['tabname'] : Config::get()->TRESOR_GLOBALS_NAME;
        PageLayout::setTitle($name);
        Helpbar::Get()->addPlainText(Config::get()->TRESOR_GLOBALS_NAME, _("Der Tresor ist ein Bereich in Ihrer Veranstaltung, der besonders gesicherte Inhalte beinhalten kann. Sie brauchen deswegen auch ein zweites Passwort nur für den Tresor. Selbst die Admins von Stud.IP sind nicht in der Lage, die Inhalte des Tresors auszulesen. Das können nur Sie und die anderen Mitlesenden der Veranstaltung."));
        if (\Studip\ENV === "production" && $_SERVER['HTTPS'] !== 'on') {
            PageLayout::postError(sprintf(_("Diese Seite ist nicht mit HTTPS abgesichert. %s ist so nicht sicher."), Config::get()->TRESOR_GLOBALS_NAME));
            $this->donothing = true;
        }
    }

    public function index_action()
    {
        if ($GLOBALS['perm']->have_perm("admin")) {
            PageLayout::postMessage(MessageBox::info(_("Sie sind Admin und nicht Mitglied dieser Veranstaltung. Die vorliegenden Dokumente sind nicht für Sie verschlüsselt.")));
        }

        $this->foreign_user_public_keys = TresorUserKey::findForSeminar(Context::get()->id);
        $this->coursecontainer = TresorContainer::findBySQL("seminar_id = ? ORDER BY name", array(Context::get()->id));
    }

    public function details_action($tresor_id)
    {
        $this->container = new TresorContainer($tresor_id);
        if (!$GLOBALS['perm']->have_studip_perm("autor", $this->container['seminar_id'])) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle($this->container['name']);
        $this->foreign_user_public_keys = TresorUserKey::findForSeminar($this->container['seminar_id']);
    }

    public function edit_action($tresor_id)
    {
        $this->container = new TresorContainer($tresor_id);
        if (!$GLOBALS['perm']->have_studip_perm("autor", $this->container['seminar_id'])) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(sprintf(_("%s bearbeiten"), $this->container['name']));
        $this->foreign_user_public_keys = TresorUserKey::findForSeminar($this->container['seminar_id']);
    }

    public function store_action($tresor_id = null) {
        $this->container = new TresorContainer($tresor_id);
        if (($tresor_id && !$GLOBALS['perm']->have_studip_perm("autor", $this->container['seminar_id']))
                || (!$tresor_id && !$GLOBALS['perm']->have_studip_perm("autor", Context::get()->id))) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $this->container['name'] = Request::get("name");
            $this->container['mime_type'] = Request::get("mime_type", "text/plain");
            $this->container['encrypted_content'] = Request::get("encrypted_content");
            $this->container['last_user_id'] = User::findCurrent()->id;
            if ($this->container->isNew()) {
                $this->container['seminar_id'] = Context::get()->id;
            }
            $this->container['chdate'] = time();
            $this->container->store();
            PageLayout::postMessage(MessageBox::success(_("Daten wurden verschlüsselt und gespeichert.")));
            $this->redirect("container/index");
        }
    }

    public function create_action()
    {
        if (Request::isPost()) {
            $this->container = new TresorContainer();
            $this->container['seminar_id'] = Context::get()->id;
            $this->container['name'] = Request::get("name");
            $this->container['last_user_id'] = $GLOBALS['user']->id;
            $this->container['encrypted_content'] = "";
            $this->container->store();
            PageLayout::postSuccess(_("Neuen Text initialisiert"));
            $this->redirect("container/edit/".$this->container->getId());
        }
    }

    public function delete_action($tresor_id)
    {
        if (Request::isPost()) {
            $this->container = new TresorContainer($tresor_id);
            if (!$GLOBALS['perm']->have_studip_perm("tutor", $this->container['seminar_id']) && ($this->container['last_user_id'] !== $GLOBALS['user']->id)) {
                throw new AccessDeniedException();
            }
            $this->container->delete();
            PageLayout::postSuccess(_("Objekt wurde gelöscht."));
            $this->redirect("container/index");
        }
    }

    public function update_action($tresor_id)
    {
        $this->container = new TresorContainer($tresor_id);
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $this->container['seminar_id'])) {
            throw new AccessDeniedException();
        }
        $this->container['encrypted_content'] = Request::get("encrypted_content");
        $this->container['chdate'] = time();
        $this->container->store();
        $this->render_text("updated");
    }

    public function get_updatable_for_course_action($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm("autor", $course_id)) {
            throw new AccessDeniedException();
        }
        $my_key = TresorUserKey::findMine();
        if (!$my_key) {
            throw new Exception("Sie sind abgemeldet oder haben noch keinen eigenen Schlüssel.");
        }
        $data = [];
        $earliest_date = 0;
        foreach (TresorUserKey::findForSeminar(Context::get()->id) as $key) {
            $earliest_date = max($earliest_date, $key['chdate']);
        }
        foreach (TresorContainer::findBySQL("seminar_id = ? AND chdate <= ? ORDER BY name", array($course_id, $earliest_date)) as $container) {
            if ($my_key['chdate'] <= $container['chdate']) {
                $d = $container->toRawArray();
                $d['encrypted_content'] = $container->getEncryptedContent();
                $data[] = $d;
            }
        }
        $this->render_json($data);
    }

    public function settings_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        $this->setting = new TresorSetting(Context::get()->id);
        if (Request::isPost()) {
            $this->setting['tabname'] = Request::get("tabname");
            if (!$this->setting['tabname']) {
                $this->setting->delete();
            } else {
                $this->setting->store();
            }
            PageLayout::postMessage(MessageBox::success(_("Daten wurden gespeichert.")));
            $this->redirect("container/index");
        }
    }

    public function pdfviewer_action()
    {
        $tf = new Flexi_TemplateFactory(__DIR__."/../assets/pdfjs");
        $template = $tf->open("web/viewer.php");
        $template->base_url = $this->plugin->getPluginURL()."/assets/pdfjs";
        $this->render_text($template->render());
    }

}
