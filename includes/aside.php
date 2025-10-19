<!--begin::Body-->
<?php
require_once __DIR__ . '/config.php';
$role = 'admin';

?>

<body id="kt_body" class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">

    <!--begin::Main-->
    <!--begin::Header Mobile-->
    <div id="kt_header_mobile" class="header-mobile align-items-center  header-mobile-fixed ">
        <!--begin::Logo-->
        <!-- <a href="">
            <img alt="Logo" src="<?= BASE_URL ?>assets/media/logos/logo.png" class="max-w-65px" />
        </a> -->
        <!--end::Logo-->

        <!--begin::Toolbar-->
        <div class="d-flex align-items-center">
            <!--begin::Aside Mobile Toggle-->
            <button class="btn p-0 burger-icon burger-icon-left" id="kt_aside_mobile_toggle">
                <span></span>
            </button>
            <!--end::Aside Mobile Toggle-->

            <!--begin::Header Menu Mobile Toggle-->
            <button class="btn p-0 burger-icon ml-4" id="kt_header_mobile_toggle">
                <span></span>
            </button>
            <!--end::Header Menu Mobile Toggle-->

            <!--begin::Topbar Mobile Toggle-->
            <button class="btn btn-hover-text-primary p-0 ml-2" id="kt_header_mobile_topbar_toggle">
                <span class="svg-icon svg-icon-xl"><!--begin::Svg Icon | path:assets/media/svg/icons/General/User.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <polygon points="0 0 24 0 24 24 0 24" />
                            <path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
                            <path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000" fill-rule="nonzero" />
                        </g>
                    </svg><!--end::Svg Icon--></span> </button>
            <!--end::Topbar Mobile Toggle-->
        </div>
        <!--end::Toolbar-->
    </div>
    <!--end::Header Mobile-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="d-flex flex-row flex-column-fluid page">

            <!--begin::Aside-->
            <div class="aside aside-left  aside-fixed  d-flex flex-column  flex-row-auto" id="kt_aside">
                <!--begin::Brand-->
                <div class="brand flex-column-auto " id="kt_brand">
                    <!--begin::Logo-->
                    <a href="" class="brand-logo">
                        <!-- <img alt="Logo" src="<?= BASE_URL ?>assets/media/logos/logo.png" class="max-w-90px" /> -->
                    </a>
                    <!--end::Logo-->

                    <!--begin::Toggle-->
                    <button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
                        <span class="svg-icon svg-icon svg-icon-xl"><!--begin::Svg Icon | path:assets/media/svg/icons/Navigation/Angle-double-left.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <polygon points="0 0 24 0 24 24 0 24" />
                                    <path d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z" fill="#000000" fill-rule="nonzero" transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999) " />
                                    <path d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999) " />
                                </g>
                            </svg><!--end::Svg Icon--></span> </button>
                    <!--end::Toolbar-->
                </div>
                <!--end::Brand-->

                <!--begin::Aside Menu-->
                <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">

                    <!--begin::Menu Container-->
                    <div
                        id="kt_aside_menu"
                        class="aside-menu my-4 "
                        data-menu-vertical="1"
                        data-menu-scroll="1" data-menu-dropdown-timeout="500">
                        <!--begin::Menu Nav-->
                        <ul class="menu-nav ">
                            <!-- Dashboard -->
                            <?php if ($role == "admin"): ?>
                                <?php if ($_SESSION['menu'] == 'dashboard'): ?>
                                    <li class="menu-item  menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL . 'pages/dashboard.php' ?>" class="menu-link ">
                                        <?php else: ?>
                                    <li class="menu-item" aria-haspopup="true"><a href="<?= BASE_URL . 'pages/dashboard.php' ?>" class="menu-link ">
                                        <?php endif; ?>
                                        <span class="svg-icon menu-icon"><!--begin::Svg Icon | path:assets/media/svg/icons/Design/Layers.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <polygon points="0 0 24 0 24 24 0 24" />
                                                    <path d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z" fill="#000000" fill-rule="nonzero" />
                                                    <path d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 C4.06471678,14.2708238 4.06234874,14.2738418 4.06,14.2768747 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 C4.22358765,15.5534297 4.2250303,15.55454 4.22647627,15.555646 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 C19.9376429,14.2751809 19.9363245,14.2734691 19.935,14.2717619 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 C18.654352,14.112718 18.6514778,14.1149757 18.6486172,14.1172508 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z" fill="#000000" opacity="0.3" />
                                                </g>
                                            </svg><!--end::Svg Icon--></span><span class="menu-text">Dashboard</span></a>
                                    </li>
                                    <!-- Data -->
                                    <?php if ($_SESSION['menu'] == 'data'): ?>
                                        <li class="menu-item  menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL . 'pages/data/' ?>" class="menu-link ">
                                            <?php else: ?>
                                        <li class="menu-item" aria-haspopup="true"><a href="<?= BASE_URL . 'pages/data/' ?>" class="menu-link ">
                                            <?php endif; ?>
                                            <span class="svg-icon menu-icon svg-icon-2x"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\metronic\theme\html\demo1\dist/../src/media/svg/icons\Communication\Clipboard-list.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <rect x="0" y="0" width="24" height="24" />
                                                        <path d="M8,3 L8,3.5 C8,4.32842712 8.67157288,5 9.5,5 L14.5,5 C15.3284271,5 16,4.32842712 16,3.5 L16,3 L18,3 C19.1045695,3 20,3.8954305 20,5 L20,21 C20,22.1045695 19.1045695,23 18,23 L6,23 C4.8954305,23 4,22.1045695 4,21 L4,5 C4,3.8954305 4.8954305,3 6,3 L8,3 Z" fill="#000000" opacity="0.3" />
                                                        <path d="M11,2 C11,1.44771525 11.4477153,1 12,1 C12.5522847,1 13,1.44771525 13,2 L14.5,2 C14.7761424,2 15,2.22385763 15,2.5 L15,3.5 C15,3.77614237 14.7761424,4 14.5,4 L9.5,4 C9.22385763,4 9,3.77614237 9,3.5 L9,2.5 C9,2.22385763 9.22385763,2 9.5,2 L11,2 Z" fill="#000000" />
                                                        <rect fill="#000000" opacity="0.3" x="10" y="9" width="7" height="2" rx="1" />
                                                        <rect fill="#000000" opacity="0.3" x="7" y="9" width="2" height="2" rx="1" />
                                                        <rect fill="#000000" opacity="0.3" x="7" y="13" width="2" height="2" rx="1" />
                                                        <rect fill="#000000" opacity="0.3" x="10" y="13" width="7" height="2" rx="1" />
                                                        <rect fill="#000000" opacity="0.3" x="7" y="17" width="2" height="2" rx="1" />
                                                        <rect fill="#000000" opacity="0.3" x="10" y="17" width="7" height="2" rx="1" />
                                                    </g>
                                                </svg><!--end::Svg Icon--></span>
                                            <span class="menu-text">Data</span></a>
                                        </li>
                                        <!-- Preference -->
                                        <?php if ($_SESSION['menu'] == 'add_line' || $_SESSION['menu'] == 'add_model' || $_SESSION['menu'] == 'add_application' || $_SESSION['menu'] == 'add_path_csv' ||  $_SESSION['menu'] == 'add_csv_name'  || $_SESSION['menu'] == 'add_csv_header' || $_SESSION['menu'] == 'add_model_setting'): ?>
                                            <li class="menu-item  menu-item-submenu menu-item-open" aria-haspopup="true" data-menu-toggle="hover"><a href="javascript:;" class="menu-link menu-toggle">
                                                <?php else: ?>
                                            <li class="menu-item  menu-item-submenu" aria-haspopup="true" data-menu-toggle="hover"><a href="javascript:;" class="menu-link menu-toggle">
                                                <?php endif; ?>
                                                <span class="svg-icon menu-icon svg-icon-2x"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\metronic\theme\html\demo1\dist/../src/media/svg/icons\Code\Compiling.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                            <rect x="0" y="0" width="24" height="24" />
                                                            <path d="M2.56066017,10.6819805 L4.68198052,8.56066017 C5.26776695,7.97487373 6.21751442,7.97487373 6.80330086,8.56066017 L8.9246212,10.6819805 C9.51040764,11.267767 9.51040764,12.2175144 8.9246212,12.8033009 L6.80330086,14.9246212 C6.21751442,15.5104076 5.26776695,15.5104076 4.68198052,14.9246212 L2.56066017,12.8033009 C1.97487373,12.2175144 1.97487373,11.267767 2.56066017,10.6819805 Z M14.5606602,10.6819805 L16.6819805,8.56066017 C17.267767,7.97487373 18.2175144,7.97487373 18.8033009,8.56066017 L20.9246212,10.6819805 C21.5104076,11.267767 21.5104076,12.2175144 20.9246212,12.8033009 L18.8033009,14.9246212 C18.2175144,15.5104076 17.267767,15.5104076 16.6819805,14.9246212 L14.5606602,12.8033009 C13.9748737,12.2175144 13.9748737,11.267767 14.5606602,10.6819805 Z" fill="#000000" opacity="0.3" />
                                                            <path d="M8.56066017,16.6819805 L10.6819805,14.5606602 C11.267767,13.9748737 12.2175144,13.9748737 12.8033009,14.5606602 L14.9246212,16.6819805 C15.5104076,17.267767 15.5104076,18.2175144 14.9246212,18.8033009 L12.8033009,20.9246212 C12.2175144,21.5104076 11.267767,21.5104076 10.6819805,20.9246212 L8.56066017,18.8033009 C7.97487373,18.2175144 7.97487373,17.267767 8.56066017,16.6819805 Z M8.56066017,4.68198052 L10.6819805,2.56066017 C11.267767,1.97487373 12.2175144,1.97487373 12.8033009,2.56066017 L14.9246212,4.68198052 C15.5104076,5.26776695 15.5104076,6.21751442 14.9246212,6.80330086 L12.8033009,8.9246212 C12.2175144,9.51040764 11.267767,9.51040764 10.6819805,8.9246212 L8.56066017,6.80330086 C7.97487373,6.21751442 7.97487373,5.26776695 8.56066017,4.68198052 Z" fill="#000000" />
                                                        </g>
                                                    </svg><!--end::Svg Icon--></span><span class="menu-text">Preference</span><i class="menu-arrow"></i></a>
                                                <div class="menu-submenu "><i class="menu-arrow"></i>
                                                    <ul class="menu-subnav">
                                                        <li class="menu-item  menu-item-parent" aria-haspopup="true">
                                                            <span class="menu-link"><span class="menu-text">Preference</span></span>
                                                        </li>
                                                        <?php if ($_SESSION['menu'] == 'add_line'): ?>
                                                            <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_line/" class="menu-link ">
                                                                <?php else: ?>
                                                            <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_line/" class="menu-link ">
                                                                <?php endif; ?>
                                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                <span class="menu-text">Add Line</span></a>
                                                            </li>
                                                            <?php if ($_SESSION['menu'] == 'add_model'): ?>
                                                                <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_model/" class="menu-link ">
                                                                    <?php else: ?>
                                                                <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_model/" class="menu-link ">
                                                                    <?php endif; ?>
                                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                    <span class="menu-text">Add Model</span></a>
                                                                </li>
                                                                <?php if ($_SESSION['menu'] == 'add_application'): ?>
                                                                    <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_application/" class="menu-link ">
                                                                        <?php else: ?>
                                                                    <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_application/" class="menu-link ">
                                                                        <?php endif; ?>
                                                                        <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                        <span class="menu-text">Add Application</span></a>
                                                                    </li>
                                                                    <?php if ($_SESSION['menu'] == 'add_path_csv'): ?>
                                                                        <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_path_csv/" class="menu-link ">
                                                                            <?php else: ?>
                                                                        <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_path_csv/" class="menu-link ">
                                                                            <?php endif; ?>
                                                                            <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                            <span class="menu-text">Add Path CSV</span></a>
                                                                        </li>
                                                                        <?php if ($_SESSION['menu'] == 'add_csv_name'): ?>
                                                                            <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_csv_name/" class="menu-link ">
                                                                                <?php else: ?>
                                                                            <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_csv_name/" class="menu-link ">
                                                                                <?php endif; ?>
                                                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                                <span class="menu-text">Add CSV Name</span></a>
                                                                            </li>
                                                                            <?php if ($_SESSION['menu'] == 'add_csv_header'): ?>
                                                                                <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_csv_header/" class="menu-link ">
                                                                                    <?php else: ?>
                                                                                <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_csv_header/" class="menu-link ">
                                                                                    <?php endif; ?>
                                                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                                    <span class="menu-text">Add CSV Header</span></a>
                                                                                </li>
                                                                                <?php if ($_SESSION['menu'] == 'add_model_setting'): ?>
                                                                                    <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_model_setting/" class="menu-link ">
                                                                                        <?php else: ?>
                                                                                    <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/preference/add_model_setting/" class="menu-link ">
                                                                                        <?php endif; ?>
                                                                                        <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                                        <span class="menu-text">Add Model Setting</span></a>
                                                                                    </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <!-- setting -->
                                            <?php if ($_SESSION['menu'] == 'admin' || $_SESSION['menu'] == 'user'): ?>
                                                <li class="menu-item  menu-item-submenu menu-item-open" aria-haspopup="true" data-menu-toggle="hover"><a href="javascript:;" class="menu-link menu-toggle">
                                                    <?php else: ?>
                                                <li class="menu-item  menu-item-submenu" aria-haspopup="true" data-menu-toggle="hover"><a href="javascript:;" class="menu-link menu-toggle">
                                                    <?php endif; ?>
                                                    <span class="svg-icon menu-icon svg-icon-2x"><!--begin::Svg Icon | path:C:\wamp64\www\keenthemes\themes\metronic\theme\html\demo1\dist/../src/media/svg/icons\General\Settings-2.svg--><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                <rect x="0" y="0" width="24" height="24" />
                                                                <path d="M5,8.6862915 L5,5 L8.6862915,5 L11.5857864,2.10050506 L14.4852814,5 L19,5 L19,9.51471863 L21.4852814,12 L19,14.4852814 L19,19 L14.4852814,19 L11.5857864,21.8994949 L8.6862915,19 L5,19 L5,15.3137085 L1.6862915,12 L5,8.6862915 Z M12,15 C13.6568542,15 15,13.6568542 15,12 C15,10.3431458 13.6568542,9 12,9 C10.3431458,9 9,10.3431458 9,12 C9,13.6568542 10.3431458,15 12,15 Z" fill="#000000" />
                                                            </g>
                                                        </svg><!--end::Svg Icon--></span><span class="menu-text">Setting</span><i class="menu-arrow"></i></a>
                                                    <div class="menu-submenu "><i class="menu-arrow"></i>
                                                        <ul class="menu-subnav">

                                                            <li class="menu-item  menu-item-parent" aria-haspopup="true">
                                                                <span class="menu-link"><span class="menu-text">Setting</span></span>
                                                            </li>
                                                            <?php if ($_SESSION['menu'] == 'admin'): ?>
                                                                <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/setting/admin/" class="menu-link ">
                                                                    <?php else: ?>
                                                                <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/setting/admin/" class="menu-link ">
                                                                    <?php endif; ?>
                                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                    <span class="menu-text">Admin</span></a>
                                                                </li>
                                                                <?php if ($_SESSION['menu'] == 'user'): ?>
                                                                    <li class="menu-item menu-item-active" aria-haspopup="true"><a href="<?= BASE_URL ?>pages/setting/user/" class="menu-link ">
                                                                        <?php else: ?>
                                                                    <li class="menu-item " aria-haspopup="true"><a href="<?= BASE_URL ?>pages/setting/user/" class="menu-link ">
                                                                        <?php endif; ?>
                                                                        <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                        <span class="menu-text">User</span></a>
                                                                    </li>
                                                        </ul>
                                                    </div>
                                                </li>


                                            <?php endif; ?>


                        </ul>
                        <!--end::Menu Nav-->
                    </div>
                    <!--end::Menu Container-->
                </div>
                <!--end::Aside Menu-->
            </div>
            <!--end::Aside-->