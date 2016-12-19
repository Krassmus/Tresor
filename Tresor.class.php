<?php

require_once __DIR__."/lib/TresorContainer.php";
require_once __DIR__."/lib/TresorUserKey.php";

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
        }
    }

    function getIconNavigation($course_id, $last_visit, $user_id) {
        $icon = new Navigation(_("Tresor"), PluginEngine::getURL($this, array(), "container/index"));
        $new_container = TresorContainer::countBySQL("seminar_id = :course_id AND chdate > :last_visit AND last_user_id != :user_id", array(
            'course_id' => $course_id,
            'last_visit' => $last_visit,
            'user_id' => $GLOBALS['user']->id
        ));
        if ($new_container > 0) {
            $icon->setURL(PluginEngine::getURL($this, array('highlight' => $last_visit), "container/index"));
            $icon->setImage(Icon::create("lock-locked", "new"), array('title' => sprintf(_("Tresor - %s Änderungen"), $new_container)));
        } else {
            $icon->setImage(Icon::create("lock-locked", "inactive"), array('title' => _("Tresor")));
        }
        return $icon;
    }


    function getTabNavigation($course_id) {
        $tab = new Navigation(_("Tresor"), PluginEngine::getURL($this, array(), "container/index"));
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