<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\InventoryHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        $admin = User::create([
            'name' => 'WMS Admin',
            'email' => 'admin@wms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $operator = User::create([
            'name' => 'WMS Operator',
            'email' => 'operator@wms.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic goods and spare parts'],
            ['name' => 'Automotive', 'slug' => 'automotive', 'description' => 'Automotive components and accessories'],
            ['name' => 'Packaging', 'slug' => 'packaging', 'description' => 'Boxes, tape, bubble wrap, etc.'],
            ['name' => 'Chemicals', 'slug' => 'chemicals', 'description' => 'Industrial chemicals and cleaning supplies'],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[] = Category::create($cat);
        }

        $locationsData = [
            ['bin_code' => 'Cabang Karawang', 'zone' => 'Karawang'],
            ['bin_code' => 'Cabang Cikarang', 'zone' => 'Cikarang'],
            ['bin_code' => 'Cabang Bekasi', 'zone' => 'Bekasi'],
            ['bin_code' => 'Cabang Jakarta Timur', 'zone' => 'Jakarta Timur'],
            ['bin_code' => 'Cabang Jakarta Utara', 'zone' => 'Jakarta Utara'],
        ];
        $locationModels = [];
        foreach ($locationsData as $loc) {
            $locationModels[] = Location::create($loc);
        }

        $itemsData = [

            [
                'category_id' => $categoryModels[0]->id,
                'sku' => 'SKU-ELEC-001',
                'name' => 'Industrial PLC Controller',
                'description' => 'Programmable logic controller for assembly lines',
                'unit' => 'pcs',
                'initial_stock' => 100,
            ],
            [
                'category_id' => $categoryModels[0]->id,
                'sku' => 'SKU-ELEC-002',
                'name' => 'Optical Sensor Model X',
                'description' => 'High precision proximity sensor',
                'unit' => 'pcs',
                'initial_stock' => 250,
            ],

            [
                'category_id' => $categoryModels[1]->id,
                'sku' => 'SKU-AUTO-101',
                'name' => 'Engine Spark Plug V2',
                'description' => 'High performance spark plug for heavy machinery',
                'unit' => 'box',
                'initial_stock' => 50,
            ],
            [
                'category_id' => $categoryModels[1]->id,
                'sku' => 'SKU-AUTO-102',
                'name' => 'Synthetic Engine Oil 5W-30',
                'description' => 'Premium engine lubrication oil',
                'unit' => 'canister',
                'initial_stock' => 80,
            ],

            [
                'category_id' => $categoryModels[2]->id,
                'sku' => 'SKU-PACK-201',
                'name' => 'Corrugated Box 40x40x40',
                'description' => 'Heavy duty packaging box',
                'unit' => 'pcs',
                'initial_stock' => 1000,
            ],

            [
                'category_id' => $categoryModels[3]->id,
                'sku' => 'SKU-CHEM-301',
                'name' => 'Isopropyl Alcohol 99%',
                'description' => 'Industrial solvent and cleaning alcohol',
                'unit' => 'drum',
                'initial_stock' => 10,
            ],
        ];

        $itemModels = [];
        foreach ($itemsData as $item) {
            $itemModels[] = Item::create($item);
        }

        $batches = ['BCH-20260601', 'BCH-20260602', 'BCH-20260603'];

        $tx1Code = 'TRX-IN-' . date('Ymd') . '-0001';
        $item1 = $itemModels[0];
        $loc1 = $locationModels[0];

        $tx1 = Transaction::create([
            'transaction_code' => $tx1Code,
            'type' => 'goods_in',
            'item_id' => $item1->id,
            'qty' => 100,
            'batch_no' => $batches[0],
            'expired_at' => Carbon::now()->addYears(2),
            'user_id' => $operator->id,
            'origin_location_id' => null,
            'destination_location_id' => $loc1->id,
            'transaction_date' => Carbon::now()->subDays(5),
        ]);

        Stock::create([
            'item_id' => $item1->id,
            'location_id' => $loc1->id,
            'qty' => 100,
            'batch_no' => $batches[0],
            'expired_at' => Carbon::now()->addYears(2),
            'status' => 'available',
        ]);

        InventoryHistory::create([
            'transaction_id' => $tx1->id,
            'item_id' => $item1->id,
            'location_id' => $loc1->id,
            'batch_no' => $batches[0],
            'qty_before' => 0,
            'qty_change' => 100,
            'qty_after' => 100,
        ]);

        $tx2Code = 'TRX-IN-' . date('Ymd') . '-0002';
        $item2 = $itemModels[1];
        $loc2 = $locationModels[1];

        $tx2 = Transaction::create([
            'transaction_code' => $tx2Code,
            'type' => 'goods_in',
            'item_id' => $item2->id,
            'qty' => 250,
            'batch_no' => $batches[1],
            'expired_at' => Carbon::now()->addYears(1),
            'user_id' => $operator->id,
            'origin_location_id' => null,
            'destination_location_id' => $loc2->id,
            'transaction_date' => Carbon::now()->subDays(4),
        ]);

        Stock::create([
            'item_id' => $item2->id,
            'location_id' => $loc2->id,
            'qty' => 250,
            'batch_no' => $batches[1],
            'expired_at' => Carbon::now()->addYears(1),
            'status' => 'available',
        ]);

        InventoryHistory::create([
            'transaction_id' => $tx2->id,
            'item_id' => $item2->id,
            'location_id' => $loc2->id,
            'batch_no' => $batches[1],
            'qty_before' => 0,
            'qty_change' => 250,
            'qty_after' => 250,
        ]);

        $tx3Code = 'TRX-IN-' . date('Ymd') . '-0003';
        $item3 = $itemModels[2];
        $loc3 = $locationModels[2];

        $tx3 = Transaction::create([
            'transaction_code' => $tx3Code,
            'type' => 'goods_in',
            'item_id' => $item3->id,
            'qty' => 50,
            'batch_no' => $batches[2],
            'expired_at' => Carbon::now()->addMonth(),
            'user_id' => $operator->id,
            'origin_location_id' => null,
            'destination_location_id' => $loc3->id,
            'transaction_date' => Carbon::now()->subDays(3),
        ]);

        Stock::create([
            'item_id' => $item3->id,
            'location_id' => $loc3->id,
            'qty' => 50,
            'batch_no' => $batches[2],
            'expired_at' => Carbon::now()->addMonth(),
            'status' => 'available',
        ]);

        InventoryHistory::create([
            'transaction_id' => $tx3->id,
            'item_id' => $item3->id,
            'location_id' => $loc3->id,
            'batch_no' => $batches[2],
            'qty_before' => 0,
            'qty_change' => 50,
            'qty_after' => 50,
        ]);

        $tx4Code = 'TRX-IN-' . date('Ymd') . '-0004';
        $item4 = $itemModels[3];
        $loc4 = $locationModels[3];

        $tx4 = Transaction::create([
            'transaction_code' => $tx4Code,
            'type' => 'goods_in',
            'item_id' => $item4->id,
            'qty' => 100,
            'batch_no' => $batches[0],
            'expired_at' => Carbon::now()->addYears(3),
            'user_id' => $operator->id,
            'origin_location_id' => null,
            'destination_location_id' => $loc4->id,
            'transaction_date' => Carbon::now()->subDays(2),
        ]);

        $stockItem4 = Stock::create([
            'item_id' => $item4->id,
            'location_id' => $loc4->id,
            'qty' => 100,
            'batch_no' => $batches[0],
            'expired_at' => Carbon::now()->addYears(3),
            'status' => 'available',
        ]);

        InventoryHistory::create([
            'transaction_id' => $tx4->id,
            'item_id' => $item4->id,
            'location_id' => $loc4->id,
            'batch_no' => $batches[0],
            'qty_before' => 0,
            'qty_change' => 100,
            'qty_after' => 100,
        ]);

        $tx5Code = 'TRX-OUT-' . date('Ymd') . '-0001';
        $tx5 = Transaction::create([
            'transaction_code' => $tx5Code,
            'type' => 'goods_out',
            'item_id' => $item4->id,
            'qty' => 20,
            'batch_no' => $batches[0],
            'expired_at' => Carbon::now()->addYears(3),
            'user_id' => $operator->id,
            'origin_location_id' => $loc4->id,
            'destination_location_id' => null,
            'transaction_date' => Carbon::now()->subDay(),
        ]);

        $stockItem4->update(['qty' => 80]);

        InventoryHistory::create([
            'transaction_id' => $tx5->id,
            'item_id' => $item4->id,
            'location_id' => $loc4->id,
            'batch_no' => $batches[0],
            'qty_before' => 100,
            'qty_change' => -20,
            'qty_after' => 80,
        ]);

        $tx6Code = 'TRX-IN-' . date('Ymd') . '-0005';
        $item5 = $itemModels[4];
        $loc5 = $locationModels[4];

        $tx6 = Transaction::create([
            'transaction_code' => $tx6Code,
            'type' => 'goods_in',
            'item_id' => $item5->id,
            'qty' => 1000,
            'batch_no' => 'BCH-PACK-GLOBAL',
            'expired_at' => null,
            'user_id' => $operator->id,
            'origin_location_id' => null,
            'destination_location_id' => $loc5->id,
            'transaction_date' => Carbon::now()->subDays(6),
        ]);

        Stock::create([
            'item_id' => $item5->id,
            'location_id' => $loc5->id,
            'qty' => 1000,
            'batch_no' => 'BCH-PACK-GLOBAL',
            'expired_at' => null,
            'status' => 'available',
        ]);

        InventoryHistory::create([
            'transaction_id' => $tx6->id,
            'item_id' => $item5->id,
            'location_id' => $loc5->id,
            'batch_no' => 'BCH-PACK-GLOBAL',
            'qty_before' => 0,
            'qty_change' => 1000,
            'qty_after' => 1000,
        ]);

        $tx7Code = 'TRX-IN-' . date('Ymd') . '-0006';
        $item6 = $itemModels[5];
        $loc6 = $locationModels[4];

        $tx7 = Transaction::create([
            'transaction_code' => $tx7Code,
            'type' => 'goods_in',
            'item_id' => $item6->id,
            'qty' => 10,
            'batch_no' => 'BCH-CHEM-001',
            'expired_at' => Carbon::now()->addYear(),
            'user_id' => $operator->id,
            'origin_location_id' => null,
            'destination_location_id' => $loc6->id,
            'transaction_date' => Carbon::now()->subDays(7),
        ]);

        Stock::create([
            'item_id' => $item6->id,
            'location_id' => $loc6->id,
            'qty' => 10,
            'batch_no' => 'BCH-CHEM-001',
            'expired_at' => Carbon::now()->addYear(),
            'status' => 'quarantined',
        ]);

        InventoryHistory::create([
            'transaction_id' => $tx7->id,
            'item_id' => $item6->id,
            'location_id' => $loc6->id,
            'batch_no' => 'BCH-CHEM-001',
            'qty_before' => 0,
            'qty_change' => 10,
            'qty_after' => 10,
        ]);
    }
}
