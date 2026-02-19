<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsDemoSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'dashboard']);
        // firstOrCreate permissions
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_role']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_role']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_role']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_role']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_user']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_user']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_user']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_user']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'settings']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_product']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_product']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_product']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_product']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'show_inventory_product']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'show_wallet_price_product']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_client']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_client']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_client']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_client']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_sale']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_sale']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_sale']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_sale']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'return']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_purchase']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_purchase']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_purchase']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_purchase']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'register_transport']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'list_transport']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'edit_transport']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'delete_transport']);

        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'conversions']);
        Permission::firstOrCreate(['guard_name' => 'api', 'name' => 'kardex']);

        // firstOrCreate roles and assign existing permissions

        $role3 = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

       $user = User::firstOrCreate(
    ['email' => 'laravest@gmail.com'], // condición de búsqueda
    [
        'name' => 'Super-Admin User',
        'password' => bcrypt('12345678')
    ]
);


        $user->assignRole($role3);
    }
}

// Ejecutar $php artisan migrate:fresh --seed --seeder=PermissionsDemoSeeder
