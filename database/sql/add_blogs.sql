-- Blog management table for admin panel and public blog pages.
-- Run this migration before using /admin/blogs.

CREATE TABLE IF NOT EXISTS blogs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    blog_key VARCHAR(100) NULL,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(240) NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    image_alt VARCHAR(180) NULL,
    category VARCHAR(120) NOT NULL DEFAULT 'Print Tips',
    badge_theme VARCHAR(20) NOT NULL DEFAULT 'purple',
    author_name VARCHAR(120) NOT NULL DEFAULT 'RCS Print Team',
    meta_title VARCHAR(220) NULL,
    meta_description VARCHAR(255) NULL,
    published_at DATETIME NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_blogs_key (blog_key),
    UNIQUE KEY uq_blogs_slug (slug),
    KEY idx_blogs_active_featured_sort (is_active, is_featured, sort_order),
    KEY idx_blogs_slug_active (slug, is_active),
    KEY idx_blogs_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO blogs
    (blog_key,title,slug,excerpt,content,featured_image,image_alt,category,badge_theme,author_name,meta_title,meta_description,published_at,sort_order,is_featured,is_active,created_at,updated_at)
SELECT
    'business-card-finish',
    'How to Choose the Perfect Business Card Finish',
    'how-to-choose-the-perfect-business-card-finish',
    'Learn when to pick matte, gloss, textured or premium laminated cards for a stronger first impression.',
    '<h2>Why finish matters</h2><p>Your business card is often the first printed touchpoint for your brand. The right finish makes it feel premium and memorable.</p><h3>Popular finish options</h3><ul><li><strong>Matte:</strong> elegant, smooth and easy to read.</li><li><strong>Gloss:</strong> bright, shiny and great for colorful designs.</li><li><strong>Textured:</strong> best for premium brands and invitations.</li></ul><p>Choose a finish that matches your design, audience and budget.</p>',
    'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=900&q=85&fit=crop',
    'Premium printed business cards arranged on a desk',
    'Print Tips', 'purple', 'RCS Print Team',
    'How to Choose the Perfect Business Card Finish',
    'A quick guide to matte, gloss, textured and premium business card finishes.',
    '2026-05-09 10:00:00', 0, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE blog_key = 'business-card-finish');

INSERT INTO blogs
    (blog_key,title,slug,excerpt,content,featured_image,image_alt,category,badge_theme,author_name,meta_title,meta_description,published_at,sort_order,is_featured,is_active,created_at,updated_at)
SELECT
    'flyer-design-ideas',
    '5 Flyer Design Ideas That Get More Customers',
    '5-flyer-design-ideas-that-get-more-customers',
    'Simple layout, color and copy tips to make your next flyer campaign clear, attractive and conversion focused.',
    '<h2>Make your flyer easy to scan</h2><p>A good flyer communicates the offer within seconds. Keep the headline bold, use one main image and place the call-to-action clearly.</p><ol><li>Use a strong headline.</li><li>Keep the design uncluttered.</li><li>Highlight one offer only.</li><li>Add phone, WhatsApp or QR code.</li><li>Print on good paper for better trust.</li></ol><p>These small choices can improve response from local promotions.</p>',
    'https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?w=900&q=85&fit=crop',
    'Creative flyer and brochure design samples',
    'Design Ideas', 'orange', 'RCS Print Team',
    '5 Flyer Design Ideas That Get More Customers',
    'Practical flyer layout, copy and printing tips for local marketing campaigns.',
    '2026-05-05 10:00:00', 1, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE blog_key = 'flyer-design-ideas');

INSERT INTO blogs
    (blog_key,title,slug,excerpt,content,featured_image,image_alt,category,badge_theme,author_name,meta_title,meta_description,published_at,sort_order,is_featured,is_active,created_at,updated_at)
SELECT
    'bulk-printing-checklist',
    'Bulk Printing Checklist for Events and Shops',
    'bulk-printing-checklist-for-events-and-shops',
    'Plan quantities, paper type, delivery timing and finishing options before placing your next large print order.',
    '<h2>Plan before you print</h2><p>Bulk printing becomes smoother when your design, quantity and delivery timeline are ready before production starts.</p><h3>Checklist</h3><ul><li>Confirm final design size and bleed.</li><li>Choose paper GSM and finish.</li><li>Approve proof before bulk production.</li><li>Keep delivery date and address ready.</li></ul><blockquote>For urgent event printing, always keep one extra day for proofing and packing.</blockquote>',
    'https://images.unsplash.com/photo-1600172454284-934feca24de6?w=900&q=85&fit=crop',
    'Stacks of brochures and colorful printed material',
    'Bulk Orders', 'green', 'RCS Print Team',
    'Bulk Printing Checklist for Events and Shops',
    'A simple checklist for flyers, brochures, posters and bulk business printing orders.',
    '2026-05-02 10:00:00', 2, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE blog_key = 'bulk-printing-checklist');

INSERT INTO blogs
    (blog_key,title,slug,excerpt,content,featured_image,image_alt,category,badge_theme,author_name,meta_title,meta_description,published_at,sort_order,is_featured,is_active,created_at,updated_at)
SELECT
    'brochure-printing-trust',
    'Why Professional Brochure Printing Builds Trust',
    'why-professional-brochure-printing-builds-trust',
    'A well-designed brochure explains your business clearly and gives customers something professional to remember.',
    '<h2>Brochures make your brand tangible</h2><p>Digital marketing is important, but printed brochures still help customers compare services, prices and benefits in a focused way.</p><p>Use high-quality images, clear headings and a simple structure: problem, solution, benefits and contact details.</p><h3>Best use cases</h3><ul><li>Showroom handouts</li><li>Real estate projects</li><li>Product catalogs</li><li>Event and exhibition kits</li></ul>',
    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=900&q=85&fit=crop',
    'Printed brochures and marketing material on a desk',
    'Branding', 'purple', 'RCS Print Team',
    'Why Professional Brochure Printing Builds Trust',
    'How well-designed printed brochures can improve customer trust and brand recall.',
    '2026-04-28 10:00:00', 3, 1, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM blogs WHERE blog_key = 'brochure-printing-trust');
