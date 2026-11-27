# Implementation Plan: Migrate UrbanOffice.co.id to Pure PHP + HTML + MySQL

This document outlines the architecture, database schema, performance configuration, security rules, and migration steps for converting the WordPress-based website `https://urbanoffice.co.id` into a lightweight, high-performance, and custom-built PHP 8.3+ and MySQL 8 website.

---

## User Review Required

> [!IMPORTANT]
> Please review the architectural and styling choices proposed below:
> 1. **Premium Aesthetic & Custom Framework**: We will use Vanilla CSS combined with Google Fonts (Inter, Outfit) to build a modern, high-converting, and beautiful user experience. Hover animations, micro-interactions, glassmorphism, and elegant dark/light contrasts will be utilized.
> 2. **Dynamic Page Routing**: Static landing pages will be organized as physical folders containing `index.php` (e.g. `/virtual-office-surabaya/index.php`). This preserves the exact WordPress URLs while avoiding complex PHP routing engines, ensuring maximum performance.
> 3. **Dynamic Blog Engine**: All blog articles, categories, and tags will reside in a MySQL database. Rewrite rules will cleanly route requests (e.g., `/blog/post-slug/` to `/blog/article.php?slug=post-slug`).
> 4. **CMS Admin Panel**: Located at `/admin`. This will allow managing blog content, media library, settings, and page SEO tags.

---

## Open Questions

> [!IMPORTANT]
> Before executing, please confirm the following:
> 1. **WordPress Importer Access**: Will we run the WordPress importer locally or connect directly to a remote WordPress database? If running locally, we can import from a database export file (`.sql`).
> 2. **E-commerce Features**: The original sitemap contains WooCommerce URLs like `/shop/`, `/cart/`, and `/checkout/`. Do you need a working shop system (checkout, payment gateway), or are these pages deprecated/unused in favor of direct WhatsApp contact forms? (Proposed: We will preserve these URLs with clean landing pages explaining how to order, or deprecate them if unused, prioritizing the WhatsApp lead form).
> 3. **WhatsApp Number & Lead Routing**: Submissions will route to the database and open a pre-filled WhatsApp link. Is `081 0762 0100` (`+6285107620100`) the primary WhatsApp number for leads?

---

## Website Discovery & Inventory

### 1. Complete URL Map & Preservation

To preserve search engine rankings and bookmarks, we will map and keep all URLs exactly as they exist:

| Original WordPress URL | New PHP File Location | Page Type | Status |
|---|---|---|---|
| `/` | `/public_html/index.php` | Static PHP Template | Home |
| `/virtual-office-surabaya/` | `/public_html/virtual-office-surabaya/index.php` | Static PHP Template | Hub Page |
| `/sewa-kantor-surabaya/` | `/public_html/sewa-kantor-surabaya/index.php` | Static PHP Template | Landing Page |
| `/meeting-room-surabaya/` | `/public_html/meeting-room-surabaya/index.php` | Static PHP Template | Landing Page |
| `/coworking-space-urban-office/` | `/public_html/coworking-space-urban-office/index.php` | Static PHP Template | Landing Page |
| `/event-space-55k-perjam-urbanoffice/` | `/public_html/event-space-55k-perjam-urbanoffice/index.php` | Static PHP Template | Landing Page |
| `/murah-sewa-sharing-room-office-free-service-charge/` | `/public_html/sharing-room-office/index.php` | Static PHP Template | Landing Page |
| `/pendirian-perorangan-plus-virtual-office/` | `/public_html/pendirian-perorangan-plus-virtual-office/index.php` | Static PHP Template | Service Page |
| `/pendirian-pt-include-virtual-office/` | `/public_html/pendirian-pt-include-virtual-office/index.php` | Static PHP Template | Service Page |
| `/pendirian-cv-virtual-office/` | `/public_html/pendirian-cv-virtual-office/index.php` | Static PHP Template | Service Page |
| `/pendirian-pma-plus-virtual-office-duplicated/` | `/public_html/pendirian-pma-plus-virtual-office-duplicated/index.php` | Static PHP Template | Service Page |
| `/pajak-dan-akunting/` | `/public_html/pajak-dan-akunting/index.php` | Static PHP Template | Service Page |
| `/urban-office-karir/` | `/public_html/urban-office-karir/index.php` | Static PHP Template | Info Page |
| `/lokasi-urban-office-2/` | `/public_html/lokasi-urban-office/index.php` | Static PHP Template | Info Page |
| `/gallery/` | `/public_html/gallery/index.php` | Static PHP Template | Media Page |
| `/our-history/` | `/public_html/our-history/index.php` | Static PHP Template | Info Page |
| `/our-vision/` | `/public_html/our-vision/index.php` | Static PHP Template | Info Page |
| `/our-achievements/` | `/public_html/our-achievements/index.php` | Static PHP Template | Info Page |
| `/blog/` or `/news-2/` | `/public_html/blog/index.php` | Dynamic PHP Page | Blog Listing |
| `/blog/post-slug/` | `/public_html/blog/article.php?slug=post-slug` | Dynamic PHP Page | Single Post |
| `/category/category-slug/` | `/public_html/blog/category.php?slug=category-slug` | Dynamic PHP Page | Category Page |
| `/tag/tag-slug/` | `/public_html/blog/tag.php?slug=tag-slug` | Dynamic PHP Page | Tag Page |
| `/sitemap.xml` | `/public_html/sitemap.xml` | Dynamically Generated | Sitemap |
| `/robots.txt` | `/public_html/robots.txt` | Static File | Crawl Control |

