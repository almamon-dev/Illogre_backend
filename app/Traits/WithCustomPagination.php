<?php

namespace App\Traits;

trait WithCustomPagination
{
    /**
     * Create a limited page range for pagination
     */
    public function getPageRange($paginator, $range = 3): array
    {
        $totalPages = $paginator->lastPage();
        $currentPage = $paginator->currentPage();

        $start = max($currentPage - $range, 1);
        $end = min($currentPage + $range, $totalPages);

        return range($start, $end);
    }
}
