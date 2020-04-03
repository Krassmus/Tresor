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

}