### 2. Component Inventory

To avoid code duplication, components will be factored into modular PHP includes in `/public_html/inc/components/`:

1. `header.php` - Brand identity, navigation menu, and CTA buttons.
2. `footer.php` - Corporate information, contacts, dynamic sitemap link, and social channels.
3. `hero.php` - Customizable top section accepting `$title`, `$subtitle`, `$image`, and `$cta` params.
4. `features.php` - Facilities list grid (high-speed WiFi, mail handling, reception, signature board, etc.).
5. `pricing_cards.php` - Flexible cards displaying packages (Starter, Luxury, Priority) with specs, pricing, and purchase links.
6. `faq.php` - Accordion-style layout loading questions & answers dynamically.
7. `testimonials.php` - Beautiful slider showing Google reviews with 5-star ratings.
8. `contact_form.php` - Secure AJAX-driven contact form writing to database and triggering WhatsApp.
9. `cta_section.php` - Bottom call-to-action block.

### 3. SEO Map

All SEO configurations will be stored in the database `pages` table for landing pages and the `posts` table for blog posts.
* **Header Metadata Generator (`inc/seo.php`)**: Dynamically outputs meta tags, Open Graph (OG), and Twitter Card markup based on current page URL.
* **JSON-LD Schema Markup**:
  - **Organization Schema**: Applied on Homepage. Includes business name, logo, social profiles, and phone numbers.
  - **LocalBusiness Schema**: Applied on location-specific pages. Includes GPS coordinates, address, and opening hours.
  - **FAQ Schema**: Outputs structured Q&A data when FAQs are present.
  - **Article Schema**: Automatically generated on single blog posts.
  - **Breadcrumb Schema**: Applied site-wide to represent tree path.

### 4. Image Inventory

WordPress uploads will be optimized. During migration:
* All images will be parsed, renamed semantically, and converted to the modern WebP format.
* Image delivery will leverage `loading="lazy"` native lazy-loading with explicit `width` and `height` properties to achieve a CLS (Cumulative Layout Shift) of 0.
* High-priority hero banners will use `<link rel="preload" as="image" href="...">` in the header for faster LCP.

---

## File Structure

The project structure will be created inside the workspace:

