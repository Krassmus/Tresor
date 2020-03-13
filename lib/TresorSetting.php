<?php

class TresorSetting extends SimpleORMap
{

    protected static function configure($config = array())
    {
        $config['db_table'] = 'tresor_settings';
        parent::configure($config);
    }

}