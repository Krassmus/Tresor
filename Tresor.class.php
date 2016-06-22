<?php

require_once __DIR__."/lib/TresorContainer.php";
require_once __DIR__."/lib/TresorUserKey.php";
require_once __DIR__."/lib/TresorGroupKey.php";

class Tresor extends StudIPPlugin implements StandardPlugin {

    function getIconNavigation($course_id, $last_visit, $user_id) {
        return null;
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