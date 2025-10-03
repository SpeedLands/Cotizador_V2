<?php 
// Esta vista es llamada por MenuCell::renderRootItems()
// Utiliza clases de Tailwind para estilizar radios/checkboxes
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?php foreach ($rootItems as $item): ?>
        <label class="block cursor-pointer">
            <input type="<?= esc($item['tipo_ui']) ?>" 
                   name="menu_selection[<?= esc($item['id_item']) ?>]" 
                   value="<?= esc($item['id_item']) ?>" 
                   class="peer sr-only" 
                   data-item-type="<?= esc($item['tipo_ui']) ?>"
                   data-item-id="<?= esc($item['id_item']) ?>">
            
            <div class="p-4 border-2 border-gray-200 rounded-lg shadow-sm hover:border-indigo-400 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition duration-150">
                <h3 class="font-semibold text-lg text-gray-800"><?= esc($item['nombre_item']) ?></h3>
                <p class="text-sm text-gray-500"><?= esc($item['descripcion']) ?></p>
                <?php if ($item['precio_unitario'] > 0): ?>
                    <span class="text-xs font-medium text-indigo-500">$<?= esc(number_format($item['precio_unitario'], 2)) ?></span>
                <?php endif; ?>
            </div>
        </label>
    <?php endforeach; ?>
</div>