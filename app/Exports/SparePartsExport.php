<?php

namespace App\Exports;

use App\Models\VehicleDetails;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SparePartsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $search, $vehicleSearch;

    public function __construct($search = null, $vehicleSearch = false)
    {
        $this->search = $search;
        $this->vehicleSearch = $vehicleSearch;
    }

    /**
     * Use query to stream data efficiently
     */
    public function query()
    {
        $query = VehicleDetails::query();

        if (!$this->vehicleSearch) {
            $query->whereNull('vehicle_id');
        }else{
            $query->with('vehicle');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $search = $this->search;
                if (!$this->vehicleSearch) {
                    $q->where('parts_name', 'like', "%{$search}%")
                        ->orWhere('car_name', 'like', "%{$search}%")
                        ->orWhere('car_model', 'like', "%{$search}%")
                        ->orWhere('uid', $search);
                }else{
                    $q->where('parts_name', 'like', "%{$search}%")
                        ->orWhere('uid', $search);
                }
            });
        }

        return $query->orderBy('id', 'DESC');
    }

    /**
     * Add column headings
     */
    public function headings(): array
    {
        return [
            'UID',
            'Parts Name',
            'Car Name',
            'Car Model',
            'Quantity',
        ];
    }

    /**
     * Map each row for export
     */
    public function map($sparePart): array
    {
        return [
            $sparePart->uid,
            $sparePart->parts_name,
            $sparePart->vehicle ? $sparePart->vehicle?->name : $sparePart->car_name,
            $sparePart->car_model,
            $sparePart->parts_quantity,
        ];
    }
}
