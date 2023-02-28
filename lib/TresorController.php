<?php
require_once 'app/controllers/plugin_controller.php';

abstract class TresorController extends PluginController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::addScript($this->plugin->getPluginURL()."/assets/Tresor.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/openpgp.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL()."/assets/Tresor.css");

        $this->donothing = false;
        if (\Studip\ENV === "production" && $_SERVER['HTTPS'] !== 'on') {
            PageLayout::postError(sprintf(_("Diese Seite ist nicht mit HTTPS abgesichert. %s ist so nicht sicher."), Config::get()->TRESOR_GLOBALS_NAME));
            $this->donothing = true;
        }
    }
}
