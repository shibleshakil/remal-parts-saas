<?php

namespace App\Exports;

use App\Models\ExpenseType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExpenseListExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $search, $fromDate, $toDate;
    protected $grandTotal = 0;

    /**
     * Constructor for filters
     */
    public function __construct($search = null, $fromDate = null, $toDate = null)
    {
        $this->search = $search;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    /**
     * Query to fetch filtered data
     */
    public function query()
    {
        $query = ExpenseType::query()
            ->withSum(['expenses as total_amount' => function ($q) {
                // Apply date filter on the expenses relation
                if ($this->fromDate || $this->toDate) {
                    $from = $this->fromDate ?? date('Y-m-d');
                    $to = $this->toDate ?? date('Y-m-d');
                    $q->whereBetween('expense_date', [$from, $to]);
                }
            }], 'amount');

        // Search filter
        if (!empty($this->search)) {
            $query->where('name', 'like', "%{$this->search}%");
        }

        // Date range filter
        if ($this->fromDate || $this->toDate) {
            $from = $this->fromDate ?? date('Y-m-d');
            $to = $this->toDate ?? date('Y-m-d');

            $query->whereHas('expenses', function ($q2) use ($from, $to) {
                $q2->whereBetween('expense_date', [$from, $to]);
            });
        }

        return $query->orderBy('name', 'ASC');
    }

    /**
     * Excel headings
     */
    public function headings(): array
    {
        return [
            'Expense Type',
            'Total Amount',
        ];
    }

    /**
     * Map data for each Excel row
     */
    public function map($expense): array
    {
        // Add to running grand total
        $this->grandTotal += $expense->total_amount ?? 0;

        return [
            $expense->name ?? 'N/A',
            $expense->total_amount ?? 0,
        ];
    }

    /**
     * Add Grand Total Row at the bottom
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Find the last row after data is written
                $highestRow = $event->sheet->getHighestRow() + 1;

                // Write Grand Total label and value
                $event->sheet->setCellValue("A{$highestRow}", 'Grand Total');
                $event->sheet->setCellValue("B{$highestRow}", $this->grandTotal);

                // Bold the Grand Total row
                $event->sheet->getStyle("A{$highestRow}:B{$highestRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]);
            },
        ];
    }
}
