<?php
declare(strict_types=1);

namespace App\Core;

class Pagination {
    public static function getParams(int $totalItems, int $perPage = 25): array {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        $totalPages = (int)ceil($totalItems / $perPage);
        if ($totalPages > 0 && $page > $totalPages) $page = $totalPages;
        
        $offset = ($page - 1) * $perPage;
        
        return [
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'offset' => $offset,
            'limit' => $perPage
        ];
    }

    public static function render(int $page, int $totalPages, string $baseUrl = ''): string {
        if ($totalPages <= 1) return '';

        // Clean up baseUrl: remove existing page parameter
        $urlParts = parse_url($baseUrl);
        $query = [];
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
            unset($query['page']);
        }
        $base = ($urlParts['path'] ?? '');
        $queryString = http_build_query($query);
        $fullBase = $base . ($queryString ? '?' . $queryString . '&' : '?');

        $html = '<div class="pagination-container" style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 25px; padding: 20px 0;">';
        
        // Previous page
        if ($page > 1) {
            $html .= '<a href="' . $fullBase . 'page=' . ($page - 1) . '" class="pagination-btn"><i data-lucide="chevron-left" class="icon-lucide"></i></a>';
        } else {
            $html .= '<span class="pagination-btn disabled"><i data-lucide="chevron-left" class="icon-lucide"></i></span>';
        }

        // Pages
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);

        if ($startPage > 1) {
            $html .= '<a href="' . $fullBase . 'page=1" class="pagination-btn">1</a>';
            if ($startPage > 2) $html .= '<span class="pagination-gap">...</span>';
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i === $page) ? 'active' : '';
            $html .= '<a href="' . $fullBase . 'page=' . $i . '" class="pagination-btn ' . $activeClass . '">' . $i . '</a>';
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) $html .= '<span class="pagination-gap">...</span>';
            $html .= '<a href="' . $fullBase . 'page=' . $totalPages . '" class="pagination-btn">' . $totalPages . '</a>';
        }

        // Next page
        if ($page < $totalPages) {
            $html .= '<a href="' . $fullBase . 'page=' . ($page + 1) . '" class="pagination-btn"><i data-lucide="chevron-right" class="icon-lucide"></i></a>';
        } else {
            $html .= '<span class="pagination-btn disabled"><i data-lucide="chevron-right" class="icon-lucide"></i></span>';
        }

        $html .= '</div>';
        return $html;
    }
}