```
public_html/
│
├── index.php                         # Homepage template
│
├── about/
│   └── index.php                     # About page
│
├── virtual-office-surabaya/
│   └── index.php                     # Virtual Office Surabaya page
│
├── sewa-kantor-surabaya/
│   └── index.php                     # Private Office Surabaya page
│
├── meeting-room-surabaya/
│   └── index.php                     # Meeting Room page
│
├── coworking-space-urban-office/
│   └── index.php                     # Coworking Space page
│
├── event-space-55k-perjam-urbanoffice/
│   └── index.php                     # Event Space page
│
├── sharing-room-office/
│   └── index.php                     # Sharing Room page
│
├── kemitraan-urban-office/
│   └── index.php                     # Partnership page
│
├── urban-office-karir/
│   └── index.php                     # Careers page
│
├── lokasi-urban-office/
│   └── index.php                     # Location directory page
│
├── pajak-dan-akunting/
│   └── index.php                     # Accounting & tax services page
│
├── gallery/
│   └── index.php                     # Image Gallery
│
├── our-history/
│   └── index.php                     # History timeline page
│
├── our-vision/
│   └── index.php                     # Vision and mission page
│
├── our-achievements/
│   └── index.php                     # Achievements page
│
├── newsletter/
│   └── index.php                     # Newsletter subscription page
│
├── blog/
│   ├── index.php                     # Dynamic blog feed
│   ├── article.php                   # Dynamic single article view
│   ├── category.php                  # Dynamic category view
│   └── tag.php                       # Dynamic tag view
│
├── admin/                            # Administrative Control Panel
│   ├── index.php                     # Login page and overview dashboard
│   ├── posts.php                     # CRUD posts management
│   ├── categories.php                # CRUD category management
│   ├── tags.php                      # CRUD tag management
│   ├── pages.php                     # Landing page metadata & settings
│   ├── media.php                     # Media uploads library
│   ├── leads.php                     # Lead capture dashboard (Excel export)
│   ├── settings.php                  # Global website options
│   ├── redirects.php                 # URL redirects manager
│   ├── logs.php                      # Activity logs display
│   ├── logout.php                    # Terminate admin session
│   └── auth.php                      # Authentication middleware
│
├── assets/                           # Frontend static assets
│   ├── css/
│   │   ├── style.css                 # Master stylesheet (Minified in production)
│   │   └── admin.css                 # Admin styling
│   ├── js/
│   │   ├── main.js                   # Master scripts
│   │   └── admin.js                  # Admin scripts
│   ├── fonts/                        # Local web font files
│   └── images/                       # Optimized webp visuals
│
├── inc/                              # Core backend libraries
│   ├── config.php                    # Application definitions & configurations
│   ├── database.php                  # Database driver (PDO wrapper class)
│   ├── functions.php                 # Utilities, security filters, caching engine
│   ├── seo.php                       # Header metadata and schema constructor
│   ├── header.php                    # Shared frontend header template
│   ├── footer.php                    # Shared frontend footer template
│   └── importer.php                  # Database migrator script
│
├── cache/                            # Storage for server-side HTML page cache
├── uploads/                          # User-uploaded directory
├── sitemap.xml                       # Self-generating sitemap script
├── robots.txt                        # Search crawler control file
└── nginx.conf                        # Nginx config equivalent to .htaccess
```

---

## Database Design

MySQL 8 schema built using PDO prepared statements. All dynamic components query this database.

### SQL Schema Creation script

```sql
CREATE DATABASE IF NOT EXISTS urban_office DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE urban_office;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) DEFAULT 'editor',
    status TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Pages Table (For metadata management of static templates)
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(255) NULL,
    canonical_url VARCHAR(255) NULL,
    og_title VARCHAR(255) NULL,
    og_description VARCHAR(255) NULL,
    og_image VARCHAR(255) NULL,
    schema_faq JSON NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_slug (slug)
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    INDEX idx_cat_slug (slug)
) ENGINE=InnoDB;

-- Tags Table
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    INDEX idx_tag_slug (slug)
) ENGINE=InnoDB;

-- Posts Table
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'draft', -- draft, published
    published_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_slug (slug),
    INDEX idx_post_status (status, published_at DESC)
) ENGINE=InnoDB;

-- Post Categories Join Table
CREATE TABLE IF NOT EXISTS post_categories (
    post_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (post_id, category_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Post Tags Join Table
CREATE TABLE IF NOT EXISTS post_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Media Library Table
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- Leads Table
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    service VARCHAR(100) NOT NULL,
    message TEXT NULL,
    ip_address VARCHAR(45) NULL,
    status VARCHAR(20) DEFAULT 'unread', -- unread, read, processed, spam
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Redirects Table (301/302 URL mappings)
CREATE TABLE IF NOT EXISTS redirects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    old_url VARCHAR(255) NOT NULL UNIQUE,
    new_url VARCHAR(255) NOT NULL,
    type INT DEFAULT 301,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_old_url (old_url)
) ENGINE=InnoDB;

-- Activity Logs Table (Security auditing)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

---

## Security Implementation Plan

We will enforce strict security principles site-wide:

1. **CSRF Protection**: Form actions will require a token verified against the session (`$_SESSION['csrf_token']`).
2. **XSS Protection**: HTML entities will be escaped dynamically on output using `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
3. **SQL Injection Protection**: Strictly utilize PDO Prepared Statements for all database interactions.
4. **Prepared Session Config**: Secure session parameters:
   ```php
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_secure', 1);
   ini_set('session.use_only_cookies', 1);
   ini_set('session.cookie_samesite', 'Strict');
   ```
5. **Rate Limiting**: Limit login attempts by tracking IP addresses and logging failed attempts inside the `activity_logs` table. Implement a 15-minute lockout after 5 failed attempts.
6. **File Upload Security**: Validate media files by checking MIME types (`finfo_file`) rather than extensions, strip metadata, rename files cryptographically, and block direct execution in `uploads/` directory via Nginx rules.

