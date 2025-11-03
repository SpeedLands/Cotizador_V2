<?php if (!empty($items)): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($items as $item): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col">
                <?php if (isset($item['tipo_ui']) && $item['tipo_ui'] === 'quantity'): ?>

                    <?php if (!empty($item['imagen_url'])): ?>
                        <img src="<?= base_url($item['imagen_url']) ?>" alt="<?= esc($item['nombre_item']) ?>" class="w-full h-48 object-cover">
                    <?php endif; ?>

                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-lg font-semibold"><?= esc($item['nombre_item']) ?></h3>
                        <p class="text-gray-600 mt-1 text-sm flex-grow"><?= esc($item['descripcion']) ?></p>

                        <?php if ($item['precio_unitario'] > 0): ?>
                            <div class="mt-2 text-lg font-bold text-gray-800">
                                $<?= number_format($item['precio_unitario'], 2) ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 flex items-center justify-end gap-2 simple-quantity-item" data-item-id="<?= $item['id_item'] ?>">
                            <button type="button" class="quantity-btn quantity-decrease-btn">-</button>
                            <input type="number" value="0" min="0" class="quantity-input w-16 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <button type="button" class="quantity-btn quantity-increase-btn">+</button>
                        </div>
                    </div>

                <?php else: // 'checkbox' for complex items ?>

                    <div class="cursor-pointer menu-item-selectable flex flex-col flex-grow"
                         data-id="<?= $item['id_item'] ?>"
                         data-has-children="<?= $item['has_children'] ? 'true' : 'false' ?>">

                        <?php if (!empty($item['imagen_url'])): ?>
                            <img src="<?= base_url($item['imagen_url']) ?>" alt="<?= esc($item['nombre_item']) ?>" class="w-full h-48 object-cover">
                        <?php endif; ?>

                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="text-lg font-semibold"><?= esc($item['nombre_item']) ?></h3>
                            <p class="text-gray-600 mt-1 flex-grow"><?= esc($item['descripcion']) ?></p>

                            <?php if ($item['precio_unitario'] > 0): ?>
                                <div class="mt-2 text-lg font-bold text-gray-800">
                                    $<?= number_format($item['precio_unitario'], 2) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No hay platillos disponibles en esta categoría.</p>
<?php endif; ?>