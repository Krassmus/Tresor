<?php

class TresorContainer extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'tresor_container';
        parent::configure($config);
    }

}