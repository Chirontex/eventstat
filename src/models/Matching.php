<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat\Models;

use Magnate\Tables\ActiveRecord;

/**
 * Matching AR model.
 * @since 0.1.5
 */
class Matching extends ActiveRecord
{

    /**
     * @since 0.1.5
     */
    public static function tableName() : string
    {
        
        return 'eventstat_matching';

    }

}
