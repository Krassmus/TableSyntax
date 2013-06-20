<?php

/*
 *  Copyright (c) 2013  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class TableSyntax extends StudIPPlugin implements SystemPlugin {

    static public function transformTabTable($markup, $matches) {
        $lines = explode("\n", $matches[1]);
        foreach ($lines as $key => $line) {
            if ($line) {
                $line = explode("\t", $line);
                foreach ($line as $key2 => $l) {
                    $line[$key2] = "\"".str_replace("\"", "\"\"", trim($l))."\"";
                }
                $lines[$key] = implode(";", $line);
            }
        }
        $csv = implode("\n", $lines);

        $folder_id = self::getFolderId();

        echo($csv);
        die();
    }

    protected function getFolderId() {
        $db = DBManager::get();
        $context = Request::get("cid");
        $folder_id = md5("Blubber_".$context."_".$GLOBALS['user']->id);
        $parent_folder_id = md5("Blubber_".$context);
        if ($context) {
            $folder_id = $parent_folder_id;
        }
        $folder = $db->query(
            "SELECT * " .
            "FROM folder " .
            "WHERE folder_id = ".$db->quote($folder_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if (!$folder) {
            $folder = $db->query(
                "SELECT * " .
                "FROM folder " .
                "WHERE folder_id = ".$db->quote($parent_folder_id)." " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
            if (!$folder) {
                $db->exec(
                    "INSERT IGNORE INTO folder " .
                    "SET folder_id = ".$db->quote($parent_folder_id).", " .
                        "range_id = ".$db->quote($context).", " .
                        "user_id = ".$db->quote($GLOBALS['user']->id).", " .
                        "name = ".$db->quote("BlubberDateien").", " .
                        "permission = '7', " .
                        "mkdate = ".$db->quote(time()).", " .
                        "chdate = ".$db->quote(time())." " .
                "");
            }
            if ($context) {
                $db->exec(
                    "INSERT IGNORE INTO folder " .
                    "SET folder_id = ".$db->quote($folder_id).", " .
                        "range_id = ".$db->quote($parent_folder_id).", " .
                        "user_id = ".$db->quote($GLOBALS['user']->id).", " .
                        "name = ".$db->quote(get_fullname()).", " .
                        "permission = '7', " .
                        "mkdate = ".$db->quote(time()).", " .
                        "chdate = ".$db->quote(time())." " .
                "");
            }
        }
    }

    public function __construct() {
        parent::__construct();
        StudipTransformFormat::addStudipMarkup("tabtable", "((?:^[^\n]*\t[^\n]*\n)+)", "", "TableSyntax::transformTabTable");
        // "((?:^[^\n]*\t[^\n]*\n)+)"
    }

}