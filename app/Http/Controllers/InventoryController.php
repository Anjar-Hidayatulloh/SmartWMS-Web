<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $zone = $request->input('zone');
        $status = $request->input('status');

        $query = Stock::with(['item.category', 'location'])->latest();

        if ($search) {
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            })->orWhere('batch_no', 'like', "%{$search}%");
        }

        if ($zone) {
            $query->whereHas('location', function($q) use ($zone) {
                $q->where('zone', $zone);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $stocks = $query->paginate(15)->withQueryString();

        $zones = Location::select('zone')->distinct()->pluck('zone');

        return view('inventory.index', compact('stocks', 'zones', 'search', 'zone', 'status'));
    }

    public function quarantine(Stock $stock)
    {
        $stock->update(['status' => 'quarantined']);

        return redirect()->route('inventory.index')
            ->with('success', "Stok batch {$stock->batch_no} berhasil dikarantina.");
    }

    public function release(Stock $stock)
    {
        $stock->update(['status' => 'available']);

        return redirect()->route('inventory.index')
            ->with('success', "Stok batch {$stock->batch_no} berhasil dilepas dari karantina.");
    }

    public function exportExcel()
    {
        $stocks = Stock::with(['item.category', 'location'])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LAPORAN STOK INVENTARIS WAREHOUSE (SMART WMS)');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Tanggal Ekspor: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);

        $headers = ['No', 'SKU Barang', 'Nama Barang', 'Kategori', 'Batch No.', 'Kedaluwarsa', 'Lokasi Bin', 'Zona', 'Status', 'Jumlah (Qty)', 'Satuan'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '4', $header);
            $sheet->getStyle($column . '4')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        $row = 5;
        $no = 1;
        foreach ($stocks as $stock) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $stock->item->sku);
            $sheet->setCellValue('C' . $row, $stock->item->name);
            $sheet->setCellValue('D' . $row, $stock->item->category->name);
            $sheet->setCellValue('E' . $row, $stock->batch_no);
            $sheet->setCellValue('F' . $row, $stock->expired_at ? $stock->expired_at->format('d-m-Y') : '-');
            $sheet->setCellValue('G' . $row, $stock->location->bin_code);
            $sheet->setCellValue('H' . $row, $stock->location->zone);
            $sheet->setCellValue('I' . $row, ucfirst($stock->status));
            $sheet->setCellValue('J' . $row, $stock->qty);
            $sheet->setCellValue('K' . $row, $stock->item->unit);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $fileName = 'Stock_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
