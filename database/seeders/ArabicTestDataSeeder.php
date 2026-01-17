<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Rating;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ArabicTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete all data except admin
        $this->command->info('Deleting existing data...');
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Delete in order to respect foreign key constraints
        DB::table('ratings')->truncate();
        $this->command->info('Ratings deleted.');
        
        DB::table('bookings')->truncate();
        $this->command->info('Bookings deleted.');
        
        DB::table('apartments')->truncate();
        $this->command->info('Apartments deleted.');
        
        // Delete all users except admin
        User::where('mobile_number', '!=', '+963991877688')
            ->where('mobile_number', '!=', '0991877688')
            ->where('mobile_number', '!=', '00963991877688')
            ->delete();
        $this->command->info('Users deleted (admin preserved).');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Copy 1.png to storage for all users
        $sourcePath = base_path('../1.png');
        $photoPath = 'users/photos/1.png';
        $idPhotoPath = 'users/id-photos/1.png';
        
        if (file_exists($sourcePath)) {
            Storage::disk('public')->put($photoPath, file_get_contents($sourcePath));
            Storage::disk('public')->put($idPhotoPath, file_get_contents($sourcePath));
            $this->command->info('Photo copied to storage.');
        } else {
            $this->command->warn('1.png not found at: ' . $sourcePath);
            $photoPath = null;
            $idPhotoPath = null;
        }
        
        // Create approved users (2 tenant, 2 owner)
        $approvedUsers = [];
        
        // Approved Tenants
        $approvedUsers[] = User::create([
            'mobile_number' => '+963991111111',
            'password' => Hash::make('password123'),
            'first_name' => 'أحمد',
            'last_name' => 'محمد',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1995-05-15',
            'role' => 'tenant',
            'status' => 'approved',
            'language_preference' => 'ar',
            'balance' => 50000.00,
        ]);
        
        $approvedUsers[] = User::create([
            'mobile_number' => '+963992222222',
            'password' => Hash::make('password123'),
            'first_name' => 'فاطمة',
            'last_name' => 'علي',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1998-08-20',
            'role' => 'tenant',
            'status' => 'approved',
            'language_preference' => 'ar',
            'balance' => 75000.00,
        ]);
        
        // Approved Owners
        $approvedUsers[] = User::create([
            'mobile_number' => '+963993333333',
            'password' => Hash::make('password123'),
            'first_name' => 'خالد',
            'last_name' => 'حسن',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1985-03-10',
            'role' => 'owner',
            'status' => 'approved',
            'language_preference' => 'ar',
            'balance' => 100000.00,
        ]);
        
        $approvedUsers[] = User::create([
            'mobile_number' => '+963994444444',
            'password' => Hash::make('password123'),
            'first_name' => 'سارة',
            'last_name' => 'إبراهيم',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1990-11-25',
            'role' => 'owner',
            'status' => 'approved',
            'language_preference' => 'ar',
            'balance' => 150000.00,
        ]);
        
        $this->command->info('Created 4 approved users (2 tenant, 2 owner).');
        
        // Create pending users (2 tenant, 2 owner)
        $pendingUsers = [];
        
        $pendingUsers[] = User::create([
            'mobile_number' => '+963995555555',
            'password' => Hash::make('password123'),
            'first_name' => 'يوسف',
            'last_name' => 'محمود',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1997-02-14',
            'role' => 'tenant',
            'status' => 'pending',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $pendingUsers[] = User::create([
            'mobile_number' => '+963996666666',
            'password' => Hash::make('password123'),
            'first_name' => 'مريم',
            'last_name' => 'عمر',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1999-07-30',
            'role' => 'tenant',
            'status' => 'pending',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $pendingUsers[] = User::create([
            'mobile_number' => '+963997777777',
            'password' => Hash::make('password123'),
            'first_name' => 'عمر',
            'last_name' => 'عبدالله',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1988-09-12',
            'role' => 'owner',
            'status' => 'pending',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $pendingUsers[] = User::create([
            'mobile_number' => '+963998888888',
            'password' => Hash::make('password123'),
            'first_name' => 'ليلى',
            'last_name' => 'نور',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1992-04-18',
            'role' => 'owner',
            'status' => 'pending',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $this->command->info('Created 4 pending users (2 tenant, 2 owner).');
        
        // Create rejected users (2 tenant, 2 owner)
        $rejectedUsers = [];
        
        $rejectedUsers[] = User::create([
            'mobile_number' => '+963999999999',
            'password' => Hash::make('password123'),
            'first_name' => 'طارق',
            'last_name' => 'صالح',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1996-06-22',
            'role' => 'tenant',
            'status' => 'rejected',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $rejectedUsers[] = User::create([
            'mobile_number' => '+963910101010',
            'password' => Hash::make('password123'),
            'first_name' => 'نورا',
            'last_name' => 'راشد',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '2000-01-05',
            'role' => 'tenant',
            'status' => 'rejected',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $rejectedUsers[] = User::create([
            'mobile_number' => '+963911111111',
            'password' => Hash::make('password123'),
            'first_name' => 'باسم',
            'last_name' => 'كريم',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1987-12-08',
            'role' => 'owner',
            'status' => 'rejected',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $rejectedUsers[] = User::create([
            'mobile_number' => '+963912121212',
            'password' => Hash::make('password123'),
            'first_name' => 'هدى',
            'last_name' => 'زين',
            'personal_photo' => $photoPath,
            'id_photo' => $idPhotoPath,
            'date_of_birth' => '1993-10-15',
            'role' => 'owner',
            'status' => 'rejected',
            'language_preference' => 'ar',
            'balance' => 0.00,
        ]);
        
        $this->command->info('Created 4 rejected users (2 tenant, 2 owner).');
        
        // Get owners for apartments
        $owners = array_filter($approvedUsers, fn($u) => $u->role === 'owner');
        $tenants = array_filter($approvedUsers, fn($u) => $u->role === 'tenant');
        
        // Create 5 apartments
        $apartments = [];
        $governorates = ['دمشق', 'حلب', 'اللاذقية', 'حمص', 'طرطوس'];
        $governoratesEn = ['Damascus', 'Aleppo', 'Latakia', 'Homs', 'Tartus'];
        $cities = ['دمشق', 'حلب', 'اللاذقية', 'حمص', 'طرطوس'];
        $citiesEn = ['Damascus', 'Aleppo', 'Latakia', 'Homs', 'Tartus'];
        $addresses = [
            'شارع الجمهورية، حي المزة',
            'شارع بارون، حي العزيزية',
            'شارع الكورنيش، حي الرمل',
            'شارع الحميدية، حي باب السباع',
            'شارع الكورنيش، حي الأموي'
        ];
        $addressesEn = [
            'Al-Jumhuriya Street, Al-Mazzeh',
            'Baron Street, Al-Aziziyah',
            'Corniche Street, Al-Raml',
            'Al-Hamidiyah Street, Bab Al-Sabaa',
            'Corniche Street, Al-Umayyad'
        ];
        
        foreach (range(0, 4) as $i) {
            $owner = $owners[array_rand($owners)];
            $apartments[] = Apartment::create([
                'owner_id' => $owner->id,
                'governorate' => $governoratesEn[$i],
                'governorate_ar' => $governorates[$i],
                'city' => $citiesEn[$i],
                'city_ar' => $cities[$i],
                'address' => $addressesEn[$i],
                'address_ar' => $addresses[$i],
                'nightly_price' => rand(50, 200),
                'monthly_price' => rand(1000, 5000),
                'bedrooms' => rand(1, 4),
                'bathrooms' => rand(1, 3),
                'living_rooms' => rand(1, 2),
                'size' => rand(60, 200),
                'description' => 'Beautiful apartment in the heart of the city.',
                'description_ar' => 'شقة جميلة في قلب المدينة.',
                'photos' => ['apartments/photos/test1.jpg'],
                'amenities' => ['WiFi', 'Parking', 'Air Conditioning'],
                'status' => 'active',
            ]);
        }
        
        $this->command->info('Created 5 apartments.');
        
        // Create 5 bookings
        foreach (range(0, 4) as $i) {
            $tenant = $tenants[array_rand($tenants)];
            $apartment = $apartments[$i];
            
            $checkIn = now()->addDays(rand(1, 30));
            $checkOut = $checkIn->copy()->addDays(rand(1, 7));
            $nights = $checkIn->diffInDays($checkOut);
            $totalRent = $apartment->nightly_price * $nights;
            
            Booking::create([
                'tenant_id' => $tenant->id,
                'apartment_id' => $apartment->id,
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'number_of_guests' => rand(1, 4),
                'payment_method' => 'wallet',
                'total_rent' => $totalRent,
                'status' => ['pending', 'approved', 'completed', 'cancelled'][rand(0, 3)],
            ]);
        }
        
        $this->command->info('Created 5 bookings.');
        $this->command->info('Arabic test data seeded successfully!');
    }
}
