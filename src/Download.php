<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat;

use Magnate\AdminPage;

/**
 * Download page class.
 * @since 0.1.2
 */
class Download extends AdminPage
{

    /**
     * @since 0.1.3
     */
    protected function init() : self
    {

        $this->filtersInit();
        
        return $this;

    }

    /**
     * Initialize filters.
     * @since 0.1.3
     * 
     * @return $this
     */
    protected function filtersInit() : self
    {

        add_filter('eventstat-download-events', function() {

            $events = $this->wpdb->get_results(
                "SELECT t.ID, t.post_title, t1.meta_value AS start_time
                    FROM `".$this->wpdb->prefix."posts` AS t
                    LEFT JOIN `".$this->wpdb->prefix."postmeta` AS t1
                    ON t.ID = t1.post_id
                    WHERE t.post_type = 'ajde_events'
                    AND t1.meta_key = 'evcal_srow'
                    ORDER BY t.ID DESC",
                ARRAY_A
            );

            return $events;

        });

        return $this;

    }

}
