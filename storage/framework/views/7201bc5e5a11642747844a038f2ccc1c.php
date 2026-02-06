<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <form wire:submit.prevent="submit">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = [
                // Temas Originais
                'pourple'    => ['name' => 'Roxo',           'image' => 'pourple.png'],
                'black'      => ['name' => 'Preto',          'image' => 'black.png'],
                'blue'       => ['name' => 'Azul',           'image' => 'blue.png'],
                'red'        => ['name' => 'Vermelho',       'image' => 'red.png'],
                'pink'       => ['name' => 'Rosa',           'image' => 'pink.png'],
                'limon'      => ['name' => 'Limão',          'image' => 'limon.png'],
                'brown'      => ['name' => 'Marrom',         'image' => 'brown.png'],
                'skyblue'    => ['name' => 'Azul Celeste',   'image' => 'skyblue.png'],
                'whiteblue'  => ['name' => 'Azul e Branco',  'image' => 'whiteblue.png'],
                'greendark'  => ['name' => 'Verde Escuro',   'image' => 'greendark.png'],
                'orange'     => ['name' => 'Laranja',        'image' => 'orange.png'],
                'green'      => ['name' => 'Verde',          'image' => 'green.png'],
                'yellow'     => ['name' => 'Amarelo',        'image' => 'yellow.png'],
                'greenwhite' => ['name' => 'Verde e Branco', 'image' => 'greenwhite.png'],
                'gray'       => ['name' => 'Cinza',          'image' => 'gray.png'],

                // Temas já existentes (Novos Temas anteriores)
                'violet'     => ['name' => 'Violeta',        'image' => 'violet.png'],
                'aqua'       => ['name' => 'Azul Água',      'image' => 'aqua.png'],
                'magenta'    => ['name' => 'Magenta',        'image' => 'magenta.png'],
                'teal'       => ['name' => 'Verde-Azulado',  'image' => 'teal.png'],
                'indigo'     => ['name' => 'Índigo',         'image' => 'indigo.png'],
                'coral'      => ['name' => 'Coral',          'image' => 'coral.png'],
                'navy'       => ['name' => 'Azul Marinho',   'image' => 'navy.png'],
                'mint'       => ['name' => 'Menta',          'image' => 'mint.png'],
                'crimson'    => ['name' => 'Carmesim',       'image' => 'crimson.png'],
                'sapphire'   => ['name' => 'Safira',         'image' => 'sapphire.png'],
                'olive'      => ['name' => 'Verde Oliva',    'image' => 'olive.png'],
                'copper'     => ['name' => 'Cobre',          'image' => 'copper.png'],
                'ruby'       => ['name' => 'Rubi',           'image' => 'ruby.png'],
                'amethyst'   => ['name' => 'Ametista',       'image' => 'amethyst.png'],
                'peach'      => ['name' => 'Pêssego',        'image' => 'peach.png'],

                // 15 Novos Temas Criados (mistura de cores com alto contraste para os textos)
                'fierycontrast'    => ['name' => 'Contraste Flamejante',  'image' => 'fierycontrast.png'],
                'oceansunrise'     => ['name' => 'Amanhecer Oceânico',     'image' => 'oceansunrise.png'],
                'forestglow'       => ['name' => 'Brilho da Floresta',     'image' => 'forestglow.png'],
                'electricsky'      => ['name' => 'Céu Elétrico',           'image' => 'electricsky.png'],
                'urbanchic'        => ['name' => 'Chique Urbano',          'image' => 'urbanchic.png'],
                'sunsetdream'      => ['name' => 'Sonho do Entardecer',     'image' => 'sunsetdream.png'],
                'tropicalvibe'     => ['name' => 'Vibração Tropical',      'image' => 'tropicalvibe.png'],
                'midnightaurora'   => ['name' => 'Aurora da Meia-Noite',     'image' => 'midnightaurora.png'],
                'vibrantcitrus'    => ['name' => 'Cítrico Vibrante',       'image' => 'vibrantcitrus.png'],
                'royalelegance'    => ['name' => 'Elegância Real',         'image' => 'royalelegance.png'],
                'rusticcharm'      => ['name' => 'Charme Rústico',         'image' => 'rusticcharm.png'],
                'icecrystal'       => ['name' => 'Cristal de Gelo',        'image' => 'icecrystal.png'],
                'modernmonochrome' => ['name' => 'Monocromático Moderno',  'image' => 'modernmonochrome.png'],
                'sunnybreeze'      => ['name' => 'Brisa Ensolarada',       'image' => 'sunnybreeze.png'],
                'radiantbloom'     => ['name' => 'Florescimento Radiante', 'image' => 'radiantbloom.png'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $theme): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border p-4 rounded text-center">
                    <h3 class="font-semibold text-lg mb-2"><?php echo e($theme['name']); ?></h3>
                    <img src="<?php echo e(asset('storage/themes/' . $theme['image'])); ?>" alt="<?php echo e($theme['name']); ?>" class="mx-auto my-2 h-20 w-20 object-cover rounded-md">
                    <div class="mt-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="radio"
                                name="selectedTheme"
                                value="<?php echo e($key); ?>"
                                wire:model="selectedTheme"
                                class="form-radio"
                            />
                            <span class="ml-2">Selecionar</span>
                        </label>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <div class="mt-4">
            <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?>Aplicar Tema <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /www/wwwroot/demo05.whitehayon.site/resources/views/filament/pages/select-theme.blade.php ENDPATH**/ ?>