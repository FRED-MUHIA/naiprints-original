<?php

namespace App\Support;

class PageDefaults
{
    public static function home(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'kicker' => 'Original Prints & Merch',
                        'title' => 'Pick A Design',
                        'highlight' => '& Prints',
                        'body' => 'Custom tees, hoodies, jerseys, and uniforms crafted in Nairobi. Upload your art or choose a ready design and we will print it fast.',
                        'primary' => ['label' => 'Start Designing', 'url' => '#'],
                        'secondary' => ['label' => 'Explore Catalog', 'url' => '#'],
                        'stats' => [
                            ['value' => '24-72h', 'label' => 'Fast turnaround'],
                            ['value' => '1500+', 'label' => 'Designs ready'],
                            ['value' => 'KSh 1,200+', 'label' => 'Starter packs'],
                        ],
                    ],
                ],
                [
                    'type' => 'tiles',
                    'data' => [
                        'tiles' => [
                            ['kicker' => 'Faith Line', 'title' => 'Pray', 'theme' => 'hero-pray'],
                            ['kicker' => 'Streetwear', 'title' => 'Follow Your Own Path', 'theme' => 'hero-path'],
                            ['kicker' => 'Statement Tees', 'title' => 'Bold. Loud. You.', 'theme' => 'hero-bold'],
                        ],
                    ],
                ],
                [
                    'type' => 'category_carousel',
                    'data' => [
                        'kicker' => 'Shop By Category',
                        'title' => 'Design categories',
                        'view_all_label' => 'View all categories ↗',
                        'view_all_url' => '#',
                        'items' => [
                            ['title' => 'Uncategorized', 'count' => 3, 'image_url' => '', 'icon' => '📦', 'url' => '#'],
                            ['title' => 'T-shirt', 'count' => 16, 'image_url' => '', 'icon' => '👕', 'url' => '#'],
                            ['title' => 'Hoodie', 'count' => 9, 'image_url' => '', 'icon' => '🧥', 'url' => '#'],
                            ['title' => 'Polo T-shirt', 'count' => 1, 'image_url' => '', 'icon' => '👔', 'url' => '#'],
                            ['title' => 'Pillows', 'count' => 2, 'image_url' => '', 'icon' => '🧸', 'url' => '#'],
                            ['title' => 'Kids', 'count' => 8, 'image_url' => '', 'icon' => '🧒', 'url' => '#'],
                        ],
                    ],
                ],
                [
                    'type' => 'collection_showcase',
                    'data' => [
                        'kicker' => 'Ethical suppliers',
                        'badge_image' => '',
                        'badge_alt' => 'Ethical suppliers',
                        'hero_image' => '',
                        'hero_alt' => 'Design studio preview',
                        'hero_note' => '',
                        'items' => [
                            [
                                'label' => 'Birthdays',
                                'image_url' => '',
                                'url' => '/shop',
                            ],
                            [
                                'label' => 'Corporate Tshirts',
                                'image_url' => '',
                                'url' => '/shop',
                            ],
                            [
                                'label' => 'Couple',
                                'image_url' => '',
                                'url' => '/shop',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'best_sellers',
                    'data' => [
                        'kicker' => 'Featured Prints',
                        'title' => 'Best sellers right now',
                        'meta_left' => 'Weekly drops',
                        'meta_right' => 'Limited runs',
                        'product_category_id' => null,
                    ],
                ],
                [
                    'type' => 'gift_finder',
                    'data' => [
                        'title' => 'Need to Find a Gift?',
                        'lead_before' => 'Find the perfect',
                        'lead_after' => 'for your favorite:',
                        'select_options' => ['T-Shirt', 'Hoodie', 'Polo', 'Cap'],
                        'product_category_id' => null,
                        'cta_label' => 'View more designs',
                        'cta_url' => '/shop',
                        'carousel_interval' => 5,
                    ],
                ],
                [
                    'type' => 'how_it_works',
                    'data' => [
                        'kicker' => 'How It Works',
                        'title' => 'From idea to delivery in four steps',
                        'steps' => [
                            ['title' => 'Pick your garment', 'body' => 'Choose tees, hoodies, polos, jerseys, or tote bags.'],
                            ['title' => 'Upload your artwork', 'body' => 'Drop your design or let our team create one for you.'],
                            ['title' => 'Approve your mockup', 'body' => 'We send a digital proof before we print anything.'],
                            ['title' => 'Receive your order', 'body' => 'Express delivery in Nairobi and nationwide shipping.'],
                        ],
                    ],
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'kicker' => 'Corporate & Bulk Orders',
                        'title' => 'Outfit your team in 72 hours.',
                        'body' => 'From branded polos to event merch, our production team handles large orders fast. Request a quote and get a dedicated account manager.',
                        'primary' => ['label' => 'Request a quote', 'url' => '#'],
                        'secondary' => ['label' => 'View lookbook', 'url' => '#'],
                        'stats' => [
                            ['value' => '250+', 'label' => 'Corporate clients'],
                            ['value' => '5,000+', 'label' => 'Units weekly'],
                        ],
                    ],
                ],
                [
                    'type' => 'design_studio',
                    'data' => [
                        'kicker' => 'Design Studio',
                        'title' => 'Build your custom tee in minutes',
                        'body' => 'Upload your logo, pick colors, and preview before checkout.',
                        'background_image' => '',
                        'button' => ['label' => 'Launch designer', 'url' => '#'],
                    ],
                ],
                [
                    'type' => 'footer_notice',
                    'data' => [
                        'headline' => '*15% off Office Supplies:',
                        'message' => 'Valid through 11:59 PM PST on 3/30/26. Prices as marked. Max $75 discount off per order. Discount does not apply to taxes or shipping. Cannot be combined with other discounts, rebates or offers, including negotiated pricing.',
                        'background_color' => '#efefef',
                        'bar_color' => '#ff4b00',
                        'text_color' => '#0f172a',
                    ],
                ],
            ],
        ];
    }

    public static function shop(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'shop_header',
                    'data' => [
                        'title' => 'Shop',
                        'subtitle' => 'Browse the latest custom apparel.',
                    ],
                ],
            ],
        ];
    }

    public static function checkout(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'shop_header',
                    'data' => [
                        'title' => 'Checkout',
                        'subtitle' => 'Finalize your order.',
                    ],
                ],
            ],
        ];
    }

    public static function embroidery(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'embroidery_page',
                    'data' => [
                        'hero' => [
                            'kicker' => 'Embroidery Branding',
                            'title' => 'Embroidery that elevates your brand in',
                            'highlight' => 'Nairobi and beyond.',
                            'body' => 'Nai Prints delivers premium embroidery branding services designed to make your business stand out. From embroidered patches and uniforms to caps, hats, and custom apparel, we combine precision digitizing, durable threads, and expert stitching to create clean, professional results that strengthen brand identity and visibility.',
                            'primary' => ['label' => 'Request a Quote', 'url' => '/shop'],
                            'secondary' => ['label' => 'View Apparel', 'url' => '/shop'],
                            'stats' => [
                                ['value' => 'Small or Bulk', 'label' => 'Flexible order sizes.'],
                                ['value' => 'Tailored Fit', 'label' => 'Made to your brand.'],
                                ['value' => 'Regional Reach', 'label' => 'Nairobi + East Africa.'],
                            ],
                        ],
                        'info_cards' => [
                            [
                                'kicker' => 'Why Embroidery',
                                'title' => 'Long-lasting, premium branding.',
                                'body' => 'Embroidery gives your brand a polished, tactile finish that resists fading and keeps your logo crisp after repeated wear and washes. Perfect for customer-facing teams, corporate gifting, and retail.',
                                'image_url' => '',
                                'image_alt' => '',
                            ],
                            [
                                'kicker' => 'Service Coverage',
                                'title' => '',
                                'body' => 'We serve Nairobi, across Kenya, and the surrounding region. Whether you need a limited batch for a startup or high-volume uniforms for enterprise teams, we tailor production to your timeline and budget.',
                                'image_url' => '',
                                'image_alt' => '',
                            ],
                        ],
                        'services' => [
                            'kicker' => 'Key Services',
                            'title' => 'Embroidery solutions built for visibility.',
                            'items' => [
                                [
                                    'title' => 'Embroidered Patches',
                                    'body' => 'Custom patches for uniforms, workwear, and promotional gear. Durable stitching that keeps your logo sharp and consistent across teams.',
                                    'image_url' => '',
                                    'image_alt' => '',
                                ],
                                [
                                    'title' => 'Embroidered Uniforms',
                                    'body' => 'Professional uniforms for corporate, hospitality, schools, and institutions. We ensure consistent brand placement and premium finish.',
                                    'image_url' => '',
                                    'image_alt' => '',
                                ],
                                [
                                    'title' => 'Embroidered Caps & Hats',
                                    'body' => 'High-visibility headwear for staff and events. Precision embroidery for bold, readable branding on every cap.',
                                    'image_url' => '',
                                    'image_alt' => '',
                                ],
                                [
                                    'title' => 'Custom Embroidered Apparel',
                                    'body' => 'Polos, jackets, hoodies, and premium wearables tailored to your brand. Perfect for teams, merchandise, and client gifts.',
                                    'image_url' => '',
                                    'image_alt' => '',
                                ],
                            ],
                        ],
                        'impact' => [
                            'kicker' => 'Brand Impact',
                            'title' => 'Stand out with professional embroidery.',
                            'body' => 'Embroidered branding communicates credibility and quality. It helps businesses build a strong visual identity that customers recognize and trust. With Nai Prints, every stitch is aligned to your brand guidelines for cohesive, professional presentation.',
                            'bullets' => [
                                'Clean, consistent logo reproduction.',
                                'Premium threads that hold color and shape.',
                                'Durable finishes for daily wear and long-term use.',
                            ],
                        ],
                        'process' => [
                            'kicker' => 'How We Work',
                            'steps' => [
                                ['title' => 'Design & Digitizing', 'body' => 'We refine your logo and prepare precise embroidery files.'],
                                ['title' => 'Sampling & Approval', 'body' => 'We test stitch quality and finalize placement with you.'],
                                ['title' => 'Production & Delivery', 'body' => 'We scale from small to large orders with consistent output.'],
                            ],
                        ],
                        'portfolio' => [
                            'kicker' => 'Embroidery Portfolio',
                            'title' => 'Recent embroidery work for teams and brands.',
                            'items' => [
                                ['image_url' => '', 'image_alt' => 'Embroidered patch sample'],
                                ['image_url' => '', 'image_alt' => 'Embroidered uniform branding'],
                                ['image_url' => '', 'image_alt' => 'Embroidered cap logo'],
                                ['image_url' => '', 'image_alt' => 'Custom embroidered hoodie'],
                                ['image_url' => '', 'image_alt' => 'Corporate polo embroidery'],
                                ['image_url' => '', 'image_alt' => 'Embroidered merch detail'],
                            ],
                        ],
                        'cta' => [
                            'kicker' => 'Ready to Brand',
                            'title' => 'Let’s build your embroidery identity.',
                            'body' => 'Share your logo and project details. Nai Prints will recommend the best embroidery options for your team, event, or retail line in Nairobi, across Kenya, and the region.',
                            'primary' => ['label' => 'Start Your Order', 'url' => '/shop'],
                            'secondary' => ['label' => 'Talk to a Specialist', 'url' => '/shop'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function jersey(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'jersey_page',
                    'data' => [
                        'hero' => [
                            'kicker' => 'Custom Jersey Printing Kenya',
                            'title' => 'Jersey Sales and Custom Branding for',
                            'highlight' => 'Teams, Schools & Football Academies',
                            'body' => 'From school tournaments to football league weekends, Nai Prints delivers premium jerseys with names, numbers, and sponsor branding. We support small and bulk orders with fast production in Nairobi and delivery across Kenya.',
                            'image' => '',
                            'image_alt' => 'Custom jersey showcase',
                            'primary' => ['label' => 'Shop Jerseys', 'url' => '/shop'],
                            'secondary' => ['label' => 'Open 3D Designer', 'url' => '/mockup'],
                            'stats' => [
                                ['value' => '48-72 Hrs', 'label' => 'Typical turnaround'],
                                ['value' => '10+ Pieces', 'label' => 'Minimum team order'],
                                ['value' => 'Nationwide', 'label' => 'Delivery in Kenya'],
                            ],
                        ],
                        'audiences' => [
                            'kicker' => 'Who We Serve',
                            'title' => 'Made for competitive and school sports programs',
                            'items' => [
                                [
                                    'title' => 'Teams & Clubs',
                                    'body' => 'Match-day jerseys, away kits, training tops, and goalkeeper designs with full customization.',
                                ],
                                [
                                    'title' => 'Schools',
                                    'body' => 'Inter-house and inter-school sports kits with clear numbering, durable print quality, and consistent sizing.',
                                ],
                                [
                                    'title' => 'Football Academies',
                                    'body' => 'Academy kits with player names, sponsor logos, and repeat-order consistency for every season.',
                                ],
                            ],
                        ],
                        'designs' => [
                            'kicker' => 'Jersey Gallery',
                            'title' => 'Different jersey design inspirations',
                            'items' => [
                                ['image_url' => 'https://source.unsplash.com/1200x1200/?football,jersey,red', 'image_alt' => 'Red football jersey design'],
                                ['image_url' => 'https://source.unsplash.com/1200x1200/?soccer,uniform,blue', 'image_alt' => 'Blue soccer jersey design'],
                                ['image_url' => 'https://source.unsplash.com/1200x1200/?sports,jersey,green', 'image_alt' => 'Green sports jersey design'],
                                ['image_url' => 'https://source.unsplash.com/1200x1200/?football,academy,kit', 'image_alt' => 'Football academy jersey kit'],
                                ['image_url' => 'https://source.unsplash.com/1200x1200/?school,sports,uniform', 'image_alt' => 'School sports jersey design'],
                                ['image_url' => 'https://source.unsplash.com/1200x1200/?team,kit,football', 'image_alt' => 'Team football jersey set'],
                            ],
                        ],
                        'video' => [
                            'kicker' => 'Branding Video',
                            'title' => 'See custom jersey branding in action',
                            'embed_url' => 'https://www.youtube.com/embed?listType=search&list=custom+jersey+printing+kenya',
                        ],
                        'cta' => [
                            'title' => 'Ready to create your next jersey set?',
                            'body' => 'Use our 3D designer to start your concept, or contact our team for school and club bulk pricing.',
                            'primary' => ['label' => 'Launch 3D Designer', 'url' => '/mockup'],
                            'secondary' => ['label' => 'Request Team Quote', 'url' => '/corporate'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function designer(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'designer_page',
                    'data' => [
                        'brand_name' => 'Nai Prints',
                        'brand_subtitle' => 'Design Studio',
                        'topbar_title' => 'Untitled design · 1080px × 1080px',
                        'top_menu' => ['File', 'Resize', 'Upload'],
                        'user_initials' => 'FM',
                        'add_to_cart_label' => 'Add to cart',
                        'add_to_cart_url' => '#',
                        'back_to_shop_label' => 'Back to shop',
                        'back_to_shop_url' => '/',
                        'share_label' => 'Share',
                        'left_rail' => [
                            ['label' => 'Tools', 'icon' => '🧰'],
                            ['label' => 'Projects', 'icon' => '📁'],
                            ['label' => 'Apps', 'icon' => '🧩'],
                            ['label' => 'Magic', 'icon' => '✨'],
                            ['label' => 'QR', 'icon' => '🔳'],
                            ['label' => 'Voice', 'icon' => '🔊'],
                            ['label' => 'Tracer', 'icon' => '🧪'],
                            ['label' => 'Mockups', 'icon' => '🖼️'],
                        ],
                        'mockups' => [
                            'tshirt' => ['front' => '', 'back' => ''],
                            'polo' => ['front' => '', 'back' => ''],
                            'hoodie' => ['front' => '', 'back' => ''],
                            'cap' => ['front' => ''],
                            'mug' => ['front' => ''],
                            'pillow' => ['front' => '', 'back' => ''],
                        ],
                        'products' => [
                            'tshirt' => ['name' => 'T-Shirt', 'icon' => '👕'],
                            'polo' => ['name' => 'Polo Shirt', 'icon' => '🧥'],
                            'hoodie' => ['name' => 'Hoodie', 'icon' => '🧢'],
                            'cap' => ['name' => 'Cap', 'icon' => '🧢'],
                            'mug' => ['name' => 'Cup / Mug', 'icon' => '☕'],
                            'pillow' => ['name' => 'Pillow', 'icon' => '🛏️'],
                        ],
                        'print_areas' => [
                            'tshirt' => [
                                'front' => ['x' => 157, 'y' => 175, 'w' => 260, 'h' => 280],
                                'back' => ['x' => 145, 'y' => 146, 'w' => 260, 'h' => 300],
                            ],
                            'polo' => [
                                'front' => ['x' => 185, 'y' => 240, 'w' => 230, 'h' => 260],
                                'back' => ['x' => 185, 'y' => 230, 'w' => 230, 'h' => 280],
                            ],
                            'hoodie' => [
                                'front' => ['x' => 180, 'y' => 260, 'w' => 240, 'h' => 260],
                                'back' => ['x' => 180, 'y' => 250, 'w' => 240, 'h' => 280],
                            ],
                            'cap' => [
                                'front' => ['x' => 200, 'y' => 310, 'w' => 200, 'h' => 130],
                            ],
                            'mug' => [
                                'front' => ['x' => 190, 'y' => 240, 'w' => 190, 'h' => 200],
                            ],
                            'pillow' => [
                                'front' => ['x' => 180, 'y' => 230, 'w' => 240, 'h' => 240],
                                'back' => ['x' => 180, 'y' => 230, 'w' => 240, 'h' => 240],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function mockup(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'mockup_page',
                    'data' => [
                        'brand_name' => 'Nai Prints',
                        'brand_mark' => 'NP',
                        'brand_logo' => '',
                        'topbar_title' => 'Untitled design - 1080px × 1080px',
                        'top_menu' => ['File', 'Resize', 'Editing'],
                        'file_title' => 'Untitled design',
                        'file_size' => '1080px × 1080px',
                        'save_label' => 'Save design',
                        'add_to_cart_label' => 'Add to cart',
                        'add_to_cart_url' => '#',
                        'back_to_shop_label' => 'Back to shop',
                        'back_to_shop_url' => '/',
                        'share_label' => 'Share',
                        'product_colors' => [
                            'tshirt' => [
                                ['name' => 'Classic White', 'value' => '#f8fafc'],
                                ['name' => 'Ink Black', 'value' => '#111827'],
                                ['name' => 'Sand', 'value' => '#f5e9d8'],
                            ],
                            'hoodie' => [
                                ['name' => 'Heather', 'value' => '#e5e7eb'],
                                ['name' => 'Charcoal', 'value' => '#1f2937'],
                                ['name' => 'Storm', 'value' => '#334155'],
                            ],
                            'polo' => [
                                ['name' => 'Ivory', 'value' => '#f9fafb'],
                                ['name' => 'Navy', 'value' => '#1e293b'],
                                ['name' => 'Olive', 'value' => '#4b5d4a'],
                            ],
                            'cap' => [
                                ['name' => 'Classic', 'value' => '#f9fafb'],
                                ['name' => 'Midnight', 'value' => '#0f172a'],
                                ['name' => 'Clay', 'value' => '#d9b99b'],
                            ],
                            'mug' => [
                                ['name' => 'Glossy', 'value' => '#f9fafb'],
                                ['name' => 'Graphite', 'value' => '#334155'],
                                ['name' => 'Cream', 'value' => '#f3e9d9'],
                            ],
                            'pillow' => [
                                ['name' => 'Soft White', 'value' => '#f9fafb'],
                                ['name' => 'Stone', 'value' => '#cbd5f5'],
                                ['name' => 'Rose', 'value' => '#f4c7c3'],
                            ],
                            'totebag' => [
                                ['name' => 'Canvas', 'value' => '#f5f2ea'],
                                ['name' => 'Natural', 'value' => '#e7ddc8'],
                                ['name' => 'Charcoal', 'value' => '#4b5563'],
                            ],
                        ],
                        'mockups' => [
                            'tshirt' => ['front' => '', 'back' => ''],
                            'hoodie' => ['front' => '', 'back' => ''],
                            'polo' => ['front' => '', 'back' => ''],
                            'cap' => ['front' => ''],
                            'mug' => ['front' => ''],
                            'pillow' => ['front' => '', 'back' => ''],
                            'totebag' => ['front' => '', 'back' => ''],
                        ],
                        'templates' => [
                            'all_results' => [],
                        ],
                        'rail_labels' => [
                            'mockups' => 'Products',
                            'templates' => 'Templates',
                            'apps' => 'Apps',
                            'uploads' => 'Uploads',
                            'edit' => 'Edit',
                            'qr' => 'QR Code',
                            'voice' => 'AI Voice',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function corporate(): array
    {
        return [
            'sections' => [
                [
                    'type' => 'corporate_page',
                    'data' => [
                        'hero' => [
                            'kicker' => 'Update',
                            'kicker_note' => '14 new products available',
                            'title' => 'The easy way to sell merch online',
                            'body' => 'Let Nai Prints take care of printing, shipping, and support so you can focus on your team.',
                            'primary' => ['label' => 'Start designing', 'url' => '/designer'],
                            'secondary' => ['label' => 'Dashboard', 'url' => '/admin'],
                            'image' => '/images/corporate-hero.png',
                            'image_alt' => 'Merch platform preview',
                            'stats' => [
                                ['value' => '250+', 'label' => 'Corporate clients'],
                                ['value' => '72h', 'label' => 'Average turnaround'],
                                ['value' => '5k+', 'label' => 'Units weekly'],
                            ],
                        ],
                        'highlights' => [
                            [
                                'title' => 'Dedicated account manager',
                                'body' => 'A single point of contact to handle approvals, mockups, and timelines.',
                            ],
                            [
                                'title' => 'Flexible fulfillment',
                                'body' => 'Ship to one office or distribute orders to individual addresses.',
                            ],
                            [
                                'title' => 'Brand consistency',
                                'body' => 'Locked brand colors, artwork placement, and uniform sizing guidance.',
                            ],
                        ],
                        'process' => [
                            'kicker' => 'How it works',
                            'title' => 'Launch corporate merch in four steps',
                            'steps' => [
                                [
                                    'title' => 'Share your brief',
                                    'body' => 'Tell us the event, audience size, and delivery date.',
                                ],
                                [
                                    'title' => 'Approve the mockups',
                                    'body' => 'We send visual proofs and fit specs for sign-off.',
                                ],
                                [
                                    'title' => 'Production & quality checks',
                                    'body' => 'Every unit is printed, inspected, and packed in-house.',
                                ],
                                [
                                    'title' => 'Delivery & reorders',
                                    'body' => 'We ship fast and keep your design on file for repeats.',
                                ],
                            ],
                        ],
                        'logos' => [
                            'kicker' => 'Trusted by teams',
                            'title' => 'Outfitting high-growth brands',
                            'items' => ['Fintech teams', 'Schools', 'Startups', 'NGOs', 'Hospitality groups', 'Events'],
                        ],
                        'cta' => [
                            'title' => 'Ready for a corporate quote?',
                            'body' => 'Send your requirements and get a tailored proposal within 24 hours.',
                            'primary' => ['label' => 'Request a quote', 'url' => '/shop'],
                            'secondary' => ['label' => 'Talk to sales', 'url' => '/shop'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function header(): array
    {
        return [
            'logo_url' => '/images/logo.svg',
            'favicon_url' => '/images/logo.svg',
            'announcement_speed' => 120,
            'announcement_items' => [
                ['text' => 'Wear Your Vibe'],
                ['text' => 'Express Yourself'],
                ['text' => 'Prints That Speak'],
                ['text' => '10% Off All Orders', 'highlight' => true],
            ],
            'menu' => [
                ['label' => 'Shop', 'url' => '/shop'],
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Men', 'url' => '#'],
                ['label' => 'Women', 'url' => '#'],
                ['label' => 'Sports', 'url' => '#'],
                ['label' => 'Kids', 'url' => '#'],
                ['label' => 'Corporate', 'url' => '/corporate'],
                ['label' => 'Embroidery', 'url' => '/embroidery'],
                ['label' => 'Jersey', 'url' => '/jersey'],
                ['label' => '3D Designer', 'url' => '/3d-designer'],
                ['label' => 'Birthday', 'url' => '#'],
                ['label' => 'Couple', 'url' => '#'],
                ['label' => 'Contact', 'url' => '/contact'],
            ],
            'menu_strip' => [
                ['icon' => '🍀', 'label' => 'St Patricks Day', 'url' => '#'],
                ['icon' => '🎧', 'label' => 'Music', 'url' => '#'],
                ['icon' => '🎬', 'label' => 'Movies', 'url' => '#'],
                ['icon' => '📺', 'label' => 'Television', 'url' => '#'],
                ['icon' => '🏀', 'label' => 'Sports', 'url' => '#'],
                ['icon' => '🧵', 'label' => 'Vintage', 'url' => '#'],
                ['icon' => '🐾', 'label' => 'Animals', 'url' => '#'],
                ['icon' => '🤣', 'label' => 'Funny', 'url' => '#'],
                ['icon' => '👕', 'label' => 'T-Shirts', 'url' => '#'],
                ['icon' => '🧦', 'label' => 'Socks', 'url' => '#', 'badge' => 'NEW'],
                ['icon' => '🧥', 'label' => 'Hoodies', 'url' => '#'],
                ['icon' => '👜', 'label' => 'Bags', 'url' => '#'],
                ['icon' => '☕', 'label' => 'Mugs', 'url' => '#'],
                ['icon' => '🧢', 'label' => 'Hats', 'url' => '#'],
                ['icon' => '🧒', 'label' => 'Kids T-Shirts', 'url' => '#'],
            ],
            'menu_strip_cta' => ['label' => 'See All Products', 'url' => '/shop'],
            'menu_strip_cta_position' => 8,
        ];
    }

    public static function footer(): array
    {
        return [
            'brand' => 'Nai Prints',
            'logo_url' => '/images/logo.svg',
            'description' => 'T-shirt & garment printing in Nairobi.',
            'hours' => ['Mon-Sat', '8am-7pm'],
            'whatsapp_number' => '254700000000',
            'whatsapp_message' => 'Hello I need assistance',
            'whatsapp_scroll' => true,
            'whatsapp_threshold' => 180,
            'whatsapp_tooltip' => 'Chat with us on WhatsApp',
            'columns' => [
                [
                    'title' => 'Shop',
                    'links' => [
                        ['label' => 'Men', 'url' => '#'],
                        ['label' => 'Women', 'url' => '#'],
                        ['label' => 'Kids', 'url' => '#'],
                        ['label' => 'Gifts', 'url' => '#'],
                    ],
                ],
                [
                    'title' => 'Company',
                    'links' => [
                        ['label' => 'About', 'url' => '#'],
                        ['label' => 'Production', 'url' => '#'],
                        ['label' => 'Wholesale', 'url' => '#'],
                        ['label' => 'Careers', 'url' => '#'],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'links' => [
                        ['label' => '0700 000 000', 'url' => '#'],
                        ['label' => 'hello@naiprints.co.ke', 'url' => '#'],
                        ['label' => 'Kimathi Street, Nairobi', 'url' => '#'],
                    ],
                ],
            ],
            'copyright' => '© 2026 Nai Prints. All rights reserved.',
        ];
    }

    public static function purchasePopups(): array
    {
        return [
            'enabled' => true,
            'interval' => 12,
            'items' => [
                [
                    'name' => 'Gilbert',
                    'product' => 'Cool To Be Kind',
                    'time' => '52 minutes ago',
                    'image' => '',
                    'url' => '/shop',
                    'product_id' => null,
                ],
                [
                    'name' => 'Tasha',
                    'product' => 'Street Mood Hoodie',
                    'time' => '15 minutes ago',
                    'image' => '',
                    'url' => '/shop',
                    'product_id' => null,
                ],
                [
                    'name' => 'Brian',
                    'product' => 'Kids Joy Set',
                    'time' => '1 hour ago',
                    'image' => '',
                    'url' => '/shop',
                    'product_id' => null,
                ],
            ],
        ];
    }

    public static function section(string $type): array
    {
        $defaults = [
            'hero' => self::home()['sections'][0],
            'tiles' => self::home()['sections'][1],
            'category_carousel' => self::home()['sections'][2],
            'collection_showcase' => self::home()['sections'][3],
            'best_sellers' => self::home()['sections'][4],
            'gift_finder' => self::home()['sections'][5],
            'how_it_works' => self::home()['sections'][6],
            'cta' => self::home()['sections'][7],
            'design_studio' => self::home()['sections'][8],
            'footer_notice' => self::home()['sections'][9],
            'shop_header' => self::shop()['sections'][0],
            'embroidery_page' => self::embroidery()['sections'][0],
            'jersey_page' => self::jersey()['sections'][0],
            'corporate_page' => self::corporate()['sections'][0],
            'designer_page' => self::designer()['sections'][0],
            'mockup_page' => self::mockup()['sections'][0],
        ];

        return $defaults[$type] ?? ['type' => $type, 'data' => []];
    }
}
