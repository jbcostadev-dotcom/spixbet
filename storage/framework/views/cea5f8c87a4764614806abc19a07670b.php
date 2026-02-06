<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH /home/u717556091/domains/scriptifyaz.cloud/public_html/vendor/filament/forms/src/../resources/views/components/grid.blade.php ENDPATH**/ ?>