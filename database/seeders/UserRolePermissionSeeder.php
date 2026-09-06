<?php

namespace Database\Seeders;

use App\Helpers\RoutePermissionHelper;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Label file path for permission labels.
     *
     * @var string
     */
    protected string $labelFile;

    public function __construct()
    {
        $this->labelFile = lang_path('en/permission.php');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create super admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'              => 'Super Admin',
                'password'          => bcrypt('Admin@123'),
                'email_verified_at' => now(),
            ]
        );

        // get all routes (auth routes excluded)
        $routes = RoutePermissionHelper::getAllRoutes();

        // create role
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        $permissionNames = [];

        foreach ($routes as $routeGroups) {
            foreach ($routeGroups as $route) {
                $permission = Permission::firstOrCreate(['name' => $route]);
                $permissionNames[] = $permission->name;
            }
        }

        // sync permissions to role
        $role->syncPermissions($permissionNames);

        // remove permissions that no longer match any route (detaches from roles too)
        $stalePermissions = Permission::whereNotIn('name', $permissionNames)->get();
        if ($stalePermissions->isNotEmpty()) {
            Permission::whereIn('id', $stalePermissions->pluck('id'))->delete();
            $this->command?->warn('Deleted stale permissions: ' . $stalePermissions->pluck('name')->implode(', '));
        }

        // assign role to user (spatie pivot + users.role_id column)
        $user->assignRole($role);
        $user->forceFill(['role_id' => $role->id])->save();

        // sync label file with current routes
        $this->syncLabelFile($permissionNames);
    }

    /**
     * Sync the permission label file with the current route permissions.
     */
    protected function syncLabelFile(array $permissionNames): void
    {
        $current = [];

        if (File::exists($this->labelFile)) {
            $current = File::getRequire($this->labelFile);
            $current = is_array($current) ? $current : [];
        }

        $updated = [];

        // Always generate fresh action-only labels using makeLabel()
        foreach ($permissionNames as $name) {
            $updated[$name] = $this->makeLabel($name);
        }

        // only write when something changed
        if ($updated === $current) {
            return;
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
     * Generate a human-readable action-only label from a permission name.
     * e.g. "vehicle.index" => "View List", "vehicle.invoice" => "Invoice"
     */
    protected function makeLabel(string $name): string
    {
        // 1. If single word (like "home", "profile", "stockSummary"), return Title Case
        if (!str_contains($name, '.')) {
            $cleaned = str_replace(['_', '-'], ' ', trim($name));
            $splitCamel = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $cleaned);
            return ucwords(strtolower(trim($splitCamel)));
        }

        // 2. Extract only the action part after the last dot (e.g. "sales.parts-return.index" => "index")
        $actionKey = last(explode('.', $name));

        // 3. Clean up underscores/hyphens and camelCase (e.g. "ajaxStore" => "ajax Store")
        $cleaned = str_replace(['_', '-'], ' ', trim($actionKey));
        $splitCamel = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $cleaned);
        $action = ucwords(strtolower(trim($splitCamel)));

        // 4. Action mapping dictionary
        $friendlyActions = [
            'Index'                 => 'View List',
            'Create'                => 'Create',
            'Store'                 => 'Save / Add',
            'Ajax Store'            => 'Ajax Save / Add',
            'Edit'                  => 'Edit',
            'Update'                => 'Save Changes',
            'Show'                  => 'Show Details',
            'Delete'                => 'Remove',
            'Trash'                 => 'View Trash',
            'Restore'               => 'Restore',
            'Print'                 => 'Print',
            'Export'                => 'Export',
            'Download'              => 'Download',
            'Invoice'               => 'Invoice',
            'Invoice List'          => 'Invoice List',
            'Print Barcode'         => 'Print Barcode',
            'Print Qrcode'          => 'Print QR Code',
            'Get Spare Parts'       => 'Get Spare Parts',
            'Get Data By Barcode'   => 'Get Data By Barcode',
            'Custome Store Via Ajax'=> 'Custom Save via Ajax',
            'Bank Store Via Ajax'   => 'Bank Save via Ajax',
            'Return Form'           => 'Return Form',
            'Return'                => 'Process Return',
            'Pay Due'               => 'Pay Due',
            'Store To Sale'         => 'Save to Sale',
            'Get Vehicle Details'   => 'Get Vehicle Details',
            'Get Item'              => 'Get Item',
            'Convert To Sale'       => 'Convert to Sale',
            'Get Balance'           => 'View Balance',
            'Pay'                   => 'Process Payment',
            'Pay Form'              => 'Payment Form',
            'Transactions'          => 'View Transactions',
            'Switch'                => 'Switch Branch',
            'Set Permissions Form'  => 'Set Permissions Form',
            'Store Permissions'     => 'Save Permissions',
            'Get Parts By Branch'   => 'Get Parts by Branch',
            'Purchase Vehicle'      => 'Purchase Vehicle',
        ];

        return $friendlyActions[$action] ?? $action;
    }

    /**
     * Write labels to the lang file.
     */
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
