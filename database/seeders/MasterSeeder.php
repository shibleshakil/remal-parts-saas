<?php

namespace Database\Seeders;

use App\Helpers\RoutePermissionHelper;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Master seeder: branch + super admin + roles/permissions synced from routes.
 *
 * Re-run any time routes are added/renamed/removed:
 *   php artisan db:seed --class=MasterSeeder
 *
 * Every run UPSERTS (updateOrCreate): existing branch/super-admin rows are
 * refreshed to match, new route permissions are created, stale permissions
 * (routes that no longer exist) are deleted, and the permission label file
 * (lang/en/permission.php) is regenerated to match.
 *
 * Caution: re-seeding resets the Super Admin password to Admin@123.
 */
class MasterSeeder extends Seeder
{
    protected string $labelFile;
    protected Branch $branch;

    /**
     * Human-readable module titles used as headings in the permissions UI.
     * Keys are route-name prefixes (part before the first dot); single-word
     * route names are their own module.
     */
    protected array $moduleTitles = [
        'run'             => 'System Commands',
        'home'            => 'Dashboard',
        'stockSummary'    => 'Stock Summary',
        'profitSummary'   => 'Profit Summary',
        'profile'         => 'Profile',
        'vehicle'         => 'Vehicles',
        'spare-parts'     => 'Spare Parts',
        'productSearch'   => 'Product Search',
        'sales'           => 'Sales',
        'quotes'          => 'Quotes',
        'banks'           => 'Banks',
        'balances'        => 'Balance In',
        'balances-out'    => 'Balance Out',
        'customer'        => 'Customers',
        'supplier'        => 'Suppliers',
        'expense-type'    => 'Expense Types',
        'expense'         => 'Expenses',
        'report'          => 'Reports',
        'purchase'        => 'Purchases',
        'user'            => 'Users',
        'balance-statement' => 'Balance Statement',
        'branch'          => 'Branches',
        'roles'           => 'Roles & Permissions',
        'stock-movment'   => 'Stock Transfers',
    ];

    /**
     * Friendly labels for permission action segments (part after the last dot).
     */
    protected array $actionLabels = [
        'index'               => 'View List',
        'create'              => 'Create',
        'store'               => 'Save / Add',
        'ajaxStore'           => 'Ajax Save / Add',
        'edit'                => 'Edit',
        'update'              => 'Save Changes',
        'show'                => 'Show Details',
        'delete'              => 'Remove',
        'trash'               => 'View Trash',
        'restore'             => 'Restore',
        'print'               => 'Print',
        'export'              => 'Export',
        'download'            => 'Download',
        'invoice'             => 'Invoice',
        'invoiceList'         => 'Invoice List',
        'print_barcode'       => 'Print Barcode',
        'print_qrcode'        => 'Print QR Code',
        'getSpareParts'       => 'Get Spare Parts',
        'getDataByBarcode'    => 'Get Data By Barcode',
        'customeStoreViaAjax' => 'Custom Save via Ajax',
        'bankStoreViaAjax'    => 'Bank Save via Ajax',
        'returnForm'          => 'Return Form',
        'return'              => 'Process Return',
        'returnPurchase'      => 'Return Purchase Form',
        'returnPurchasePost'  => 'Process Purchase Return',
        'pay-due'             => 'Pay Due',
        'payForm'             => 'Payment Form',
        'pay'                 => 'Process Payment',
        'transactions'        => 'View Transactions',
        'storeToSale'         => 'Save to Sale',
        'getVehicleDetails'   => 'Get Vehicle Details',
        'getItem'             => 'Get Item',
        'convertToSale'       => 'Convert to Sale',
        'getBalance'          => 'View Balance',
        'switch'              => 'Switch Branch',
        'setPermissionsForm'  => 'Set Permissions Form',
        'storePermissions'    => 'Save Permissions',
        'getPartsByBranch'    => 'Get Parts by Branch',
        'purchaseVehicle'     => 'Purchase Vehicle',
    ];

    public function __construct()
    {
        $this->labelFile = lang_path('en/permission.php');
    }

    public function run(): void
    {
        $this->seedBranch();
        $user = $this->seedSuperAdmin();
        $permissionNames = $this->syncPermissions();
        $this->assignRole($user);
        $this->syncLabelFile($permissionNames);
    }

