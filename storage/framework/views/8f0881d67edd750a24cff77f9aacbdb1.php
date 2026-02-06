<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'active' => false,
    'activeIcon' => null,
    'badge' => null,
    'badgeColor' => null,
    'grouped' => false,
    'last' => false,
    'first' => false,
    'icon' => null,
    'shouldOpenUrlInNewTab' => false,
    'isWireNavigate' => false,
    'url',
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'active' => false,
    'activeIcon' => null,
    'badge' => null,
    'badgeColor' => null,
    'grouped' => false,
    'last' => false,
    'first' => false,
    'icon' => null,
    'shouldOpenUrlInNewTab' => false,
    'isWireNavigate' => false,
    'url',
]); ?>
<?php foreach (array_filter(([
    'active' => false,
    'activeIcon' => null,
    'badge' => null,
    'badgeColor' => null,
    'grouped' => false,
    'last' => false,
    'first' => false,
    'icon' => null,
    'shouldOpenUrlInNewTab' => false,
    'isWireNavigate' => false,
    'url',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<li
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'fi-sidebar-item',
        'fi-active fi-sidebar-item-active' => $active,
    ]); ?>"
>
    <a
        href="<?php echo e($url); ?>"
        <?php if($shouldOpenUrlInNewTab): ?>
            target="_blank"
        <?php elseif($isWireNavigate): ?>
            wire:navigate
        <?php endif; ?>
        x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
        <?php if(filament()->isSidebarCollapsibleOnDesktop()): ?>
            x-data="{ tooltip: false }"
            x-effect="
                tooltip = $store.sidebar.isOpen
                    ? false
                    : {
                          content: <?php echo \Illuminate\Support\Js::from($slot->toHtml())->toHtml() ?>,
                          placement: document.dir === 'rtl' ? 'left' : 'right',
                          theme: $store.theme,
                      }
            "
            x-tooltip.html="tooltip"
        <?php endif; ?>
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'fi-sidebar-item-button relative flex items-center justify-center gap-x-3 rounded-lg px-2 py-2 text-sm outline-none transition duration-75 hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-white/5 dark:focus:bg-white/5',
            'bg-gray-100 dark:bg-white/5' => $active,
        ]); ?>"
    >
        <!--[if BLOCK]><![endif]--><?php if(filled($icon)): ?>
            <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => ($active && $activeIcon) ? $activeIcon : $icon,'class' => \Illuminate\Support\Arr::toCssClasses([
                    'fi-sidebar-item-icon h-6 w-6',
                    'text-gray-400 dark:text-gray-500' => ! $active,
                    'text-primary-600 dark:text-primary-400' => $active,
                ])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($active && $activeIcon) ? $activeIcon : $icon),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(\Illuminate\Support\Arr::toCssClasses([
                    'fi-sidebar-item-icon h-6 w-6',
                    'text-gray-400 dark:text-gray-500' => ! $active,
                    'text-primary-600 dark:text-primary-400' => $active,
                ]))]); ?>
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
        <?php elseif($grouped): ?>
            <div
                class="fi-sidebar-item-grouped-border relative flex h-6 w-6 items-center justify-center"
            >
                <!--[if BLOCK]><![endif]--><?php if(! $first): ?>
                    <div
                        class="absolute -top-1/2 bottom-1/2 w-px bg-gray-300 dark:bg-gray-600"
                    ></div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <!--[if BLOCK]><![endif]--><?php if(! $last): ?>
                    <div
                        class="absolute -bottom-1/2 top-1/2 w-px bg-gray-300 dark:bg-gray-600"
                    ></div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                <div
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'relative h-1.5 w-1.5 rounded-full',
                        'bg-gray-400 dark:bg-gray-500' => ! $active,
                        'bg-primary-600 dark:bg-primary-400' => $active,
                    ]); ?>"
                ></div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <span
            <?php if(filament()->isSidebarCollapsibleOnDesktop()): ?>
                x-show="true"
                x-transition:enter="lg:transition lg:delay-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            <?php endif; ?>
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'fi-sidebar-item-label flex-1 truncate',
                'text-gray-700 dark:text-gray-200' => ! $active,
                'text-primary-600 dark:text-primary-400' => $active,
                'font-semibold' => ! $grouped,
                'font-medium' => $grouped,
            ]); ?>"
        >
            <?php echo e($slot); ?>

        </span>

        <!--[if BLOCK]><![endif]--><?php if(filled($badge)): ?>
            <span
                <?php if(filament()->isSidebarCollapsibleOnDesktop()): ?>
                    x-show="true"
                    x-transition:enter="lg:transition lg:delay-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                <?php endif; ?>
            >
                <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => $badgeColor]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($badgeColor)]); ?>
                    <?php echo e($badge); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
            </span>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </a>
</li>
<?php /**PATH /home/u450093867/domains/bazeus.com.br/public_html/resources/views/vendor/filament-page-with-sidebar/components/item.blade.php ENDPATH**/ ?>