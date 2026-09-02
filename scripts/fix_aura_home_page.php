<?php
/**
 * Safely rebuild the Aura Spa Home page Elementor payload.
 *
 * Run: php scripts/fix_aura_home_page.php
 */

require dirname( __DIR__ ) . '/wp-load.php';

if ( ! class_exists( '\Elementor\Plugin' ) ) {
	fwrite( STDERR, "Elementor plugin not available.\n" );
	exit( 1 );
}

$post_id = 379;

$data = [
	[
		'id' => 'hero_section',
		'elType' => 'section',
		'settings' => [
			'layout' => 'full_width',
			'padding' => [ 'unit' => 'px', 'top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0 ],
			'background_background' => 'classic',
			'background_color' => '#F4EFE9',
			'border_radius' => [ 'unit' => 'px', 'top' => 32, 'right' => 32, 'bottom' => 32, 'left' => 32 ],
		],
		'elements' => [
			[
				'id' => 'hero_col_left',
				'elType' => 'column',
				'settings' => [ '_column_size' => 55 ],
				'elements' => [
					[
						'id' => 'eyebrow',
						'elType' => 'widget',
						'widgetType' => 'heading',
						'settings' => [
							'title' => 'Luxury wellness escape',
							'header_size' => 'p',
							'size' => '18',
							'text_color' => '#8C7267',
							'typography_typography' => 'custom',
							'typography_font_weight' => '500',
						],
					],
					[
						'id' => 'title',
						'elType' => 'widget',
						'widgetType' => 'heading',
						'settings' => [
							'title' => 'Escape the demands of everyday life and step into a sanctuary of calm.',
							'header_size' => 'h1',
							'size' => '64',
							'text_color' => '#2B2624',
							'typography_typography' => 'custom',
							'typography_font_weight' => '600',
						],
					],
					[
						'id' => 'subtitle',
						'elType' => 'widget',
						'widgetType' => 'text-editor',
						'settings' => [
							'editor' => "<p style='color:#5D4F49; font-size:18px; line-height:1.8; margin:0 0 28px;'>Designed with timeless elegance and inspired by the calming essence of nature, Aura Spa offers thoughtfully curated wellness experiences that restore balance, nurture confidence, and awaken the senses.</p>",
						],
					],
					[
						'id' => 'cta_row',
						'elType' => 'widget',
						'widgetType' => 'button',
						'settings' => [
							'text' => 'Book a Treatment',
							'link' => [ 'url' => '/book-appointment' ],
							'size' => 'sm',
							'background_color' => '#D7B8AD',
							'text_color' => '#2A221F',
							'border_radius' => [ 'unit' => 'px', 'top' => 999, 'right' => 999, 'bottom' => 999, 'left' => 999 ],
							'padding' => [ 'unit' => 'px', 'top' => 18, 'right' => 30, 'bottom' => 18, 'left' => 30 ],
						],
					],
				],
			],
			[
				'id' => 'hero_col_right',
				'elType' => 'column',
				'settings' => [ '_column_size' => 45 ],
				'elements' => [
					[
						'id' => 'hero_image',
						'elType' => 'widget',
						'widgetType' => 'image',
						'settings' => [
							'image' => [ 'url' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=1000&q=80' ],
							'image_size' => 'large',
							'border_radius' => [ 'unit' => 'px', 'top' => 24, 'right' => 24, 'bottom' => 24, 'left' => 24 ],
						],
					],
				],
			],
		],
	],
	[
		'id' => 'benefits_section',
		'elType' => 'section',
		'settings' => [
			'padding' => [ 'unit' => 'px', 'top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0 ],
		],
		'elements' => [
			[
				'id' => 'benefits_heading',
				'elType' => 'widget',
				'widgetType' => 'heading',
				'settings' => [
					'title' => 'Why guests choose Aura Spa',
					'header_size' => 'h2',
					'size' => '42',
					'text_align' => 'center',
					'text_color' => '#2B2624',
				],
			],
			[
				'id' => 'benefits_cards',
				'elType' => 'widget',
				'widgetType' => 'text-editor',
				'settings' => [
					'editor' => "<div style='display:grid; gap:18px; grid-template-columns:repeat(3, minmax(0, 1fr));'><div style='background:#fff; border-radius:18px; padding:22px 20px; border:1px solid #EDE2DB; box-shadow:0 14px 30px rgba(43,38,36,0.05);'><h3 style='margin:0 0 10px; color:#2B2624; font-size:22px;'>Tailored rituals</h3><p style='margin:0; color:#5D4F49; line-height:1.7;'>Tailored rituals designed around your goals.</p></div><div style='background:#fff; border-radius:18px; padding:22px 20px; border:1px solid #EDE2DB; box-shadow:0 14px 30px rgba(43,38,36,0.05);'><h3 style='margin:0 0 10px; color:#2B2624; font-size:22px;'>Expert care</h3><p style='margin:0; color:#5D4F49; line-height:1.7;'>Skilled therapists and refined treatment plans.</p></div><div style='background:#fff; border-radius:18px; padding:22px 20px; border:1px solid #EDE2DB; box-shadow:0 14px 30px rgba(43,38,36,0.05);'><h3 style='margin:0 0 10px; color:#2B2624; font-size:22px;'>Elevated calm</h3><p style='margin:0; color:#5D4F49; line-height:1.7;'>A peaceful, elevated spa experience from start to finish.</p></div></div>",
				],
			],
		],
	],
	[
		'id' => 'rituals_section',
		'elType' => 'section',
		'settings' => [
			'padding' => [ 'unit' => 'px', 'top' => 20, 'right' => 0, 'bottom' => 80, 'left' => 0 ],
			'background_background' => 'classic',
			'background_color' => '#FBF8F5',
		],
		'elements' => [
			[
				'id' => 'rituals_heading',
				'elType' => 'widget',
				'widgetType' => 'heading',
				'settings' => [
					'title' => 'Signature rituals',
					'header_size' => 'h2',
					'size' => '38',
					'text_align' => 'center',
					'text_color' => '#2B2624',
				],
			],
			[
				'id' => 'ritual_cards',
				'elType' => 'widget',
				'widgetType' => 'text-editor',
				'settings' => [
					'editor' => "<div style='display:grid; gap:18px; grid-template-columns:repeat(3, minmax(0,1fr));'><div style='background:#fff; border-radius:18px; padding:20px; border:1px solid #EDE2DB; box-shadow:0 10px 24px rgba(43,38,36,0.04);'><h3 style='margin:0 0 10px; color:#2B2624; font-size:22px;'>Deep Renewal Massage</h3><p style='margin:0; color:#5D4F49; line-height:1.7;'>A restorative body ritual that melts tension and restores a sense of ease.</p></div><div style='background:#fff; border-radius:18px; padding:20px; border:1px solid #EDE2DB; box-shadow:0 10px 24px rgba(43,38,36,0.04);'><h3 style='margin:0 0 10px; color:#2B2624; font-size:22px;'>Glow Facial Therapy</h3><p style='margin:0; color:#5D4F49; line-height:1.7;'>A brightening facial ritual that leaves skin fresh, luminous, and deeply hydrated.</p></div><div style='background:#fff; border-radius:18px; padding:20px; border:1px solid #EDE2DB; box-shadow:0 10px 24px rgba(43,38,36,0.04);'><h3 style='margin:0 0 10px; color:#2B2624; font-size:22px;'>Wellness Packages</h3><p style='margin:0; color:#5D4F49; line-height:1.7;'>Curated combinations for full-body balance, calm, and elevated self-care.</p></div></div>",
				],
			],
		],
	],
];

$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES );
if ( false === $json ) {
	fwrite( STDERR, "wp_json_encode failed.\n" );
	exit( 2 );
}

$decoded = json_decode( $json, true );
if ( JSON_ERROR_NONE !== json_last_error() ) {
	fwrite( STDERR, 'JSON decode failed: ' . json_last_error_msg() . PHP_EOL );
	exit( 3 );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $post_id );

$document = Elementor\Plugin::instance()->documents->get( $post_id );
if ( ! $document ) {
	fwrite( STDERR, "Document for post {$post_id} was not found.\n" );
	exit( 5 );
}

$document->save( [
	'settings' => [ 'hide_title' => 'yes' ],
	'elements' => $data,
] );

$document->set_is_built_with_elementor( true );

$html = Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $post_id );
$required = [
	'Luxury wellness escape',
	'Book a Treatment',
	'Why guests choose Aura Spa',
	'Signature rituals',
	'Deep Renewal Massage',
];

foreach ( $required as $needle ) {
	if ( false === strpos( $html, $needle ) ) {
		fwrite( STDERR, "Missing content: {$needle}\n" );
		exit( 4 );
	}
}

echo "JSON_OK=yes\n";
echo "RAW_JSON_LEN=" . strlen( $json ) . "\n";
echo "HTML_LEN=" . strlen( $html ) . "\n";
foreach ( $required as $needle ) {
	echo $needle . ' => yes' . PHP_EOL;
}