    /**
     * Branch: Main Branch (id 1) — created or UPDATED to match on every run,
     * so re-seeding refreshes stale data.
     */
    protected function seedBranch(): Branch
    {
        $this->branch = Branch::updateOrCreate(
            ['id' => 1],
            [
                'name'    => 'Main Branch',
                'code'    => '0001',
                'phone'   => '1234567890',
                'email'   => 'admin@admin.com',
                'address' => 'Kohinur Tower, RD:7, Gulshan 1, Dhaka, Bangladesh',
            ]
        );

        $this->command?->info('Branch ready: ' . $this->branch->name . ' (' . $this->branch->code . ')');

        return $this->branch;
    }

    /**
     * Super Admin: created or UPDATED to match on every run.
     * Note: re-seeding resets the password to the default Admin@123.
     */
    protected function seedSuperAdmin(): User
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'              => 'Super Admin',
                'password'          => bcrypt('Admin@123'),
                'email_verified_at' => now(),
                'branch_id'         => $this->branch->id,
                'is_active'         => 1,
            ]
        );

        $this->command?->info('Super Admin ready: admin@admin.com (password reset to Admin@123)');

        return $user;
    }

    /**
     * Create one permission per named route (auth routes excluded) and
     * remove permissions whose route no longer exists.
     *
     * @return string[] all current permission names
     */
    protected function syncPermissions(): array
    {
        $routes = RoutePermissionHelper::getAllRoutes();

        $permissionNames = [];
        foreach ($routes as $routeGroups) {
            foreach ($routeGroups as $route) {
                $permission = Permission::firstOrCreate(['name' => $route]);
                $permissionNames[] = $permission->name;
            }
        }

        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->syncPermissions($permissionNames);

        // Remove permissions that no longer match any route
        // (detaches them from roles too).
        $stale = Permission::whereNotIn('name', $permissionNames)->pluck('name');
        if ($stale->isNotEmpty()) {
            Permission::whereIn('name', $stale)->delete();
            $this->command?->warn('Deleted stale permissions: ' . $stale->implode(', '));
        }

        $this->command?->info(count($permissionNames) . ' permissions synced from routes');

        return $permissionNames;
    }

    protected function assignRole(User $user): void
    {
        $role = Role::where('name', 'Super Admin')->firstOrFail();

        $user->assignRole($role);
        $user->forceFill(['role_id' => $role->id])->save();
    }

    /**
     * Regenerate lang/en/permission.php so every route has a readable label.
     * Keys: module names (route prefix) + every permission name.
     */
    protected function syncLabelFile(array $permissionNames): void
    {
        $current = [];
        if (File::exists($this->labelFile)) {
            $current = File::getRequire($this->labelFile);
            $current = is_array($current) ? $current : [];
        }

        $updated = [];

        // Module headings
        foreach ($this->moduleTitles as $module => $title) {
            $updated[$module] = $title;
        }

        // Module headings for route prefixes not listed above
        foreach ($permissionNames as $name) {
            $module = explode('.', $name)[0];
            if (!isset($updated[$module])) {
                $updated[$module] = $this->titleCase($module);
            }
        }

        // Permission labels
        foreach ($permissionNames as $name) {
            $updated[$name] = $this->makeLabel($name);
        }

        if ($updated === $current) {
            return; // nothing changed
        }

        $this->writeLabelFile($updated);

        $added   = array_diff(array_keys($updated), array_keys($current));
        $removed = array_diff(array_keys($current), array_keys($updated));

        if ($removed) {
            $this->command?->warn('Removed permission labels: ' . implode(', ', $removed));
        }
        if ($added) {
            $this->command?->info('Added permission labels: ' . implode(', ', $added));
        }
    }

    /**
     * "sales.parts-return.index" -> "Process Return" (action-based label).
     */
    protected function makeLabel(string $name): string
    {
        if (!str_contains($name, '.')) {
            return $this->titleCase($name);
        }

        $actionKey = last(explode('.', $name));

        return $this->actionLabels[$actionKey]
            ?? $this->titleCase($actionKey);
    }

    protected function titleCase(string $value): string
    {
        $cleaned = str_replace(['_', '-'], ' ', trim($value));
        $splitCamel = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $cleaned);

        return ucwords(strtolower(trim($splitCamel)));
    }

    protected function writeLabelFile(array $labels): void
    {
        File::ensureDirectoryExists(dirname($this->labelFile));

        $content = "<?php\n\nreturn [\n";

        foreach ($labels as $key => $value) {
            $key   = var_export($key, true);
            $value = var_export($value, true);
            $content .= "    $key => $value,\n";
        }

        $content .= "];\n";

        File::put($this->labelFile, $content);
    }
}
