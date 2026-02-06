<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">

    <meta name=mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black-translucent>
    <meta http-equiv=X-UA-Compatible content="ie=edge">
    <meta http-equiv=Content-Security-Policy content=upgrade-insecure-requests>

    <?php $setting = \Helper::getSetting() ?>
    <?php if(!empty($setting['software_favicon'])): ?>
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('/storage/' . $setting['software_favicon'])); ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/fontawesome.min.css')); ?>">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700&family=Roboto+Condensed:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100&display=swap"
        rel="stylesheet">
    <title><?php echo e(env('APP_NAME')); ?></title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <?php $custom = \Helper::getCustom() ?>
    <style>
        body {
            font-family: <?php echo e($custom['font_family_default'] ?? "'Roboto Condensed', sans-serif"); ?>;
        }

        :root {
            --ci-primary-color: <?php echo e($custom['primary_color']); ?>;
            --ci-primary-opacity-color: <?php echo e($custom['primary_opacity_color']); ?>;
            --ci-secundary-color: <?php echo e($custom['secundary_color']); ?>;
            --ci-gray-dark: <?php echo e($custom['gray_dark_color']); ?>;
            --ci-gray-light: <?php echo e($custom['gray_light_color']); ?>;
            --ci-gray-medium: <?php echo e($custom['gray_medium_color']); ?>;
            --ci-gray-over: <?php echo e($custom['gray_over_color']); ?>;
            --title-color: <?php echo e($custom['title_color']); ?>;
            --text-color: <?php echo e($custom['text_color']); ?>;
            --value-color-jackpot: <?php echo e($custom['value_color_jackpot']); ?>;
            --value-color-navtop: <?php echo e($custom['value_wallet_navtop']); ?>;
            --bonus-color-dep: <?php echo e($custom['bonus_color_dep']); ?>;
            --sub-text-color: <?php echo e($custom['sub_text_color']); ?>;
            --text-sub-color: <?php echo e($custom['text_sub_color']); ?>;
            --back-sub-color: <?php echo e($custom['back_sub_color']); ?>;
            --item-sub-color: <?php echo e($custom['item_sub_color']); ?>;
            --title-sub-color: <?php echo e($custom['title_sub_color']); ?>;
            --placeholder-color: <?php echo e($custom['placeholder_color']); ?>;
            --placeholder-background: <?php echo e($custom['placeholder_background']); ?>;
            --text-color-footer: <?php echo e($custom['text_color_footer']); ?>;
            --opacity-bottom-nav: <?php echo e($custom['opacity_bottom_nav']); ?>;
            --opacity-categories: <?php echo e($custom['opacity_categories']); ?>;
            --card-transaction: <?php echo e($custom['card_transaction']); ?>;
            --values-deposit: <?php echo e($custom['colors_deposit_value']); ?>;
            --colors-players: <?php echo e($custom['color_players']); ?>;
            --standard-color: #1C1E22;
            --shadow-color: #111415;
            --page-shadow: linear-gradient(to right, #111415, rgba(17, 20, 21, 0));
            --autofill-color: #f5f6f7;
            --yellow-color: #FFBF39;
            --yellow-dark-color: #d7a026;
            --border-radius: <?php echo e($custom['border_radius']); ?>;
            --tw-border-spacing-x: 0;
            --tw-border-spacing-y: 0;
            --tw-translate-x: 0;
            --tw-translate-y: 0;
            --tw-rotate: 0;
            --tw-skew-x: 0;
            --tw-skew-y: 0;
            --tw-scale-x: 1;
            --tw-scale-y: 1;
            --tw-scroll-snap-strictness: proximity;
            --tw-ring-offset-width: 0px;
            --tw-ring-offset-color: #fff;
            --tw-ring-color: rgba(59, 130, 246, .5);
            --tw-ring-offset-shadow: 0 0 #0000;
            --tw-ring-shadow: 0 0 #0000;
            --tw-shadow: 0 0 #0000;
            --tw-shadow-colored: 0 0 #0000;

            --botao-deposito-text-nav: <?php echo e($custom['botao_deposito_text_nav']); ?>;
            --botao-login-text-nav: <?php echo e($custom['botao_login_text_nav']); ?>;
            --botao-registro-text-nav: <?php echo e($custom['botao_registro_text_nav']); ?>;
            --botao-login-text-modal: <?php echo e($custom['botao_login_text_modal']); ?>;
            --botao-registro-text-modal: <?php echo e($custom['botao_registro_text_modal']); ?>;

            --border-registro-nav: <?php echo e($custom['botao_registro_border_nav']); ?>;
            --border-login-nav: <?php echo e($custom['botao_login_border_nav']); ?>;
            --border-deposito-nav: <?php echo e($custom['botao_deposito_border_nav']); ?>;
            --text-opacity: <?php echo e($custom['text_opacity']); ?>;
            --background-color-category: <?php echo e($custom['background_color_category']); ?>;
            --background-color-jackpot: <?php echo e($custom['background_color_jackpot']); ?>;


            --botao-deposito-text-dep: <?php echo e($custom['botao_deposito_text_dep']); ?>;
            --botao-deposito-background-dep: <?php echo e($custom['botao_deposito_background_dep']); ?>;
            --botao-deposito-border-dep: <?php echo e($custom['botao_deposito_border_dep']); ?>;

            --botao-deposito-text-saq: <?php echo e($custom['botao_deposito_text_saq']); ?>;
            --botao-deposito-background-saq: <?php echo e($custom['botao_deposito_background_saq']); ?>;
            --botao-deposito-border-saq: <?php echo e($custom['botao_deposito_border_saq']); ?>;



            /* Adicionando unidades apropriadas */
            --invert-config: <?php echo e($custom['invert_percentage']); ?>%;
            --sepia-config: <?php echo e($custom['sepia_percentage']); ?>%;
            --saturate-config: <?php echo e($custom['saturate_percentage']); ?>%;
            --hue-rotate-config: <?php echo e($custom['hue_rotate_deg']); ?>deg;
            --brightness-config: <?php echo e($custom['brightness_percentage']); ?>%;
            --contrast-config: <?php echo e($custom['contrast_percentage']); ?>%;

            --input-primary: <?php echo e($custom['input_primary']); ?>;
            --input-primary-dark: <?php echo e($custom['input_primary_dark']); ?>;

            --carousel-banners: <?php echo e($custom['carousel_banners']); ?>;
            --carousel-banners-dark: <?php echo e($custom['carousel_banners_dark']); ?>;


            --sidebar-color: <?php echo e($custom['sidebar_color']); ?> !important;
            --sidebar-color-dark: <?php echo e($custom['sidebar_color_dark']); ?> !important;

            --color-bt-1: <?php echo e($custom['color_bt_1']); ?> !important;
            --color-bt-1-dark: <?php echo e($custom['color_bt_1_dark']); ?> !important;

            --color-bt-2: <?php echo e($custom['color_bt_2']); ?> !important;
            --color-bt-2-dark: <?php echo e($custom['color_bt_2_dark']); ?> !important;

            --color-bt-3: <?php echo e($custom['color_bt_3']); ?> !important;
            --color-bt-3-dark: <?php echo e($custom['color_bt_3_dark']); ?> !important;

            --color-bt-4: <?php echo e($custom['color_bt_4']); ?> !important;
            --color-bt-4-dark: <?php echo e($custom['color_bt_4_dark']); ?> !important;

            --background-bottom-navigation: <?php echo e($custom['background_bottom_navigation']); ?> !important;
            --background-bottom-navigation-dark: <?php echo e($custom['background_bottom_navigation_dark']); ?> !important;

            --background-color-cassino: <?php echo e($custom['background_color_cassino']); ?> !important;
            --background-color-cassino-dark: <?php echo e($custom['background_color_cassino_dark']); ?> !important;

            --background-itens-selected: <?php echo e($custom['Border_bottons_and_selected']); ?> !important;
            --background-itens-selected-dark: <?php echo e($custom['Border_bottons_and_selected_dark']); ?> !important;

            --borders-and-dividers-colors: <?php echo e($custom['borders_and_dividers_colors']); ?> !important;
            --borders-and-dividers-colors-dark: <?php echo e($custom['borders_and_dividers_colors_dark']); ?> !important;

            --search-back: <?php echo e($custom['search_back']); ?> !important;
            --search-back-dark: <?php echo e($custom['search_back_dark']); ?> !important;


            --botao-deposito-background-nav: <?php echo e($custom['botao_deposito_background_nav']); ?> !important;
            --botao-deposito-background-nav-dark: <?php echo e($custom['botao_deposito_background_nav_dark']); ?> !important;

            --botao-login-background-nav: <?php echo e($custom['botao_login_background_nav']); ?> !important;
            --botao-login-background-nav-dark: <?php echo e($custom['botao_login_background_nav_dark']); ?> !important;

            --botao-registro-background-nav: <?php echo e($custom['botao_registro_background_nav']); ?> !important;
            --botao-registro-background-nav-dark: <?php echo e($custom['botao_registro_background_nav_dark']); ?> !important;

            --botao-login-background-modal: <?php echo e($custom['botao_login_background_modal']); ?> !important;
            --botao-login-background-modal-dark: <?php echo e($custom['botao_login_background_modal_dark']); ?> !important;

            --botao-registro-background-modal: <?php echo e($custom['botao_registro_background_modal']); ?> !important;
            --botao-registro-background-modal-dark: <?php echo e($custom['botao_registro_background_modal_dark']); ?> !important;


            --navtop-color <?php echo e($custom['navtop_color']); ?>;
            --navtop-color-dark: <?php echo e($custom['navtop_color_dark']); ?>;


            --side-menu <?php echo e($custom['side_menu']); ?>;
            --side-menu-dark: <?php echo e($custom['side_menu_dark']); ?>;

            --footer-color <?php echo e($custom['footer_color']); ?>;
            --footer-color-dark: <?php echo e($custom['footer_color_dark']); ?>;

            --card-color <?php echo e($custom['card_color']); ?>;
            --card-color-dark: <?php echo e($custom['card_color_dark']); ?>;

            --transaction-background: <?php echo e($custom['transaction_item_background']); ?>;

            --navb_icon_color: <?php echo e($custom['navb_icon_color']); ?>;



        }

        .navtop-color {
            background-color: <?php echo e($custom['navtop_color']); ?> !important;
        }

        :is(.dark .navtop-color) {
            background-color: <?php echo e($custom['navtop_color_dark']); ?> !important;
        }

        .background-bottom-navigation {
            background-color: <?php echo e($custom['background_bottom_navigation']); ?> !important;
        }

        :is(.dark .background-bottom-navigation) {
            background-color: <?php echo e($custom['background_bottom_navigation_dark']); ?> !important;
        }

        .bg-base {
            background-color: <?php echo e($custom['background_base']); ?>;
        }

        :is(.dark .bg-base) {
            background-color: <?php echo e($custom['background_base_dark']); ?>;
        }

        .background-color-cassino {
            background-color: <?php echo e($custom['background_color_cassino']); ?>;
        }

        :is(.dark .background-color-cassino) {
            background-color: <?php echo e($custom['background_color_cassino_dark']); ?>;
        }

        .background-itens-selected {
            background-color: <?php echo e($custom['Border_bottons_and_selected']); ?>;
        }

        :is(.dark .background-itens-selected) {
            background-color: <?php echo e($custom['Border_bottons_and_selected_dark']); ?>;
        }
    </style>

    <?php if(!empty($custom['custom_css'])): ?>
        <style>
            <?php echo $custom['custom_css']; ?>

        </style>
    <?php endif; ?>

    <?php if(!empty($custom['custom_header'])): ?>
        <?php echo $custom['custom_header']; ?>

    <?php endif; ?>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>



    <script>
        //         document.addEventListener('contextmenu', function(e) {
        //            e.preventDefault();
        //        });
        //

        //        document.addEventListener('keydown', function(e) {
        //           if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') || (e.ctrlKey && e.key === 'U')) {
        //                e.preventDefault();
        //           }
        //       });

        //
    </script>

</head>

<body color-theme="dark" class="bg-base text-gray-800 dark:text-gray-300 ">
    <div id="infinitysoft"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.0.0/datepicker.min.js"></script>
    <script>
        window.Livewire?.on('copiado', (texto) => {
            navigator.clipboard.writeText(texto).then(() => {
                Livewire.emit('copiado');
            });
        });

        window._token = '<?php echo e(csrf_token()); ?>';
        //if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        if (localStorage.getItem('color-theme') === 'light') {
            document.documentElement.classList.remove('dark')
            document.documentElement.classList.add('light');
        } else {
            document.documentElement.classList.remove('light')
            document.documentElement.classList.add('dark')
        }
    </script>

    <?php if(!empty($custom['custom_js'])): ?>
        <script>
            <?php echo $custom['custom_js']; ?>

        </script>
    <?php endif; ?>

    <?php if(!empty($custom['custom_body'])): ?>
        <?php echo $custom['custom_body']; ?>

    <?php endif; ?>

    <?php if(!empty($custom)): ?>
        <script>
            const custom = <?php echo json_encode($custom); ?>;
        </script>
    <?php endif; ?>

</body>

</html>
<?php /**PATH /home/u109567241/domains/easypg.online/public_html/resources/views/layouts/app.blade.php ENDPATH**/ ?>