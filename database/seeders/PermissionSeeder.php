<?php

namespace Database\Seeders;

use App\Models\ContractType;
use App\Models\SupportType;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            // Master
            ['guard_name' => 'web', 'model' => 'States'],
            ['guard_name' => 'web', 'model' => 'Districts'],
            ['guard_name' => 'web', 'model' => 'Pincodes'],
            ['guard_name' => 'web', 'model' => 'Cities'],
            ['guard_name' => 'web', 'model' => 'Stages'],
            ['guard_name' => 'web', 'model' => 'Tax'],
            ['guard_name' => 'web', 'model' => 'Unit of Measurement'],
            ['guard_name' => 'web', 'model' => 'Roles and Permissions', 'SplPermission' => 1],
            ['guard_name' => 'web', 'model' => 'Users', 'SplPermission' => 1],
            ['guard_name' => 'web', 'model' => 'Lead Source', 'SplPermission' => 1],
            ['guard_name' => 'web', 'model' => 'Lead', 'SplPermission' => 1],
            ['guard_name' => 'web', 'model' => 'Contents', 'SplPermission' => 1],

            ['guard_name' => 'web', 'model' => 'Product Category'],
            ['guard_name' => 'web', 'model' => 'Product'],
            ['guard_name' => 'web', 'model' => 'Warehouse'],
            ['guard_name' => 'web', 'model' => 'Contract Type'],

            // Manage Projects
            ['guard_name' => 'web', 'model' => 'Project Specifications'],
            ['guard_name' => 'web', 'model' => 'Amenities'],
            ['guard_name' => 'web', 'model' => 'Sites'],
            ['guard_name' => 'web', 'model' => 'Projects'],
            ['guard_name' => 'web', 'model' => 'Project Tasks'],
            ['guard_name' => 'web', 'model' => 'Visitors'],
            ['guard_name' => 'web', 'model' => 'Support Tickets'],
            ['guard_name' => 'web', 'model' => 'Blogs'],
            ['guard_name' => 'web', 'model' => 'Labor Designations'],
            ['guard_name' => 'web', 'model' => 'Labors'],
            ['guard_name' => 'web', 'model' => 'Payrolls'],
            ['guard_name' => 'web', 'model' => 'Purchase Requests'],
            ['guard_name' => 'web', 'model' => 'Purchase Orders'],
            ['guard_name' => 'web', 'model' => 'Project Stocks'],
            ['guard_name' => 'web', 'model' => 'Project Reports'],
        ];

        $updatedModules = [];

        foreach ($modules as $module) {
            $options = ['Create', 'Edit', 'View', 'Delete', 'Restore', 'Excel', 'PDF', 'CSV', 'Copy', 'Print'];

            if (array_key_exists('SplPermission', $module) && $module['SplPermission']) {
                $options[] = 'Special';
                unset($module['SplPermission']);
            }
            foreach ($options as $option) {
                if (!Permission::whereName($option . ' ' . $module['model'])->exists()) {
                    $permission = $module;
                    $permission['name'] = $option . ' ' . $module['model'];
//                    $updatedModules[] = $permission;
                    Permission::Create($permission);
                }
            }
        }

//        $dbPermission = Permission::all()->pluck('name');
//        $collectionPermission = collect($updatedModules)->pluck('name');

//        $differenceArray = array_diff($dbPermission->toArray(), $collectionPermission->toArray());
//        Permission::whereIn('name', $differenceArray)->delete();

