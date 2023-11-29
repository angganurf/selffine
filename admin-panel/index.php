<?php
$page  = 'dashboard';
$pages = scandir('admin-panel/pages');

unset($pages[0]);
unset($pages[1]);


$admin = new Admin();

if (!empty($_GET['page'])) {
    $page = $admin::secure($_GET['page']);
    $admin->currp = $page;
}

if (!in_array($page, $pages)) {
    header("Location: $site_url/404");
    exit();
}


if (in_array($page, $pages)) {
   $page_content = $admin->loadPage("$page/content");
}
if (empty($page_content)) {
    header("Location: " . Wo_SeoLink('index.php?link1=admin-cp'));
    exit();
}






$notify_count = $db->where('recipient_id',0)->where('admin',1)->where('seen',0)->getValue(T_NOTIF,'COUNT(*)');
$notifications = $db->where('recipient_id',0)->where('admin',1)->where('seen',0)->orderBy('id','DESC')->get(T_NOTIF);
$old_notifications = $db->where('recipient_id',0)->where('admin',1)->where('seen',0,'!=')->orderBy('id','DESC')->get(T_NOTIF,5);
$mode = 'day';
if (!empty($_COOKIE['mode']) && $_COOKIE['mode'] == 'night') {
    $mode = 'night';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Panel | <?php echo $config['site_name']; ?></title>
    <link rel="icon" href="<?php echo $config['site_url']; ?>/media/img/icon.<?php echo $config['favicon_extension']; ?>" type="image/png">


    <!-- Main css -->
    <link rel="stylesheet" href="<?php echo pxp_acp_link('vendors/bundle.css');?>" type="text/css">

    <!-- Google font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Daterangepicker -->
    <link rel="stylesheet" href="<?php echo(pxp_acp_link('vendors/datepicker/daterangepicker.css')) ?>" type="text/css">

    <!-- DataTable -->
    <link rel="stylesheet" href="<?php echo(pxp_acp_link('vendors/dataTable/datatables.min.css')) ?>" type="text/css">

<!-- App css -->
    <link rel="stylesheet" href="<?php echo(pxp_acp_link('assets/css/app.css')) ?>" type="text/css">
    <!-- Main scripts -->
<script src="<?php echo(pxp_acp_link('vendors/bundle.js')) ?>"></script>

    <!-- Apex chart -->
    <script src="<?php echo(pxp_acp_link('vendors/charts/apex/apexcharts.min.js')) ?>"></script>

    <!-- Daterangepicker -->
    <script src="<?php echo(pxp_acp_link('vendors/datepicker/daterangepicker.js')) ?>"></script>

    <!-- DataTable -->
    <script src="<?php echo(pxp_acp_link('vendors/dataTable/datatables.min.js')) ?>"></script>

    <!-- Dashboard scripts -->
    <script src="<?php echo(pxp_acp_link('assets/js/examples/pages/dashboard.js')) ?>"></script>
    <script src="<?php echo pxp_acp_link('vendors/charts/chartjs/chart.min.js'); ?>"></script>

<!-- App scripts -->

<link href="<?php echo pxp_acp_link('vendors/sweetalert/sweetalert.css'); ?>" rel="stylesheet" />
<script src="<?php echo pxp_acp_link('assets/js/admin.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo(pxp_acp_link('vendors/select2/css/select2.min.css')) ?>" type="text/css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
<script src="<?php echo pxp_acp_link('vendors/tinymce/js/tinymce/tinymce.min.js'); ?>"></script>
<script src="<?php echo pxp_acp_link('vendors/bootstrap-tagsinput/src/bootstrap-tagsinput.js'); ?>"></script>
<link href="<?php echo pxp_acp_link('vendors/bootstrap-tagsinput/src/bootstrap-tagsinput.css'); ?>" rel="stylesheet" />
<?php if ($page == 'custom-code') { ?>
<script src="<?php echo pxp_acp_link('vendors/codemirror-5.30.0/lib/codemirror.js'); ?>"></script>
<script src="<?php echo pxp_acp_link('vendors/codemirror-5.30.0/mode/css/css.js'); ?>"></script>
<script src="<?php echo pxp_acp_link('vendors/codemirror-5.30.0/mode/javascript/javascript.js'); ?>"></script>
<link rel="stylesheet" href="<?php echo pxp_acp_link('vendors/codemirror-5.30.0/lib/codemirror.css'); ?>">
<?php } ?>


    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
        <!-- Css -->
        <link rel="stylesheet" href="<?php echo(pxp_acp_link('vendors/lightbox/magnific-popup.css')) ?>" type="text/css">

        <!-- Javascript -->
        <script src="<?php echo(pxp_acp_link('vendors/lightbox/jquery.magnific-popup.min.js')) ?>"></script>
        <script src="<?php echo(pxp_acp_link('vendors/charts/justgage/raphael-2.1.4.min.js')) ?>"></script>
        <script src="<?php echo(pxp_acp_link('vendors/charts/justgage/justgage.js')) ?>"></script>
    <script src="<?php echo pxp_acp_link('assets/js/jquery.form.min.js'); ?>"></script>
    <script>
        function acpajax_url(path) {
            return '<?php echo($config['site_url']); ?>/aj/admin/' + path;
        }
    </script>
    <style>
        body {background-color: #222;}
        .btn.btn-primary, a.btn[href="#next"], a.btn[href="#previous"] {color: #fff !important;background: #ea759a;border-color: #ea759a;}
        .btn.btn-primary:not(:disabled):not(.disabled):hover, a.btn[href="#next"]:not(:disabled):not(.disabled):hover, a.btn[href="#previous"]:not(:disabled):not(.disabled):hover, .btn.btn-primary:not(:disabled):not(.disabled):focus, a.btn[href="#next"]:not(:disabled):not(.disabled):focus, a.btn[href="#previous"]:not(:disabled):not(.disabled):focus, .btn.btn-primary:not(:disabled):not(.disabled):active, a.btn[href="#next"]:not(:disabled):not(.disabled):active, a.btn[href="#previous"]:not(:disabled):not(.disabled):active, .btn.btn-primary:not(:disabled):not(.disabled).active, a.btn[href="#next"]:not(:disabled):not(.disabled).active, a.btn[href="#previous"]:not(:disabled):not(.disabled).active {background: #ed6f97;border-color: #ed6f97;}
        body.dark .navigation .navigation-menu-body ul li a.active, .breadcrumb .breadcrumb-item.active, body.dark .breadcrumb li.breadcrumb-item.active, body.dark .navigation .navigation-menu-body ul li a.active .nav-link-icon {color: #ea759a !important;}
        .card form .form-check-inline input:checked {background-color: #ea759a;}
        .card form .form-check-inline input:checked + label::before, .card form .form-check-inline input:active + label::before {border-color: #ea759a;}
        .card form .form-check-inline label::after {background-color: #ea759a;}
        .select2-container--default.select2-container--focus .select2-selection--multiple {border: 2px solid #ea759a !important;}
    </style>
</head>
<script type="text/javascript">

    $(function() {

        $(document).on('click', 'a[data-ajax]', function(e) {
            $(document).off('click', '.ranges ul li');
            $(document).off('click', '.applyBtn');
            e.preventDefault();
            if (($(this)[0].hasAttribute("data-sent") && $(this).attr('data-sent') == '0') || !$(this)[0].hasAttribute("data-sent")) {
                if (!$(this)[0].hasAttribute("data-sent") && !$(this).hasClass('waves-effect')) {
                    $('.navigation-menu-body').find('a').removeClass('active');
                    $(this).addClass('active');
                }
                window.history.pushState({state:'new'},'', $(this).attr('href'));
                $(".barloading").css("display","block");
                if ($(this)[0].hasAttribute("data-sent")) {
                    $(this).attr('data-sent', "1");
                }
                var url = $(this).attr('data-ajax');
                // url = url.substring('?path='.length);
                // url = url.replace("&", "?");
                // console.log(url)

                // location.href = "<?php echo(pxp_acp_link()) ?>"+url;

                $.post("<?php echo($config['site_url']) ?>/admin_load.php" + url, {url:url}, function (data) {
                    $(".barloading").css("display","none");
                    if ($('#redirect_link')[0].hasAttribute("data-sent")) {
                        $('#redirect_link').attr('data-sent', "0");
                    }
                    json_data = JSON.parse($(data).filter('#json-data').val());
                    $('.content').html(data);
                    setTimeout(function () {
                      $(".content").getNiceScroll().resize()
                    }, 500);
                    $(".content").animate({ scrollTop: 0 }, "slow");
                });
            }
        });
        $(window).on("popstate", function (e) {
            location.reload();
        });
    });
</script>
<body <?php echo ($mode == 'night' ? 'class="dark"' : ''); ?>>
    <div class="barloading"></div>
    <a id="redirect_link" href="" data-ajax="" data-sent="0"></a>
    <div class="colors"> <!-- To use theme colors with Javascript -->
        <div class="bg-primary"></div>
        <div class="bg-primary-bright"></div>
        <div class="bg-secondary"></div>
        <div class="bg-secondary-bright"></div>
        <div class="bg-info"></div>
        <div class="bg-info-bright"></div>
        <div class="bg-success"></div>
        <div class="bg-success-bright"></div>
        <div class="bg-danger"></div>
        <div class="bg-danger-bright"></div>
        <div class="bg-warning"></div>
        <div class="bg-warning-bright"></div>
    </div>
<!-- Preloader -->
<div class="preloader">
    <div class="preloader-icon"></div>
    <span>Loading...</span>
</div>
<!-- ./ Preloader -->

<!-- Sidebar group -->
<div class="sidebar-group">

</div>
<!-- ./ Sidebar group -->

<!-- Layout wrapper -->
<div class="layout-wrapper">

    <!-- Header -->
    <div class="header d-print-none">
        <div class="header-container">
            <div class="header-left">
                <div class="navigation-toggler">
                    <a href="#" data-action="navigation-toggler">
                        <i data-feather="menu"></i>
                    </a>
                </div>

                <div class="header-logo">
                    <a href="<?php echo $config['site_url'] ?>">
                        <img class="logo" src="<?php echo $config['site_url']; ?>/media/img/light-logo.<?php echo($config['logo_extension']) ?>" alt="logo">
                    </a>
                </div>
            </div>

            <div class="header-body">
                <div class="header-body-left">
                    <ul class="navbar-nav">
                        <li class="nav-item mr-3">
                            <div class="header-search-form">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <button class="btn">
                                            <i data-feather="search"></i>
                                        </button>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Search"  onkeyup="searchInFiles($(this).val())">
                                    <div class="pt_admin_hdr_srch_reslts" id="search_for_bar"></div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="header-body-right">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link <?php if ($notify_count > 0) { ?> nav-link-notify<?php } ?>" title="Notifications" data-toggle="dropdown">
                                <i data-feather="bell"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-big">
                                <div
                                    class="border-bottom px-4 py-3 text-center d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Notifications</h5>
                                    <?php if ($notify_count > 0) { ?>
                                    <small class="opacity-7"><?php echo $notify_count; ?>   unread notifications</small>
                                    <?php } ?>
                                </div>
                                <div class="dropdown-scroll">
                                    <ul class="list-group list-group-flush">
                                        <?php if ($notify_count > 0) { ?>
                                            <li class="px-4 py-2 text-center small text-muted bg-light">Unread Notifications</li>
                                            <?php if (!empty($notifications)) {
                                                    foreach ($notifications as $key => $notify) {
                                                        $page_ = '';
                                                        $text = '';
                                                        if ($notify->type == 'bank') {
                                                            $page_ = 'bank-receipts';
                                                            $text = 'You have a new bank payment awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'verify') {
                                                            $page_ = 'manage-verification-reqeusts';
                                                            $text = 'You have a new verification requests awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'refund') {
                                                            $page_ = 'pro-refund';
                                                            $text = 'You have a new refund requests awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'with') {
                                                            $page_ = 'payment-reqeuests';
                                                            $text = 'You have a new withdrawal requests awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'report') {
                                                            $page_ = 'manage-reports';
                                                            $text = 'You have a new reports awaiting your approval';
                                                        }
                                                ?>
                                            <li class="px-4 py-3 list-group-item">
                                                <a href="<?php echo pxp_acp_link($page_); ?>" class="d-flex align-items-center hide-show-toggler">
                                                    <div class="flex-shrink-0">
                                                        <figure class="avatar mr-3">
                                                            <span
                                                                class="avatar-title bg-info-bright text-info rounded-circle">
                                                                <?php if ($notify->type == 'bank') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                                                <?php }elseif ($notify->type == 'verify') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2196f3" d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z"></path></svg>
                                                                <?php }elseif ($notify->type == 'refund') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                                                <?php }elseif ($notify->type == 'with') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                                                <?php }elseif ($notify->type == 'report') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                                <?php } ?>

                                                            </span>
                                                        </figure>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 line-height-20 d-flex justify-content-between">
                                                            <?php echo $text; ?>
                                                        </p>
                                                        <span class="text-muted small"><?php echo time2str($notify->time); ?></span>
                                                    </div>
                                                </a>
                                            </li>
                                            <?php } } ?>
                                        <?php } ?>
                                        <?php if ($notify_count == 0 && !empty($old_notifications)) { ?>
                                            <li class="px-4 py-2 text-center small text-muted bg-light">Old Notifications</li>
                                            <?php
                                                    foreach ($old_notifications as $key => $notify) {
                                                        $page_ = '';
                                                        $text = '';
                                                        if ($notify->type == 'bank') {
                                                            $page_ = 'bank-receipts';
                                                            $text = 'You have a new bank payment awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'verify') {
                                                            $page_ = 'verification-requests';
                                                            $text = 'You have a new verification requests awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'refund') {
                                                            $page_ = 'pro-refund';
                                                            $text = 'You have a new refund requests awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'with') {
                                                            $page_ = 'payment-requests';
                                                            $text = 'You have a new withdrawal requests awaiting your approval';
                                                        }
                                                        elseif ($notify->type == 'report') {
                                                            $page_ = 'manage-reports';
                                                            $text = 'You have a new reports awaiting your approval';
                                                        }
                                                ?>
                                            <li class="px-4 py-3 list-group-item">
                                                <a href="<?php echo pxp_acp_link($page_); ?>" class="d-flex align-items-center hide-show-toggler">
                                                    <div class="flex-shrink-0">
                                                        <figure class="avatar mr-3">
                                                            <span class="avatar-title bg-secondary-bright text-secondary rounded-circle">
                                                                <?php if ($notify->type == 'bank') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                                                <?php }elseif ($notify->type == 'verify') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#2196f3" d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z"></path></svg>
                                                                <?php }elseif ($notify->type == 'refund') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                                                <?php }elseif ($notify->type == 'with') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                                                <?php }elseif ($notify->type == 'report') { ?>
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-flag"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                                <?php } ?>
                                                            </span>
                                                        </figure>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <p class="mb-0 line-height-20 d-flex justify-content-between">
                                                            <?php echo $text; ?>
                                                        </p>
                                                        <span class="text-muted small"><?php echo time2str($notify->time); ?></span>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php } } ?>
                                    </ul>
                                </div>
                                <?php if ($notify_count > 0) { ?>
                                <div class="px-4 py-3 text-right border-top">
                                    <ul class="list-inline small">
                                        <li class="list-inline-item mb-0">
                                            <a href="javascript:void(0)" onclick="ReadNotify()">Mark All Read</a>
                                        </li>
                                    </ul>
                                </div>
                                <?php } ?>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" title="User menu" data-toggle="dropdown">
                                <figure class="avatar avatar-sm">
                                    <img src="<?php echo $me['avatar']; ?>"
                                         class="rounded-circle"
                                         alt="avatar">
                                </figure>
                                <span class="ml-2 d-sm-inline d-none"><?php echo $me['name']; ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-big">
                                <div class="text-center py-4">
                                    <figure class="avatar avatar-lg mb-3 border-0">
                                        <img src="<?php echo $me['avatar']; ?>"
                                             class="rounded-circle" alt="image">
                                    </figure>
                                    <h5 class="text-center"><?php echo $me['name']; ?></h5>
                                    <div class="mb-3 small text-center text-muted"><?php echo $me['email']; ?></div>
                                    <a href="<?php echo $me['url']; ?>" class="btn btn-outline-light btn-rounded">View Profile</a>
                                </div>
                                <div class="list-group">
                                    <a href="<?php echo $config['site_url'].'/signout'; ?>" class="list-group-item text-danger">Sign Out!</a>
                                    <?php if ($mode == 'night') { ?>
                                        <a href="javascript:void(0)" class="list-group-item admin_mode" onclick="ChangeMode('day')">
                                            <span id="night-mode-text">Day mode</span>
                                            <svg class="feather feather-moon float-right" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                                        </a>
                                    <?php }else{ ?>
                                        <a href="javascript:void(0)" class="list-group-item admin_mode" onclick="ChangeMode('night')">
                                            <span id="night-mode-text">Night mode</span>
                                            <svg class="feather feather-moon float-right" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                                        </a>
                                    <?php } ?>

                                </div>
                            </div>
                        </li>
                    </ul>
                </div>


            </div>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item header-toggler">
                    <a href="#" class="nav-link">
                        <i data-feather="arrow-down"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- ./ Header -->

    <!-- Content wrapper -->
    <div class="content-wrapper">
        <!-- begin::navigation -->
        <div class="navigation">
            <div class="navigation-header">
                <span>Navigation</span>
                <a href="#">
                    <i class="ti-close"></i>
                </a>
            </div>
            <div class="navigation-menu-body">
                <ul>
                    <li>
                        <a class="<?php echo $admin->activeMenu('dashboard'); ?>" href="<?php echo pxp_acp_link('dashboard');?>" data-ajax="?path=dashboard">
                            <span class="nav-link-icon">
                                <i class="material-icons">dashboard</i>
                            </span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a class="<?php echo ($page == 'general-settings' || $page == 'site-settings' || $page == 'email-settings' || $page == 'social-login' || $page == 's3' || $page == 'chat-settings' || $page == 'playtube_support' || $page == 'payment-settings' || $page == 'live') ? 'class="open"' : ''; ?>" href="javascript:void(0);">
                            <span class="nav-link-icon">
                                <i class="material-icons">settings</i>
                            </span>
                            <span>Settings</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('general-settings'); ?>" class="<?php echo $admin->activeMenu('general-settings'); ?>" data-ajax="?path=general-settings">General Configuration</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('site-settings'); ?>" class="<?php echo $admin->activeMenu('site-settings'); ?>" data-ajax="?path=site-settings">Website Information</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('email-settings'); ?>" class="<?php echo $admin->activeMenu('email-settings'); ?>" data-ajax="?path=email-settings">E-mail & SMS Settings</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('chat-settings'); ?>" class="<?php echo $admin->activeMenu('chat-settings'); ?>" data-ajax="?path=chat-settings">Chat & Video/Audio</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('social-login'); ?>" class="<?php echo $admin->activeMenu('social-login'); ?>" data-ajax="?path=social-login">Social Login Settings</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('s3'); ?>" class="<?php echo $admin->activeMenu('s3'); ?>" data-ajax="?path=s3">File Upload Import Configuration</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('playtube_support'); ?>" class="<?php echo $admin->activeMenu('playtube_support'); ?>" data-ajax="?path=playtube_support">PlayTube Support</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('live'); ?>" class="<?php echo $admin->activeMenu('live'); ?>" data-ajax="?path=live">Setup Live Streaming</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'manage-langs' || $page == 'add-language') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">language</i>
                            </span>
                            <span>Languages</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-langs'); ?>" class="<?php echo $admin->activeMenu('manage-langs'); ?><?php echo $admin->activeMenu('edit-language'); ?>" data-ajax="?path=manage-langs">
                                    Manage languages
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('add-language'); ?>" class="<?php echo $admin->activeMenu('add-language'); ?>" data-ajax="?path=add-language">
                                    Add language & keys
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'manage-users' || $page == 'manage-verification-requests' || $page == 'manage-business-requests' || $page == 'manage_fundings' || $page == 'blacklist' || $page == 'affiliates-settings') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">account_circle</i>
                            </span>
                            <span>Users</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-users'); ?>" class="<?php echo $admin->activeMenu('manage-users'); ?>" data-ajax="?path=manage-users">
                                    Manage Users
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-verification-requests'); ?>" class="<?php echo $admin->activeMenu('manage-verification-requests'); ?>" data-ajax="?path=manage-verification-requests">
                                    Manage Verification Requests
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-business-requests'); ?>" class="<?php echo $admin->activeMenu('manage-business-requests'); ?>" data-ajax="?path=manage-business-requests">
                                    Manage Business Requests
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('manage_fundings'); ?>" class="<?php echo $admin->activeMenu('manage_fundings'); ?>" data-ajax="?path=manage_fundings">
                                    Manage Fundings
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('blacklist'); ?>" class="<?php echo $admin->activeMenu('blacklist'); ?>" data-ajax="?path=blacklist">
                                    Black List
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('affiliates-settings'); ?>" class="<?php echo $admin->activeMenu('affiliates-settings'); ?>" data-ajax="?path=affiliates-settings">
                                    Affiliates Settings
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'ads' || $page == 'payment-settings' || $page == 'ads-settings' || $page == 'manage-ads' || $page == 'payment-requests' || $page == 'bank-receipts' || $page == 'earnings') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">attach_money</i>
                            </span>
                            <span>Payments & Ads</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('payment-settings'); ?>" class="<?php echo $admin->activeMenu('payment-settings'); ?>" data-ajax="?path=payment-settings">Payment Configuration</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('ads-settings'); ?>" class="<?php echo $admin->activeMenu('ads-settings'); ?>" data-ajax="?path=ads-settings">
                                    Advertisements Settings
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-ads'); ?>" class="<?php echo $admin->activeMenu('manage-ads'); ?>" data-ajax="?path=manage-ads">
                                    Manage User Advertisements
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('payment-requests'); ?>" class="<?php echo $admin->activeMenu('payment-requests'); ?>" data-ajax="?path=payment-requests">
                                    Payment Requests
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('ads'); ?>" class="<?php echo $admin->activeMenu('ads'); ?>" data-ajax="?path=ads">Manage Site Advertisements</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('bank-receipts'); ?>" class="<?php echo $admin->activeMenu('bank-receipts'); ?>" data-ajax="?path=bank-receipts">Manage bank receipts</a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('earnings'); ?>" class="<?php echo $admin->activeMenu('earnings'); ?>" data-ajax="?path=earnings">Earnings</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'pro-settings' || $page == 'manage-pro-users') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">stars</i>
                            </span>
                            <span>Pro System</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('pro-settings'); ?>" class="<?php echo $admin->activeMenu('pro-settings'); ?>" data-ajax="?path=pro-settings">
                                    Pro System Settings
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-pro-users'); ?>" class="<?php echo $admin->activeMenu('manage-pro-users'); ?>" data-ajax="?path=manage-pro-users">
                                    Manage Pro Users
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'manage-posts' || $page == 'manage-store-categories' || $page == 'manage-store-items' || $page == 'store-revenue' || $page == 'manage-articles' || $page == 'manage-blog-categories' || $page == 'add-new-article') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">view_agenda</i>
                            </span>
                            <span>Manage Features</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-posts'); ?>" class="<?php echo $admin->activeMenu('manage-posts'); ?>" data-ajax="?path=manage-posts">
                                    Manage posts
                                </a>
                            </li>
                            <li <?php echo ($page == 'manage-store-categories' || $page == 'manage-store-items' || $page == 'store-revenue') ? 'class="open"' : ''; ?>>
                                <a href="javascript:void(0);">Image Store</a>
                                <ul class="ml-menu">
                                    <li>
                                        <a href="<?php echo pxp_acp_link('manage-store-categories'); ?>" class="<?php echo $admin->activeMenu('manage-store-categories'); ?>" data-ajax="?path=manage-store-categories">
                                            Manage Store Categories
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo pxp_acp_link('manage-store-items'); ?>" class="<?php echo $admin->activeMenu('manage-store-items'); ?>" data-ajax="?path=manage-store-items">
                                            Manage Store Items
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo pxp_acp_link('store-revenue'); ?>" class="<?php echo $admin->activeMenu('store-revenue'); ?>" data-ajax="?path=store-revenue">
                                            Store Revenue
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li <?php echo ($page == 'manage-articles' || $page == 'manage-blog-categories' || $page == 'add-new-article') ? 'class="open"' : ''; ?>>
                                <a href="javascript:void(0);">Blogs</a>
                                <ul class="ml-menu">
                                    <li>
                                        <a href="<?php echo pxp_acp_link('manage-articles'); ?>" class="<?php echo $admin->activeMenu('manage-articles'); ?><?php echo $admin->activeMenu('edit-article'); ?>" data-ajax="?path=manage-articles">
                                            Manage articles
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo pxp_acp_link('manage-blog-categories'); ?>" class="<?php echo $admin->activeMenu('manage-blog-categories'); ?>" data-ajax="?path=manage-blog-categories">
                                            Manage blog categories
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo pxp_acp_link('add-new-article'); ?>" class="<?php echo $admin->activeMenu('add-new-article'); ?>" data-ajax="?path=add-new-article">
                                            Add new article
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'themes' || $page == 'manage-site-design') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">color_lens</i>
                            </span>
                            <span>Design</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('themes'); ?>" class="<?php echo $admin->activeMenu('themes'); ?>" data-ajax="?path=themes">
                                    Themes
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-site-design'); ?>" class="<?php echo $admin->activeMenu('manage-site-design'); ?>" data-ajax="?path=manage-site-design">
                                    Change Site Design
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'profile-reports' || $page == 'post-reports' || $page == 'fund-reports') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">flag</i>
                            </span>
                            <span>Reports</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('profile-reports'); ?>" class="<?php echo $admin->activeMenu('profile-reports'); ?>" data-ajax="?path=profile-reports">
                                    Profile reports
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('post-reports'); ?>" class="<?php echo $admin->activeMenu('post-reports'); ?>" data-ajax="?path=post-reports">
                                    Post Reports
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('fund-reports'); ?>" class="<?php echo $admin->activeMenu('fund-reports'); ?>" data-ajax="?path=fund-reports">
                                    Funding Request Reports
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'edit-terms-pages' || $page == 'manage-pages' || $page == 'create-sitemap') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">web</i>
                            </span>
                            <span>Pages</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-pages'); ?>" class="<?php echo $admin->activeMenu('manage-pages'); ?>" data-ajax="?path=manage-pages">
                                    Manage Terms Pages
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('create-sitemap'); ?>" class="<?php echo $admin->activeMenu('create-sitemap'); ?>" data-ajax="?path=create-sitemap">
                                    Generate SiteMap
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="<?php echo ($page == 'push-notifications-system' || $page == 'manage-api-access-keys') ? 'class="open"' : ''; ?>">
                            <span class="nav-link-icon">
                                <i class="material-icons">compare_arrows</i>
                            </span>
                            <span>API Settings</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo pxp_acp_link('manage-api-access-keys'); ?>" class="<?php echo $admin->activeMenu('manage-api-access-keys'); ?>" data-ajax="?path=manage-api-access-keys">
                                    Manage API Server Key
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo pxp_acp_link('push-notifications-system'); ?>" class="<?php echo $admin->activeMenu('push-notifications-system'); ?>" data-ajax="?path=push-notifications-system">
                                    Push Notifications System
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo pxp_acp_link('create-backup'); ?>" class="<?php echo $admin->activeMenu('create-backup'); ?>" data-ajax="?path=create-backup">
                            <span class="nav-link-icon">
                                <i class="material-icons">backup</i>
                            </span>
                            <span>Backup SQL & Files</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo pxp_acp_link('changelogs'); ?>" class="<?php echo $admin->activeMenu('changelogs'); ?>" data-ajax="?path=changelogs">
                            <span class="nav-link-icon">
                                <i class="material-icons">update</i>
                            </span>
                            <span>Changelogs</span>
                        </a>
                    </li>
                    <li>
                        <a href="http://docs.pixelphotoscript.com" target="_blank" class=" waves-effect waves-block">
                            <span class="nav-link-icon">
                                <i class="material-icons">more_vert</i>
                            </span>
                            <span>FAQs &amp; Docs</span>
                        </a>
                    </li>


                    <a class="pow_link" href="https://bit.ly/2R2jrcz" target="_blank">
                        <p>Powered by</p>
                        <img src="<?php echo $config['site_url']; ?>/media/img/light-logo.<?php echo($config['logo_extension']) ?>">
                        <b class="badge">v<?php echo $config['version'];?></b>
                    </a>
                </ul>
            </div>
        </div>
        <!-- end::navigation -->

        <!-- Content body -->
        <div class="content-body">
            <!-- Content -->
            <div class="content ">
                <?php echo $page_content; ?>
            </div>
            <!-- ./ Content -->

        </div>
        <!-- ./ Content body -->
    </div>
    <!-- ./ Content wrapper -->
