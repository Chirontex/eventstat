<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Eventstat;

use Magnate\AdminPage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Eventstat\Models\Matching;
use Eventstat\Models\Presence;

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
        elseif (isset($_POST['eventstat-match-delete'])) $this->matchingDelete();
        elseif (isset($_POST['eventstat-match-update'])) $this->matchingUpdate();
        elseif (isset($_POST['eventstat-download'])) $this->downloadInit();

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

            return Matching::order(['place' => 'ASC'])->all();

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

    /**
     * Delete matching.
     * @since 0.1.8
     * 
     * @return $this
     */
    protected function matchingDelete() : self
    {

        add_action('plugins_loaded', function() {

            if (wp_verify_nonce(
                $_POST['eventstat-match-delete'],
                'eventstat-match-delete-wpnp'
            ) === false) $this->notice(
                'error',
                $this->fail_nonce_notice
            );
            else {

                $id = (int)$_POST['eventstat-match-delete-id'];

                if (empty($id)) {

                    $this->notice(
                        'error',
                        'Неверный ID записи.'
                    );

                    return;

                }

                $match = Matching::find($id);

                $match->delete();

                $this->notice(
                    'success',
                    'Сопоставление удалено!'
                );

            }

        });

        return $this;

    }

    /**
     * Update match.
     * @since 0.1.9
     * 
     * @return $this
     */
    protected function matchingUpdate() : self
    {

        add_action('plugins_loaded', function() {

            if (wp_verify_nonce(
                $_POST['eventstat-match-update'],
                'eventstat-match-update-wpnp'
            ) === false) $this->notice(
                'error',
                $this->fail_nonce_notice
            );
            else {

                $id = (int)$_POST['eventstat-match-update-id'];
                $place = (int)$_POST['eventstat-match-update-place'];
                $key = (string)$_POST['eventstat-match-update-key'];
                $alias = (string)$_POST['eventstat-match-update-alias'];

                if (empty($id) ||
                    empty($key) ||
                    empty($alias)) {

                    $this->notice(
                        'error',
                        'Недостаточно данных для обновления сопоставления.'
                    );

                    return;

                }

                $match = Matching::where(
                    [
                        [
                            'id' => [
                                'condition' => '= %d',
                                'value' => $id
                            ]
                        ]
                    ]
                )->first();
                
                $match->place = $place;
                $match->meta_key = $key;
                $match->alias = $alias;

                $match->save();

                $this->notice(
                    'success',
                    'Сопоставление успешно обновлено!'
                );

            }

        });

        return $this;

    }

    /**
     * Initialize downloading.
     * @since 0.2.0
     * 
     * @return $this
     */
    protected function downloadInit() : self
    {

        add_action('plugins_loaded', function() {

            if (wp_verify_nonce(
                $_POST['eventstat-download'],
                'eventstat-download-wpnp'
            ) === false) $this->notice(
                'error',
                $this->fail_nonce_notice
            );
            else {

                $event_id = (int)$_POST['eventstat-download-event'];

                $presence = Presence::where(
                    [
                        [
                            'event' => [
                                'condition' => '= %d',
                                'value' => $event_id
                            ]
                        ]
                    ]
                )->all();

                if (empty($presence)) {

                    $this->notice(
                        'warning',
                        'Статистика по указанному мероприятию отсутствует.'
                    );

                    return;

                }

                $spreadsheet = new Spreadsheet;

                $worksheet = $spreadsheet->getSheet(0);

                $worksheet->setTitle('Участники');

                $worksheet->setCellValue('A1', 'Общее время присутствия');
                $worksheet->setCellValue('B1', 'E-mail');

                $matching = Matching::order(['place' => 'ASC'])->all();

                $col = 3;

                foreach ($matching as $match) {

                    $worksheet
                        ->getCell($this->getColumnName($col).'1')
                            ->setValueExplicit(
                                $match->alias,
                                DataType::TYPE_STRING
                            );

                    $col += 1;

                }

                $col = 3;
                $row = 2;

                foreach ($presence as $attending) {

                    $worksheet
                        ->getCell('A'.$row)
                            ->setValueExplicit(
                                (string)$attending->presence_time,
                                DataType::TYPE_STRING
                            );

                    $user = get_userdata((int)$attending->user_id);

                    if ($user) {

                        $worksheet
                            ->getCell('B'.$row)
                                ->setValueExplicit(
                                    $user->user_email,
                                    DataType::TYPE_STRING
                                );

                        foreach ($matching as $match) {

                            $key = $match->meta_key;

                            $worksheet
                                ->getCell($this->getColumnName($col).$row)
                                    ->setValueExplicit(
                                        isset($user->$key) ?
                                            $user->$key : '',
                                        DataType::TYPE_STRING
                                    );

                            $col += 1;

                        }

                    }

                    $row += 1;
                    $col = 3;

                }

                if (!file_exists($this->path.'temp/')) {

                    if (!mkdir($this->path.'temp/')) {

                        $this->notice(
                            'error',
                            'Нет доступа к временной директории. Пожалуйста, обратитесь к администратору.'
                        );

                        return;

                    }

                }

                $ch_arr = array_merge(range('a', 'z'), range(0, 9));

                do {

                    $filename = '';

                    for ($i = 0; $i < 32; $i++) {

                        $filename .= $ch_arr[rand(0, count($ch_arr) - 1)];

                    }

                    $filename .= '.xlsx';

                } while (file_exists($this->path.'temp/'.$filename));

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save($this->path.'temp/'.$filename);

                unset($spreadsheet);
                unset($writer);

                $file = file_get_contents($this->path.'temp/'.$filename);

                unlink($this->path.'temp/'.$filename);

                header('Content-type: application; charset=utf-8');
                header('Content-disposition: attachment; filename=statistics.xlsx');

                echo $file;

                die;

            }

        });

        return $this;

    }

    /**
     * Calculates a column name by it's periodic number.
     * @since 0.2.0
     * 
     * @param int $number
     * If $number lesser than 1 or bigger than 650,
     * the method will return an empty string.
     * 
     * @return string
     */
    protected function getColumnName(int $number) : string
    {

        $name = '';

        if ($number > 0) {

            $alphabet = range('A', 'Z');

            if ($number <= count($alphabet)) $name = $alphabet[$number - 1];
            else {

                $fi = 0;

                $dif = $number - count($alphabet);

                while ($dif > count($alphabet)) {

                    $fi += 1;

                    $dif = $dif - count($alphabet);

                }

                if ($fi <= count($alphabet)) {

                    $name .= $alphabet[$fi];
                    $name .= $alphabet[$dif - 1];

                }

            }

        }

        return $name;

    }

}
