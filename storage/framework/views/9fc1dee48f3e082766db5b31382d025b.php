<?php if (isset($component)) { $__componentOriginal264637b95f4dc75c69c90ee3b9ac6a00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal264637b95f4dc75c69c90ee3b9ac6a00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-page-with-sidebar::components.page','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-page-with-sidebar::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make($this->getIncludedSidebarView(), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal264637b95f4dc75c69c90ee3b9ac6a00)): ?>
<?php $attributes = $__attributesOriginal264637b95f4dc75c69c90ee3b9ac6a00; ?>
<?php unset($__attributesOriginal264637b95f4dc75c69c90ee3b9ac6a00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal264637b95f4dc75c69c90ee3b9ac6a00)): ?>
<?php $component = $__componentOriginal264637b95f4dc75c69c90ee3b9ac6a00; ?>
<?php unset($__componentOriginal264637b95f4dc75c69c90ee3b9ac6a00); ?>
<?php endif; ?><?php /**PATH /home/u712311877/domains/lightcoral-pelican-181329.hostingersite.com/public_html/resources/views/vendor/filament-page-with-sidebar/proxy.blade.php ENDPATH**/ ?>