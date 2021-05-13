<?php
/**
 * Plugin Name: Eventstat
 * Plugin URI: https://github.com/chirontex/eventstat
 * Description: Кастомная статистика мероприятий, созданных в плагине MyEventON.
 * Version: 1.0.3
 * Author: Dmitry Shumilin
 * Author URI: mailto://chirontex@yandex.ru
 * 
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 * @since 1.0.3
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
