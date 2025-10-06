## 🚀 1. Inicialización del Proyecto

Sigue estos pasos para clonar, configurar y poner en marcha la aplicación.

### 1.1. Requisitos del Sistema

*   PHP 7.4+ (Recomendado PHP 8.1+)
*   Composer
*   Servidor Web (Apache/Nginx) o XAMPP/WAMP
*   Base de Datos MySQL/MariaDB

### 1.2. Instalación de Dependencias

Navega a la carpeta raíz del proyecto (`/mapolato`) y ejecuta Composer:

```bash
composer install
```

### 1.3. Configuración del Entorno (`.env`)

1.  Copia el archivo de ejemplo y renómbralo:
    ```bash
    copy .env.example .env
    ```
2.  Abre el archivo **`.env`** y configura las siguientes secciones:

#### A. Configuración de la Aplicación

Asegúrate de que la `baseURL` y la `encryptionKey` sean correctas para tu entorno.

```env
# URL Base (CRÍTICO para XAMPP)
app.baseURL = 'http://localhost/mapolato/public/' 

# Clave de Seguridad (Generar una nueva)
# Ejecuta: php spark key:generate
app.encryptionKey = 'base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'

# Número de WhatsApp del Negocio (Para el Deep Link de confirmación)
app.businessWhatsapp = +5215512345678 
```

#### B. Configuración de la Base de Datos

Configura los detalles de tu conexión SQL:

```env
database.default.hostname = localhost
database.default.database = mapolato_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
```

#### C. Configuración de Seguridad JWT

Configura la clave secreta y los tiempos de vida para la autenticación de administradores:

```env
# --- CONFIGURACIÓN JWT Y SEGURIDAD ---
JWT_SECRET_KEY = 'TuClaveSecretaMuyLargaYCompleja'
JWT_AT_TIME_TO_LIVE = 900       # 15 minutos
JWT_RT_TIME_TO_LIVE = 2592000   # 30 días
```

## 🛠️ 2. Inicialización de la Base de Datos y Datos de Prueba

Este proyecto requiere varias tablas y datos iniciales para el menú y el usuario administrador.

### 2.1. Ejecutar Migraciones (Creación de Tablas)

Ejecuta el comando para crear todas las tablas necesarias (cotizaciones, menú, tokens, usuarios admin):
Ejecuta el comando para crear todas las tablas necesarias (incluyendo cotizaciones con `user_id`, ítems de menú, tokens de refresco y usuarios administradores):
```bash
php spark migrate
```

### 2.2. Ejecutar Seeders (Datos Iniciales)

Ejecuta los seeders para poblar el menú dinámico y crear el usuario administrador de prueba.

```bash
# 1. Sembrar el Menú Dinámico
php spark db:seed MenuSeeder

# 2. Crear el Usuario Administrador de Prueba
php spark db:seed AdminUserSeeder

# 3. (Opcional) Sembrar Cotizaciones de Prueba
php spark db:seed QuotationDataSeeder
```

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin@gmail.com` | `admin123` |

## 🌐 3. Acceso a la Aplicación

Una vez que el servidor esté corriendo (`php spark serve` o XAMPP):

| Interfaz | URL | Propósito |
| :--- | :--- | :--- |
| **Página Pública** | `http://localhost/mapolato/public/` | Formulario de Cotización Dinámico (Clientes) |
| **Panel de Admin** | `http://localhost/mapolato/public/admin` | Interfaz de Login de Administración |

