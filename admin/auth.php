<?php
/**
 * Admin Authentication Middleware
 * Enforces admin session checks, security headers, and scheduled post publishing on dashboard panels.
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

// Defensive security & no-cache headers for all admin endpoints
send_admin_security_headers();

// Enforce admin login (and live account status check)
check_admin_auth();

// Auto-publish scheduled posts when admin accesses any HTML page
if (!defined('ADMIN_JSON_ENDPOINT') || !ADMIN_JSON_ENDPOINT) {
    auto_publish_scheduled_posts();
}
?>
