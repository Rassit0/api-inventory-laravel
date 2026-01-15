


# Ejecutar en Dev

1. Clonar el repositorio
2. Instalar dependencias `composer install`
3. Clonar `env.template` y renombrar a `.env` y completar las cariables de entorno en .env
4. Levantar la base de datos `docker compose up -d`
5. Ejecutar las migraciones y semillas `php artisan migrate:fresh --seed --seeder=PermissionsDemoSeeder`
6. Ejecutar proyecto `php artisan serve`
