<div class="wrap ts-settings-wrap">
    <h2><?php echo esc_html($title); ?></h2>
    <p class="ts-settings-feedback">
        <?php if ($isApiConnected): ?>
            <img src="data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='96px' height='96px' viewBox='0 0 96 96' enable-background='new 0 0 96 96' xml:space='preserve'%3E%3Cg%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' fill='%236BBE66' d='M48,0c26.51,0,48,21.49,48,48S74.51,96,48,96S0,74.51,0,48 S21.49,0,48,0L48,0z M26.764,49.277c0.644-3.734,4.906-5.813,8.269-3.79c0.305,0.182,0.596,0.398,0.867,0.646l0.026,0.025 c1.509,1.446,3.2,2.951,4.876,4.443l1.438,1.291l17.063-17.898c1.019-1.067,1.764-1.757,3.293-2.101 c5.235-1.155,8.916,5.244,5.206,9.155L46.536,63.366c-2.003,2.137-5.583,2.332-7.736,0.291c-1.234-1.146-2.576-2.312-3.933-3.489 c-2.35-2.042-4.747-4.125-6.701-6.187C26.993,52.809,26.487,50.89,26.764,49.277L26.764,49.277z'/%3E%3C/g%3E%3C/svg%3E" alt="" width="30">
        <?php else: ?>
            <img src="data:image/svg+xml,%3Csvg id='Layer_1' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 122.88 122.88'%3E%3Cdefs%3E%3Cstyle%3E.cls-1%7Bfill:%23f44336;fill-rule:evenodd;%7D%3C/style%3E%3C/defs%3E%3Cpath class='cls-1' d='M61.44,0A61.44,61.44,0,1,1,0,61.44,61.44,61.44,0,0,1,61.44,0ZM74.58,36.8c1.74-1.77,2.83-3.18,5-1l7,7.13c2.29,2.26,2.17,3.58,0,5.69L73.33,61.83,86.08,74.58c1.77,1.74,3.18,2.83,1,5l-7.13,7c-2.26,2.29-3.58,2.17-5.68,0L61.44,73.72,48.63,86.53c-2.1,2.15-3.42,2.27-5.68,0l-7.13-7c-2.2-2.15-.79-3.24,1-5l12.73-12.7L36.35,48.64c-2.15-2.11-2.27-3.43,0-5.69l7-7.13c2.15-2.2,3.24-.79,5,1L61.44,49.94,74.58,36.8Z'/%3E%3C/svg%3E" alt="" width="30">
        <?php endif; ?>
        <strong><?php $isApiConnected ? esc_html_e('API is connected', 'trustswiftly-verification') : esc_html_e('API is not connected', 'trustswiftly-verification') ?></strong>
    </p>
    <?php if (!$isApiConnected): ?>
    <p class="ts-settings-feedback">
        <strong>For help visit: <a href="https://docs.trustswiftly.com/web/wordpress" target="_blank">Trust Swiftly</a></strong>
    </p>
    <?php endif;?>
    <?php if ($isApiConnected && !$unavailableTemplates): ?>
        <p class="ts-settings-feedback">
            <?php if (count($missingOptions) == 0): ?>
                <img src="data:image/svg+xml,%3Csvg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px' width='96px' height='96px' viewBox='0 0 96 96' enable-background='new 0 0 96 96' xml:space='preserve'%3E%3Cg%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' fill='%236BBE66' d='M48,0c26.51,0,48,21.49,48,48S74.51,96,48,96S0,74.51,0,48 S21.49,0,48,0L48,0z M26.764,49.277c0.644-3.734,4.906-5.813,8.269-3.79c0.305,0.182,0.596,0.398,0.867,0.646l0.026,0.025 c1.509,1.446,3.2,2.951,4.876,4.443l1.438,1.291l17.063-17.898c1.019-1.067,1.764-1.757,3.293-2.101 c5.235-1.155,8.916,5.244,5.206,9.155L46.536,63.366c-2.003,2.137-5.583,2.332-7.736,0.291c-1.234-1.146-2.576-2.312-3.933-3.489 c-2.35-2.042-4.747-4.125-6.701-6.187C26.993,52.809,26.487,50.89,26.764,49.277L26.764,49.277z'/%3E%3C/g%3E%3C/svg%3E" alt="" width="30">
                <strong><?php esc_html_e('All Required Options Set', 'trustswiftly-verification'); ?></strong>
            <?php else: ?>
                <img src="data:image/svg+xml,%3Csvg id='Layer_1' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 122.88 122.88'%3E%3Cdefs%3E%3Cstyle%3E.cls-1%7Bfill:%23f44336;fill-rule:evenodd;%7D%3C/style%3E%3C/defs%3E%3Cpath class='cls-1' d='M61.44,0A61.44,61.44,0,1,1,0,61.44,61.44,61.44,0,0,1,61.44,0ZM74.58,36.8c1.74-1.77,2.83-3.18,5-1l7,7.13c2.29,2.26,2.17,3.58,0,5.69L73.33,61.83,86.08,74.58c1.77,1.74,3.18,2.83,1,5l-7.13,7c-2.26,2.29-3.58,2.17-5.68,0L61.44,73.72,48.63,86.53c-2.1,2.15-3.42,2.27-5.68,0l-7.13-7c-2.2-2.15-.79-3.24,1-5l12.73-12.7L36.35,48.64c-2.15-2.11-2.27-3.43,0-5.69l7-7.13c2.15-2.2,3.24-.79,5,1L61.44,49.94,74.58,36.8Z'/%3E%3C/svg%3E" alt="" width="30">
                <strong><?php esc_html_e('Please fill out the following options: ' . implode(', ', $missingOptions), 'trustswiftly-verification'); ?></strong>
            <?php endif; ?>
        </p>
    <?php endif; ?>
    <?php if ($isApiConnected && $unavailableTemplates): ?>
    <p class="ts-settings-feedback">
            <img src="data:image/svg+xml,%3Csvg id='Layer_1' data-name='Layer 1' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 122.88 122.88'%3E%3Cdefs%3E%3Cstyle%3E.cls-1%7Bfill:%23f44336;fill-rule:evenodd;%7D%3C/style%3E%3C/defs%3E%3Cpath class='cls-1' d='M61.44,0A61.44,61.44,0,1,1,0,61.44,61.44,61.44,0,0,1,61.44,0ZM74.58,36.8c1.74-1.77,2.83-3.18,5-1l7,7.13c2.29,2.26,2.17,3.58,0,5.69L73.33,61.83,86.08,74.58c1.77,1.74,3.18,2.83,1,5l-7.13,7c-2.26,2.29-3.58,2.17-5.68,0L61.44,73.72,48.63,86.53c-2.1,2.15-3.42,2.27-5.68,0l-7.13-7c-2.2-2.15-.79-3.24,1-5l12.73-12.7L36.35,48.64c-2.15-2.11-2.27-3.43,0-5.69l7-7.13c2.15-2.2,3.24-.79,5,1L61.44,49.94,74.58,36.8Z'/%3E%3C/svg%3E" alt="" width="30">
            <strong><?php esc_html_e('Please create a template on the trust swiftly ', 'trustswiftly-verification'); ?></strong>
    </p>
    <?php endif; ?>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
            settings_fields('ts_settings_option_group');
            do_settings_sections('ts-settings');
            submit_button();
        ?>
    </form>
    <script type="text/javascript">
        (function($) {
            $(function () {
                wp.codeEditor.initialize($('#custom_css'), cm_settings);
                if ($('#verify_on').val()==='before_checkout')
                {
                    $('#send_verify_link').removeAttr('checked').attr('disabled',true);
                }
                $('#verify_on').on('change',function (){
                    if ($(this).val()==='before_checkout'){
                        $('#send_verify_link').removeAttr('checked').attr('disabled',true);
                    }else{
                        $('#send_verify_link').attr('disabled',false);
                    }
                });
            });
        })(jQuery);
    </script>
</div>