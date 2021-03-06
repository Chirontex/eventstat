<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat;

use Magnate\EntryPoint;
use Magnate\Injectors\EntryPointInjector;
use Magnate\Injectors\AdminPageInjector;
use Magnate\Exceptions\ActiveRecordCollectionException;
use Eventstat\Tables\PresenceTable;
use Eventstat\Tables\MatchingTable;
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
        new MatchingTable;

        new Download(
            new EntryPointInjector(
                $this->path,
                $this->url
            ),
            (new AdminPageInjector(
                'eventstat-download',
                $this->path.'views/download.php',
                'Выгрузка статистики мероприятий',
                'Статистика мероприятий',
                8,
                '',
                $this->url.'assets/icons/clock.svg'
            ))->addStyle(
                'bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css',
                [],
                '5.0.0'
            )->addStyle(
                'eventstat-download',
                $this->url.'assets/css/download.css',
                [],
                '0.0.3'
            )->addScript(
                'eventstat-download',
                $this->url.'assets/js/download.js',
                [],
                '0.0.5'
            )
        );

        $this
            ->apiInit()
            ->scriptAdd()
            ->buttonShortcodeInit()
            ->presenceTrackingInit();
        
        return $this;

    }

    /**
     * Initialize button shortcode.
     * @since 1.0.2
     * 
     * @return $this
     */
    protected function buttonShortcodeInit() : self
    {

        add_shortcode('es-button', function($atts, $content) {

            $atts = shortcode_atts([
                'event' => '',
                'class' => '',
                'style' => '',
                'id' => ''
            ], $atts);

            $post_id = (int)$atts['event'];

            $post = get_post($post_id);

            $user_id = get_current_user_id();

            if (empty($post) ||
                empty($user_id)) return;

            if (strpos($content, '|||') === false) $content = 'Подтвердите присутствие|||Подтверждение пока не требуется';

            $content = explode('|||', $content);

            date_default_timezone_set('GMT');

            $start = date("Y-m-d H:i:s", $post->evcal_srow);
            $end = date("Y-m-d H:i:s", $post->evcal_erow);

            date_default_timezone_set($post->_evo_tz);
            
            $now = time();            

            $start = strtotime($start);
            $end = strtotime($end);

            if ($now > $end) return;

            $available_clicks = (int)(($end - ($now > $start ? $now : $start))/900);

            $id = empty($atts['id']) ?
                'eventstat-presence-button' : $atts['id'];

            ob_start();

?>
<script>
eventstatClient.availableClicks = <?= $available_clicks ?>;
</script>
<button type="button" id="<?= htmlspecialchars($id) ?>" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" <?= $now < $start || $available_clicks === 0 ? 'disabled="true" ' : '' ?>onclick="eventstatClient.click('<?= htmlspecialchars($id) ?>', <?= $post_id ?>, <?= $user_id ?>, '<?= md5('eventstat-button-'.$post_id.'-'.$user_id) ?>');">
    <span id="<?= htmlspecialchars($id) ?>-content-0"><?= $content[0] ?></span>
    <span id="<?= htmlspecialchars($id) ?>-content-1" style="display: none;"><?= $content[1] ?></span>
</button>
<?php

            if ($available_clicks > 0) {

                if ($now < $start) {

?>
<script>
setTimeout(
    () => {
        const button = document.getElementById('<?= htmlspecialchars($id) ?>');

        if (button.hasAttribute('disabled')) button.removeAttribute('disabled');
    },
    <?= ($start - $now) * 1000 ?>
);
</script>
<?php

                }

            }

            return ob_get_clean();

        });

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
                '0.1.5'
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
                                $presence->clicks = 0;
                                $presence->last_checking = date("Y-m-d H:i:s", $now);

                                $presence->save();

                            } else return [
                                'code' => $e->getCode(),
                                'message' => $e->getMessage()
                            ];

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

            register_rest_route(
                'eventstat/v1',
                '/click',
                [
                    'methods' => 'POST',
                    'callback' => function(WP_REST_Request $request) {

                        $post_id = (int)$request->get_param('eventstat-button-event');
                        $user_id = (int)$request->get_param('eventstat-button-user');

                        if (empty($post_id) ||
                            empty($user_id)) return [
                                'code' => -99,
                                'message' => 'Too few arguments for this argument.'
                            ];

                        date_default_timezone_set(ini_get('date.timezone'));

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
                                            'value' => $post_id
                                        ]
                                    ]
                                ]
                            )->first();

                            $presence->clicks += 1;

                            $presence->save();

                        } catch (ActiveRecordCollectionException $e) {

                            if ($e->getCode() === -9) {

                                $presence = new Presence;

                                $presence->user_id = $user_id;
                                $presence->event = $post_id;
                                $presence->presence_time = '00:00:00';
                                $presence->clicks = 1;
                                $presence->last_checking = date("Y-m-d H:i:s", $now);

                                $presence->save();

                            } else return [
                                'code' => $e->getCode(),
                                'message' => $e->getMessage()
                            ];

                        }

                        return [
                            'code' => 0,
                            'message' => 'Success.'
                        ];

                    },
                    'permission_callback' => function(WP_REST_Request $request) {

                        return $request->get_param('eventstat-button-key') ===
                            md5('eventstat-button-'.
                                $request->get_param('eventstat-button-event').'-'.
                                    $request->get_param('eventstat-button-user'));

                    }
                ]
            );

        });

        return $this;

    }

}
