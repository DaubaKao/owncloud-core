<?php

require_once "base.php";

use OC\DatabaseSessionHandler;

class Core extends OC {
    private static $PREFILTERS = array();

    public static function init() {
        parent::init();
        $preFilters = self::$server->getSystemConfig()->getValue("preFilters");
        self::registers($preFilters);
    }

	public static function initSession() {
        $config = self::$server->getSystemConfig();
        $handler = new DatabaseSessionHandler();
        $handler->init($config->getValue("dbtype"),
                       $config->getValue("dbhost"),
                       $config->getValue("dbuser"),
                       $config->getValue("dbpassword"),
                       $config->getValue("dbname"));
        $session_Est = session_set_save_handler($handler, true);
        parent::initSession();
    }

    public static function handleRequest() {
        foreach(self::$PREFILTERS as $preFilter) {
            $preFilter->run();
        }

        parent::handleRequest();
    }

    public static function registers($preFilters) {
        foreach($preFilters as $preFilter) {
            $preFilter = new $preFilter();
            self::$PREFILTERS[] = $preFilter;
        }
    }
}

Core::init();
