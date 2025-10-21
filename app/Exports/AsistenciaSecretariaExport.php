<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciaSecretariaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
  private Collection $sections;
  private array $totals;
  public function __construct(Collection $sections, array $totals)
  {
    $this->sections = $sections;
    $this->totals = $totals;
  }

  public function collection(): Collection
  {
    $rows = $this->sections->map(function (array $section) {
      return [
        $section['grado'],
        $section['seccion'],
        $section['masculinos'],
        $section['femeninos'],
        $section['total'],
      ];
    });

    if ($rows->isEmpty()) {
      return $rows;
    }

    return $rows->push([
      'Totales',
      '',
      $this->totals['masculinos'] ?? 0,
      $this->totals['femeninos'] ?? 0,
      $this->totals['total'] ?? 0,
    ]);
  }

  public function headings(): array
  {
    return ['Grado', 'Sección', 'Hombres', 'Mujeres', 'Total'];
  }

  public function styles(Worksheet $sheet): array
  {
    return [
      1 => ['font' => ['bold' => true]],
    ];
  }
}
