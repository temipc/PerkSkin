<?php
// Navigate from Config dir (app/Config) to project root and then to database folder
return [
  'driver' => 'sqlite',
  'database' => dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'webdb.sqlite',
  'charset' => 'utf8',
];
