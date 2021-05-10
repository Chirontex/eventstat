<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat;

use Magnate\EntryPoint;
use Magnate\Exceptions\ActiveRecordCollectionException;
use Eventstat\Tables\PresenceTable;
use Eventstat\Models\Presence;
use WP_REST_Request;

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

        $this
            ->apiInit()
            ->scriptAdd()
            ->presenceTrackingInit();
        
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

            $event = get_post($event_id);

            $lag = 3600;

            $start_time = (int)$event->evcal_srow;
            $end_time = (int)$event->evcal_erow;

            date_default_timezone_set('UTC');

            $start_time = date("Y-m-d H:i:s", $start_time);
            $end_time = date("Y-m-d H:i:s", $end_time);

            date_default_timezone_set($event->_evo_tz);

            $start_time = strtotime($start_time) - $lag;
            $end_time = strtotime($end_time) + $lag;

            $now = time();

            if ($now > $end_time) return $content;

            ob_start();

            if ($now < $start_time) {

?>
<script>
setTimeout(() => {
    eventstatClient.check(
        <?= $event_id ?>,
        <?= $user_id ?>,
        '<?= md5('eventstat-client-check-'.$event_id.'-'.$user_id) ?>'
    );
}, <?= ($start_time - $now) * 1000 ?>);
</script>
<?php

            } else {

?>
<script>
eventstatClient.check(
    <?= $event_id ?>,
    <?= $user_id ?>,
    '<?= md5('eventstat-client-check-'.$event_id.'-'.$user_id) ?>'
);
</script>
<?php

            }

            return ob_get_clean().$content;

        });

        return $this;

    }

    /**
     * Add client script.
     * @since 0.0.6
     * 
     * @return $this
     */
    protected function scriptAdd() : self
    {

        add_action('wp_enqueue_scripts', function() {

            wp_enqueue_script(
                'eventstat-client',
                $this->url.'assets/js/eventstat-client.js',
                [],
                '0.1.3'
            );

        });

        return $this;

    }

    /**
     * Initialize REST API route.
     * @since 0.0.7
     * 
     * @return $this
     */
    protected function apiInit() : self
    {

        add_action('rest_api_init', function() {

            register_rest_route(
                'eventstat/v1',
                '/check',
                [
                    'methods' => 'POST',
                    'callback' => function(WP_REST_Request $request) {

                        $event_id = (int)$request->get_param('eventstat-check-event');
                        $user_id = (int)$request->get_param('eventstat-check-user');

                        $end_time = $this->wpdb->get_results(
                            "SELECT t.meta_value
                                FROM `".$this->wpdb->prefix."postmeta` AS t
                                WHERE t.post_id = ".$event_id."
                                AND t.meta_key = 'evcal_erow'",
                            ARRAY_A
                        );

                        if (empty($end_time)) return [
                            'code' => -99,
                            'message' => 'Event not found.'
                        ];

                        $end_time = (int)$end_time[0]['meta_value'] + 3600;

                        date_default_timezone_set('UTC');

                        $end_time = date("Y-m-d H:i:s", $end_time);

                        date_default_timezone_set(ini_get('date.timezone'));

                        $end_time = strtotime($end_time);

                        $now = time();

                        try {

                            $presence = Presence::where(
                                [
                                    [
                                        'user_id' => [
                                            'condition' => '= %d',
                                            'value' => $user_id
                                        ],
                                        'event' => [
                                            'condition' => '= %d',
                                            'value' => $event_id
                                        ]
                                    ]
                                ]
                            )->first();

                            $presence_time = strtotime($presence->presence_time);

                            $last_checking = strtotime($presence->last_checking);

                            if ($now > $end_time) {

                                if ($last_checking < $end_time) {

                                    $presence->presence_time = date(
                                        "H:i:s",
                                        $presence_time + ($end_time - $last_checking)
                                    );

                                } else return [
                                    'code' => -98,
                                    'message' => 'Event is over.'
                                ];

                            } else {

                                $presence->presence_time = date(
                                    "H:i:s",
                                    $presence_time + ($now - $last_checking)
                                );    

                            }

                            $presence->last_checking = date("Y-m-d H:i:s", $now);

                            $presence->save();

                        } catch (ActiveRecordCollectionException $e) {

                            if ($e->getCode() === -9) {

                                $presence = new Presence;

                                $presence->user_id = $user_id;
                                $presence->event = $event_id;
                                $presence->presence_time = '00:00:00';
                                $presence->last_checking = date("Y-m-d H:i:s", $now);

                                $presence->save();

                            } else throw $e;

                        }

                        return [
                            'code' => 0,
                            'message' => 'Success.'
                        ];

                    },
                    'permission_callback' => function(WP_REST_Request $request) {

                        return $request->get_param('eventstat-check-key') ===
                            md5('eventstat-client-check-'.
                                $request->get_param('eventstat-check-event').
                                '-'.$request->get_param('eventstat-check-user'));

                    }
                ]
            );

        });

        return $this;

    }

}
