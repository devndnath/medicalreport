<?php

namespace Database\Seeders;

use App\Models\Factory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Factory::orderBy('id')->get() as $factory) {
            $this->seedForFactory($factory);
        }
    }

    protected function seedForFactory(Factory $factory): void
    {
        $suppliers = [
            [
                'name' => 'Square Pharmaceuticals Ltd.',
                'contact_person' => 'Md. Karim Ahmed',
                'phone' => '01812345601',
                'email' => 'sales@square.com.bd',
                'address' => 'Gazipur, Dhaka, Bangladesh',
                'business_details' => 'Manufacturer and distributor of pharmaceutical products',
                'status' => 'active',
            ],
            [
                'name' => 'Beximco Pharmaceuticals Ltd.',
                'contact_person' => 'Rahman Hassan',
                'phone' => '01812345602',
                'email' => 'orders@beximco.com.bd',
                'address' => 'Narsingdi, Bangladesh',
                'business_details' => 'Manufacturer of antibiotics, APIs, and finished dosages',
                'status' => 'active',
            ],
            [
                'name' => 'Incepta Pharmaceuticals Ltd.',
                'contact_person' => 'Fatima Yasmin',
                'phone' => '01812345603',
                'email' => 'supply@incepta.com.bd',
                'address' => 'Dhaka, Bangladesh',
                'business_details' => 'Producer of general medicines and first-aid supplies',
                'status' => 'active',
            ],
            [
                'name' => 'ACI Pharmaceuticals Ltd.',
                'contact_person' => 'Nasir Khan',
                'phone' => '01812345604',
                'email' => 'procurement@aci.com.bd',
                'address' => 'Dhaka, Bangladesh',
                'business_details' => 'Manufacturer and distributor of medical supplies',
                'status' => 'active',
            ],
            [
                'name' => 'Healthcare Supplies Bangladesh',
                'contact_person' => 'Habib Miah',
                'phone' => '01812345605',
                'email' => 'info@hcsupply.bd',
                'address' => 'Tejgaon, Dhaka, Bangladesh',
                'business_details' => 'Distributor of medical supplies, gauze, bandages, and cotton',
                'status' => 'active',
            ],
            [
                'name' => 'Relief Trading Company',
                'contact_person' => 'Arif Ahmed',
                'phone' => '01812345606',
                'email' => 'orders@reliefbd.com',
                'address' => 'Motijheel, Dhaka, Bangladesh',
                'business_details' => 'Wholesale distributor of medicines and medical supplies',
                'status' => 'active',
            ],
            [
                'name' => 'Delta Pharma Ltd.',
                'contact_person' => 'Jamshed Khan',
                'phone' => '01812345607',
                'email' => 'sales@deltapharma.com.bd',
                'address' => 'Narayanganj, Bangladesh',
                'business_details' => 'Manufacturer of topical creams and ointments',
                'status' => 'active',
            ],
            [
                'name' => 'Chemist Supplies Store',
                'contact_person' => 'Monirul Islam',
                'phone' => '01812345608',
                'email' => 'contact@chemiststore.bd',
                'address' => 'Sadarghat, Dhaka, Bangladesh',
                'business_details' => 'Retail and wholesale pharmacy supplier',
                'status' => 'active',
            ],
            [
                'name' => 'Medical Equipment International',
                'contact_person' => 'Karim Hassan',
                'phone' => '01812345609',
                'email' => 'info@medequip.bd',
                'address' => 'Bashundhara, Dhaka, Bangladesh',
                'business_details' => 'Supplier of medical equipment and first-aid supplies',
                'status' => 'active',
            ],
            [
                'name' => 'Green Leaf Pharmaceuticals',
                'contact_person' => 'Shamim Ahmed',
                'phone' => '01812345610',
                'email' => 'orders@greenleaf.com.bd',
                'address' => 'Savar, Dhaka, Bangladesh',
                'business_details' => 'Manufacturer of syrups and liquid medicines',
                'status' => 'active',
            ],
        ];

        foreach ($suppliers as $data) {
            Supplier::firstOrCreate(
                ['factory_id' => $factory->id, 'name' => $data['name']],
                [
                    'factory_id' => $factory->id,
                    'name' => $data['name'],
                    'contact_person' => $data['contact_person'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'business_details' => $data['business_details'],
                    'status' => $data['status'],
                    'remarks' => 'Seeded supplier for testing and stock management.',
                ]
            );
        }
    }
}
