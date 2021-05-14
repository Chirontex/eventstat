# Eventstat 1.0.8

Кастомная статистика мероприятий, созданных в плагине MyEventON.

## Требования

1. PHP 7.4 или выше

2. WordPress 5 или выше

3. Плагин MyEventON.

## Использование

Статистика присутствия собирается на странице мероприятия автоматически. Ничего для этого со страницей делать не нужно. Единственное условие — настройка внешней ссылки при создании мероприятия.

Выгрузка статистики настраивается на соответствующей странице в админпанели.

С помощью шорткода **es-button** можно добавить кнопку подтверждения присутствия, которая становится активной для взаимодействия каждые 15 минут. Шорткод имеет вид: `[es-button event="123" id="prs-cnf-button"]content before|||content after[/es-button]`.

Атрибуты:

* event — ID мероприятия.

* id — ID элемента кнопки в DOM-дереве.

Также, можно указать атрибуты class и style, значение которых аналогично одноимённым атрибутам HTML.

Внутри шорткода указывается контент кнопки в разблокированном и заблокированном виде через разделитель |||.