//        $modulesIds = Permission::all()->pluck('id');
        $permissions = Permission::all();
        foreach (SYSTEM_ROLES as $role) {
            $role = Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
//            $role->givePermissionTo($modulesIds);
            $role->syncPermissions($permissions);
        }
        $super_admin_role_id = Role::where('name', SUPER_ADMIN_ROLE_NAME)->first()->id;
        $supervisor_role_id = Role::where('name', SITE_SUPERVISOR_ROLE_NAME)->first()->id;
        $engineer_role_id = Role::where('name', ENGINEER_ROLE_NAME)->first()->id;
        $vendor_role_id = Role::where('name', VENDOR_ROLE_NAME)->first()->id;
        $contractor_role_id = Role::where('name', CONTRACTOR_ROLE_NAME)->first()->id;
        $users = [
            [
                "name" => 'Vinith Kumar',
                "email" => 'vinithkumarpropluslogics@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $super_admin_role_id,
            ],
            [
                "name" => 'Naveen',
                "email" => 'navinproplus222@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $super_admin_role_id,
            ],
            [
                "name" => 'Anand',
                "email" => 'anand@propluslogics.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $super_admin_role_id,
            ]
        ];

        foreach ($users as $userData) {
            $admin = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $admin->assignRole($super_admin_role_id);
        }

        $supervisors = [
            [
                "name" => 'Supervisor 1',
                "email" => 'vinithkumarpropluslogics+s1@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $supervisor_role_id,
            ],
            [
                "name" => 'Supervisor 2',
                "email" => 'vinithkumarpropluslogics+s2@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $supervisor_role_id,
            ],
            [
                "name" => 'Supervisor 3',
                "email" => 'vinithkumarpropluslogics+s3@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $supervisor_role_id,
            ]
        ];

        foreach ($supervisors as $supervisorData) {
            $supervisor = User::updateOrCreate(
                ['email' => $supervisorData['email']],
                $supervisorData
            );
            $supervisor->assignRole($supervisor_role_id);
        }

        $engineers = [
            [
                "name" => 'Engineer 1',
                "email" => 'vinithkumarpropluslogics+engineer1@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $engineer_role_id,
            ],
            [
                "name" => 'Engineer 2',
                "email" => 'vinithkumarpropluslogics+engineer2@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $engineer_role_id,
            ],
            [
                "name" => 'Engineer 3',
                "email" => 'vinithkumarpropluslogics+engineer3@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $engineer_role_id,
            ]
        ];

        foreach ($engineers as $engineerData) {
            $engineer = User::updateOrCreate(
                ['email' => $engineerData['email']],
                $engineerData
            );
            $engineer->assignRole($engineer_role_id);
        }

        $vendors = [
            [
                "name" => 'Vendor 1',
                "email" => 'vinithkumarpropluslogics+vendor1@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $vendor_role_id,
            ],
            [
                "name" => 'Vendor 2',
                "email" => 'vinithkumarpropluslogics+vendor2@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $vendor_role_id,
            ],
            [
                "name" => 'Vendor 3',
                "email" => 'vinithkumarpropluslogics+vendor3@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $vendor_role_id,
            ]
        ];

        foreach ($vendors as $vendorData) {
            $vendor = User::updateOrCreate(
                ['email' => $vendorData['email']],
                $vendorData
            );
            $vendor->assignRole($vendor_role_id);
        }

        $contractors = [
            [
                "name" => 'Contractor 1',
                "email" => 'vinithkumarpropluslogics+contractor1@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $contractor_role_id,
            ],
            [
                "name" => 'Contractor 2',
                "email" => 'vinithkumarpropluslogics+contractor2@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $contractor_role_id,
            ],
            [
                "name" => 'Contractor 3',
                "email" => 'vinithkumarpropluslogics+contractor3@gmail.com',
                "password" => Hash::make('proplus1234$'),
                "role_id" => $contractor_role_id,
            ]
        ];

        foreach ($contractors as $contractorData) {
            $contractor = User::updateOrCreate(
                ['email' => $contractorData['email']],
                $contractorData
            );
            $contractor->assignRole($contractor_role_id);
        }

        $construction_contract_types = [
            'Site Preparation & Excavation',  // Stage 1: Land preparation
            'Foundation & Footings',          // Stage 2: Base of the structure
            'Structural Work',                // Stage 3: Concrete, steel, and masonry
            'Plumbing & Drainage',            // Stage 4: Underground & internal plumbing
            'Electrical Works',               // Stage 5: Wiring, panels, and conduits
            'HVAC (Heating, Ventilation, and Air Conditioning)', // Stage 6: Climate control systems
            'Roofing',                        // Stage 7: Roof construction
            'Waterproofing',                  // Stage 8: Preventing leaks & dampness
            'Flooring & Tiling',              // Stage 9: Internal floor finishing
            'Carpentry & Woodwork',           // Stage 10: Doors, windows, and cabinetry
            'Painting & Finishing',           // Stage 11: Wall & ceiling finishes
            'Glass & Aluminum Work',          // Stage 12: Windows, partitions, and facades
            'Fire Safety & Suppression Systems', // Stage 13: Fire alarms, sprinklers
            'Elevators & Escalators',         // Stage 14: Vertical transportation
            'Interior Designing & Furnishing', // Stage 15: Final interior touches
            'Landscaping & Exterior Development' // Stage 16: Outdoor areas & beautification
        ];

        foreach ($construction_contract_types as $construction_contract_type) {
            ContractType::firstOrCreate(['name' => $construction_contract_type, 'is_active' => true]);
        }

        foreach (SUPPORT_TYPES as $support_type) {
            SupportType::firstOrCreate(['name' => $support_type, 'is_active' => true]);
        }

    }
}
