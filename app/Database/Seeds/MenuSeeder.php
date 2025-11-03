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
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $model->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // --- ESTRUCTURA DE CATEGORÍA RAÍZ ÚNICA (Nivel 1) ---
        // Se elimina el "Paso 1", solo existe una presentación principal.
        $rootPresentations = [
            ['id' => 'platillos_individuales', 'nombre' => 'Platillos Principales', 'desc' => 'Elige entre nuestros platillos y servicios.', 'tipo_comida' => 'ambos', 'ui' => 'radio'],
        ];

        $root_ids = [];
        foreach ($rootPresentations as $cat) {
            $model->insert([
                'nombre_item' => $cat['nombre'],
                'parent_id' => null,
                'tipo_ui' => $cat['ui'],
                'descripcion' => $cat['desc'],
                'precio_unitario' => 0.00,
                'activo' => 1,
                'tipo_comida' => $cat['tipo_comida'],
            ]);
            $root_ids[$cat['id']] = $model->getInsertID();
        }

        // --- CATEGORÍAS CONSOLIDADAS (Nivel 2) ---
        // Todas las subcategorías ahora cuelgan de la única presentación raíz.
        $consolidatedCategories = [
            // Categorías Originales
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'chilaquiles', 'tipo_comida' => 'desayuno'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'omelettes', 'tipo_comida' => 'desayuno'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'combitos', 'tipo_comida' => 'desayuno'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'waffles, hotcakes y especiales', 'tipo_comida' => 'desayuno'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'cuernitos', 'tipo_comida' => 'desayuno'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'mapoburgers', 'tipo_comida' => 'comida'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'tacos', 'tipo_comida' => 'comida'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'platillos tradicionales', 'tipo_comida' => 'ambos'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'comidas', 'tipo_comida' => 'comida'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'bebidas', 'tipo_comida' => 'ambos'],
            // Categorías Fusionadas
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'menú kids', 'tipo_comida' => 'ambos'],
            ['parent_id_key' => 'platillos_individuales', 'nombre' => 'catering y barras', 'tipo_comida' => 'ambos'],
        ];

        $category_ids = [];
        foreach ($consolidatedCategories as $cat) {
            $model->insert([
                'nombre_item' => $cat['nombre'],
                'parent_id' => $root_ids[$cat['parent_id_key']],
                'tipo_ui' => 'checkbox',
                'descripcion' => '',
                'precio_unitario' => 0.00,
                'activo' => 1,
                'tipo_comida' => $cat['tipo_comida'],
            ]);
            $category_ids[$cat['nombre']] = $model->getInsertID();
        }


        // --- Lógica de inserción para Chilaquiles con personalización anidada ---
        if (isset($category_ids['chilaquiles'])) {
            $chilaquiles_cat_id = $category_ids['chilaquiles'];

            // 1. Insertar el platillo principal "Mapo Bowl"
            $model->insert([
                'nombre_item' => 'Mapo Bowl',
                'parent_id' => $chilaquiles_cat_id,
                'tipo_ui' => 'checkbox', // El usuario selecciona el platillo
                'descripcion' => 'Algo más rápido y sencillo, un bowl de chilaquiles donde tu eliges la salsa y tu proteína.',
                'precio_unitario' => 140.00,
                'activo' => 1,
                'per_person' => true,
            ]);
            $mapo_bowl_id = $model->getInsertID();

            // 2. Crear el nivel de personalización "Salsa"
            $model->insert(['parent_id' => $mapo_bowl_id, 'nombre_item' => 'Salsa', 'tipo_ui' => 'radio', 'descripcion' => 'Elige una salsa.', 'per_person' => false]);
            $salsa_level_id = $model->getInsertID();

            // Insertar opciones de Salsa
            $salsas = ['Salsa Chipotle', 'Salsa Molcajeteada', 'Salsa Mole', 'Salsa Roja', 'Salsa Suiza', 'Salsa Verde Agua'];
            foreach ($salsas as $salsa) {
                $model->insert(['parent_id' => $salsa_level_id, 'nombre_item' => $salsa, 'tipo_ui' => 'radio', 'precio_unitario' => 0.00, 'per_person' => false]);
            }

            // 3. Crear el nivel de personalización "Proteína"
            $model->insert(['parent_id' => $mapo_bowl_id, 'nombre_item' => 'Proteína', 'tipo_ui' => 'radio', 'descripcion' => 'Elige una proteína.', 'per_person' => false]);
            $proteina_level_id = $model->getInsertID();

            // Insertar opciones de Proteína
            $proteinas = [
                ['nombre' => 'Birria Mapolato', 'precio' => 30.00], ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
                ['nombre' => 'Huevo Estrellado', 'precio' => 0.00], ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
                ['nombre' => 'Pechuga Desmenuzada', 'precio' => 0.00], ['nombre' => 'Prensado', 'precio' => 0.00],
                ['nombre' => 'Sin Proteina', 'precio' => 0.00],
            ];
            foreach ($proteinas as $proteina) {
                $model->insert(['parent_id' => $proteina_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
            }

            // Insertar los otros platillos de chilaquiles sin personalización por ahora
            $this->addChilaquilesCustomization($model, $chilaquiles_cat_id);
            $this->addChilaquilesMixtosCustomization($model, $chilaquiles_cat_id);
        }


        // --- PLATILLOS (Nivel 3) ---
        // Hijos de las categorías de "Platillos Individuales" (Excluyendo Chilaquiles que ya se insertaron)
        $menuItems = [
            // OMELETTES
            ['parent' => 'omelettes', 'nombre' => 'OMELETTE MUZQUIZ', 'precio' => 189, 'desc' => ''],
            // COMBITOS
            // WAFFLES, HOTCAKES Y ESPECIALES
            // CUERNITOS
            ['parent' => 'cuernitos', 'nombre' => 'CUERNITO VEGETARIANO', 'precio' => 179, 'desc' => ''],
            ['parent' => 'cuernitos', 'nombre' => 'CUERNITO HAM & CHEESE', 'precio' => 179, 'desc' => ''],
            ['parent' => 'cuernitos', 'nombre' => 'CUERNITO BACON CHEESE', 'precio' => 179, 'desc' => ''],
            // MAPOBURGERS
            ['parent' => 'mapoburgers', 'nombre' => 'SMASH SENCILLA', 'precio' => 215, 'desc' => '2 carnes, 2 quesos americanos, pan brioche, aderezo casero y cebolla caramelizada. Incluye papas fritas.'],
            ['parent' => 'mapoburgers', 'nombre' => 'ORDEN ONION RINGS', 'precio' => 159, 'desc' => '300 gramos de aros de cebolla fritos en una mezcla con cerveza.'],
            ['parent' => 'mapoburgers', 'nombre' => 'PEPINILLOS FRITOS', 'precio' => 198, 'desc' => '300 gramos de pepinillos fritos, con un empanizado picosito.'],
            // TACOS
            ['parent' => 'tacos', 'nombre' => 'ORDEN DE QUESABIRRIAS', 'precio' => 145, 'desc' => 'La orden incluye 3 quesabirrias con consomé y verdura'],
            ['parent' => 'tacos', 'nombre' => 'TACOS DORADOS DE CARNE MOLIDA', 'precio' => 150, 'desc' => '5 tacos dorados rellenos de carne molida, acompañados de lechuga, tomate, cebolla, queso fresco y crema, además de su respectiva salsa.'],
            // PLATILLOS TRADICIONALES
            ['parent' => 'platillos tradicionales', 'nombre' => 'PLATILLO DE MARIO', 'precio' => 199, 'desc' => 'Los 4 guisos favoritos del dueño: Bistec en Salsa, Queso panela en salsa, Chicharrón Prensado y Chicharrón Tronador, acompañado de frijoles.'],
            ['parent' => 'platillos tradicionales', 'nombre' => 'CHORIQUESO DE MUZQUIZ', 'precio' => 189, 'desc' => ''],
            // COMIDAS
            ['parent' => 'comidas', 'nombre' => 'ENCHILADAS SUIZAS', 'precio' => 175, 'desc' => ''],
            ['parent' => 'comidas', 'nombre' => 'ENCHIPOTLADAS', 'precio' => 175, 'desc' => ''],
            ['parent' => 'comidas', 'nombre' => 'ENMOLADAS', 'precio' => 175, 'desc' => ''],
            // BEBIDAS (Fusionadas del seeder antiguo)
            ['parent' => 'bebidas', 'nombre' => 'JUGO DE NARANJA MEDIO LITRO', 'precio' => 65, 'desc' => 'Jugo de naranja elaborado cada mañana en la sucursal, la porción es de 500 ml'],
            ['parent' => 'bebidas', 'nombre' => 'Botella de Agua', 'precio' => 25.00, 'desc' => ''],
            ['parent' => 'bebidas', 'nombre' => 'Café Inagotable', 'precio' => 35.00, 'desc' => ''],
            ['parent' => 'bebidas', 'nombre' => 'Caramel Macchiato Helado', 'precio' => 65.00, 'desc' => ''],
            ['parent' => 'bebidas', 'nombre' => 'Chocomilk litro', 'precio' => 80.00, 'desc' => ''],
            ['parent' => 'bebidas', 'nombre' => 'Jugo de Naranja Litro', 'precio' => 140.00, 'desc' => ''],
            ['parent' => 'bebidas', 'nombre' => 'Moka Helado', 'precio' => 65.00, 'desc' => ''],
            // MENÚ KIDS (Ahora una subcategoría)
            ['parent' => 'menú kids', 'nombre' => 'French Kids', 'precio' => 99.00, 'desc' => ''],
            ['parent' => 'menú kids', 'nombre' => 'Hotcakes Kids', 'precio' => 99.00, 'desc' => ''],
            ['parent' => 'menú kids', 'nombre' => 'Huevito Revuelto', 'precio' => 99.00, 'desc' => ''],
            ['parent' => 'menú kids', 'nombre' => 'Kidsadillas', 'precio' => 99.00, 'desc' => ''],
            ['parent' => 'menú kids', 'nombre' => 'Pollito a la Plancha', 'precio' => 125.00, 'desc' => ''],
            ['parent' => 'menú kids', 'nombre' => 'Takids Dorados', 'precio' => 100.00, 'desc' => ''],
            // CATERING Y BARRAS (Ahora una subcategoría)
            ['parent' => 'catering y barras', 'nombre' => 'Menudo por Litro', 'precio' => 160.00, 'desc' => ''],
        ];

        foreach ($menuItems as $item) {
            if (isset($category_ids[$item['parent']])) {
                $model->insert([
                    'nombre_item' => $item['nombre'],
                    'parent_id' => $category_ids[$item['parent']],
                    'tipo_ui' => $item['tipo_ui'] ?? 'quantity',
                    'descripcion' => $item['desc'],
                    'precio_unitario' => $item['precio'],
                    'activo' => 1,
                    'per_person' => $item['per_person'] ?? true, // Por defecto, los platillos son por persona
                ]);
            }
        }

        if (isset($category_ids['omelettes'])) {
            $this->addOmeletteClasicoCustomization($model, $category_ids['omelettes']);
            $this->addOmeletteVegetarianoCustomization($model, $category_ids['omelettes']);
            $this->addOmeletteTocinoCustomization($model, $category_ids['omelettes']);
        }

        if (isset($category_ids['combitos'])) {
            $this->addCombitoChilaquilesWafflesCustomization($model, $category_ids['combitos']);
            $this->addCombitoChilaquilesHotcakesCustomization($model, $category_ids['combitos']);
            $this->addCombitoCuernitoWafflesCustomization($model, $category_ids['combitos']);
            $this->addCombitoCuernitoHotcakesCustomization($model, $category_ids['combitos']);
            $this->addCombitoOmeletteWaffleCustomization($model, $category_ids['combitos']);
            $this->addCombitoOmeletteHotcakesCustomization($model, $category_ids['combitos']);
        }

        if (isset($category_ids['waffles, hotcakes y especiales'])) {
            $this->addFrenchToastCustomization($model, $category_ids['waffles, hotcakes y especiales']);
            $this->addLosWafflesCustomization($model, $category_ids['waffles, hotcakes y especiales']);
            $this->addLosHotcakesCustomization($model, $category_ids['waffles, hotcakes y especiales']);
            $this->addAlmuerzoAmericanoSencilloCustomization($model, $category_ids['waffles, hotcakes y especiales']);
            $this->addAmericanoDeluxCustomization($model, $category_ids['waffles, hotcakes y especiales']);
            $this->addPancakeBurgerCustomization($model, $category_ids['waffles, hotcakes y especiales']);
        }

        if (isset($category_ids['mapoburgers'])) {
            $this->addMozzaSmashCustomization($model, $category_ids['mapoburgers']);
            $this->addOnionBbqSmashCustomization($model, $category_ids['mapoburgers']);
            $this->addBaconSmashCustomization($model, $category_ids['mapoburgers']);
            $this->addChickenTendersCustomization($model, $category_ids['mapoburgers']);
            $this->addChiknClasicaCustomization($model, $category_ids['mapoburgers']);
            $this->addChiknBufaloCustomization($model, $category_ids['mapoburgers']);
        }

        if (isset($category_ids['tacos'])) {
            $this->addTacosCustomization($model, $category_ids['tacos']);
        }

        if (isset($category_ids['platillos tradicionales'])) {
            $this->addMachacadoConHuevoCustomization($model, $category_ids['platillos tradicionales']);
            $this->addMachacadoSinHuevoCustomization($model, $category_ids['platillos tradicionales']);
            $this->addChicharronConHuevoCustomization($model, $category_ids['platillos tradicionales']);
            $this->addChicharronSinHuevoCustomization($model, $category_ids['platillos tradicionales']);
            $this->addQuesoPanelaEnSalsaCustomization($model, $category_ids['platillos tradicionales']);
            $this->addHuevosDivorciadosCustomization($model, $category_ids['platillos tradicionales']);
            $this->addPlatilloBistecEnSalsaCustomization($model, $category_ids['platillos tradicionales']);
            $this->addPlatilloChicharronPrensadoCustomization($model, $category_ids['platillos tradicionales']);
            $this->addHuevosRevueltosConChorizoCustomization($model, $category_ids['platillos tradicionales']);
            $this->addHuevosRevueltosConJamonCustomization($model, $category_ids['platillos tradicionales']);
            $this->addHuevosRevueltosConTocinoCustomization($model, $category_ids['platillos tradicionales']);
        }

        if (isset($category_ids['bebidas'])) {
            $this->addLicuadoChicoCustomization($model, $category_ids['bebidas']);
            $this->addRefrescoCustomization($model, $category_ids['bebidas']);
            $this->addAguaChicaCustomization($model, $category_ids['bebidas']);
            $this->addAguaGrandeCustomization($model, $category_ids['bebidas']);
            $this->addLicuadoGrandeCustomization($model, $category_ids['bebidas']);
        }

        if (isset($category_ids['comidas'])) {
            $this->addPlatillosDePolloCustomization($model, $category_ids['comidas']);
            $this->addOtrosPlatillosFuertesCustomization($model, $category_ids['comidas']);
        }

        // La modalidad de servicio se manejará en el frontend, ya no se necesita aquí.
        // if (isset($root_ids['modalidad_servicio'])) {
        //     $this->addModalidadServicioOptions($model, $root_ids['modalidad_servicio']);
        // }

        if (isset($category_ids['catering y barras'])) {
            $catering_barras_cat_id = $category_ids['catering y barras'];
            $this->addBarraCafeCustomization($model, $catering_barras_cat_id);
            $this->addMesaBocadillosCustomization($model, $catering_barras_cat_id);
            $this->addMesaPostresCustomization($model, $catering_barras_cat_id);
        }
    }

    private function addPlatillosDePolloCustomization($model, $parentId)
    {
        $model->insert([
            'nombre_item' => 'Platillos de Pollo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Selecciona una de nuestras especialidades de pollo.',
            'precio_unitario' => 0.00,
            'activo' => 1,
        ]);
        $platillosPolloId = $model->getInsertID();

        $platillosDePollo = [
            'Pollo en crema de brocoli',
            'Pollo en crema de chipotle',
            'Pollo en salsa de arandano',
            'Pollo en salsa orange',
            'Pollo relleno de espinacas',
            'Pollo relleno de jamón y queso',
        ];

        foreach ($platillosDePollo as $platillo) {
            $model->insert([
                'parent_id'       => $platillosPolloId,
                'nombre_item'     => $platillo,
                'tipo_ui'         => 'quantity',
                'descripcion'     => 'Especialidad de la casa.',
                'precio_unitario' => 0.00,
                'activo'          => 1,
            ]);
        }
    }

    private function addOtrosPlatillosFuertesCustomization($model, $parentId)
    {
        $model->insert([
            'nombre_item' => 'Otros Platillos Fuertes',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Otras opciones de platos fuertes.',
            'precio_unitario' => 0.00,
            'activo' => 1,
        ]);
        $otrosPlatillosFuertesId = $model->getInsertID();

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
            $model->insert([
                'parent_id'       => $otrosPlatillosFuertesId,
                'nombre_item'     => $platillo,
                'tipo_ui'         => 'quantity',
                'descripcion'     => 'Especialidad de la casa.',
                'precio_unitario' => 0.00,
                'activo'          => 1,
            ]);
        }
    }

    private function addMesaBocadillosCustomization($model, $parentId)
    {
        $model->insert([
            'nombre_item' => 'Mesa de bocadillos',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Selecciona para ver opciones de bocadillos.',
            'precio_unitario' => 150.00,
            'activo' => 1,
        ]);
        $mesaBocadillosId = $model->getInsertID();

        $bocadillos = [
            ['nombre' => 'Mini Brownies', 'precio' => 0.00],
            ['nombre' => 'Mini Cheesecakes de sabores', 'precio' => 0.00],
            ['nombre' => 'Muffin de zanahoria', 'precio' => 0.00],
            ['nombre' => 'Mini donas', 'precio' => 0.00],
            ['nombre' => 'Fresas con chocolate', 'precio' => 0.00],
            ['nombre' => 'Shots de pay de limón', 'precio' => 0.00],
            ['nombre' => 'Shots de cheesecake oreo', 'precio' => 0.00],
            ['nombre' => 'Shots mousse de fresa', 'precio' => 0.00],
            ['nombre' => 'Pretzel bañados en chocolate', 'precio' => 0.00],
            ['nombre' => 'Galletas de chispas de chocolate', 'precio' => 0.00],
            ['nombre' => 'Galletas de avena', 'precio' => 0.00],
            ['nombre' => 'Galletas de smore', 'precio' => 0.00],
        ];

        foreach ($bocadillos as $item) {
            $model->insert(['parent_id' => $mesaBocadillosId, 'nombre_item' => $item['nombre'], 'tipo_ui' => 'quantity', 'descripcion' => '', 'precio_unitario' => $item['precio'], 'activo' => 1]);
        }
    }

    private function addMesaPostresCustomization($model, $parentId)
    {
        $model->insert([
            'nombre_item' => 'Mesa de postres',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Selecciona para ver opciones de postres.',
            'precio_unitario' => 150.00,
            'activo' => 1,
        ]);
        $mesaPostresId = $model->getInsertID();

        // Sub-nivel para Opciones Saladas
        $model->insert(['parent_id' => $mesaPostresId, 'nombre_item' => 'Saladas', 'tipo_ui' => 'checkbox']);
        $saladasId = $model->getInsertID();
        $opcionesSaladas = [
            'Cuernitos de jamos', 'marinitas', 'wraps', 'panecitos preparados', 'quesitos con salami'
        ];
        foreach ($opcionesSaladas as $item) {
            $model->insert(['parent_id' => $saladasId, 'nombre_item' => $item, 'tipo_ui' => 'quantity', 'precio_unitario' => 0.00, 'activo' => 1]);
        }

        // Sub-nivel para Opciones Dulces
        $model->insert(['parent_id' => $mesaPostresId, 'nombre_item' => 'Dulces', 'tipo_ui' => 'checkbox']);
        $dulcesId = $model->getInsertID();
        $opcionesDulces = [
            'shots de pay de limon', 'shots de cheesecake oreo', 'mini muffin zanahoria', 'mini brownies'
        ];
        foreach ($opcionesDulces as $item) {
            $model->insert(['parent_id' => $dulcesId, 'nombre_item' => $item, 'tipo_ui' => 'quantity', 'precio_unitario' => 0.00, 'activo' => 1]);
        }
    }

    private function addBarraCafeCustomization($model, $parentId)
    {
        $model->insert([
            'nombre_item' => 'Barra de cafe',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Selecciona para ver opciones de enchufe.',
            'precio_unitario' => 65.00,
            'activo' => 1,
        ]);
        $barraCafeId = $model->getInsertID();

        $options = [
            ['nombre' => 'Sí hay acceso a enchufes cerca', 'descripcion' => 'No se requiere extensión.', 'precio' => 0.00],
            ['nombre' => 'No hay cerca, se necesita extensión', 'descripcion' => 'Costo adicional por extensión.', 'precio' => 50.00],
        ];

        foreach ($options as $option) {
            $model->insert([
                'parent_id'       => $barraCafeId,
                'nombre_item'     => 'Enchufes: ' . $option['nombre'],
                'tipo_ui'         => 'radio',
                'descripcion'     => $option['descripcion'],
                'precio_unitario' => $option['precio'],
                'activo'          => 1,
            ]);
        }
    }

    private function addModalidadServicioOptions($model, $parentId)
    {
        $options = [
            ['nombre' => 'Buffet / Self Service. (Menores a 20 personas)', 'descripcion' => 'Los invitados se sirven directamente.', 'precio' => 0.00],
            ['nombre' => 'Buffet asistido o servido por staff (Costo adicional)', 'descripcion' => 'Personal para asistir.', 'precio' => 500.00],
            ['nombre' => 'Servicio a la mesa. (Costo adicional)', 'descripcion' => 'Meseros incluidos.', 'precio' => 1500.00],
        ];

        foreach ($options as $option) {
            $model->insert([
                'parent_id'       => $parentId,
                'nombre_item'     => $option['nombre'],
                'tipo_ui'         => 'radio',
                'descripcion'     => $option['descripcion'],
                'precio_unitario' => $option['precio'],
                'activo'          => 1,
            ]);
        }
    }

    private function addChilaquilesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "CHILAQUILES"
        $model->insert([
            'nombre_item' => 'CHILAQUILES',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Preparados a tu antojo, elige la salsa, la proteína y dos guarniciones.',
            'precio_unitario' => 185.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chilaquiles_id = $model->getInsertID();

        // Nivel: SALSA
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => 'SALSA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $salsa_level_id = $model->getInsertID();
        $salsas = ['Salsa Chipotle', 'Salsa Molcajeteada', 'Salsa Mole', 'Salsa Roja', 'Salsa Suiza', 'Salsa Verde Agua'];
        foreach ($salsas as $salsa) {
            $model->insert(['parent_id' => $salsa_level_id, 'nombre_item' => $salsa, 'tipo_ui' => 'radio', 'precio_unitario' => 0.00, 'per_person' => false]);
        }

        // Nivel: PROTEINA
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => 'PROTEINA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina_level_id = $model->getInsertID();
        $proteinas = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00], ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00], ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Pechuga Desmenuzada', 'precio' => 0.00], ['nombre' => 'Prensado', 'precio' => 0.00],
            ['nombre' => 'Sin Proteina', 'precio' => 0.00],
        ];
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }

        // Nivel: 1er Guarnicion
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '1er Guarnicion', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00], ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00], ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00], ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00], ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00], ['nombre' => 'Pieza Huevito Estrellado', 'precio' => 15.00],
            ['nombre' => 'Pieza Huevito Revuelto', 'precio' => 15.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addChilaquilesMixtosCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "CHILAQUILES MIXTOS"
        $model->insert([
            'nombre_item' => 'CHILAQUILES MIXTOS',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Preparados a tu antojo, pero puedes elegir dos salsas y dos proteínas.',
            'precio_unitario' => 195.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chilaquiles_id = $model->getInsertID();

        // SALSAS
        $salsas = ['Salsa Chipotle', 'Salsa Molcajeteada', 'Salsa Mole', 'Salsa Roja', 'Salsa Suiza', 'Salsa Verde Agua'];
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '1er Salsa', 'tipo_ui' => 'radio', 'per_person' => false]);
        $salsa1_level_id = $model->getInsertID();
        foreach ($salsas as $salsa) {
            $model->insert(['parent_id' => $salsa1_level_id, 'nombre_item' => $salsa, 'tipo_ui' => 'radio', 'precio_unitario' => 0.00, 'per_person' => false]);
        }
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '2da Salsa', 'tipo_ui' => 'radio', 'per_person' => false]);
        $salsa2_level_id = $model->getInsertID();
        foreach ($salsas as $salsa) {
            $model->insert(['parent_id' => $salsa2_level_id, 'nombre_item' => $salsa, 'tipo_ui' => 'radio', 'precio_unitario' => 0.00, 'per_person' => false]);
        }

        // PROTEINAS
        $proteinas = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00], ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00], ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Pechuga Desmenuzada', 'precio' => 0.00], ['nombre' => 'Prensado', 'precio' => 0.00],
            ['nombre' => 'Sin Proteina', 'precio' => 0.00],
        ];
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '1er Proteína', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina1_level_id = $model->getInsertID();
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina1_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '2da Proteína', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina2_level_id = $model->getInsertID();
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina2_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }

        // GUARNICIONES
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00], ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00], ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00], ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00], ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00], ['nombre' => 'Pieza Huevito Estrellado', 'precio' => 15.00],
            ['nombre' => 'Pieza Huevito Revuelto', 'precio' => 15.00],
        ];
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '1er Guarnicion', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
        $model->insert(['parent_id' => $chilaquiles_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addOmeletteClasicoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "OMELETTE CLASICO"
        $model->insert([
            'nombre_item' => 'OMELETTE CLASICO',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Omelette Clasico',
            'precio_unitario' => 169.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $omelette_id = $model->getInsertID();

        // Definir las guarniciones basadas en el JSON
        $guarniciones = [
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas Chorizo', 'precio' => 0.00],
            ['nombre' => 'Papas Mexicana', 'precio' => 0.00],
        ];

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $omelette_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $omelette_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addOmeletteVegetarianoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "OMELETTE VEGETARIANO"
        $model->insert([
            'nombre_item' => 'OMELETTE VEGETARIANO',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Omelette Vegetariano',
            'precio_unitario' => 169.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $omelette_id = $model->getInsertID();

        // Definir las guarniciones basadas en el JSON
        $guarniciones = [
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas Chorizo', 'precio' => 0.00],
            ['nombre' => 'Papas Mexicana', 'precio' => 0.00],
        ];

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $omelette_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $omelette_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addOmeletteTocinoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "OMELETTE TOCINO"
        $model->insert([
            'nombre_item' => 'OMELETTE TOCINO',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Omelette Tocino',
            'precio_unitario' => 179.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $omelette_id = $model->getInsertID();

        // Definir las guarniciones basadas en el JSON
        $guarniciones = [
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas Chorizo', 'precio' => 0.00],
            ['nombre' => 'Papas Mexicana', 'precio' => 0.00],
        ];

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $omelette_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $omelette_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addCombitoChilaquilesWafflesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "COMBITO DE CHILAQUILES CON WAFFLES"
        $model->insert([
            'nombre_item' => 'COMBITO DE CHILAQUILES CON WAFFLES',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 185.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $combito_id = $model->getInsertID();

        // Nivel: SALSA
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'SALSA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $salsa_level_id = $model->getInsertID();
        $salsas = [
            ['nombre' => 'Salsa Chipotle', 'precio' => 0.00],
            ['nombre' => 'Salsa Mole', 'precio' => 0.00],
            ['nombre' => 'Salsa Roja', 'precio' => 0.00],
            ['nombre' => 'Salsa Suiza', 'precio' => 0.00],
            ['nombre' => 'Salsa Verde Agua', 'precio' => 0.00],
        ];
        foreach ($salsas as $salsa) {
            $model->insert(['parent_id' => $salsa_level_id, 'nombre_item' => $salsa['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $salsa['precio'], 'per_person' => false]);
        }

        // Nivel: PROTEINA
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'PROTEINA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina_level_id = $model->getInsertID();
        $proteinas = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Huevitos Estrellados', 'precio' => 30.00],
            ['nombre' => 'Huevitos Revueltos', 'precio' => 30.00],
            ['nombre' => 'Pechuga Desmenuzada', 'precio' => 0.00],
            ['nombre' => 'Prensado', 'precio' => 0.00],
        ];
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Sin Topping (Tradicional)', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }
    }

    private function addCombitoOmeletteHotcakesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "COMBITO OMELETTE CON HOTCAKES"
        $model->insert([
            'nombre_item' => 'COMBITO OMELETTE CON HOTCAKES',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 175.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $combito_id = $model->getInsertID();

        // Nivel: PROTEINA
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'PROTEINA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina_level_id = $model->getInsertID();
        $proteinas = [
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'CHORIQUESO', 'precio' => 0.00],
            ['nombre' => 'CLARAS', 'precio' => 0.00],
            ['nombre' => 'CLASICO', 'precio' => 0.00],
            ['nombre' => 'MACHACADO', 'precio' => 0.00],
            ['nombre' => 'VEGETARIANO', 'precio' => 0.00],
        ];
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Chispas de Chocolate', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Sin Topping (Tradicional)', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }
    }

    private function addCombitoOmeletteWaffleCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "COMBITO OMELETTE CON WAFFLE"
        $model->insert([
            'nombre_item' => 'COMBITO OMELETTE CON WAFFLE',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 175.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $combito_id = $model->getInsertID();

        // Nivel: PROTEINA
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'PROTEINA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina_level_id = $model->getInsertID();
        $proteinas = [
            ['nombre' => 'CHORIQUESO', 'precio' => 0.00],
            ['nombre' => 'CLARAS', 'precio' => 0.00],
            ['nombre' => 'CLASICO', 'precio' => 0.00],
            ['nombre' => 'MACHACADO', 'precio' => 0.00],
            ['nombre' => 'TOCINO', 'precio' => 0.00],
            ['nombre' => 'VEGETARIANO', 'precio' => 0.00],
        ];
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Sin Topping (Tradicional)', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }
    }

    private function addCombitoCuernitoHotcakesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "COMBITO DE CUERNITO CON HOTCAKES"
        $model->insert([
            'nombre_item' => 'COMBITO DE CUERNITO CON HOTCAKES',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 175.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $combito_id = $model->getInsertID();

        // Nivel: PREPARACION
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'PREPARACION', 'tipo_ui' => 'radio', 'per_person' => false]);
        $preparacion_level_id = $model->getInsertID();
        $preparaciones = [
            ['nombre' => 'BACON CHEESE', 'precio' => 0.00],
            ['nombre' => 'CHORIQUESO', 'precio' => 0.00],
            ['nombre' => 'HAM & CHEESE', 'precio' => 0.00],
            ['nombre' => 'VEGETARIANO', 'precio' => 0.00],
        ];
        foreach ($preparaciones as $preparacion) {
            $model->insert(['parent_id' => $preparacion_level_id, 'nombre_item' => $preparacion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $preparacion['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Chispas de Chocolate', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Sin Topping (Tradicional)', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }
    }

    private function addCombitoCuernitoWafflesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "COMBITO CUERNITO CON WAFFLES"
        $model->insert([
            'nombre_item' => 'COMBITO CUERNITO CON WAFFLES',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 175.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $combito_id = $model->getInsertID();

        // Nivel: Cuernito
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Cuernito', 'tipo_ui' => 'radio', 'per_person' => false]);
        $cuernito_level_id = $model->getInsertID();
        $cuernitos = [
            ['nombre' => 'BACON CHEESE', 'precio' => 0.00],
            ['nombre' => 'CHORIQUESO', 'precio' => 0.00],
            ['nombre' => 'CLASICO', 'precio' => 0.00],
            ['nombre' => 'HAM & CHEESE', 'precio' => 0.00],
            ['nombre' => 'VEGETARIANO', 'precio' => 0.00],
        ];
        foreach ($cuernitos as $cuernito) {
            $model->insert(['parent_id' => $cuernito_level_id, 'nombre_item' => $cuernito['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $cuernito['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Sin Topping (Tradicional)', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }
    }

    private function addCombitoChilaquilesHotcakesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "COMBITO DE CHILAQUILES CON HOTCAKES"
        $model->insert([
            'nombre_item' => 'COMBITO DE CHILAQUILES CON HOTCAKES',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 185.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $combito_id = $model->getInsertID();

        // Nivel: SALSA
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'SALSA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $salsa_level_id = $model->getInsertID();
        $salsas = [
            ['nombre' => 'Salsa Chipotle', 'precio' => 0.00],
            ['nombre' => 'Salsa Mole', 'precio' => 0.00],
            ['nombre' => 'Salsa Roja', 'precio' => 0.00],
            ['nombre' => 'Salsa Suiza', 'precio' => 0.00],
            ['nombre' => 'Salsa Verde Agua', 'precio' => 0.00],
        ];
        foreach ($salsas as $salsa) {
            $model->insert(['parent_id' => $salsa_level_id, 'nombre_item' => $salsa['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $salsa['precio'], 'per_person' => false]);
        }

        // Nivel: PROTEINA
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'PROTEINA', 'tipo_ui' => 'radio', 'per_person' => false]);
        $proteina_level_id = $model->getInsertID();
        $proteinas = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Huevitos Estrellados', 'precio' => 30.00],
            ['nombre' => 'Huevitos Revueltos', 'precio' => 30.00],
            ['nombre' => 'Pechuga Desmenuzada', 'precio' => 0.00],
            ['nombre' => 'Prensado', 'precio' => 0.00],
        ];
        foreach ($proteinas as $proteina) {
            $model->insert(['parent_id' => $proteina_level_id, 'nombre_item' => $proteina['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $proteina['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $combito_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Chispas de Chocolate', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Sin Topping (Tradicional)', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }
    }

    private function addFrenchToastCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "FRENCH TOAST"
        $model->insert([
            'nombre_item' => 'FRENCH TOAST',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 99.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $french_toast_id = $model->getInsertID();

        // Nivel: ¿Fruta?
        $model->insert(['parent_id' => $french_toast_id, 'nombre_item' => '¿Fruta?', 'tipo_ui' => 'radio', 'per_person' => false]);
        $fruta_level_id = $model->getInsertID();
        $frutas = [
            ['nombre' => 'Fruta Para Waffles y Hotcakes', 'precio' => 41.00],
        ];
        foreach ($frutas as $fruta) {
            $model->insert(['parent_id' => $fruta_level_id, 'nombre_item' => $fruta['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $fruta['precio'], 'per_person' => false]);
        }

        // Nivel: TOPPING
        $model->insert(['parent_id' => $french_toast_id, 'nombre_item' => 'TOPPING', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'Pistache', 'precio' => 40.00],
            ['nombre' => 'TRADICIONAL', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }

        // Nivel: EXTRA TOPPING
        $model->insert(['parent_id' => $french_toast_id, 'nombre_item' => 'EXTRA TOPPING', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extra_topping_level_id = $model->getInsertID();
        $extra_toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
        ];
        foreach ($extra_toppings as $extra_topping) {
            $model->insert(['parent_id' => $extra_topping_level_id, 'nombre_item' => $extra_topping['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra_topping['precio'], 'per_person' => false]);
        }
    }

    private function addLosHotcakesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Los Hotcakes"
        $model->insert([
            'nombre_item' => 'Los Hotcakes',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 88.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $hotcakes_id = $model->getInsertID();

        // Nivel: Fruta
        $model->insert(['parent_id' => $hotcakes_id, 'nombre_item' => 'Fruta', 'tipo_ui' => 'radio', 'per_person' => false]);
        $fruta_level_id = $model->getInsertID();
        $frutas = [
            ['nombre' => 'Fruta Para Waffles y Hotcakes', 'precio' => 41.00],
        ];
        foreach ($frutas as $fruta) {
            $model->insert(['parent_id' => $fruta_level_id, 'nombre_item' => $fruta['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $fruta['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $hotcakes_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'TRADICIONAL', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }

        // Nivel: EXTRA TOPPING
        $model->insert(['parent_id' => $hotcakes_id, 'nombre_item' => 'EXTRA TOPPING', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extra_topping_level_id = $model->getInsertID();
        $extra_toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
        ];
        foreach ($extra_toppings as $extra_topping) {
            $model->insert(['parent_id' => $extra_topping_level_id, 'nombre_item' => $extra_topping['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra_topping['precio'], 'per_person' => false]);
        }
    }

    private function addLosWafflesCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Los Waffles"
        $model->insert([
            'nombre_item' => 'Los Waffles',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 89.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $waffles_id = $model->getInsertID();

        // Nivel: Fruta
        $model->insert(['parent_id' => $waffles_id, 'nombre_item' => 'Fruta', 'tipo_ui' => 'radio', 'per_person' => false]);
        $fruta_level_id = $model->getInsertID();
        $frutas = [
            ['nombre' => 'Fruta Para Waffles y Hotcakes', 'precio' => 41.00],
        ];
        foreach ($frutas as $fruta) {
            $model->insert(['parent_id' => $fruta_level_id, 'nombre_item' => $fruta['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $fruta['precio'], 'per_person' => false]);
        }

        // Nivel: Topping
        $model->insert(['parent_id' => $waffles_id, 'nombre_item' => 'Topping', 'tipo_ui' => 'radio', 'per_person' => false]);
        $topping_level_id = $model->getInsertID();
        $toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
            ['nombre' => 'TRADICIONAL', 'precio' => 0.00],
        ];
        foreach ($toppings as $topping) {
            $model->insert(['parent_id' => $topping_level_id, 'nombre_item' => $topping['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $topping['precio'], 'per_person' => false]);
        }

        // Nivel: EXTRA TOPPING
        $model->insert(['parent_id' => $waffles_id, 'nombre_item' => 'EXTRA TOPPING', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extra_topping_level_id = $model->getInsertID();
        $extra_toppings = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
        ];
        foreach ($extra_toppings as $extra_topping) {
            $model->insert(['parent_id' => $extra_topping_level_id, 'nombre_item' => $extra_topping['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra_topping['precio'], 'per_person' => false]);
        }
    }

    private function addAlmuerzoAmericanoSencilloCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Almuerzo Americano Sencillo"
        $model->insert([
            'nombre_item' => 'Almuerzo Americano Sencillo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '2 hotcakes, 3 tiras de tocino y 2 huevos',
            'precio_unitario' => 179.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $almuerzo_id = $model->getInsertID();

        // Nivel: Tipo de Desayuno
        $model->insert(['parent_id' => $almuerzo_id, 'nombre_item' => 'Tipo de Desayuno', 'tipo_ui' => 'radio', 'per_person' => false]);
        $desayuno_level_id = $model->getInsertID();
        $desayunos = [
            ['nombre' => 'Americano Hotcakes', 'precio' => 0.00],
            ['nombre' => 'Americano Pan Frances', 'precio' => 0.00],
            ['nombre' => 'Americano Waffles', 'precio' => 0.00],
        ];
        foreach ($desayunos as $desayuno) {
            $model->insert(['parent_id' => $desayuno_level_id, 'nombre_item' => $desayuno['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $desayuno['precio'], 'per_person' => false]);
        }

        // Nivel: Huevo
        $model->insert(['parent_id' => $almuerzo_id, 'nombre_item' => 'Huevo', 'tipo_ui' => 'radio', 'per_person' => false]);
        $huevo_level_id = $model->getInsertID();
        $huevos = [
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
        ];
        foreach ($huevos as $huevo) {
            $model->insert(['parent_id' => $huevo_level_id, 'nombre_item' => $huevo['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $huevo['precio'], 'per_person' => false]);
        }

        // Nivel: Extras
        $model->insert(['parent_id' => $almuerzo_id, 'nombre_item' => 'Extras', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'Extra Cajeta', 'precio' => 30.00],
            ['nombre' => 'Extra Cheesecake Blueberries', 'precio' => 30.00],
            ['nombre' => 'Extra Hersheys', 'precio' => 30.00],
            ['nombre' => 'Extra Lechera', 'precio' => 30.00],
            ['nombre' => 'Extra Nutella', 'precio' => 30.00],
            ['nombre' => 'Fruta Para Waffles y Hotcakes', 'precio' => 41.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addLicuadoGrandeCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "LICUADO GRANDE"
        $model->insert([
            'nombre_item' => 'LICUADO GRANDE',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'LICUADO GRANDE',
            'precio_unitario' => 79.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: Fruta
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Fruta', 'tipo_ui' => 'radio', 'per_person' => false]);
        $fruta_level_id = $model->getInsertID();
        $frutas = [
            ['nombre' => 'LICUADO DE FRESA', 'precio' => 0.00],
            ['nombre' => 'LICUADO DE MELON', 'precio' => 0.00],
            ['nombre' => 'LICUADO DE PAPAYA', 'precio' => 0.00],
            ['nombre' => 'LICUADO PLATANO', 'precio' => 0.00],
        ];
        foreach ($frutas as $fruta) {
            $model->insert(['parent_id' => $fruta_level_id, 'nombre_item' => $fruta['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $fruta['precio'], 'per_person' => false]);
        }

        // Nivel: LECHE
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'LECHE', 'tipo_ui' => 'radio', 'per_person' => false]);
        $leche_level_id = $model->getInsertID();
        $leches = [
            ['nombre' => 'Deslactosada', 'precio' => 0.00],
            ['nombre' => 'Entera', 'precio' => 0.00],
        ];
        foreach ($leches as $leche) {
            $model->insert(['parent_id' => $leche_level_id, 'nombre_item' => $leche['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $leche['precio'], 'per_person' => false]);
        }

        // Nivel: Endulzante y Extras
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Endulzante y Extras', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'AVENA', 'precio' => 0.00],
            ['nombre' => 'Canela', 'precio' => 0.00],
            ['nombre' => 'CHOCOMILK', 'precio' => 0.00],
            ['nombre' => 'EXTRA PLATANO', 'precio' => 15.00],
            ['nombre' => 'GRANOLA', 'precio' => 0.00],
            ['nombre' => 'VAINILLA', 'precio' => 0.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addAguaGrandeCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "AGUA GRANDE"
        $model->insert([
            'nombre_item' => 'AGUA GRANDE',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'AGUA GRANDE',
            'precio_unitario' => 65.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: Sabor
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Sabor', 'tipo_ui' => 'radio', 'per_person' => false]);
        $sabor_level_id = $model->getInsertID();
        $sabores = [
            ['nombre' => 'AGUA DE FRESA', 'precio' => 0.00],
            ['nombre' => 'AGUA DE MELON', 'precio' => 0.00],
            ['nombre' => 'AGUA DE PAPAYA', 'precio' => 0.00],
            ['nombre' => 'AGUA DE PEPINO', 'precio' => 0.00],
            ['nombre' => 'AGUA DE PIÑA', 'precio' => 0.00],
            ['nombre' => 'AGUA DE SANDIA', 'precio' => 0.00],
            ['nombre' => 'JAMAICA', 'precio' => 0.00],
        ];
        foreach ($sabores as $sabor) {
            $model->insert(['parent_id' => $sabor_level_id, 'nombre_item' => $sabor['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $sabor['precio'], 'per_person' => false]);
        }

        // Nivel: Endulzante y Fruta Extra
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Endulzante y Fruta Extra', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'AZUCAR NORMAL', 'precio' => 0.00],
            ['nombre' => 'EXTRA FRESA', 'precio' => 15.00],
            ['nombre' => 'EXTRA PEPINO', 'precio' => 15.00],
            ['nombre' => 'EXTRA PIÑA', 'precio' => 15.00],
            ['nombre' => 'SIN AZUCAR', 'precio' => 0.00],
            ['nombre' => 'SPLENDA', 'precio' => 0.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addChiknBufaloCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Chikn Bufalo"
        $model->insert([
            'nombre_item' => 'Chikn Bufalo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Hamburguesa de pechuga de pollo empanizada y bañada en salsa buffalo y ranch, contiene lechuga y tomate, aderezo mayo chipotle.',
            'precio_unitario' => 185.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chikn_bufalo_id = $model->getInsertID();

        // Nivel: Extras
        $model->insert(['parent_id' => $chikn_bufalo_id, 'nombre_item' => 'Extras', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'Extra Aguacate', 'precio' => 30.00],
            ['nombre' => 'Extra Tocino', 'precio' => 45.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addAmericanoDeluxCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Americano Delux"
        $model->insert([
            'nombre_item' => 'Americano Delux',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '3 hotcakes, 2 salchichas americanas, tocino y huevo.',
            'precio_unitario' => 229.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $almuerzo_id = $model->getInsertID();

        // Nivel: Pan
        $model->insert(['parent_id' => $almuerzo_id, 'nombre_item' => 'Pan', 'tipo_ui' => 'radio', 'per_person' => false]);
        $pan_level_id = $model->getInsertID();
        $panes = [
            ['nombre' => 'Americano Hotcakes', 'precio' => 0.00],
            ['nombre' => 'Americano Pan Frances', 'precio' => 0.00],
            ['nombre' => 'Americano Waffles', 'precio' => 0.00],
        ];
        foreach ($panes as $pan) {
            $model->insert(['parent_id' => $pan_level_id, 'nombre_item' => $pan['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $pan['precio'], 'per_person' => false]);
        }

        // Nivel: Huevos
        $model->insert(['parent_id' => $almuerzo_id, 'nombre_item' => 'Huevos', 'tipo_ui' => 'radio', 'per_person' => false]);
        $huevo_level_id = $model->getInsertID();
        $huevos = [
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
        ];
        foreach ($huevos as $huevo) {
            $model->insert(['parent_id' => $huevo_level_id, 'nombre_item' => $huevo['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $huevo['precio'], 'per_person' => false]);
        }

        // Nivel: Extra Topping
        $model->insert(['parent_id' => $almuerzo_id, 'nombre_item' => 'Extra Topping', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'Blueberries Cheesecake', 'precio' => 30.00],
            ['nombre' => 'Cajeta', 'precio' => 30.00],
            ['nombre' => 'Fruta Para Waffles y Hotcakes', 'precio' => 41.00],
            ['nombre' => 'Hersheys', 'precio' => 30.00],
            ['nombre' => 'Lechera', 'precio' => 30.00],
            ['nombre' => 'Nutella', 'precio' => 30.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addPancakeBurgerCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "PANCAKE BURGER"
        $model->insert([
            'nombre_item' => 'PANCAKE BURGER',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 189.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $pancake_burger_id = $model->getInsertID();

        // Nivel: Acompañamiento
        $model->insert(['parent_id' => $pancake_burger_id, 'nombre_item' => 'Acompañamiento', 'tipo_ui' => 'radio', 'per_person' => false]);
        $acompanamiento_level_id = $model->getInsertID();
        $acompanamientos = [
            ['nombre' => 'Fruta de Temporada', 'precio' => 0.00],
            ['nombre' => 'papitas fritas', 'precio' => 0.00],
        ];
        foreach ($acompanamientos as $acompanamiento) {
            $model->insert(['parent_id' => $acompanamiento_level_id, 'nombre_item' => $acompanamiento['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $acompanamiento['precio'], 'per_person' => false]);
        }
    }

    private function addMozzaSmashCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "MOZZA SMASH"
        $model->insert([
            'nombre_item' => 'MOZZA SMASH',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '2 carnes, 2 quesos americanos, 3 triangulos de queso mozzarela frito sobre una cama de aguacate, en pan brioche con aderezo casero. Incluye papas frit',
            'precio_unitario' => 229.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $mozza_smash_id = $model->getInsertID();

        // Nivel: Cambiar Papas
        $model->insert(['parent_id' => $mozza_smash_id, 'nombre_item' => 'Cambiar Papas', 'tipo_ui' => 'radio', 'per_person' => false]);
        $cambiar_papas_level_id = $model->getInsertID();
        $papas = [
            ['nombre' => 'Papas de Camote', 'precio' => 20.00],
        ];
        foreach ($papas as $papa) {
            $model->insert(['parent_id' => $cambiar_papas_level_id, 'nombre_item' => $papa['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $papa['precio'], 'per_person' => false]);
        }
    }

    private function addChickenTendersCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "CHICKEN TENDERS"
        $model->insert([
            'nombre_item' => 'CHICKEN TENDERS',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Deliciosos tenders empanizados y jugosos, acompañados de papas fritas.',
            'precio_unitario' => 0.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chicken_tenders_id = $model->getInsertID();

        // Nivel: Piezas
        $model->insert(['parent_id' => $chicken_tenders_id, 'nombre_item' => 'Piezas', 'tipo_ui' => 'radio', 'per_person' => false]);
        $piezas_level_id = $model->getInsertID();
        $piezas = [
            ['nombre' => '3 Piezas con papas fritas', 'precio' => 139.00],
            ['nombre' => '5 Piezas con papas fritas', 'precio' => 179.00],
            ['nombre' => '7 Piezas con Papas Fritas', 'precio' => 229.00],
        ];
        foreach ($piezas as $pieza) {
            $model->insert(['parent_id' => $piezas_level_id, 'nombre_item' => $pieza['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $pieza['precio'], 'per_person' => false]);
        }

        // Nivel: Cambiar Papas
        $model->insert(['parent_id' => $chicken_tenders_id, 'nombre_item' => 'Cambiar Papas', 'tipo_ui' => 'radio', 'per_person' => false]);
        $cambiar_papas_level_id = $model->getInsertID();
        $papas = [
            ['nombre' => 'Papas de Camote', 'precio' => 20.00],
        ];
        foreach ($papas as $papa) {
            $model->insert(['parent_id' => $cambiar_papas_level_id, 'nombre_item' => $papa['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $papa['precio'], 'per_person' => false]);
        }
    }

    private function addBaconSmashCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "BACON SMASH"
        $model->insert([
            'nombre_item' => 'BACON SMASH',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '2 carnes, 2 quesos americanos, cebolla caramelizada, tiras de tocino, aderezo casero, pan brioche. Incluye papas fritas.',
            'precio_unitario' => 229.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $bacon_smash_id = $model->getInsertID();

        // Nivel: CAMBIAR PAPAS?
        $model->insert(['parent_id' => $bacon_smash_id, 'nombre_item' => 'CAMBIAR PAPAS?', 'tipo_ui' => 'radio', 'per_person' => false]);
        $cambiar_papas_level_id = $model->getInsertID();
        $papas = [
            ['nombre' => 'Papas de Camote', 'precio' => 20.00],
        ];
        foreach ($papas as $papa) {
            $model->insert(['parent_id' => $cambiar_papas_level_id, 'nombre_item' => $papa['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $papa['precio'], 'per_person' => false]);
        }
    }

    private function addOnionBbqSmashCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "ONION BBQ SMASH"
        $model->insert([
            'nombre_item' => 'ONION BBQ SMASH',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '2 carnes, 2 quesos, aros de cebolla y salsa BBQ. Incluye papas fritas.',
            'precio_unitario' => 219.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $onion_bbq_smash_id = $model->getInsertID();

        // Nivel: Cambiar Papas
        $model->insert(['parent_id' => $onion_bbq_smash_id, 'nombre_item' => 'Cambiar Papas', 'tipo_ui' => 'radio', 'per_person' => false]);
        $cambiar_papas_level_id = $model->getInsertID();
        $papas = [
            ['nombre' => 'Papas de Camote', 'precio' => 20.00],
        ];
        foreach ($papas as $papa) {
            $model->insert(['parent_id' => $cambiar_papas_level_id, 'nombre_item' => $papa['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $papa['precio'], 'per_person' => false]);
        }
    }

    private function addChiknClasicaCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Chikn Clasica"
        $model->insert([
            'nombre_item' => 'Chikn Clasica',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Hamburguesa de pechuga de pollo con aderezo mayo chipotle, queso amarillo tipo americano, pan brioche, lechuga y tomate.',
            'precio_unitario' => 175.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chikn_clasica_id = $model->getInsertID();

        // Nivel: Extras
        $model->insert(['parent_id' => $chikn_clasica_id, 'nombre_item' => 'Extras', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'Extra Aguacate', 'precio' => 30.00],
            ['nombre' => 'Extra Tocino', 'precio' => 45.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addTacosCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Tacos"
        $model->insert([
            'nombre_item' => 'Tacos',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 0.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $tacos_id = $model->getInsertID();

        // Nivel: Guisos
        $model->insert(['parent_id' => $tacos_id, 'nombre_item' => 'Guisos', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $guisos_level_id = $model->getInsertID();
        $guisos = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'De Chicharron Prensado', 'precio' => 25.00],
            ['nombre' => 'De Frijoles con Chorizo', 'precio' => 25.00],
            ['nombre' => 'De Frijoles Naturales', 'precio' => 25.00],
            ['nombre' => 'De Huevo con Chorizo', 'precio' => 25.00],
            ['nombre' => 'De Huevo con Machacado', 'precio' => 25.00],
            ['nombre' => 'De Huevo con Tocino', 'precio' => 25.00],
            ['nombre' => 'De Panela en Salsa', 'precio' => 25.00],
            ['nombre' => 'De Papas a la Mexicana', 'precio' => 25.00],
            ['nombre' => 'De Papas con Chorizo', 'precio' => 25.00],
            ['nombre' => 'Huevo con Jamon', 'precio' => 25.00],
        ];
        foreach ($guisos as $guiso) {
            $model->insert(['parent_id' => $guisos_level_id, 'nombre_item' => $guiso['nombre'], 'tipo_ui' => 'quantity', 'precio_unitario' => $guiso['precio'], 'per_person' => false]);
        }
    }

    private function addMachacadoConHuevoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Machacado en Salsa con Huevo"
        $model->insert([
            'nombre_item' => 'Machacado en Salsa con Huevo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Revuelto con chile, tomate y cebolla. Puedes elegir 2 guarniciones sencillas',
            'precio_unitario' => 195.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $machacado_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $machacado_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $machacado_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addMachacadoSinHuevoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Machacado en Salsa Sin Huevo"
        $model->insert([
            'nombre_item' => 'Machacado en Salsa Sin Huevo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Revuelto con chile, tomate y cebolla. Puedes elegir 2 guarniciones sencillas',
            'precio_unitario' => 180.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $machacado_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $machacado_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $machacado_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addChicharronConHuevoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Chicharron en Salsa con Huevo"
        $model->insert([
            'nombre_item' => 'Chicharron en Salsa con Huevo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Chicharron cuerito en salsa roja. Puedes elegir 2 guarniciones sencillas',
            'precio_unitario' => 195.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chicharron_id = $model->getInsertID();

        // Nivel: Elige 2 Guarniciones
        $model->insert(['parent_id' => $chicharron_id, 'nombre_item' => 'Elige 2 Guarniciones', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $guarniciones_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarniciones_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addChicharronSinHuevoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Chicharron En Salsa Sin Huevo"
        $model->insert([
            'nombre_item' => 'Chicharron En Salsa Sin Huevo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 180.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chicharron_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $chicharron_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $chicharron_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addQuesoPanelaEnSalsaCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Queso Panela En Salsa"
        $model->insert([
            'nombre_item' => 'Queso Panela En Salsa',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Queso panela en salsa, las guarniciones tu las eliges.',
            'precio_unitario' => 189.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $queso_panela_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $queso_panela_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $queso_panela_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addHuevosDivorciadosCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Huevos Divorciados"
        $model->insert([
            'nombre_item' => 'Huevos Divorciados',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => '',
            'precio_unitario' => 150.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $huevos_divorciados_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $huevos_divorciados_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijoles con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $huevos_divorciados_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        $guarniciones2 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijoles con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones2 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addHuevosRevueltosConTocinoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Huevos Revueltos con Tocino"
        $model->insert([
            'nombre_item' => 'Huevos Revueltos con Tocino',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'HUEVOS REVUELTOS CON TOCINO',
            'precio_unitario' => 165.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones1 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones1 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        $guarniciones2 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones2 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addHuevosRevueltosConJamonCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Huevos Revueltos con Jamón"
        $model->insert([
            'nombre_item' => 'Huevos Revueltos con Jamón',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'HUEVOS REVUELTOS CON JAMÓN',
            'precio_unitario' => 150.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones1 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones1 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        $guarniciones2 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones2 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addPlatilloBistecEnSalsaCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Platillo Bistec en Salsa"
        $model->insert([
            'nombre_item' => 'Platillo Bistec en Salsa',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Rico bistec en salsa con 2 guarniciones.',
            'precio_unitario' => 195.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $bistec_id = $model->getInsertID();

        // Nivel: Elige 2 Guarniciones
        $model->insert(['parent_id' => $bistec_id, 'nombre_item' => 'Elige 2 Guarniciones', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $guarniciones_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarniciones_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addPlatilloChicharronPrensadoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Platillo de Chicharron Prensado"
        $model->insert([
            'nombre_item' => 'Platillo de Chicharron Prensado',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'Delicioso chicharron prensado con 2 guarniciones.',
            'precio_unitario' => 180.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $chicharron_id = $model->getInsertID();

        // Nivel: Elige 2 Guarniciones
        $model->insert(['parent_id' => $chicharron_id, 'nombre_item' => 'Elige 2 Guarniciones', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $guarniciones_level_id = $model->getInsertID();
        $guarniciones = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones as $guarnicion) {
            $model->insert(['parent_id' => $guarniciones_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addHuevosRevueltosConChorizoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "Huevos Revueltos con Chorizo"
        $model->insert([
            'nombre_item' => 'Huevos Revueltos con Chorizo',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'HUEVOS REVUELTOS CON CHORIZO',
            'precio_unitario' => 165.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: 1er Guarnición
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => '1er Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion1_level_id = $model->getInsertID();
        $guarniciones1 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Huevo Estrellado', 'precio' => 0.00],
            ['nombre' => 'Huevo Revuelto', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones1 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion1_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }

        // Nivel: 2da Guarnición
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => '2da Guarnición', 'tipo_ui' => 'radio', 'per_person' => false]);
        $guarnicion2_level_id = $model->getInsertID();
        $guarniciones2 = [
            ['nombre' => 'Birria Mapolato', 'precio' => 30.00],
            ['nombre' => 'Bistec en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Prensado en Salsa', 'precio' => 30.00],
            ['nombre' => 'Chicharron Tronador en Salsa', 'precio' => 30.00],
            ['nombre' => 'Frijolitos', 'precio' => 0.00],
            ['nombre' => 'Frijolitos con Chorizo', 'precio' => 0.00],
            ['nombre' => 'Panela en Salsa', 'precio' => 30.00],
            ['nombre' => 'Papas a la Mexicana', 'precio' => 0.00],
            ['nombre' => 'Papas con Chorizo', 'precio' => 0.00],
        ];
        foreach ($guarniciones2 as $guarnicion) {
            $model->insert(['parent_id' => $guarnicion2_level_id, 'nombre_item' => $guarnicion['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $guarnicion['precio'], 'per_person' => false]);
        }
    }

    private function addLicuadoChicoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "LICUADO CHICO"
        $model->insert([
            'nombre_item' => 'LICUADO CHICO',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'LICUADO CHICO',
            'precio_unitario' => 60.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: Fruta
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Fruta', 'tipo_ui' => 'radio', 'per_person' => false]);
        $fruta_level_id = $model->getInsertID();
        $frutas = [
            ['nombre' => 'LICUADO DE FRESA', 'precio' => 0.00],
            ['nombre' => 'LICUADO DE MELON', 'precio' => 0.00],
            ['nombre' => 'LICUADO DE PAPAYA', 'precio' => 0.00],
            ['nombre' => 'LICUADO PLATANO', 'precio' => 0.00],
        ];
        foreach ($frutas as $fruta) {
            $model->insert(['parent_id' => $fruta_level_id, 'nombre_item' => $fruta['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $fruta['precio'], 'per_person' => false]);
        }

        // Nivel: LECHE
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'LECHE', 'tipo_ui' => 'radio', 'per_person' => false]);
        $leche_level_id = $model->getInsertID();
        $leches = [
            ['nombre' => 'Deslactosada', 'precio' => 0.00],
            ['nombre' => 'Entera', 'precio' => 0.00],
        ];
        foreach ($leches as $leche) {
            $model->insert(['parent_id' => $leche_level_id, 'nombre_item' => $leche['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $leche['precio'], 'per_person' => false]);
        }

        // Nivel: Endulzante y Extras
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Endulzante y Extras', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'AVENA', 'precio' => 0.00],
            ['nombre' => 'Canela', 'precio' => 0.00],
            ['nombre' => 'CHOCOMILK', 'precio' => 0.00],
            ['nombre' => 'EXTRA PLATANO', 'precio' => 15.00],
            ['nombre' => 'GRANOLA', 'precio' => 0.00],
            ['nombre' => 'VAINILLA', 'precio' => 0.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }

    private function addRefrescoCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "REFRESCO"
        $model->insert([
            'nombre_item' => 'REFRESCO',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'REFRESCO',
            'precio_unitario' => 40.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: SABOR
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'SABOR', 'tipo_ui' => 'radio', 'per_person' => false]);
        $sabor_level_id = $model->getInsertID();
        $sabores = [
            ['nombre' => 'Coca Cola S Azucar', 'precio' => 0.00],
            ['nombre' => 'COCA DIETA', 'precio' => 0.00],
            ['nombre' => 'COCA REGULAR', 'precio' => 0.00],
            ['nombre' => 'FANTA FRESA', 'precio' => 0.00],
            ['nombre' => 'FANTA NARANJA', 'precio' => 0.00],
            ['nombre' => 'FRESCA', 'precio' => 0.00],
            ['nombre' => 'MANZANITA', 'precio' => 0.00],
            ['nombre' => 'SPRITE', 'precio' => 0.00],
        ];
        foreach ($sabores as $sabor) {
            $model->insert(['parent_id' => $sabor_level_id, 'nombre_item' => $sabor['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $sabor['precio'], 'per_person' => false]);
        }
    }

    private function addAguaChicaCustomization($model, $parentId)
    {
        // 1. Insertar el platillo principal "AGUA CHICA"
        $model->insert([
            'nombre_item' => 'AGUA CHICA',
            'parent_id' => $parentId,
            'tipo_ui' => 'checkbox',
            'descripcion' => 'AGUA CHICA',
            'precio_unitario' => 45.00,
            'activo' => 1,
            'per_person' => true,
        ]);
        $main_dish_id = $model->getInsertID();

        // Nivel: Sabor
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Sabor', 'tipo_ui' => 'radio', 'per_person' => false]);
        $sabor_level_id = $model->getInsertID();
        $sabores = [
            ['nombre' => 'AGUA DE FRESA', 'precio' => 0.00],
            ['nombre' => 'AGUA DE MELON', 'precio' => 0.00],
            ['nombre' => 'AGUA DE PAPAYA', 'precio' => 0.00],
            ['nombre' => 'AGUA DE PEPINO', 'precio' => 0.00],
            ['nombre' => 'AGUA DE PIÑA', 'precio' => 0.00],
            ['nombre' => 'AGUA DE SANDIA', 'precio' => 0.00],
            ['nombre' => 'JAMAICA', 'precio' => 0.00],
        ];
        foreach ($sabores as $sabor) {
            $model->insert(['parent_id' => $sabor_level_id, 'nombre_item' => $sabor['nombre'], 'tipo_ui' => 'radio', 'precio_unitario' => $sabor['precio'], 'per_person' => false]);
        }

        // Nivel: Fruta Extra y Endulzantes
        $model->insert(['parent_id' => $main_dish_id, 'nombre_item' => 'Fruta Extra y Endulzantes', 'tipo_ui' => 'checkbox', 'per_person' => false]);
        $extras_level_id = $model->getInsertID();
        $extras = [
            ['nombre' => 'AZUCAR NORMAL', 'precio' => 0.00],
            ['nombre' => 'EXTRA FRESA', 'precio' => 15.00],
            ['nombre' => 'EXTRA PEPINO', 'precio' => 15.00],
            ['nombre' => 'EXTRA PIÑA', 'precio' => 15.00],
            ['nombre' => 'SIN AZUCAR', 'precio' => 0.00],
            ['nombre' => 'SPLENDA', 'precio' => 0.00],
        ];
        foreach ($extras as $extra) {
            $model->insert(['parent_id' => $extras_level_id, 'nombre_item' => $extra['nombre'], 'tipo_ui' => 'checkbox', 'precio_unitario' => $extra['precio'], 'per_person' => false]);
        }
    }
}