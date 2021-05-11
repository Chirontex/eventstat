<?php
/**
 * Plugin Name: Eventstat
 * Plugin URI: https://github.com/chirontex/eventstat
 * Description: Кастомная статистика мероприятий, созданных в плагине MyEventON.
 * Version: 0.1.9
 * Author: Dmitry Shumilin
 * Author URI: mailto://chirontex@yandex.ru
 * 
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 * @since 0.1.9
 */
use Magnate\Injectors\EntryPointInjector;
use Eventstat\Main;

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/eventstat-autoload.php';

new Main(
    new EntryPointInjector(
        plugin_dir_path(__FILE__),
        plugin_dir_url(__FILE__)
    )
);
