<?php
require_once __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

?>
<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">
    <meta charset="utf-8" />
    <title>RPA </title>
    <meta name="description" content="Updates and statistics" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!--begin::Fonts-->
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" /> end::Fonts -->

    <!--begin::Global Theme Styles(used by all pages)-->
    <link href="<?= BASE_URL ?>assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= BASE_URL ?>assets/plugins/custom/prismjs/prismjs.bundle.css" rel="stylesheet" type="text/css" />

    <link href="<?= BASE_URL ?>assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Theme Styles-->

    <!--begin::Layout Themes(used by all pages)-->

    <link href="<?= BASE_URL ?>assets/css/themes/layout/header/base/light.css" rel="stylesheet" type="text/css" />
    <!-- <link href="<?= BASE_URL ?>assets/css/themes/layout/header/menu/light.css" rel="stylesheet" type="text/css" /> -->
    <link href="<?= BASE_URL ?>assets/css/themes/layout/brand/dark.css" rel="stylesheet" type="text/css" />
    <link href="<?= BASE_URL ?>assets/css/themes/layout/aside/dark.css" rel="stylesheet" type="text/css" />
    <style>
        /* Menyembunyikan custom footer saat aside di-minimize */
        body.aside-minimize .aside-footer,
        body.aside-minimize-hoverable.aside-minimize:not(:hover) .aside-footer {
            display: none !important;
        }

        /* Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
    <!-- <link rel="shortcut icon" href="<?= BASE_URL ?>assets/media/favicon.ico" /> -->
</head>