<?php

class TresorUserKey extends SimpleORMap {

    public static function findMine()
    {
        return self::findOneBySQL("user_id = ?", array($GLOBALS['user']->id));
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'tresor_user_keys';
        parent::configure($config);
    }

}