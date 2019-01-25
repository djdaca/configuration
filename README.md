PHP Simple INI Wrapper
==============

Configuration class provide parsing and writing extended INI

API
--------------
$INI = Ini::getInstance();

$INI->addFile('/config.ini');

.....

config.ini

[section]

var=1

.....

$var = Ini::section('var');

or ...

$var = $INI->section('var');

or ...

$var = $INI->getSection('section', 'var');

and if you want modify config just call

$INI->set('section', array('var' => 2));

Simple - or not ? :)

.....

You can export or import by methods $INI->export() and $INI->import ($config)

Best of end

If u want modify config - you can write array to ini file by method $INI->write($file);

INI
--------------
Extend ini syntax is similar to http://en.wikipedia.org/wiki/INI_file but u 
can write multiple levels of key by dot like: 

key1.key2.key3 = value

This will be translated to:

array('key1' => array('key2' => array('key3' => 'value')));
