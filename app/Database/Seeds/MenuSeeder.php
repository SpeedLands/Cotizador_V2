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
            ['nombre_item' => 'Comida Principal', 'parent_id' => null, 'tipo_ui' => 'checkbox', 'descripcion' => 'Selecciona el tipo de servicio de comida principal.', 'precio_unitario' => 0.00, 'activo' => 1],
            ['nombre_item' => 'Mesas y Barras Temáticas', 'parent_id' => null, 'tipo_ui' => 'checkbox', 'descripcion' => 'Selecciona mesas de bocadillos, postres, snacks o ensaladas.', 'precio_unitario' => 0.00, 'activo' => 1],
            ['nombre_item' => 'Charcutería', 'parent_id' => null, 'tipo_ui' => 'checkbox', 'descripcion' => 'Opciones de tablas, mesas o porciones individuales.', 'precio_unitario' => 0.00, 'activo' => 1],
            ['nombre_item' => 'Bebidas y Estaciones', 'parent_id' => null, 'tipo_ui' => 'checkbox', 'descripcion' => 'Estaciones de café o bebidas en dispensador.', 'precio_unitario' => 0.00, 'activo' => 1],
            ['nombre_item' => 'Modalidad de Servicio', 'parent_id' => null, 'tipo_ui' => 'checkbox', 'descripcion' => 'Define el tipo de servicio para tu evento.', 'precio_unitario' => 0.00, 'activo' => 1],
            ['nombre_item' => 'Otros Servicios', 'parent_id' => null, 'tipo_ui' => 'checkbox', 'descripcion' => 'Opciones adicionales no listadas.', 'precio_unitario' => 0.00, 'activo' => 1],
        ];
        $model->insertBatch($rootCategories); // INSERCIÓN 1

        // Obtener IDs de Nivel 1
        $comidaId      = $model->where('nombre_item', 'Comida Principal')->first()['id_item'];
        $mesasBarrasId = $model->where('nombre_item', 'Mesas y Barras Temáticas')->first()['id_item'];
        $charcuteriaId = $model->where('nombre_item', 'Charcutería')->first()['id_item'];
        $bebidasId     = $model->where('nombre_item', 'Bebidas y Estaciones')->first()['id_item'];
        $modalidadId   = $model->where('nombre_item', 'Modalidad de Servicio')->first()['id_item'];
        $otrosId       = $model->where('nombre_item', 'Otros Servicios')->first()['id_item'];


        // =================================================================
        // ETAPA 2: ÍTEMS INTERMEDIOS (Nivel 2)
        // =================================================================
        $level2Items = [];
        $rawLevel2 = [
            // Hijos de Comida Principal
            ['nombre' => 'Consumo en chafers / baños maria', 'parent_id' => $comidaId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Cobro por persona.', 'precio_base' => 100.00],
            ['nombre' => 'Consumo en cazuelas', 'parent_id' => $comidaId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Cobro por persona.', 'precio_base' => 10.00],
            ['nombre' => 'Lunch box', 'parent_id' => $comidaId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Cobro por persona.', 'precio_base' => 10.00],
            
            // Hijos de Mesas y Barras (Estos serán padres de Nivel 3)
            // IMPORTANTE: Usamos 'radio' o 'checkbox' aquí para que disparen la carga de sus hijos
            ['nombre' => 'Mesa de bocadillos / canapés (mínimo 30 personas)', 'parent_id' => $mesasBarrasId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Selecciona para ver opciones.', 'precio_base' => 10.00],
            ['nombre' => 'Mesa de Postres (mínimo 30 personas)', 'parent_id' => $mesasBarrasId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Selecciona para ver opciones.', 'precio_base' => 10.00],
            ['nombre' => 'Mesa de snacks (papitas y dulces) (mínimo 30 personas)', 'parent_id' => $mesasBarrasId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Precio fijo.', 'precio_base' => 10.00],
            ['nombre' => 'Barra de ensaladas (mínimo 30 personas)', 'parent_id' => $mesasBarrasId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Precio fijo.', 'precio_base' => 10.00],

            // Hijos de Charcutería
            ['nombre' => 'Tabla de charcutería (Arriba de 10 personas)', 'parent_id' => $charcuteriaId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Precio fijo.', 'precio_base' => 10.00],
            ['nombre' => 'Mesa de charcuteria (Arriba de 50 personas)', 'parent_id' => $charcuteriaId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Precio fijo.', 'precio_base' => 10.00],
            ['nombre' => 'Charcutería individual (Arriba de 15 personas)', 'parent_id' => $charcuteriaId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Cobro por porción.', 'precio_base' => 10.00],

            // Hijos de Bebidas (Estación de Café será padre de Nivel 3)
            ['nombre' => 'Estación de Café', 'parent_id' => $bebidasId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Selecciona para ver opciones de enchufe.', 'precio_base' => 10.00],
            ['nombre' => 'Bebida en dispensador (aguas de sabor)', 'parent_id' => $bebidasId, 'tipo_ui' => 'checkbox', 'descripcion' => '', 'precio_base' => 0.00],

            // Hijos de Modalidad
            ['nombre' => 'Buffet / Self Service. (Menores a 20 personas)', 'parent_id' => $modalidadId, 'tipo_ui' => 'radio', 'descripcion' => 'Los invitados se sirven directamente.', 'precio_base' => 0.00],
            ['nombre' => 'Buffet asistido o servido por staff (Costo adicional)', 'parent_id' => $modalidadId, 'tipo_ui' => 'radio', 'descripcion' => 'Personal para asistir.', 'precio_base' => 500.00],
            ['nombre' => 'Servicio a la mesa. (Costo adicional)', 'parent_id' => $modalidadId, 'tipo_ui' => 'radio', 'descripcion' => 'Meseros incluidos.', 'precio_base' => 1500.00],

            // Hijos de Otros 
            // ['nombre' => 'Otros:', 'parent_id' => $otrosId, 'tipo_ui' => 'checkbox', 'descripcion' => 'Especificar en notas.', 'precio_base' => 0.00],
        ];

        foreach ($rawLevel2 as $item) {
            $level2Items[] = [
                'parent_id'       => $item['parent_id'],
                'nombre_item'     => $item['nombre'],
                'tipo_ui'         => $item['tipo_ui'],
                'descripcion'     => $item['descripcion'],
                'precio_unitario' => $item['precio_base'],
                'activo'          => 1,
            ];
        }
        $model->insertBatch($level2Items); // INSERCIÓN 2 (CRÍTICA PARA QUE FUNCIONE LO SIGUIENTE)


        // =================================================================
        // ETAPA 3: ÍTEMS DETALLADOS (Nivel 3 - Hijos de los de Nivel 2)
        // =================================================================
        
        // Ahora sí podemos buscar los IDs porque ya se hizo la INSERCIÓN 2
        $parentBocadillosId = $model->where('nombre_item', 'Mesa de bocadillos / canapés (mínimo 30 personas)')->first()['id_item'];
        $parentPostresId    = $model->where('nombre_item', 'Mesa de Postres (mínimo 30 personas)')->first()['id_item'];
        $parentCafeId       = $model->where('nombre_item', 'Estación de Café')->first()['id_item'];

        $level3Items = [];

        // 3.1 Detalle Bocadillos
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
            $level3Items[] = ['parent_id' => $parentBocadillosId, 'nombre_item' => $item['nombre'], 'tipo_ui' => 'quantity', 'descripcion' => '', 'precio_unitario' => $item['precio'], 'activo' => 1];
        }

        // 3.2 Detalle Postres
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
            $level3Items[] = ['parent_id' => $parentPostresId, 'nombre_item' => $item['nombre'], 'tipo_ui' => 'quantity', 'descripcion' => '', 'precio_unitario' => $item['precio'], 'activo' => 1];
        }

        // 3.3 Detalle Café (Preguntas)
        $cafeOpciones = [
            ['nombre' => 'Sí hay acceso a enchufes cerca', 'tipo_ui' => 'radio', 'descripcion' => 'No se requiere extensión.', 'precio_base' => 0.00],
            ['nombre' => 'No hay cerca, se necesita extensión', 'tipo_ui' => 'radio', 'descripcion' => 'Costo adicional por extensión.', 'precio_base' => 50.00],
        ];
        foreach ($cafeOpciones as $item) {
            $level3Items[] = ['parent_id' => $parentCafeId, 'nombre_item' => 'Enchufes: ' . $item['nombre'], 'tipo_ui' => $item['tipo_ui'], 'descripcion' => $item['descripcion'], 'precio_unitario' => $item['precio_base'], 'activo' => 1];
        }

        $model->insertBatch($level3Items); // INSERCIÓN 3
    }
}