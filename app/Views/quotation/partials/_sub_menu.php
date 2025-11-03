<?php 
// app/Views/quotation/partials/_sub_menu.php
// Se asume que $options ahora incluye 'has_children'
?>

<!-- Título para el JavaScript de la modal -->
<div id="modal-content-title" data-parent-name="<?= esc($parentName ?? 'Selección de Opciones') ?>" class="hidden"></div>

<h3 class="text-xl font-semibold mb-3 text-indigo-700 border-b pb-2">
    <?= esc($parentName ?? 'Opciones Detalladas') ?>
</h3>

<div class="space-y-3">
    <?php foreach ($options as $option): ?>
        <?php 
            // Usar la variable has_children pasada por el controlador
            $isNavigationalCheckbox = $option['has_children'];
            $isFinalItem = !$isNavigationalCheckbox;
        ?>

        <div class="p-3 border rounded-lg bg-white">
            
            <?php if ($isFinalItem): ?>
                <!-- ÍTEM FINAL (Checkbox, Radio, o Quantity) -->
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center w-full">
                        <?php if ($option['tipo_ui'] === 'quantity'): ?>
                            <!-- Tipo Quantity -->
                            <label for="qty_<?= esc($option['id_item']) ?>" class="text-gray-700 mr-4 w-4/5"><?= esc($option['nombre_item']) ?> ($<?= esc(number_format($option['precio_unitario'], 2)) ?>)</label>
                            <input type="number" 
                                   name="menu_selection[<?= esc($option['id_item']) ?>]" 
                                   id="qty_<?= esc($option['id_item']) ?>" 
                                   min="0" 
                                   value="0" 
                                   data-parent-id="<?= esc($option['parent_id']) ?>"
                                   class="w-1/5 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-center">
                        <?php else: ?>
                            <!-- Tipo Checkbox/Radio -->
                            <input type="<?= esc($option['tipo_ui']) ?>" 
                                   name="menu_selection[<?= esc($option['id_item']) ?>]" 
                                   id="opt_<?= esc($option['id_item']) ?>" 
                                   value="<?= esc($option['id_item']) ?>"
                                   data-parent-id="<?= esc($option['parent_id']) ?>"
                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <label for="opt_<?= esc($option['id_item']) ?>" class="ml-3 text-gray-700 cursor-pointer">
                                <?= esc($option['nombre_item']) ?> 
                                <span class="text-sm text-gray-500">(<?= esc($option['descripcion']) ?>)</span>
                            </label>
                        <?php endif; ?>
                    </div>
                    <?php if ($option['precio_unitario'] > 0 && $option['tipo_ui'] !== 'quantity'): ?>
                        <span class="font-medium text-indigo-500">$<?= esc(number_format($option['precio_unitario'], 2)) ?></span>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- ÍTEM DE NAVEGACIÓN (Botón para Nivel 3) -->
                <button type="button" 
                        class="modal-nav-item w-full text-left flex justify-between items-center font-semibold text-gray-800 hover:text-blue-600 transition"
                        data-item-id="<?= esc($option['id_item']) ?>">
                    <span><?= esc($option['nombre_item']) ?></span>
                    <span class="text-sm text-gray-500">(<?= esc($option['descripcion']) ?>)</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>