---

## Performance Optimization Strategy

Targeting PageSpeed Mobile > 90 and Desktop > 95:

1. **Brotli & Gzip Compression**: Enforced at the Nginx layer for text assets.
2. **Minification**: Runtime PHP compiler minifies HTML before sending. CSS and JS files are minified on save.
3. **HTTP Caching**: Far-future headers (`Cache-Control: max-age=31536000, public`) for CSS, JS, and image assets.
4. **Server-Side File Caching (TTFB < 50ms)**:
   - Dynamic pages (e.g. blog, category listings) will utilize a server-side static compiler file cache.
   - Cache generates HTML snippets inside `cache/` directory. If the file exists and is younger than 1 hour, it is loaded immediately without database overhead.
5. **Optimized Banners**:
   - WebP image format.
   - `fetchpriority="high"` for hero images.
   - Native lazy loading with proper dimensional ratios to prevent reflows.

---

## Nginx Configuration

Save this file as `nginx.conf` in the project root:

```nginx
# Nginx Configuration File (Equivalent to .htaccess)

# Gzip Compression
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
gzip_proxied any;
gzip_comp_level 6;

# Brotli Compression (If supported by Nginx build)
# brotli on;
# brotli_types text/plain text/css application/json application/javascript text/xml application/xml;

# Security Headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://www.google-analytics.com https://stats.g.doubleclick.net;" always;

# Prevent Execution of scripts in uploads folder
location ~* ^/uploads/.*\.php$ {
    deny all;
}

# Static File Cache Headers
location ~* \.(?:ico|css|js|gif|jpe?g|png|svg|webp|woff2?|eot|ttf|otf)$ {
    expires 1y;
    add_header Cache-Control "public, no-transform";
    access_log off;
}

# Global Index and Router
location / {
    try_files $uri $uri/ /index.php?$args;
}

# Redirects preservation (Clean URLs mapping)
# Dynamic routing for Blog posts
rewrite ^/blog/([a-zA-Z0-9\-]+)/?$ /blog/article.php?slug=$1 last;

# Dynamic routing for Blog Categories
rewrite ^/category/([a-zA-Z0-9\-]+)/?$ /blog/category.php?slug=$1 last;

# Dynamic routing for Blog Tags
rewrite ^/tag/([a-zA-Z0-9\-]+)/?$ /blog/tag.php?slug=$1 last;

# Dynamic routing for XML Sitemap
rewrite ^/sitemap\.xml$ /sitemap.xml last;

# Dynamic routing for robots.txt
rewrite ^/robots\.txt$ /robots.txt last;

# Catch-all rules for directory indexes (enforce trailing slashes or clean redirects)
location ~ ^/([a-zA-Z0-9\-]+)$ {
    try_files $uri /$1/index.php?$args;
}
```

---

## WordPress Database Importer (`inc/importer.php`)

A custom migrator script will run locally to parse WordPress MySQL schemas (typically `wp_posts`, `wp_postmeta`, `wp_terms`, `wp_term_relationships`, and `wp_term_taxonomy`) and load them directly into our new database design.

### Core Importer Functionality:
1. Connect to both databases (Source WordPress DB and Destination MySQL DB).
2. Query and import active users from `wp_users` with placeholder secure passwords.
3. Query active blog categories and tags from taxonomy tables and load into `categories` and `tags`.
4. Fetch active posts of type `post` and status `publish`.
   - Strip Elementor specific shortcodes and markup, formatting layout dynamically.
   - Extract featured image paths and resolve path mapping.
   - Map custom Yoast SEO fields (`_yoast_wpseo_title`, `_yoast_wpseo_metadesc`) to `meta_title` and `meta_description`.
5. Map post relationships and build records in `post_categories` and `post_tags`.
6. Output a summary report of imported items.

---

## Verification Plan

### 1. Automated Verification Checks
- **PHP Linting**: Execute `php -l` on all PHP scripts before staging to find syntax faults.
- **SQL Integrity**: Run queries checking foreign keys and database indexes.
- **W3C Validation & Lighthouse Check**: Verify output templates locally.
- **Load Testing**: Benchmark index page with `ab -n 1000 -c 10` (Apache Bench) to verify TTFB targets under load.

### 2. Manual Verification
- Review template rendering on mobile and desktop devices.
- Confirm submission leads correctly insert into database and pop up the appropriate WhatsApp link.
- Validate sitemap outputs and test Nginx rewrite routes locally.
