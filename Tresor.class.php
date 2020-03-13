<?php

require_once __DIR__."/lib/TresorContainer.php";
require_once __DIR__."/lib/TresorUserKey.php";
require_once __DIR__."/lib/TresorSetting.php";

class Tresor extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct()
    {
        parent::__construct();
        if ($GLOBALS['user']->id === "nobody") {
            PageLayout::addHeadElement("script", array(), 'sessionStorage.setItem("STUDIP.Tresor.passphrase", "");');
        }
        if ($GLOBALS['perm']->have_perm("autor") && !$GLOBALS['perm']->have_perm("admin")) {
            $navigation = new Navigation(_("Tresor"), PluginEngine::getURL($this, array(), "userdata/settings"));
            if (Navigation::hasItem("/profile/settings")) {
                Navigation::addItem("/profile/settings/tresor", $navigation);
            }
            if ((stripos($_SERVER['REQUEST_URI'], "dispatch.php/start") !== false) || (stripos($_SERVER['REQUEST_URI'], "dispatch.php/my_courses") !== false)
                    && !TresorUserKey::findMine()) {
                $statement = DBManager::get()->prepare("
                    SELECT 1
                    FROM tresor_container
                        INNER JOIN seminar_user ON (seminar_user.Seminar_id = tresor_container.seminar_id)
                    WHERE seminar_user.user_id = ?
                ");
                $statement->execute([$GLOBALS['user']->id]);
                $exist = $statement->fetch(PDO::FETCH_COLUMN);
                if ($exist) {
                    $tf = new Flexi_TemplateFactory(__DIR__."/views");
                    $template = $tf->open("container/_user_key.php");
                    $template->plugin = $this;
                    PageLayout::addBodyElements($template->render());
                    PageLayout::addScript($this->getPluginURL()."/assets/Tresor.js");
                    PageLayout::addScript($this->getPluginURL()."/assets/openpgp.js");
                    PageLayout::addStylesheet($this->getPluginURL()."/assets/Tresor.css");
                }
            }
        }
    }

    function getIconNavigation($course_id, $last_visit, $user_id) {
        $setting = TresorSetting::find($course_id);
        $name = $setting && $setting['tabname'] ? $setting['tabname'] : _("Tresor");
        $icon = new Navigation($name, PluginEngine::getURL($this, array(), "container/index"));
        $new_container = TresorContainer::countBySQL("seminar_id = :course_id AND chdate > :last_visit AND last_user_id != :user_id", array(
            'course_id' => $course_id,
            'last_visit' => $last_visit,
            'user_id' => $GLOBALS['user']->id
        ));
        if ($new_container > 0) {
            $icon->setURL(PluginEngine::getURL($this, array('highlight' => $last_visit), "container/index"));
            $icon->setImage(Icon::create("lock-locked", "new"), array('title' => $name." - ".sprintf(_("%s Änderungen"), $new_container)));
        } else {
            $icon->setImage(Icon::create("lock-locked", "inactive"), array('title' => $name));
        }
        return $icon;
    }


    function getTabNavigation($course_id) {
        $setting = TresorSetting::find($course_id);
        $name = $setting && $setting['tabname'] ? $setting['tabname'] : _("Tresor");
        $tab = new Navigation($name, PluginEngine::getURL($this, array(), "container/index"));
        $tab->setImage(Icon::create("lock-locked", "info_alt"));
        $tab->setActiveImage(Icon::create("lock-locked", "info"));
        return array('tresor' => $tab);
    }

    function getNotificationObjects($course_id, $since, $user_id) {
        return null;
    }

    function getInfoTemplate($course_id) {
        return null;
    }

}