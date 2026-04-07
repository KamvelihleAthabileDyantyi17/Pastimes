<?php
function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $price): string {
    return 'R ' . number_format($price, 2);
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60) . ' min ago';
    if ($diff < 86400)   return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800)  return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

function conditionBadgeClass(string $cond): string {
    return match($cond) {
        'New'      => 'condition-new',
        'Like New' => 'condition-like-new',
        'Good'     => 'condition-good',
        'Fair'     => 'condition-fair',
        'Worn'     => 'condition-worn',
        default    => 'badge-gray',
    };
}

function statusBadgeClass(string $status): string {
    return match($status) {
        'Available' => 'badge-green',
        'Sold'      => 'badge-red',
        'Traded'    => 'badge-purple',
        default     => 'badge-gray',
    };
}

function productImageTag(string $path, string $title, string $cssClass = ''): string {
    if ($path && file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
        return '<img src="' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($title) . '" class="' . $cssClass . '">';
    }
    $emoji = match(true) {
        str_contains(strtolower($title), 'shoe') || str_contains(strtolower($title), 'sneaker') || str_contains(strtolower($title), 'air') => '👟',
        str_contains(strtolower($title), 'jacket') || str_contains(strtolower($title), 'coat')  => '🧥',
        str_contains(strtolower($title), 'watch') => '⌚',
        str_contains(strtolower($title), 'dress') => '👗',
        str_contains(strtolower($title), 'jeans') || str_contains(strtolower($title), 'pant')  => '👖',
        default => '👕',
    };
    return '<div class="product-card-image-placeholder">' . $emoji . '</div>';
}
