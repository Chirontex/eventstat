<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat\Tables;

use Magnate\Tables\Migration;

/**
 * Matching table migration class.
 * @since 0.1.4
 */
class MatchingTable extends Migration
{

    /**
     * @since 0.1.4
     */
    protected function up() : self
    {

        $this->table('eventstat_matching')
            ->field('place', 'INT(11) NOT NULL')
            ->field('meta_key', 'VARCHAR(255) NOT NULL')
            ->field('alias', 'VARCHAR(255) NOT NULL');

        return $this;

    }

}
