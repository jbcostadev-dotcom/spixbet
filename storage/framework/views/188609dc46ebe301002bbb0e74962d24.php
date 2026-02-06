<?php if(!empty(\Helper::getSetting())): ?>
    <div>
        <?php if(!empty(\Helper::getSetting()['software_logo_white']) && is_string(\Helper::getSetting()['software_logo_white'])): ?>
            <img src="<?php echo e(asset('storage/'. \Helper::getSetting()['software_logo_white'])); ?>" alt="" class="show-in-dark h-8">
        <?php endif; ?>

        <?php if(!empty(\Helper::getSetting()['software_logo_black']) && is_string(\Helper::getSetting()['software_logo_black'])): ?>
            <img src="<?php echo e(asset('storage/'. \Helper::getSetting()['software_logo_black'])); ?>" alt="" class="show-in-light h-8">
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH /Users/juliokrepski/Repositorios/cassino/resources/views/filament/components/logo.blade.php ENDPATH**/ ?>