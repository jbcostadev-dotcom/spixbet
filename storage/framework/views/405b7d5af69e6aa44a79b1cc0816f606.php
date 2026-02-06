<?php
    $sidebar = static::getResource()::sidebar($this->record);
    
    $sidebarWidths = config('filament-page-with-sidebar.sidebar_width') ?? [
        'sm' => 12,
        'md' => 3,
        'lg' => 3,
        'xl' => 3,
        '2xl' => 3,
    ];
?>

<div class="mt-8">
    <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
        <div class="col-[--col-span-default]
                        sm:col-[--col-span-sm]
                        md:col-[--col-span-md]
                        lg:col-[--col-span-lg]
                        xl:col-[--col-span-xl]
                        2xl:col-[--col-span-2xl]
                        rounded"
            style="--col-span-default: span 12;
                        --col-span-sm: span <?php echo e($sidebarWidths['sm'] ?? 12); ?>;
                        --col-span-md: span <?php echo e($sidebarWidths['md'] ?? 3); ?>;
                        --col-span-lg: span <?php echo e($sidebarWidths['lg'] ?? 3); ?>;
                        --col-span-xl: span <?php echo e($sidebarWidths['xl'] ?? 3); ?>;
                        --col-span-2xl: span <?php echo e($sidebarWidths['2xl'] ?? 3); ?>;">
            <div class="">
                <div class="flex items-center rtl:space-x-reverse">
                    <!--[if BLOCK]><![endif]--><?php if($sidebar->getTitle() != null || $sidebar->getDescription() != null): ?>
                        <div class="w-full">
                            <?php if($sidebar->getTitle() != null): ?>
                                <h3 class="text-base font-medium text-slate-700 dark:text-white truncate block">
                                    <?php echo e($sidebar->getTitle()); ?>

                                </h3>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                            <!--[if BLOCK]><![endif]--><?php if($sidebar->getDescription()): ?>
                                <p class="text-xs text-gray-400 flex items-center gap-x-1">
                                    <?php echo e($sidebar->getDescription()); ?>


                                    <!--[if BLOCK]><![endif]--><?php if($sidebar->getDescriptionCopyable()): ?>
                                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['xOn:click.prevent' => '
                                            window.navigator.clipboard.writeText(\''.e($sidebar->getDescription()).'\');
                                            $tooltip(\'Copied to clipboard\', { timeout: 1500 })
                                        ','icon' => 'heroicon-o-clipboard-document','class' => 'h-4 w-4 cursor-pointer hover:text-gray-700 text-gray-400 dark:text-gray-500 dark:hover:text-gray-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click.prevent' => '
                                            window.navigator.clipboard.writeText(\''.e($sidebar->getDescription()).'\');
                                            $tooltip(\'Copied to clipboard\', { timeout: 1500 })
                                        ','icon' => 'heroicon-o-clipboard-document','class' => 'h-4 w-4 cursor-pointer hover:text-gray-700 text-gray-400 dark:text-gray-500 dark:hover:text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
                <ul class="<?php if($sidebar->getTitle() != null || $sidebar->getDescription() != null): ?> mt-4 <?php endif; ?> space-y-2 font-inter font-medium" wire:ignore>
                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sidebar->getNavigationItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal0d2b503a4560d3bcdbd2a259e83ce75f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d2b503a4560d3bcdbd2a259e83ce75f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-page-with-sidebar::components.group','data' => ['collapsible' => $group->isCollapsible(),'icon' => $group->getIcon(),'items' => $group->getItems(),'label' => $group->getLabel()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-page-with-sidebar::group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group->isCollapsible()),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group->getIcon()),'items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group->getItems()),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($group->getLabel())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d2b503a4560d3bcdbd2a259e83ce75f)): ?>
<?php $attributes = $__attributesOriginal0d2b503a4560d3bcdbd2a259e83ce75f; ?>
<?php unset($__attributesOriginal0d2b503a4560d3bcdbd2a259e83ce75f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d2b503a4560d3bcdbd2a259e83ce75f)): ?>
<?php $component = $__componentOriginal0d2b503a4560d3bcdbd2a259e83ce75f; ?>
<?php unset($__componentOriginal0d2b503a4560d3bcdbd2a259e83ce75f); ?>
<?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                </ul>
            </div>
        </div>

        <div class="col-[--col-span-default]
                        sm:col-[--col-span-sm]
                        md:col-[--col-span-md]
                        lg:col-[--col-span-lg]
                        xl:col-[--col-span-xl]
                        2xl:col-[--col-span-2xl]
                        -mt-8"
            style="--col-span-default: span 12;
                        --col-span-sm: span <?php echo e(12 - ($sidebarWidths['sm'] ?? 12)); ?>;
                        --col-span-md: span <?php echo e(12 - ($sidebarWidths['md'] ?? 3)); ?>;
                        --col-span-lg: span <?php echo e(12 - ($sidebarWidths['lg'] ?? 3)); ?>;
                        --col-span-xl: span <?php echo e(12 - ($sidebarWidths['xl'] ?? 3)); ?>;
                        --col-span-2xl: span <?php echo e(12 - ($sidebarWidths['2xl'] ?? 3)); ?>; margin-top: -2em;">
            <?php echo e($slot); ?>

        </div>
    </div>
</div>
<?php /**PATH /Users/juliokrepski/Repositorios/cassino/resources/views/vendor/filament-page-with-sidebar/components/page.blade.php ENDPATH**/ ?>