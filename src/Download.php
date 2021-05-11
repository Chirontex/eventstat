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
                "SELECT t.ID, t.post_title
                    FROM `".$this->wpdb->prefix."posts` AS t
                    WHERE t.post_type = 'ajde_events'
                    ORDER BY t.ID DESC",
                ARRAY_A
            );

            return $events;

        });

        return $this;

    }

}
