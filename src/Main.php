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

        $this->presenceTrackingInit();
        
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

    /**
     * Initialize presence tracking.
     * @since 0.0.4
     * 
     * @return $this
     */
    protected function presenceTrackingInit() : self
    {

        add_filter('the_content', function($content) {

            $user_id = get_current_user_id();

            $event_id = $this->eventCheck();

            if (empty($user_id) ||
                empty($event_id)) return $content;

            ob_start();

            //

            return ob_get_clean().$content;

        });

        return $this;

    }

}
