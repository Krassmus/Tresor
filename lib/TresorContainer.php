<?php

class TresorContainer extends SimpleORMap {

    static public function getDataPath()
    {
        return $GLOBALS['STUDIP_BASE_PATH'] . "/data/tresor";
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'tresor_container';
        $config['additional_fields']['encrypted_content'] = array(
            'get' => "getEncryptedContent",
            'set' => "setEncryptedContent"
        );
        $config['registered_callbacks']['after_delete'][] = 'deleteDataFile';
        parent::configure($config);
    }

    public function getEncryptedContent() {
        return (string) @file_get_contents($this->getFilePath());
    }

    public function setEncryptedContent($field, $value) {
        return file_put_contents($this->getFilePath(), $value);
    }

    public function getFilePath() {
        if (!file_exists(self::getDataPath())) {
            mkdir(self::getDataPath());
        }
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getDataPath()."/".$this->getId();
    }

    public function deloreanGetFilePath()
    {
        return $this->getFilePath();
    }

    public function deleteDataFile()
    {
        return @unlink($this->getFilePath());
    }

    public function needsReencryption()
    {
        if (!file_exists($this->getFilePath()) || !$this->getEncryptedContent()) {
            return false;
        }
        $statement = DBManager::get()->prepare("
            SELECT DISTINCT seminar_user.user_id
            FROM tresor_container
                INNER JOIN seminar_user ON (tresor_container.seminar_id = seminar_user.Seminar_id)
                LEFT JOIN tresor_user_keys ON (seminar_user.user_id = tresor_user_keys.user_id)
            WHERE (
                    tresor_user_keys.chdate > tresor_container.chdate
                    OR seminar_user.mkdate > tresor_container.chdate
                )
                AND tresor_container.tresor_id = :tresor_id
        ");
        $statement->execute(array(
            'tresor_id' => $this->getId()
        ));
        $user_ids = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        return count($user_ids) ? $user_ids : false;
    }

}
