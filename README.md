# SIMPINNA Tecate

Aplicación PHP con MySQL para encuestas y administración de SIMPINNA.

## Desarrollo local en macOS

Requisitos:

```bash
brew install php mysql composer
composer install
```

Copia `.env.example` como `.env.local`, configura la base local y ejecuta:

```bash
mysql -u root -e "CREATE DATABASE simpinna_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root simpinna_local < back-end/database/schema.sql
mysql -u root simpinna_local < back-end/database/seed-development.sql
php bin/admin-password.php set administrador
./start-local.sh
```

La contraseña se solicita de forma interactiva, no se muestra en pantalla y debe tener entre 12 y 128 caracteres. No hay credenciales predeterminadas.

Abre <http://localhost:8000>. El panel está en <http://localhost:8000/front-end/frames/admin/login.php>.

## Actualizar una base existente

Haz primero un respaldo fuera del directorio público y después ejecuta:

```bash
php bin/backup-database.php /ruta/privada/simpinna-antes.sql
php bin/migrate.php
php bin/admin-password.php set administrador
```

La primera migración de seguridad invalida todas las contraseñas anteriores. Repite el segundo comando para cada cuenta administrativa antes de reabrir el sitio.

Las migraciones también reparan sesiones que mezclaban encuestas, eliminan sesiones vacías, habilitan el versionado transparente y protegen las relaciones históricas.

## Instalación en VPS Debian o Ubuntu

Instala PHP 8.2 o posterior, sus extensiones y Composer:

```bash
sudo apt update
sudo apt install -y php-cli php-fpm php-mysql php-gd php-curl php-mbstring php-xml php-zip unzip composer
php -r 'foreach (["argon2id" => defined("PASSWORD_ARGON2ID"), "gd" => extension_loaded("gd"), "curl" => extension_loaded("curl"), "mysqli" => extension_loaded("mysqli")] as $n => $ok) echo $n . "=" . ($ok ? "ok" : "falta") . PHP_EOL;'
```

Dentro del proyecto instala exactamente las versiones registradas en `composer.lock`:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

En Apache habilita reescritura y permite `.htaccess` para el directorio del sitio:

```bash
sudo a2enmod rewrite
sudo systemctl reload apache2
```

La configuración del VirtualHost debe incluir `AllowOverride All`. Da escritura al usuario del servidor web únicamente en `storage` y `uploads`, conserva `.env` fuera de Git y asegúrate de que no exista un `.env.local` de desarrollo en producción.

Antes de abrir el sitio actualizado:

```bash
php bin/backup-database.php /var/backups/simpinna/antes-de-migrar.sql
php bin/migrate.php
php bin/admin-password.php set administrador
```

## Dibujos y Cloudflare R2

En desarrollo se usa almacenamiento privado local:

```dotenv
DRAWING_STORAGE_DRIVER=local
```

En producción usa un bucket R2 privado y un token limitado exclusivamente a ese bucket:

```dotenv
DRAWING_STORAGE_DRIVER=r2
R2_ACCOUNT_ID=
R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_BUCKET=
```

Para migrar dibujos existentes:

```bash
php bin/migrate-drawings-r2.php --dry-run
php bin/migrate-drawings-r2.php
php bin/cleanup-local-drawings.php
```

La limpieza local solo retira un archivo después de comprobar que su objeto existe en R2. Para reintentar eliminaciones pendientes:

```bash
php bin/process-storage-delete-queue.php
```

El bucket no debe habilitar acceso público. El panel entrega enlaces firmados de 60 segundos únicamente después de validar la sesión y el permiso de resultados.

## Archivos sensibles

Respaldos SQL, diagnósticos, `.env`, `vendor`, comandos e internals del backend están bloqueados por Apache y por el router local. Nunca guardes respaldos reales dentro del proyecto.

Si un respaldo o una credencial llegó a publicarse en un repositorio remoto, eliminar el archivo actual no basta: hay que reescribir el historial desde un clon seguro, coordinar el cambio con quienes tengan clones existentes y rotar todas las credenciales expuestas antes del siguiente despliegue.
