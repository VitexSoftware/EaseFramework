#!/usr/bin/php -f
<?php
/**
 * Send HTML mail
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2019 Vitex@hippy.cz (G)
 */
require_once '../vendor/autoload.php';
define('EASE_LOGGER', 'console');

$testMail = new \Ease\Mailer(isset($argv[1]) ? $argv[1] : constant('EASE_EMAILTO'),
    'HTML Mail');
$testMail->addItem(new \Ease\Html\H1Tag('Html Mail'));
$testMail->addItem(new \Ease\Html\ImgTag('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPjxzdmcgaGVpZ2h0PSIxODcuNTAwMDAiIGlkPSJzdmcxNDY4IiBpbmtzY2FwZTp2ZXJzaW9uPSIwLjQyIiBzb2RpcG9kaTpkb2NiYXNlPSJDOlxEb2N1bWVudHMgYW5kIFNldHRpbmdzXEphcm5vXE9tYXQgdGllZG9zdG90XHZhbmhhc3RhXG9wZW5jbGlwYXJ0c1xvbWF0XHN5bWJvbHMiIHNvZGlwb2RpOmRvY25hbWU9Im1haWwxLnN2ZyIgc29kaXBvZGk6dmVyc2lvbj0iMC4zMiIgdmVyc2lvbj0iMS4wIiB3aWR0aD0iMTg3LjUwMDAwIiB4PSIwLjAwMDAwMDAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOmNjPSJodHRwOi8vd2ViLnJlc291cmNlLm9yZy9jYy8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6aW5rc2NhcGU9Imh0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgeG1sbnM6c29kaXBvZGk9Imh0dHA6Ly9pbmtzY2FwZS5zb3VyY2Vmb3JnZS5uZXQvRFREL3NvZGlwb2RpLTAuZHRkIiB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB5PSIwLjAwMDAwMDAwIj4KICA8bWV0YWRhdGE+CiAgICA8cmRmOlJERiB4bWxuczpjYz0iaHR0cDovL3dlYi5yZXNvdXJjZS5vcmcvY2MvIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CiAgICAgIDxjYzpXb3JrIHJkZjphYm91dD0iIj4KICAgICAgICA8ZGM6dGl0bGU+ZW52ZWxvcGU8L2RjOnRpdGxlPgogICAgICAgIDxkYzpkZXNjcmlwdGlvbj48L2RjOmRlc2NyaXB0aW9uPgogICAgICAgIDxkYzpzdWJqZWN0PgogICAgICAgICAgPHJkZjpCYWc+CiAgICAgICAgICAgIDxyZGY6bGk+ZW52ZWxvcGUgbWFpbCBzeW1ib2w8L3JkZjpsaT4KICAgICAgICAgIDwvcmRmOkJhZz4KICAgICAgICA8L2RjOnN1YmplY3Q+CiAgICAgICAgPGRjOnB1Ymxpc2hlcj4KICAgICAgICAgIDxjYzpBZ2VudCByZGY6YWJvdXQ9Imh0dHA6Ly93d3cub3BlbmNsaXBhcnQub3JnLyI+CiAgICAgICAgICAgIDxkYzp0aXRsZT5KYXJubyBWYXNhbWFhPC9kYzp0aXRsZT4KICAgICAgICAgIDwvY2M6QWdlbnQ+CiAgICAgICAgPC9kYzpwdWJsaXNoZXI+CiAgICAgICAgPGRjOmNyZWF0b3I+CiAgICAgICAgICA8Y2M6QWdlbnQ+CiAgICAgICAgICAgIDxkYzp0aXRsZT5KYXJubyBWYXNhbWFhPC9kYzp0aXRsZT4KICAgICAgICAgIDwvY2M6QWdlbnQ+CiAgICAgICAgPC9kYzpjcmVhdG9yPgogICAgICAgIDxkYzpyaWdodHM+CiAgICAgICAgICA8Y2M6QWdlbnQ+CiAgICAgICAgICAgIDxkYzp0aXRsZT5KYXJubyBWYXNhbWFhPC9kYzp0aXRsZT4KICAgICAgICAgIDwvY2M6QWdlbnQ+CiAgICAgICAgPC9kYzpyaWdodHM+CiAgICAgICAgPGRjOmRhdGU+PC9kYzpkYXRlPgogICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PgogICAgICAgIDxkYzp0eXBlIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiLz4KICAgICAgICA8Y2M6bGljZW5zZSByZGY6cmVzb3VyY2U9Imh0dHA6Ly93ZWIucmVzb3VyY2Uub3JnL2NjL1B1YmxpY0RvbWFpbiIvPgogICAgICAgIDxkYzpsYW5ndWFnZT5lbjwvZGM6bGFuZ3VhZ2U+CiAgICAgIDwvY2M6V29yaz4KICAgICAgPGNjOkxpY2Vuc2UgcmRmOmFib3V0PSJodHRwOi8vd2ViLnJlc291cmNlLm9yZy9jYy9QdWJsaWNEb21haW4iPgogICAgICAgIDxjYzpwZXJtaXRzIHJkZjpyZXNvdXJjZT0iaHR0cDovL3dlYi5yZXNvdXJjZS5vcmcvY2MvUmVwcm9kdWN0aW9uIi8+CiAgICAgICAgPGNjOnBlcm1pdHMgcmRmOnJlc291cmNlPSJodHRwOi8vd2ViLnJlc291cmNlLm9yZy9jYy9EaXN0cmlidXRpb24iLz4KICAgICAgICA8Y2M6cGVybWl0cyByZGY6cmVzb3VyY2U9Imh0dHA6Ly93ZWIucmVzb3VyY2Uub3JnL2NjL0Rlcml2YXRpdmVXb3JrcyIvPgogICAgICA8L2NjOkxpY2Vuc2U+CiAgICA8L3JkZjpSREY+CiAgPC9tZXRhZGF0YT4KICA8c29kaXBvZGk6bmFtZWR2aWV3IGJvcmRlcmNvbG9yPSIjNjY2NjY2IiBib3JkZXJvcGFjaXR5PSIxLjAiIGlkPSJiYXNlIiBpbmtzY2FwZTpjdXJyZW50LWxheWVyPSJzdmcxNDY4IiBpbmtzY2FwZTpjeD0iOTMuNzUwMDAwIiBpbmtzY2FwZTpjeT0iOTMuNzUwMDAwIiBpbmtzY2FwZTpwYWdlb3BhY2l0eT0iMC4wIiBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIiBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSI0ODAiIGlua3NjYXBlOndpbmRvdy13aWR0aD0iNjQwIiBpbmtzY2FwZTp6b29tPSIxLjgzNDY2NjciIHBhZ2Vjb2xvcj0iI2ZmZmZmZiIvPgogIDxkZWZzIGlkPSJkZWZzMTQ3MCIvPgogIDxnIGlkPSJsYXllcjEiPgogICAgPGcgaWQ9ImcyNDIzIiB0cmFuc2Zvcm09Im1hdHJpeCgwLjgxMDg3NSwwLjAwMDAwMCwwLjAwMDAwMCwwLjgxMDg3NSw1LjkwOTY4MCwtNDAzLjM1NzYpIj4KICAgICAgPHJlY3QgaGVpZ2h0PSIxMDAuMDAwMDAiIGlkPSJyZWN0MjM5OCIgcng9IjMuMDAwMDAwMCIgcnk9IjMuMDAwMDAwMCIgc3R5bGU9Im9wYWNpdHk6MS4wMDAwMDAwO2ZpbGw6I2ZmZmZmZjtmaWxsLW9wYWNpdHk6MS4wMDAwMDAwO3N0cm9rZTojMDAwMDAwO3N0cm9rZS13aWR0aDozLjc1MDAwMDA7c3Ryb2tlLWxpbmVjYXA6cm91bmQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjQuMDAwMDAwMDtzdHJva2UtZGFzaGFycmF5Om5vbmU7c3Ryb2tlLWRhc2hvZmZzZXQ6MC4wMDAwMDAwMDtzdHJva2Utb3BhY2l0eToxLjAwMDAwMDAiIHdpZHRoPSIxNTguMDAwMDAiIHg9IjMwLjAwMDAwMCIgeT0iNTYwLjM2MjE4Ii8+CiAgICAgIDxwYXRoIGQ9Ik0gMzIuMDAwMDAwLDY1OC4zNjIxOCBMIDEwOC4wMDAwMCw2MDIuMzYyMTggTCAxODguMDAwMDAsNjYwLjM2MjE4IiBpZD0icGF0aDI0MDAiIHN0eWxlPSJmaWxsOm5vbmU7ZmlsbC1vcGFjaXR5OjAuNzUwMDAwMDA7ZmlsbC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlOiMwMDAwMDA7c3Ryb2tlLXdpZHRoOjMuNzUwMDAwMDtzdHJva2UtbGluZWNhcDpidXR0O3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDo0LjAwMDAwMDA7c3Ryb2tlLWRhc2hhcnJheTpub25lO3N0cm9rZS1vcGFjaXR5OjEuMDAwMDAwMCIvPgogICAgICA8cGF0aCBkPSJNIDMxLjkzNTEzNSw1NjEuMDUwNDcgTCAxMDcuNzA0NTAsNjIxLjAxODA0IEwgMTg2LjAwMDAwLDU2MC4zOTQ2MSBMIDMxLjkzNTEzNSw1NjEuMDUwNDcgeiAiIGlkPSJwYXRoMjQwMiIgc3R5bGU9ImZpbGw6I2ZmZmZmZjtmaWxsLW9wYWNpdHk6MS4wMDAwMDAwO2ZpbGwtcnVsZTpldmVub2RkO3N0cm9rZTojMDAwMDAwO3N0cm9rZS13aWR0aDozLjc1MDAwMDA7c'));

$testMail->send();

