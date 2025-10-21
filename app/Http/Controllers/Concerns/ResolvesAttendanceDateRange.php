<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait ResolvesAttendanceDateRange
{
  protected function resolveDateRange(Request $request): array
  {
    $startDateInput = $request->input('start_date');
    $endDateInput = $request->input('end_date');

    $startDate = null;
    $endDate = null;

    try {
      if ($startDateInput) {
        $startDate = Carbon::createFromFormat('Y-m-d', $startDateInput)->startOfDay();
      }
    } catch (\Throwable $exception) {
      $startDate = null;
    }

    try {
      if ($endDateInput) {
        $endDate = Carbon::createFromFormat('Y-m-d', $endDateInput)->endOfDay();
      }
    } catch (\Throwable $exception) {
      $endDate = null;
    }

    if ($startDate && $endDate && $startDate->greaterThan($endDate)) {
      [$startDate, $endDate] = [
        $endDate->copy()->startOfDay(),
        $startDate->copy()->endOfDay(),
      ];
    }

    if (!$startDate && !$endDate) {
      $now = Carbon::now();
      $startDate = $now->copy()->startOfDay();
      $endDate = $now->copy()->endOfDay();
    }

    if ($startDate && !$endDate) {
      $endDate = $startDate->copy()->endOfDay();
    }

    if (!$startDate && $endDate) {
      $startDate = $endDate->copy()->startOfDay();
    }

    return [$startDate, $endDate];
  }
}
