<?php
function setAlert($icon, $title, $text = '', $style = 'primary', $button = 'OK')
{
    $_SESSION['alert'] = [
        'icon' => $icon,
        'title' => $title,
        'text' => $text,
        'style' => $style,
        'button' => $button
    ];
}
