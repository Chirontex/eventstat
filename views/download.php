<?php
/**
 * @package Eventstat
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 * @since 0.1.2
 */
if (!defined('ABSPATH')) die;

?>
<div class="container-fluid">
    <h1 class="h3 text-center my-5">Выгрузка статистики мероприятий</h1>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="eventstat-column">
                <form action="" method="post">
                    <?php wp_nonce_field('eventstat-download-wpnp', 'eventstat-download') ?>
                    <div class="mb-3">
                        <label for="eventstat-download-event" class="form-label">
                            Выберите мероприятие
                        </label>
                        <select name="eventstat-download-event" id="eventstat-download-event" class="form-select" required="true">
<?php

foreach (apply_filters('eventstat-download-events', []) as $event) {

?>
                            <option value="<?= $event['ID'] ?>"><?= htmlspecialchars($event['post_title']).' ('.date("d.m.Y", $event['start_time']).')' ?></option>
<?php

}

?>
                        </select>
                    </div>
                    <div class="mb-3 text-center">
                        <button type="submit" class="button button-primary">Выгрузить статистику</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
            <div class="eventstat-column">
                <h5 class="text-center mb-3">Добавить метаполе пользователей в статистику</h5>
                <form action="" method="post">
                    <?php wp_nonce_field('eventstat-matching-wpnp', 'eventstat-matching') ?>
                    <div class="mb-3">
                        <label for="eventstat-matching-place" class="form-label">
                            Порядковый номер
                        </label>
                        <input type="number" name="eventstat-matching-place" id="eventstat-matching-place" class="form-control form-control-sm" placeholder="укажите порядковый номер" required="true">
                    </div>
                    <div class="mb-3">
                        <label for="eventstat-matching-meta-key" class="form-label">
                            Ключ метаполя
                        </label>
                        <input type="text" name="eventstat-matching-meta-key" id="eventstat-matching-meta-key" class="form-control form-control-sm" placeholder="введите ключ метаполя" required="true">
                    </div>
                    <div class="mb-3">
                        <label for="eventstat-matching-alias" class="form-label">
                            Псевдоним метаполя
                        </label>
                        <input type="text" name="eventstat-matching-alias" id="eventstat-matching-alias" class="form-control form-control-sm" placeholder="имя, которое будет отображаться в выгрузке" required="true">
                    </div>
                    <div class="mb-3 text-center">
                        <button type="submit" class="button button-primary">Добавить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Порядковый номер</th>
                <th>Ключ</th>
                <th>Псевдоним</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
<?php

foreach (apply_filters('eventstat-matches-table', []) as $match) {

?>
            <tr id="eventstat-match-<?= $match->id ?>-row">
                <td id="eventstat-match-<?= $match->id ?>-place"><?= $match->place ?></td>
                <td id="eventstat-match-<?= $match->id ?>-key"><?= htmlspecialchars($match->meta_key) ?></td>
                <td id="eventstat-match-<?= $match->id ?>-alias"><?= htmlspecialchars($match->alias) ?></td>
                <td id="eventstat-match-<?= $match->id ?>-edit">Редактировать</td>
                <td id="eventstat-match-<?= $match->id ?>-delete"><a href="javascript:void(0)" onclick="eventstat.matchDelete(<?= $match->id ?>);">Удалить</a></td>
            </tr>
<?php

}

?>
        </tbody>
    </table>
    <form action="" method="post" id="eventstat-match-delete-form">
        <?php wp_nonce_field('eventstat-match-delete-wpnp', 'eventstat-match-delete') ?>
    </form>
</div>