</div>
<!-- ./ Layout wrapper -->

<script src="<?php echo pxp_acp_link('vendors/sweetalert/sweetalert.min.js'); ?>"></script>
<script src="<?php echo(pxp_acp_link('vendors/select2/js/select2.min.js')) ?>"></script>
    <script src="<?php echo(pxp_acp_link('assets/js/examples/select2.js')) ?>"></script>
    <script src="<?php echo(pxp_acp_link('assets/js/app.min.js')) ?>"></script>
    <script type="text/javascript">
        function ChangeMode(mode) {
            if (mode == 'day') {
                $('body').removeClass('dark');
                $('.admin_mode').html('<span id="night-mode-text">Night mode</span><svg class="feather feather-moon float-right" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>');
                $('.admin_mode').attr('onclick', "ChangeMode('night')");
            }
            else{
                $('body').addClass('dark');
                $('.admin_mode').html('<span id="night-mode-text">Day mode</span><svg class="feather feather-moon float-right" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>');
                $('.admin_mode').attr('onclick', "ChangeMode('day')");
            }
            hash_id = $('#hash_id').val();
            $.get("<?php echo($config['site_url']) ?>/aj/main/change_mode" ,{hash_id: hash_id}, function(data) {});
        }
        $(document).ready(function(){
            $('[data-toggle="popover"]').popover();
            var hash = $('.main_session').val();
              $.ajaxSetup({
                data: {
                    hash: hash
                },
                cache: false
              });
        });
        $('body').on('click', function (e) {
            $('.dropdown-animating').removeClass('show');
            $('.dropdown-menu').removeClass('show');
        });
        function searchInFiles(keyword) {
            if (keyword.length > 2) {
                $.post(acpajax_url('search_in_pages'), {keyword: keyword}, function(data, textStatus, xhr) {
                    if (data.html != '') {
                        $('#search_for_bar').html(data.html)
                    }
                    else{
                        $('#search_for_bar').html('')
                    }
                });
            }
            else{
                $('#search_for_bar').html('')
            }
        }
        jQuery(document).ready(function($) {
            jQuery.fn.highlight = function (str, className) {
                if (str != '') {
                    var aTags = document.getElementsByTagName("h2");
                    var bTags = document.getElementsByTagName("label");
                    var cTags = document.getElementsByTagName("h3");
                    var dTags = document.getElementsByTagName("h6");
                    var searchText = str.toLowerCase();

                    if (aTags.length > 0) {
                        for (var i = 0; i < aTags.length; i++) {
                            var tag_text = aTags[i].textContent.toLowerCase();
                            if (tag_text.indexOf(searchText) != -1) {
                                $(aTags[i]).addClass(className)
                            }
                        }
                    }

                    if (bTags.length > 0) {
                        for (var i = 0; i < bTags.length; i++) {
                            var tag_text = bTags[i].textContent.toLowerCase();
                            if (tag_text.indexOf(searchText) != -1) {
                                $(bTags[i]).addClass(className)
                            }
                        }
                    }

                    if (cTags.length > 0) {
                        for (var i = 0; i < cTags.length; i++) {
                            var tag_text = cTags[i].textContent.toLowerCase();
                            if (tag_text.indexOf(searchText) != -1) {
                                $(cTags[i]).addClass(className)
                            }
                        }
                    }

                    if (dTags.length > 0) {
                        for (var i = 0; i < dTags.length; i++) {
                            var tag_text = dTags[i].textContent.toLowerCase();
                            if (tag_text.indexOf(searchText) != -1) {
                                $(dTags[i]).addClass(className)
                            }
                        }
                    }
                }
            };
            jQuery.fn.highlight("<?php echo (!empty($_GET['highlight']) ? $_GET['highlight'] : '') ?>",'highlight_text');
            <?php if ($config['exchange_update'] < time()) { ?>
            $.get(acpajax_url('exchange'),{});
            <?php } ?>
        });
        $(document).on('click', '#search_for_bar a', function(event) {
            event.preventDefault();
            location.href = $(this).attr('href');
        });
        function ReadNotify() {
            hash_id = $('#hash_id').val();
            $.get(acpajax_url('ReadNotify'),{});
            location.reload();
        }
        function delay(callback, ms) {
          var timer = 0;
          return function() {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
              callback.apply(context, args);
            }, ms || 0);
          };
        }
        let container_fluid_height = $('.container-fluid').height();
        setInterval(function () {
            if (container_fluid_height != $('.container-fluid').height()) {
                container_fluid_height = $('.container-fluid').height();
                $(".content").getNiceScroll().resize();
            }
        },500);
    </script>

</body>
</html>
