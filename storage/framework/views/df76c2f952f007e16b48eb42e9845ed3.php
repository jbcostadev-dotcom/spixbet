<!--[if BLOCK]><![endif]--><?php if(!empty(\Helper::getSetting())): ?>
    <div>
        <?php if(!empty(\Helper::getSetting()['software_logo_white']) && is_string(\Helper::getSetting()['software_logo_white'])): ?>
            <img src="<?php echo e(asset('storage/'. \Helper::getSetting()['software_logo_white'])); ?>" alt="" class="show-in-dark h-8">
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <?php if(!empty(\Helper::getSetting()['software_logo_black']) && is_string(\Helper::getSetting()['software_logo_black'])): ?>
            <img src="<?php echo e(asset('storage/'. \Helper::getSetting()['software_logo_black'])); ?>" alt="" class="show-in-light h-8">
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH /home/u712311877/domains/lightcoral-pelican-181329.hostingersite.com/public_html/resources/views/filament/components/logo.blade.php ENDPATH**/ ?>