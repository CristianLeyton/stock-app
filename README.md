# Starter Kit User Role

Este proyecto es un kit de inicio básico para crear un panel de administración en Laravel usando Filament. Permite gestionar usuarios y asignarles permisos de administrador de forma sencilla.

## Instalación

### 1. Instalar proyecto

```bash
composer install 
npm install && npm run dev
```

Editar `composer.json` y cambiar la línea:

```json
"minimum-stability": "dev"
```

### 2. Migraciones y seeders

```bash
php artisan migrate --seed
```

Esto crea el dashboard vacío y el usuario Filament.

### 3. Listo para trabajar, lo que sigue es para empezar a trabajar con otros recursos de tu proyecto

## Modelos y Recursos

### Crear modelo con migración

```bash
php artisan make:model Productos -m
```

### Crear recurso Filament (CRUD)

Primero crea las migraciones, seeders o factories necesarios.

```bash
php artisan make:filament-resource Productos
```

#### CRUD simple con modales

```bash
php artisan make:filament-resource Productos --simple --generate
```

#### CRUD con eliminaciones suaves (soft deletes)

```bash
php artisan make:filament-resource Productos --simple --generate --soft-deletes
```

En el modelo agrega:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
    // ...código existente...
}
```

En la migración agrega:

```php
Schema::table('users', function (Blueprint $table) {
    $table->softDeletes();
});
```

## Configuración de almacenamiento

En `.env` agrega:

```
FILESYSTEM_DISK="public"
```

En `config/filesystems.php`, en `disks` agrega:

```php
'public' => [
    'driver' => 'local',
    'root' => base_path('public/storage_public'),
    // 'root' => base_path('../storage_public'), // Usar al subir a InfinityFree
    'url' => env('APP_URL').'/storage_public',
    'visibility' => 'public',
]
```

## Políticas de usuario

Crear política para el modelo User:

```bash
php artisan make:policy UserPolicy --model=User
```

Esto permite bloquear acciones por defecto y gestionar permisos.

## Recomendaciones adicionales

- Configura correctamente los roles y permisos usando [Laravel Policies](https://laravel.com/docs/authorization#writing-policies).
- Protege rutas de administración con middleware `auth` y verifica el rol de usuario.
- Usa seeders para crear usuarios de prueba.
- Revisa la documentación oficial de [Filament](https://filamentphp.com/docs/3.x/panels/installation) y [Laravel](https://laravel.com/docs).

---

¡Listo! Con estos pasos tienes un panel de administración básico para gestionar usuarios y roles en Laravel con Filament


## Configurar ruta a IP local en tu pc, compartiendo con laragon

🔹 Paso 1: Fijar la IP de tu PC en la red local

Primero asegurate de que tu PC con Laragon tenga una IP fija en tu red (por ejemplo 192.168.0.100).

Si no la fijás, puede cambiar cuando reiniciás el router y dejar de funcionar.

En Windows, podés hacerlo desde Configuración de red o, mejor aún, desde la configuración del router (reservando la IP por dirección MAC).

🔹 Paso 2: Configurar el DNS en tu router

La mayoría de los routers permiten definir entradas de DNS locales.

Entrá al panel de tu router (normalmente en http://192.168.0.1 o http://192.168.1.1).

Buscá la sección de LAN / DHCP / DNS estático / Hostname mapping (el nombre cambia según el modelo).

Agregá una regla que diga:

Host: stock-app.me
IP:   192.168.0.100


Guardá los cambios y reiniciá el router si es necesario.

🔹 Paso 3: Probar en los dispositivos

En tu PC, celular o tablet conectado al WiFi del mismo router, entrá a:

https://stock-app.me


Si todo está bien configurado, debería resolver a tu PC (192.168.0.100) y mostrar la app con imágenes funcionando.

🔹 Paso 4 (Opcional): HTTPS real

Si querés que también funcione con HTTPS válido (candadito verde) en los celulares, tenés dos caminos:

Generar un certificado SSL válido para stock-app.me y usarlo en Laragon (más complicado porque stock-app.me no es un dominio público real).

O usar directamente el protocolo http://stock-app.me
 en tu red local (más simple y suficiente para pruebas internas).

✅ Con esto lográs que todos tus dispositivos usen la misma URL amigable stock-app.me, sin andar configurando cada celular manualmente.