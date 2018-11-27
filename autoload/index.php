<?php

define("AUTO_LOAD_ROOT", dirname(dirname(__FILE__)));
$json = json_decode(file_get_contents(AUTO_LOAD_ROOT . "/autoload.json"), true);

class AllFiles
{
    public $files = [];

    function __construct($folder, $page = 0, $limit = 24)
    {
        $this->read($folder);
    }

    function read($folder)
    {
        $folders = glob("$folder/*", GLOB_ONLYDIR);
        foreach ($folders as $folder) {
            $this->files[] = $folder . "/";
            $this->read($folder);
        }
        $files = array_filter(glob("$folder/*"), 'is_file');
        foreach ($files as $file) {
            $this->files[] = $file;
        }
        $this->files = array_filter($this->files, function ($file) {
            return strpos($file, ".php") !== false;
        });

    }
};

function flatten(array $array)
{
    $return = array();
    array_walk_recursive($array, function ($a) use (&$return) {
        $return[] = $a;
    });
    return $return;
}

$classes = [];

foreach ($json['folders'] as $folder) {
    $allfiles = new AllFiles(AUTO_LOAD_ROOT . '/' . $folder);
    $classes[] = $allfiles->files;
}

$classes = flatten($classes);

function autoLoad()
{
    global $classes;
    foreach ($classes as $class) {
        include $class;
    }
}
spl_autoload_register('autoLoad');