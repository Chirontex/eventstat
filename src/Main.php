<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat;

use Magnate\EntryPoint;
use Eventstat\Tables\PresenceTable;

/**
 * Mail entry point class.
 * @since 0.0.2
 */
class Main extends EntryPoint
{

    /**
     * @since 0.0.3
     */
    protected function init() : self
    {

        new PresenceTable;

        $event = $this->eventCheck();

        if ($event !== 0) {

            //

        }
        
        return $this;

    }

    /**
     * Checks if this an event page or not.
     * @since 0.0.3
     * 
     * @return int
     * If not, the method will return 0.
     */
    protected function eventCheck() : int
    {

        $url = site_url($_SERVER['REQUEST_URI']);

        $select = $this->wpdb->get_results(
            "SELECT t.post_id
                FROM `".$this->wpdb->prefix."postmeta` AS t
                WHERE t.meta_key = 'evcal_exlink'
                AND t.meta_value = '".$url."'",
            ARRAY_A
        );

        if (empty($select)) return 0;
        else return (int)$select[0]['post_id'];

    }

}
