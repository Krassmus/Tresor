<?php

require_once __DIR__."/lib/TresorContainer.php";
require_once __DIR__."/lib/TresorUserKey.php";

class Tresor extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct()
    {
        parent::__construct();
        if (User::findCurrent()->id === "nobody") {
            die("k,hh");
            PageLayout::addHeadElement("script", array(), 'sessionStorage.setItem("STUDIP.Tresor.passphrase");');
        }
    }

    function getIconNavigation($course_id, $last_visit, $user_id) {
        $icon = new Navigation(_("Tresor"), PluginEngine::getURL($this, array(), "container/index"));
        $icon->setImage(Icon::create("lock-locked", "inactive"), array('title' => _("Tresor")));
        return $icon;
    }


    function getTabNavigation($course_id) {
        $tab = new Navigation(_("Tresor"), PluginEngine::getURL($this, array(), "container/index"));
        $tab->setImage(Icon::create("lock-locked", "info_alt")->asImagePath());
        $tab->setActiveImage(Icon::create("lock-locked", "info")->asImagePath());
        return array('tresor' => $tab);
    }

    function getNotificationObjects($course_id, $since, $user_id) {
        return null;
    }

    function getInfoTemplate($course_id) {
        return null;
    }

}