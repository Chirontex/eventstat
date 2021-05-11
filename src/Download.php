<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat;

use Magnate\AdminPage;
use Eventstat\Models\Matching;

/**
 * Download page class.
 * @since 0.1.2
 */
class Download extends AdminPage
{

    /**
     * @var string $fail_nonce_notice
     * Typical checking nonce failure notice text.
     * @since 0.1.7
     */
    protected $fail_nonce_notice = 'Произошла ошибка при отправке формы. Пожалуйста, попробуйте ещё раз.';

    /**
     * @since 0.1.3
     */
    protected function init() : self
    {

        if (isset($_POST['eventstat-matching'])) $this->matchingSave();

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

        add_filter('eventstat-matches-table', function() {

            return Matching::where([])->all();

        });

        return $this;

    }

    /**
     * Saving the match.
     * @since 0.1.8
     * 
     * @return $this
     */
    protected function matchingSave() : self
    {

        add_action('plugins_loaded', function() {

            if (wp_verify_nonce(
                $_POST['eventstat-matching'],
                'eventstat-matching-wpnp'
            ) === false) $this->notice(
                'error',
                $this->fail_nonce_notice
            );
            else {

                $place = (int)$_POST['eventstat-matching-place'];

                $key = (string)$_POST['eventstat-matching-meta-key'];

                $alias = (string)$_POST['eventstat-matching-alias'];

                if (empty($key) ||
                    empty($alias)) {

                    $this->notice(
                        'error',
                        'Не указан ключ или псевдоним метаполя.'
                    );

                    return;

                }

                $matching = new Matching;

                $matching->place = $place;
                $matching->meta_key = $key;
                $matching->alias = $alias;

                $matching->save();

                $this->notice(
                    'success',
                    'Сопоставление сохранено!'
                );

            }

        });

        return $this;

    }

}
