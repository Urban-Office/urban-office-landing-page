<?php
/**
 * Small, dependency-free Lucide-style icon set for the public website.
 * Keep icons outline-only, 24px, round-capped, and driven by currentColor.
 */

if (!function_exists('uo_icon')) {
    function uo_icon(string $name, string $class = 'uo-icon'): string {
        $icons = [
            'sparkles' => '<path d="m12 3-1.3 3.5L7 8l3.7 1.5L12 13l1.3-3.5L17 8l-3.7-1.5L12 3Z"/><path d="m5 14-.8 2.2L2 17l2.2.8L5 20l.8-2.2L8 17l-2.2-.8L5 14Z"/><path d="m19 13-.8 2.2L16 16l2.2.8L19 19l.8-2.2L22 16l-2.2-.8L19 13Z"/>',
            'map-pin' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
            'building' => '<path d="M3 21h18"/><path d="M6 21V4h9v17"/><path d="M15 9h4v12"/><path d="M9 8h2M9 12h2M9 16h2"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'laptop' => '<rect width="18" height="12" x="3" y="4" rx="2"/><path d="M2 20h20"/>',
            'presentation' => '<path d="M2 3h20M4 3v13h16V3M8 21l4-5 4 5"/><path d="m8 11 3-3 2 2 3-3"/>',
            'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M2 12h20M10 12v2h4v-2"/>',
            'message-circle' => '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3 1.7-5A8 8 0 1 1 21 15Z"/>',
            'arrow-up-right' => '<path d="M7 17 17 7M7 7h10v10"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'check' => '<path d="m5 12 4 4L19 6"/>',
            'mail' => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-10 6L2 7"/>',
            'layers' => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5M3 17l9 5 9-5"/>',
            'headphones' => '<path d="M4 14a8 8 0 0 1 16 0"/><path d="M18 19c0 1.1-.9 2-2 2h-1v-7h3a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2ZM6 19a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h3v7H8a2 2 0 0 1-2-2Z"/>',
        ];

        if (!isset($icons[$name])) {
            return '';
        }

        return '<svg class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[$name] . '</svg>';
    }
}
