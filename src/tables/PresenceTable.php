<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat\Tables;

use Magnate\Tables\Migration;

/**
 * Presence table migration class.
 * @since 0.0.3
 */
class PresenceTable extends Migration
{

    /**
     * @since 0.0.3
     */
    protected function up() : self
    {

        $this->table('eventstat_presence')
            ->field('user_id', 'BIGINT(20) UNSIGNED NOT NULL')
            ->field('event', 'BIGINT(20) UNSIGNED NOT NULL')
            ->field('presence_time', 'TIME NOT NULL')
            ->field('clicks', 'MEDIUMINT(10) UNSIGNED NOT NULL')
            ->field('last_checking', 'DATETIME NOT NULL');
        
        return $this;

    }

}
