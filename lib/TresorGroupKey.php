<?php

class TresorGroupKey extends SimpleORMap {

    protected static function configure($config = array())
    {
        $config['db_table'] = 'tresor_group_keys';
        parent::configure($config);
    }

}