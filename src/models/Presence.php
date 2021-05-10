<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat\Models;

use Magnate\Tables\ActiveRecord;

/**
 * Presence table AR class.
 * @since 0.0.7
 */
class Presence extends ActiveRecord
{

    /**
     * @since 0.0.7
     */
    public static function tableName(): string
    {
        
        return 'eventstat_presence';

    }

}
