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
    <div class="eventstat-column">
        <form action="" method="post">
            <div class="mb-3">
                <label for="eventstat-download-event" class="form-label">
                    Выберите мероприятие
                </label>
                <select name="eventstat-download-event" id="eventstat-download-event" class="form-select" required="true">
<?php

foreach (apply_filters('eventstat-download-events', []) as $event) {

?>
                    <option value="<?= $event['ID'] ?>"><?= htmlspecialchars($event['post_title']) ?></option>
<?php

}

?>
                </select>
            </div>
            <div class="mb-3 text-center">
                <button class="button button-primary">Выгрузить статистику</button>
            </div>
        </form>
    </div>
</div>