<?php

class TresorUserKey extends SimpleORMap {

    public static function findMine()
    {
        return self::findOneBySQL("user_id = ?", array($GLOBALS['user']->id));
    }

    public static function findForSeminar($seminar_id) {
        $statement = DBManager::get()->prepare("
            SELECT tresor_user_keys.*
            FROM tresor_user_keys
                INNER JOIN seminar_user USING (user_id)
            WHERE seminar_user.Seminar_id = :seminar_id
        ");
        $statement->execute(array('seminar_id' => $seminar_id));
        $keys = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $keys[] = TresorUserKey::buildExisting($data);
        }
        return $keys;
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'tresor_user_keys';
        parent::configure($config);
    }

}
