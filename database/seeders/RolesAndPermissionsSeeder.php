<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each module
        $this->createProductPermissions();
        $this->createCategoryPermissions();
        $this->createSupplierPermissions();
        $this->createCustomerPermissions();
        $this->createEmployeePermissions();
        $this->createPurchaseOrderPermissions();
        $this->createSalesOrderPermissions();
        $this->createWarehousePermissions();
        $this->createInventoryPermissions();
        $this->createInventoryTransactionPermissions();
        $this->createStockAdjustmentPermissions();
        $this->createReportPermissions();
        $this->createUserPermissions();

        // Create roles and assign permissions
        $this->createRoles();

        // Create admin user
        $this->createAdminUser();
    }

    private function createProductPermissions()
    {
        Permission::create(['name' => 'product.view']);
        Permission::create(['name' => 'product.create']);
        Permission::create(['name' => 'product.edit']);
        Permission::create(['name' => 'product.delete']);
    }

    private function createCategoryPermissions()
    {
        Permission::create(['name' => 'category.view']);
        Permission::create(['name' => 'category.create']);
        Permission::create(['name' => 'category.edit']);
        Permission::create(['name' => 'category.delete']);
    }

    private function createSupplierPermissions()
    {
        Permission::create(['name' => 'supplier.view']);
        Permission::create(['name' => 'supplier.create']);
        Permission::create(['name' => 'supplier.edit']);
        Permission::create(['name' => 'supplier.delete']);
    }

    private function createCustomerPermissions()
    {
        Permission::create(['name' => 'customer.view']);
        Permission::create(['name' => 'customer.create']);
        Permission::create(['name' => 'customer.edit']);
        Permission::create(['name' => 'customer.delete']);
    }

    private function createEmployeePermissions()
    {
        Permission::create(['name' => 'employee.view']);
        Permission::create(['name' => 'employee.create']);
        Permission::create(['name' => 'employee.edit']);
        Permission::create(['name' => 'employee.delete']);
    }

    private function createPurchaseOrderPermissions()
    {
        Permission::create(['name' => 'purchase_order.view']);
        Permission::create(['name' => 'purchase_order.create']);
        Permission::create(['name' => 'purchase_order.edit']);
        Permission::create(['name' => 'purchase_order.delete']);
        Permission::create(['name' => 'purchase_order.approve']);
    }

    private function createSalesOrderPermissions()
    {
        Permission::create(['name' => 'sales_order.view']);
        Permission::create(['name' => 'sales_order.create']);
        Permission::create(['name' => 'sales_order.edit']);
        Permission::create(['name' => 'sales_order.delete']);
        Permission::create(['name' => 'sales_order.approve']);
    }

    private function createWarehousePermissions()
    {
        Permission::create(['name' => 'warehouse.view']);
        Permission::create(['name' => 'warehouse.create']);
        Permission::create(['name' => 'warehouse.edit']);
        Permission::create(['name' => 'warehouse.delete']);
    }

    private function createInventoryPermissions()
    {
        Permission::create(['name' => 'inventory.view']);
        Permission::create(['name' => 'inventory.create']);
        Permission::create(['name' => 'inventory.edit']);
        Permission::create(['name' => 'inventory.delete']);
    }

    private function createInventoryTransactionPermissions()
    {
        Permission::create(['name' => 'inventory_transaction.view']);
        Permission::create(['name' => 'inventory_transaction.create']);
    }

    private function createStockAdjustmentPermissions()
    {
        Permission::create(['name' => 'stock_adjustment.view']);
        Permission::create(['name' => 'stock_adjustment.create']);
        Permission::create(['name' => 'stock_adjustment.edit']);
        Permission::create(['name' => 'stock_adjustment.delete']);
        Permission::create(['name' => 'stock_adjustment.approve']);
    }

    private function createReportPermissions()
    {
        Permission::create(['name' => 'report.sales']);
        Permission::create(['name' => 'report.inventory']);
        Permission::create(['name' => 'report.purchase']);
    }

    private function createUserPermissions()
    {
        Permission::create(['name' => 'user.view']);
        Permission::create(['name' => 'user.create']);
        Permission::create(['name' => 'user.edit']);
        Permission::create(['name' => 'user.delete']);
        Permission::create(['name' => 'role.view']);
        Permission::create(['name' => 'role.create']);
        Permission::create(['name' => 'role.edit']);
        Permission::create(['name' => 'role.delete']);
        Permission::create(['name' => 'permission.view']);
        Permission::create(['name' => 'permission.create']);
        Permission::create(['name' => 'permission.edit']);
        Permission::create(['name' => 'permission.delete']);
    }

    private function createRoles()
    {
        // Create Administrator role and give all permissions
        $adminRole = Role::create(['name' => 'Administrator']);
        $adminRole->givePermissionTo(Permission::all());

        // Create Manager role with limited permissions
        $managerRole = Role::create(['name' => 'Manager']);
        $managerRole->givePermissionTo([
            'product.view', 'product.create', 'product.edit',
            'category.view', 'category.create', 'category.edit',
            'supplier.view', 'supplier.create', 'supplier.edit',
            'customer.view', 'customer.create', 'customer.edit',
            'employee.view',
            'purchase_order.view', 'purchase_order.create', 'purchase_order.edit', 'purchase_order.approve',
            'sales_order.view', 'sales_order.create', 'sales_order.edit', 'sales_order.approve',
            'warehouse.view',
            'inventory.view', 'inventory.edit',
            'inventory_transaction.view',
            'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.edit', 'stock_adjustment.approve',
            'report.sales', 'report.inventory', 'report.purchase',
        ]);

        // Create Sales Representative role
        $salesRole = Role::create(['name' => 'Sales Representative']);
        $salesRole->givePermissionTo([
            'product.view',
            'category.view',
            'customer.view', 'customer.create', 'customer.edit',
            'sales_order.view', 'sales_order.create', 'sales_order.edit',
            'inventory.view',
            'report.sales',
        ]);

        // Create Inventory Manager role
        $inventoryRole = Role::create(['name' => 'Inventory Manager']);
        $inventoryRole->givePermissionTo([
            'product.view',
            'category.view',
            'supplier.view',
            'purchase_order.view',
            'sales_order.view',
            'warehouse.view', 'warehouse.edit',
            'inventory.view', 'inventory.edit',
            'inventory_transaction.view', 'inventory_transaction.create',
            'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.edit',
            'report.inventory',
        ]);

        // Create Purchasing Officer role
        $purchasingRole = Role::create(['name' => 'Purchasing Officer']);
        $purchasingRole->givePermissionTo([
            'product.view',
            'category.view',
            'supplier.view', 'supplier.create', 'supplier.edit',
            'purchase_order.view', 'purchase_order.create', 'purchase_order.edit',
            'inventory.view',
            'report.purchase',
        ]);
    }

    private function createAdminUser()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $admin->assignRole('Administrator');
    }
}
