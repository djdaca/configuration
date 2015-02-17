PHP Simple INI Configuration Wrapper
==============

Configuration class provide parsing and writing extended INI

API
--------------
$Config = Configuration::getInstance();

$Config->addConfigFile('/config.ini');

.....
config.ini
[section]
var1=1
.....

$var = Configuration::section('var');

or ...

$var = $Config->section('var');

or ...

$var = $Config->getSection('section', 'var');

and if you want modify config just call

$Config->set('section', array('var' => 2));

Simple not ? :)

.....

You can export or import by methods $Config->export() and $Config->import ($config)

Best of end

If u want modify config - you can write array to ini file by method $Config->write($file);

INI
--------------
Extend ini syntax is similar to http://en.wikipedia.org/wiki/INI_file but u 
can write multiple levels of key by dot like: 

key1.key2.key3 = value

This will be translated to:

array('key1' => array('key2' => array('key3' => 'value')));
