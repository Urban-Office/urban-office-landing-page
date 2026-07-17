<?php
/**
 * Admin Authentication Middleware
 * Enforces admin session checks on dashboard panels
 */

require_once dirname(dirname(__FILE__)) . '/inc/config.php';
require_once dirname(dirname(__FILE__)) . '/inc/functions.php';

// Enforce admin login
check_admin_auth();

// Auto-publish scheduled posts when admin accesses any page
auto_publish_scheduled_posts();
?>
