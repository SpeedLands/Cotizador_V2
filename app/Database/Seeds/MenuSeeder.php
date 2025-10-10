<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\MenuItemModel;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $model = new MenuItemModel();
        
        // Limpiar la tabla antes de sembrar
        // Desactivar chequeo de claves foráneas temporalmente para permitir truncate
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $model->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // =================================================================
        // ETAPA 1: CATEGORÍAS RAÍZ (Nivel 1)
        // =================================================================
        $rootCategories = [
            [
                'nombre_item' => 'Bebidas', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Desde aguas frescas y refrescos hasta cafés y licuados.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Desayunos y Principales', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Chilaquiles, omelettes, platillos de pollo y otras especialidades.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Combos', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Paquetes completos para un desayuno o comida ideal.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Hamburguesas', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Nuestra selección de hamburguesas de pollo y res.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Tacos y Antojitos', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Taquizas, enchiladas, quesabirrias y más antojitos mexicanos.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Menú Kids', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Platillos pensados para los más pequeños.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Complementos', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Acompañamientos y guarniciones para tus platillos.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
            [
                'nombre_item' => 'Catering y Barras', 
                'parent_id' => null, 
                'tipo_ui' => 'checkbox', 
                'descripcion' => 'Servicios especiales para eventos, como mesas de postres, barras y más.', 
                'precio_unitario' => 0.00, 
                'activo' => 1
            ],
        ];
        $model->insertBatch($rootCategories); // INSERCIÓN 1

        // Obtener IDs de Nivel 1
        $bebidasId              = $model->where('nombre_item', 'Bebidas')->first()['id_item'];
        $desayunosPrincipalesId = $model->where('nombre_item', 'Desayunos y Principales')->first()['id_item'];
        $combosId               = $model->where('nombre_item', 'Combos')->first()['id_item'];
        $hamburguesasId         = $model->where('nombre_item', 'Hamburguesas')->first()['id_item'];
        $tacosAntojitosId       = $model->where('nombre_item', 'Tacos y Antojitos')->first()['id_item'];
        $menuKidsId             = $model->where('nombre_item', 'Menú Kids')->first()['id_item'];
        $complementosId         = $model->where('nombre_item', 'Complementos')->first()['id_item'];
        $cateringBarrasId       = $model->where('nombre_item', 'Catering y Barras')->first()['id_item'];


        // =================================================================
        // ETAPA 2: ÍTEMS INTERMEDIOS (Nivel 2)
        // =================================================================
        $level2Items = [];
        $rawLevel2 = [
            // --- Hijos de 'Bebidas' ---
            ['nombre' => 'Agua de sabor Grande', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 75.00],
            ['nombre' => 'Botella de Agua', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 25.00],
            ['nombre' => 'Café Inagotable', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 35.00],
            ['nombre' => 'Caramel Macchiato Helado', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 65.00],
            ['nombre' => 'Chocomilk litro', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 80.00],
            ['nombre' => 'Jugo de Naranja Litro', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 140.00],
            ['nombre' => 'Licuado Grande', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 85.00],
            ['nombre' => 'Moka Helado', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 65.00],
            ['nombre' => 'Refrescos', 'parent_id' => $bebidasId, 'tipo_ui' => 'quantity', 'precio' => 35.00],

            // --- Hijos de 'Desayunos y Principales' ---
            ['nombre' => 'Bowl de Chilaquiles', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 140.00],
            ['nombre' => 'Waffle Bowl', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 125.00],
            ['nombre' => 'Chilaquiles', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 175.00, 'descripcion' => 'Platillo base. Selecciona para elegir tu salsa y toppings.'], // Padre de Nivel 3
            ['nombre' => 'Cuernitos y fruta de temporada', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 189.00],
            ['nombre' => 'Pan Francés', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 185.00],
            ['nombre' => 'Omelette Bacon Cheese', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 179.00],
            ['nombre' => 'Omelette Ham & Cheese', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 179.00],
            ['nombre' => 'Omelette Vegetariano', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 179.00],
            ['nombre' => 'Hotcakes tradicionales', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 129.00],
            ['nombre' => 'Waffles', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 175.00],
            ['nombre' => 'Platillos de Pollo', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 0.00, 'descripcion' => 'Selecciona una de nuestras especialidades de pollo.'], // Padre de Nivel 3
            ['nombre' => 'Otros Platillos Fuertes', 'parent_id' => $desayunosPrincipalesId, 'tipo_ui' => 'checkbox', 'precio' => 0.00, 'descripcion' => 'Otras opciones de platos fuertes.'], // Padre de Nivel 3

            // --- Hijos de 'Combos' ---
            ['nombre' => 'Combo Chilaquiles y Hotcakes', 'parent_id' => $combosId, 'tipo_ui' => 'checkbox', 'precio' => 185.00],
            ['nombre' => 'Combo Cuernito y Hotcakes', 'parent_id' => $combosId, 'tipo_ui' => 'checkbox', 'precio' => 175.00],
            ['nombre' => 'Combo Omelette y Hotcakes', 'parent_id' => $combosId, 'tipo_ui' => 'checkbox', 'precio' => 175.00],

            // --- Hijos de 'Hamburguesas' ---
            ['nombre' => 'Chikn Buffalo Pollo', 'parent_id' => $hamburguesasId, 'tipo_ui' => 'checkbox', 'precio' => 198.00],
            ['nombre' => 'Chikn Clásica Pollo', 'parent_id' => $hamburguesasId, 'tipo_ui' => 'checkbox', 'precio' => 189.00],
            ['nombre' => 'Clásica de Res', 'parent_id' => $hamburguesasId, 'tipo_ui' => 'checkbox', 'precio' => 198.00],
            ['nombre' => 'Mapo Bacon Burger', 'parent_id' => $hamburguesasId, 'tipo_ui' => 'checkbox', 'precio' => 215.00],
            ['nombre' => 'Mapo Burger', 'parent_id' => $hamburguesasId, 'tipo_ui' => 'checkbox', 'precio' => 198.00],

            // --- Hijos de 'Tacos y Antojitos' ---
            ['nombre' => 'Taquiza/Guisos', 'parent_id' => $tacosAntojitosId, 'tipo_ui' => 'checkbox', 'precio' => 140.00, 'descripcion' => 'Precio base por persona. Selecciona para elegir los guisos.'], // Padre de Nivel 3
            ['nombre' => 'Enchiladas Suizas', 'parent_id' => $tacosAntojitosId, 'tipo_ui' => 'checkbox', 'precio' => 175.00],
            ['nombre' => 'Enchipotladas', 'parent_id' => $tacosAntojitosId, 'tipo_ui' => 'checkbox', 'precio' => 175.00],
            ['nombre' => 'Quesabirrias', 'parent_id' => $tacosAntojitosId, 'tipo_ui' => 'checkbox', 'precio' => 145.00],
            ['nombre' => 'Tacos de Carne Asada', 'parent_id' => $tacosAntojitosId, 'tipo_ui' => 'checkbox', 'precio' => 155.00],
            ['nombre' => 'Tacos Dorados', 'parent_id' => $tacosAntojitosId, 'tipo_ui' => 'checkbox', 'precio' => 150.00],

            // --- Hijos de 'Menú Kids' ---
            ['nombre' => 'French Kids', 'parent_id' => $menuKidsId, 'tipo_ui' => 'quantity', 'precio' => 99.00],
            ['nombre' => 'Hotcakes Kids', 'parent_id' => $menuKidsId, 'tipo_ui' => 'quantity', 'precio' => 99.00],
            ['nombre' => 'Huevito Revuelto', 'parent_id' => $menuKidsId, 'tipo_ui' => 'quantity', 'precio' => 99.00],
            ['nombre' => 'Kidsadillas', 'parent_id' => $menuKidsId, 'tipo_ui' => 'quantity', 'precio' => 99.00],
            ['nombre' => 'Pollito a la Plancha', 'parent_id' => $menuKidsId, 'tipo_ui' => 'quantity', 'precio' => 125.00],
            ['nombre' => 'Takids Dorados', 'parent_id' => $menuKidsId, 'tipo_ui' => 'quantity', 'precio' => 100.00],
            
            // --- Hijos de 'Complementos' ---
            ['nombre' => 'Arroz blanco', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00, 'descripcion' => 'Precio incluido en combos. Se puede cobrar por separado.'],
            ['nombre' => 'Ejotes guisados', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Elote amarillo', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Ensalada', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Ensalada de papa', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Ensalada de zanahoria dulce', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Espagueti verde', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Fideos chinos', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Mac n cheese', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Papas fritas', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Pasta a la boloñesa', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Pasta Alfredo', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Puré de papa', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Verduras al vapor', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Verduras con soya', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],
            ['nombre' => 'Yakimeshi', 'parent_id' => $complementosId, 'tipo_ui' => 'quantity', 'precio' => 0.00],

            // --- Hijos de 'Catering y Barras' ---
            ['nombre' => 'Barra de cafe', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 65.00, 'descripcion' => 'Selecciona para ver opciones de enchufe.'],
            ['nombre' => 'Barra de elote', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 55.00],
            ['nombre' => 'Barra de fruta', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 55.00],
            ['nombre' => 'Coffee Break', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 200.00, 'descripcion' => 'Lleva cafe, galletas y cuernitos de jamón y queso.'],
            ['nombre' => 'Mesa de bocadillos', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 150.00, 'descripcion' => 'Selecciona para ver opciones de bocadillos.'],
            ['nombre' => 'Mesa de charcuteria', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 150.00],
            ['nombre' => 'Mesa de postres', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 150.00, 'descripcion' => 'Selecciona para ver opciones de postres.'],
            ['nombre' => 'Mesa de snacks', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 120.00],
            ['nombre' => 'Tabla de charcuteria 10 persona', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'checkbox', 'precio' => 1300.00],
            ['nombre' => 'Menudo por Litro', 'parent_id' => $cateringBarrasId, 'tipo_ui' => 'quantity', 'precio' => 160.00],
        ];

        foreach ($rawLevel2 as $item) {
            $level2Items[] = [
                'parent_id'       => $item['parent_id'],
                'nombre_item'     => $item['nombre'],
                'tipo_ui'         => $item['tipo_ui'],
                'descripcion'     => $item['descripcion'] ?? '',
                'precio_unitario' => $item['precio'],
                'activo'          => 1,
            ];
        }
        $model->insertBatch($level2Items); // INSERCIÓN 2 (CRÍTICA PARA QUE FUNCIONE LO SIGUIENTE)


        // =================================================================
        // ETAPA 3: ÍTEMS DETALLADOS (Nivel 3 - Hijos de los de Nivel 2)
        // =================================================================
        
        // Ahora sí podemos buscar los IDs porque ya se hizo la INSERCIÓN 2
        $chilaquilesId            = $model->where('nombre_item', 'Chilaquiles')->first()['id_item'];
        $taquizaGuisosId          = $model->where('nombre_item', 'Taquiza/Guisos')->first()['id_item'];
        $platillosPolloId         = $model->where('nombre_item', 'Platillos de Pollo')->first()['id_item'];
        $otrosPlatillosFuertesId  = $model->where('nombre_item', 'Otros Platillos Fuertes')->first()['id_item'];
        $barraCafeId              = $model->where('nombre_item', 'Barra de cafe')->first()['id_item'];
        $mesaBocadillosId         = $model->where('nombre_item', 'Mesa de bocadillos')->first()['id_item'];
        $mesaPostresId            = $model->where('nombre_item', 'Mesa de postres')->first()['id_item'];

        $level3Items = [];

        // --- 3.1 Hijos de 'Chilaquiles' (Salsas y Toppings) ---
        // Las salsas son 'radio' porque se elige una. Los toppings son 'checkbox'.

        $salsas = ['Chipotle', 'Molcajeteada', 'Mole', 'Roja', 'Suiza (Crema)', 'Verde (Agua)'];
        foreach ($salsas as $salsa) {
            $level3Items[] = [
                'parent_id'       => $chilaquilesId,
                'nombre_item'     => 'Salsa: ' . $salsa,
                'tipo_ui'         => 'radio',
                'descripcion'     => 'Elige la salsa para tus chilaquiles.',
                'precio_unitario' => 0.00, // Las salsas no tienen costo extra
                'activo'          => 1,
            ];
        }
        // Toppings
        $level3Items[] = [
            'parent_id' => $chilaquilesId, 'nombre_item' => 'Topping: Chorizo de Múzquiz', 'tipo_ui' => 'checkbox', 
            'precio_unitario' => 30.00, 'activo' => 1, 'descripcion' => 'Añade un extra de sabor.'
        ];
        $level3Items[] = [
            'parent_id' => $chilaquilesId, 'nombre_item' => 'Topping: Pechuga de Pollo', 'tipo_ui' => 'checkbox', 
            'precio_unitario' => 0.00, 'activo' => 1, 'descripcion' => 'Añade un extra de sabor.' // No tenía precio en la lista
        ];
        $level3Items[] = [
            'parent_id' => $chilaquilesId, 'nombre_item' => 'Bistec en Salsa', 'tipo_ui' => 'checkbox',
            'precio_unitario' => 30.00, 'activo' => 1, 'descripcion' => ''
        ];
        $level3Items[] = [
            'parent_id' => $chilaquilesId, 'nombre_item' => 'Chicharrón Prenzado', 'tipo_ui' => 'checkbox',
            'precio_unitario' => 0.00, 'activo' => 1, 'descripcion' => ''
        ];



        // --- 3.2 Hijos de 'Taquiza/Guisos' (Opciones de Guisos) ---
        // Son 'checkbox' porque se pueden elegir varios guisos para una taquiza.
        $guisos = [
            ['nombre' => 'Asado de puerco', 'precio' => 0.00],
            ['nombre' => 'Birria', 'precio' => 30.00],
            ['nombre' => 'Bistec', 'precio' => 0.00],
            ['nombre' => 'Bistec a la mexicana', 'precio' => 0.00],
            ['nombre' => 'Chicharrón prensado', 'precio' => 30.00],
            ['nombre' => 'Chicharrón Tronador', 'precio' => 30.00],
            ['nombre' => 'Discada', 'precio' => 0.00],
            ['nombre' => 'Frijoles con chorizo', 'precio' => 0.00],
            ['nombre' => 'Frijoles naturales', 'precio' => 0.00],
            ['nombre' => 'Mole', 'precio' => 0.00],
            ['nombre' => 'Papas a la mexicana', 'precio' => 0.00],
            ['nombre' => 'Pastor', 'precio' => 0.00],
            ['nombre' => 'Pollo con crema', 'precio' => 0.00],
            ['nombre' => 'Pollo en crema de chipotle', 'precio' => 0.00],
            ['nombre' => 'Queso en salsa', 'precio' => 30.00],
            ['nombre' => 'Rajas con crema', 'precio' => 0.00],
            ['nombre' => 'Barbacoa Guisada', 'precio' => 220.00],
            ['nombre' => 'Chorizo Muzquiz', 'precio' => 189.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Machacado con Huevo en Salsa', 'precio' => 189.00],
        ];
        foreach ($guisos as $guiso) {
            $level3Items[] = [
                'parent_id'       => $taquizaGuisosId,
                'nombre_item'     => 'Guiso: ' . $guiso['nombre'],
                'tipo_ui'         => 'checkbox',
                'descripcion'     => 'Selecciona los guisos para tu taquiza.',
                'precio_unitario' => $guiso['precio'],
                'activo'          => 1,
            ];
        }


        // --- 3.3 Hijos de 'Platillos de Pollo' (Las variantes de pollo) ---
        // Son 'quantity' porque son platillos finales que se eligen dentro de esta sub-categoría.
        $platillosDePollo = [
            'Pollo en crema de brocoli',
            'Pollo en crema de chipotle',
            'Pollo en salsa de arandano',
            'Pollo en salsa orange',
            'Pollo relleno de espinacas',
            'Pollo relleno de jamón y queso',
        ];
        foreach ($platillosDePollo as $platillo) {
            $level3Items[] = [
                'parent_id'       => $platillosPolloId,
                'nombre_item'     => $platillo,
                'tipo_ui'         => 'quantity',
                'descripcion'     => 'Especialidad de la casa.',
                'precio_unitario' => 0.00, // El precio se define en el servicio principal (ej. buffet)
                'activo'          => 1,
            ];
        }


        // --- 3.4 Hijos de 'Otros Platillos Fuertes' (El resto de platillos) ---
        // También son 'quantity' porque son platillos finales.
        $otrosFuertes = [
            'Lasaña',
            'Boneless',
            'Brisket',
            'Fajitas de pollo con pimientos',
            'Fajitas de res',
            'Pulled Pork bbq',
            'Res y verduras tepanyaki (salado)',
            'Res y verduras teriyaki (dulce)',
        ];
        foreach ($otrosFuertes as $platillo) {
            $level3Items[] = [
                'parent_id'       => $otrosPlatillosFuertesId,
                'nombre_item'     => $platillo,
                'tipo_ui'         => 'quantity',
                'descripcion'     => 'Especialidad de la casa.',
                'precio_unitario' => 0.00, // El precio se define en el servicio principal
                'activo'          => 1,
            ];
        }

        $cafeOpciones = [
            ['nombre' => 'Sí hay acceso a enchufes cerca', 'tipo_ui' => 'radio', 'descripcion' => 'No se requiere extensión.', 'precio_base' => 0.00],
            ['nombre' => 'No hay cerca, se necesita extensión', 'tipo_ui' => 'radio', 'descripcion' => 'Costo adicional por extensión.', 'precio_base' => 50.00],
        ];
        foreach ($cafeOpciones as $item) {
            $level3Items[] = ['parent_id' => $barraCafeId, 'nombre_item' => 'Enchufes: ' . $item['nombre'], 'tipo_ui' => $item['tipo_ui'], 'descripcion' => $item['descripcion'], 'precio_unitario' => $item['precio_base'], 'activo' => 1];
        }

        $bocadillos = [
            ['nombre' => 'mini brownies', 'precio' => 5.00],
            ['nombre' => 'mini cheesecakes de sabores', 'precio' => 6.50],
            ['nombre' => 'muffin de zanahoria', 'precio' => 4.00],
            ['nombre' => 'mini donas', 'precio' => 3.50],
            ['nombre' => 'fresas con chocolate', 'precio' => 7.00],
            ['nombre' => 'shots de pay de limon', 'precio' => 5.50],
            ['nombre' => 'shots de cheescake oreo', 'precio' => 5.50],
            ['nombre' => 'shots mousse de fresa', 'precio' => 5.50],
            ['nombre' => 'pretzel bañadas en chocolate', 'precio' => 4.00],
            ['nombre' => 'galletas de chispas de chocolate', 'precio' => 3.00],
            ['nombre' => 'galletas de avena', 'precio' => 3.00],
            ['nombre' => 'galletas de smore', 'precio' => 4.50],
        ];
        foreach ($bocadillos as $item) {
            $level3Items[] = ['parent_id' => $mesaBocadillosId, 'nombre_item' => $item['nombre'], 'tipo_ui' => 'quantity', 'descripcion' => '', 'precio_unitario' => $item['precio'], 'activo' => 1];
        }

        $postres = [
            ['nombre' => 'salados: curnitos de jamos', 'precio' => 8.00],
            ['nombre' => 'salados: marinitas', 'precio' => 7.50],
            ['nombre' => 'salados: wraps', 'precio' => 9.00],
            ['nombre' => 'salados: panecitos preparadaos', 'precio' => 6.00],
            ['nombre' => 'salados: quesitos con salami', 'precio' => 8.50],
            ['nombre' => 'dulces: shots de pay de limon', 'precio' => 5.50],
            ['nombre' => 'dulces: shots de cheescake oreo', 'precio' => 5.50],
            ['nombre' => 'dulces: mini muffin zanahoria', 'precio' => 4.00],
            ['nombre' => 'dulces: mini brownies', 'precio' => 5.00],
            ['nombre' => 'dulces: vasitos de charruteria', 'precio' => 4.50],
        ];
        foreach ($postres as $item) {
            $level3Items[] = ['parent_id' => $mesaPostresId, 'nombre_item' => $item['nombre'], 'tipo_ui' => 'quantity', 'descripcion' => '', 'precio_unitario' => $item['precio'], 'activo' => 1];
        }

        if (!empty($level3Items)) {
            $model->insertBatch($level3Items);
        }
    }
}