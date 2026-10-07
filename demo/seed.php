<?php
/**
 * Demo content for Riffle.
 *
 * Used by the WordPress Playground blueprint and for local testing:
 *
 *     wp eval-file wp-content/themes/riffle/demo/seed.php
 *
 * Idempotent: it bails out if the demo posts already exist. All stories,
 * names and illustrations are made up for the demo.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

if ( get_option( 'riffle_demo_seeded' ) ) {
	return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$riffle_demo_dir = __DIR__ . '/images/';

/**
 * Wrap plain paragraphs in block markup.
 *
 * @param string[] $paras Paragraphs; strings starting with "## " become headings, "> " quotes.
 * @return string
 */
$riffle_blocks = static function ( array $paras ) {
	$out = '';
	foreach ( $paras as $p ) {
		if ( 0 === strpos( $p, '## ' ) ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( substr( $p, 3 ) ) . "</h2>\n<!-- /wp:heading -->\n\n";
		} elseif ( 0 === strpos( $p, '> ' ) ) {
			$out .= "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\"><!-- wp:paragraph -->\n<p>" . esc_html( substr( $p, 2 ) ) . "</p>\n<!-- /wp:paragraph --></blockquote>\n<!-- /wp:quote -->\n\n";
		} else {
			$out .= "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->\n\n";
		}
	}
	return $out;
};

// Site identity and reading settings.
update_option( 'blogname', 'Riffle' );
update_option( 'blogdescription', 'Notes from moving water' );
update_option( 'posts_per_page', 8 );
update_option( 'show_on_front', 'posts' );
update_option( 'users_can_register', 1 );

// Authors.
$riffle_authors = array();
foreach (
	array(
		array( 'nora', 'Nora Lindqvist', 'Nora guides on the Bow and writes about cold water, dry flies and the business of guiding.' ),
		array( 'eli', 'Eli Marchetti', 'Eli ties flies for a living and fishes small streams in the Niagara Escarpment whenever the vise lets him.' ),
		array( 'ruth', 'Ruth Okafor', 'Ruth is a freshwater ecologist and Riffle\'s conservation editor.' ),
	) as $riffle_a
) {
	$riffle_user = get_user_by( 'login', $riffle_a[0] );
	$riffle_id   = $riffle_user ? $riffle_user->ID : wp_insert_user(
		array(
			'user_login'   => $riffle_a[0],
			'user_pass'    => wp_generate_password( 24 ),
			'display_name' => $riffle_a[1],
			'first_name'   => strtok( $riffle_a[1], ' ' ),
			'description'  => $riffle_a[2],
			'role'         => 'author',
			'user_email'   => $riffle_a[0] . '@example.com',
		)
	);
	$riffle_authors[ $riffle_a[0] ] = (int) $riffle_id;
}

// Categories.
$riffle_cats = array();
foreach (
	array(
		'reports'      => array( 'River Reports', 'Conditions, hatches and honest notes from the water.' ),
		'fly-tying'    => array( 'Fly Tying', 'Patterns, materials and time at the vise.' ),
		'gear'         => array( 'Gear', 'Long-term tests of rods, reels, lines and the stuff that actually matters.' ),
		'conservation' => array( 'Conservation', 'Watersheds, wild fish and the people who look after them.' ),
		'travel'       => array( 'Travel', 'Destinations worth the drive, the flight or the hike in.' ),
		'members'      => array( 'Members', 'Long reads, deep dives and field guides for Riffle members.' ),
	) as $riffle_slug => $riffle_c
) {
	$riffle_term = term_exists( $riffle_slug, 'category' );
	if ( ! $riffle_term ) {
		$riffle_term = wp_insert_term( $riffle_c[0], 'category', array( 'slug' => $riffle_slug, 'description' => $riffle_c[1] ) );
	}
	$riffle_cats[ $riffle_slug ] = (int) ( is_array( $riffle_term ) ? $riffle_term['term_id'] : $riffle_term );
}

$riffle_body = array(
	'It is easy to forget, standing in the parking lot with coffee going cold, how much of fishing is simply paying attention. The water tells you most of what you need to know if you give it a few minutes before you start casting.',
	'## Reading the water first',
	'Seams, foam lines and the soft edges behind boulders hold fish for the same reason they hold food: the current does the work and the fish do not have to. Start at the tail of the run, fish the inside before the outside, and move slowly enough that you notice when something changes.',
	'Water temperature matters more than any fly in the box. Below about seven degrees Celsius trout are sluggish and hug the bottom; between ten and eighteen they feed with real intent. Above twenty, the kind thing is to stop fishing, or to go and find colder water upstream.',
	'> The best anglers I know spend the first ten minutes of every session not fishing at all.',
	'## What we would do differently',
	'Pack less. A single box with a dozen proven patterns covers most days, and a shorter leader turns over in wind far better than the long, elegant one you tied the night before. Keep fish wet, keep handling short, and photograph them in the net rather than in the air.',
	'None of this is new. But it is worth writing down every season, because the river keeps changing and the habits that worked last year slowly stop working without anyone announcing it.',
	'If you fish this stretch, tell us what you found. The comments are open, and the best reports end up shaping the next one.',
);

$riffle_posts = array(
	array(
		'title'   => 'The Last Good Week of October on the Upper Grand',
		'excerpt' => 'Cold mornings, blue-winged olives at noon and a river with almost nobody on it. Notes from the best week of the season.',
		'cat'     => 'reports',
		'author'  => 'nora',
		'img'     => 1,
		'sticky'  => true,
		'tags'    => array( 'brown trout', 'dry fly', 'Ontario' ),
		'lede'    => array(
			'By the second week of October the Upper Grand empties out. The summer crowds are gone, the leaves are mostly down, and the browns start moving toward the gravel with a kind of reckless appetite that makes up for the cold fingers.',
			'We fished it four days straight, starting each morning at the old mill pool and working downstream until the light went flat. What follows is what worked, what did not, and why this is still the best week of the year to be on the water.',
		),
	),
	array(
		'title'   => 'Six Nymphs That Earned a Permanent Place in My Box',
		'excerpt' => 'Not the prettiest flies at the vise, but the ones I reach for when nothing else is working.',
		'cat'     => 'fly-tying',
		'author'  => 'eli',
		'img'     => 2,
		'tags'    => array( 'nymphing', 'patterns' ),
		'lede'    => array(
			'Every tier has a few flies they would happily never tie again and a few they could tie in the dark. These six sit firmly in the second group, and after three seasons of tracking every fish in a notebook, they account for more than half of what I land.',
			'None of them are new. All of them are simple. That is the point.',
		),
	),
	array(
		'title'   => 'Why Cold-Water Refuges Are the Next Big Conservation Fight',
		'excerpt' => 'As summers warm, the spring-fed pockets that keep trout alive are becoming the most valuable real estate on the river.',
		'cat'     => 'conservation',
		'author'  => 'ruth',
		'img'     => 4,
		'members' => true,
		'tags'    => array( 'climate', 'habitat' ),
		'lede'    => array(
			'On the hottest afternoon of last July, we counted forty-one trout stacked into a pool no bigger than a living room. The river around them was twenty-four degrees. The groundwater seep at the head of the pool was eleven.',
			'Those seeps, and the shade and gravel that protect them, are now the single biggest factor in whether a river keeps its wild fish through a bad summer. Here is what is threatening them and what local groups are doing about it.',
		),
	),
	array(
		'title'   => 'Long-Term Review: Three Seasons With a Mid-Priced 9-Foot 5-Weight',
		'excerpt' => 'A rod that does nothing spectacularly and everything well. That turns out to be exactly what most of us need.',
		'cat'     => 'gear',
		'author'  => 'nora',
		'img'     => 7,
		'tags'    => array( 'rods', 'review' ),
		'lede'    => array(
			'Most rod reviews happen in a parking lot or on a casting pond. This one happened over three seasons, two broken tips and roughly two hundred days on the water, which is the only way I know to find out what a rod is really like.',
			'The short version: it is not the best rod I own, but it is the one in the truck.',
		),
	),
	array(
		'title'   => 'A Week on the Margaree, Where Salmon Fishing Still Feels Old-Fashioned',
		'excerpt' => 'Cape Breton in the fall: long swings, short days and a community built around one river.',
		'cat'     => 'travel',
		'author'  => 'ruth',
		'img'     => 5,
		'tags'    => array( 'Atlantic salmon', 'Nova Scotia' ),
		'lede'    => array(
			'There is a pool on the Margaree where the same three families have fished every October for longer than anyone can remember. They rotate through it in a quiet order that nobody wrote down and everybody follows.',
			'We spent a week learning that order, and a little about why this river has kept its character while so many others have not.',
		),
	),
	array(
		'title'   => 'The Members Field Guide to Winter Steelhead',
		'excerpt' => 'Our complete guide: reading winter water, choosing tackle, and staying safe when the shelf ice comes in.',
		'cat'     => 'members',
		'author'  => 'nora',
		'img'     => 6,
		'members' => true,
		'tags'    => array( 'steelhead', 'winter', 'guide' ),
		'lede'    => array(
			'Winter steelhead fishing is mostly about patience and a little about knowing where not to stand. The fish are fewer, the windows are shorter, and the margin for error on an icy bank is close to zero.',
			'This guide pulls together twenty years of notes from guides on the Lake Ontario tributaries, from reading slow winter water to rigging for the coldest days.',
		),
	),
	array(
		'title'   => 'Tying the Perfect Elk Hair Caddis, Step by Step',
		'excerpt' => 'The most useful dry fly ever designed, and the small details that make yours float like the ones in the shop.',
		'cat'     => 'fly-tying',
		'author'  => 'eli',
		'img'     => 9,
		'tags'    => array( 'dry fly', 'patterns', 'tutorial' ),
		'lede'    => array(
			'Al Troth designed the Elk Hair Caddis in 1957 and it has been catching fish on every continent with trout ever since. It is also, for something with four materials, surprisingly easy to tie badly.',
			'Here is the version I teach in beginner classes, with the three adjustments that make the biggest difference to how it floats.',
		),
	),
	array(
		'title'   => 'Spring Creek Notebook: Small Water, Big Lessons',
		'excerpt' => 'On tiny, clear streams every mistake shows. That is exactly why they make you a better angler.',
		'cat'     => 'reports',
		'author'  => 'eli',
		'img'     => 11,
		'tags'    => array( 'small streams', 'brook trout' ),
		'lede'    => array(
			'You can step across most of the spring creeks that drain the escarpment. The brook trout in them are rarely longer than your hand, and they spook if your shadow so much as touches the water.',
			'I have fished them for twelve years and I am still learning. Here is a season of notes.',
		),
	),
	array(
		'title'   => 'Inside the Volunteer Crews Rebuilding a Trout Stream by Hand',
		'excerpt' => 'Shovels, cedar logs and a lot of patience: how one watershed group restored two kilometres of habitat.',
		'cat'     => 'conservation',
		'author'  => 'ruth',
		'img'     => 10,
		'members' => true,
		'tags'    => array( 'habitat', 'volunteers' ),
		'lede'    => array(
			'Every second Saturday from May to October, a crew of twenty or so volunteers meets at a gravel pull-off and walks into the creek with shovels, chainsaws and enough cedar to build a small cabin.',
			'Over six years they have narrowed, deepened and shaded two kilometres of stream. The trout counts tell the rest of the story.',
		),
	),
	array(
		'title'   => 'What I Actually Carry in My Sling Pack',
		'excerpt' => 'After years of overpacking, the short list of gear that comes on every trip.',
		'cat'     => 'gear',
		'author'  => 'nora',
		'img'     => 3,
		'tags'    => array( 'packs', 'essentials' ),
		'lede'    => array(
			'I used to fish with a vest that weighed as much as a small dog. These days everything fits in a sling pack, and I catch just as many fish.',
			'This is the list, with notes on what earned its spot and what got left in the truck.',
		),
	),
	array(
		'title'   => 'Night Fishing for Big Browns: A Beginner\'s Honest Account',
		'excerpt' => 'Mice, moonless nights and a lot of nervous laughter. What I learned from my first season after dark.',
		'cat'     => 'reports',
		'author'  => 'eli',
		'img'     => 8,
		'tags'    => array( 'brown trout', 'night fishing' ),
		'lede'    => array(
			'Every big-trout angler eventually hears the same advice: if you want the biggest browns in the river, you have to fish when they feed, and they feed at night.',
			'I took that advice this summer. It went about as well as you would expect, and then, one warm night in August, much better.',
		),
	),
	array(
		'title'   => 'Alpenglow and Cutthroat: Three Days in the Kootenays',
		'excerpt' => 'Backcountry lakes, native westslope cutthroat and the long walk that keeps them quiet.',
		'cat'     => 'travel',
		'author'  => 'ruth',
		'img'     => 12,
		'tags'    => array( 'cutthroat', 'British Columbia', 'backcountry' ),
		'lede'    => array(
			'The trail climbs nine hundred metres in six kilometres, and by the time you reach the first lake you have earned every fish in it.',
			'We spent three days at the top of the basin, fishing until alpenglow and sleeping under more stars than any of us had seen in years.',
		),
	),
);

$riffle_now = time();
foreach ( array_reverse( $riffle_posts, true ) as $riffle_i => $riffle_p ) {
	$riffle_content = $riffle_blocks( array_merge( $riffle_p['lede'], $riffle_body ) );
	$riffle_cat_ids = array( $riffle_cats[ $riffle_p['cat'] ] );
	if ( ! empty( $riffle_p['members'] ) && 'members' !== $riffle_p['cat'] ) {
		$riffle_cat_ids[] = $riffle_cats['members'];
	}

	$riffle_post_id = wp_insert_post(
		array(
			'post_title'    => $riffle_p['title'],
			'post_excerpt'  => $riffle_p['excerpt'],
			'post_content'  => $riffle_content,
			'post_status'   => 'publish',
			'post_author'   => $riffle_authors[ $riffle_p['author'] ],
			'post_category' => $riffle_cat_ids,
			'tags_input'    => $riffle_p['tags'],
			'post_date'     => gmdate( 'Y-m-d H:i:s', $riffle_now - ( $riffle_i * 2 + 1 ) * DAY_IN_SECONDS ),
		),
		true
	);
	if ( is_wp_error( $riffle_post_id ) ) {
		continue;
	}

	if ( ! empty( $riffle_p['members'] ) ) {
		update_post_meta( $riffle_post_id, '_riffle_members_only', true );
	}
	if ( ! empty( $riffle_p['sticky'] ) ) {
		stick_post( $riffle_post_id );
	}

	// Featured image: copy to a temp file so sideloading never moves the original.
	$riffle_src = sprintf( '%svalley-%02d.jpg', $riffle_demo_dir, $riffle_p['img'] );
	if ( file_exists( $riffle_src ) ) {
		$riffle_tmp = wp_tempnam( basename( $riffle_src ) );
		copy( $riffle_src, $riffle_tmp );
		$riffle_att = media_handle_sideload(
			array(
				'name'     => basename( $riffle_src ),
				'tmp_name' => $riffle_tmp,
			),
			$riffle_post_id,
			$riffle_p['title']
		);
		if ( ! is_wp_error( $riffle_att ) ) {
			wp_update_post(
				array(
					'ID'           => $riffle_att,
					'post_excerpt' => 'Illustration: Riffle demo artwork.',
				)
			);
			set_post_thumbnail( $riffle_post_id, $riffle_att );
		}
	}

	// A couple of reader replies on the lead story.
	if ( ! empty( $riffle_p['sticky'] ) ) {
		foreach (
			array(
				array( 'Dan P.', 'Fished the mill pool Saturday on your advice. Olives right on schedule at 12:30. Thanks for this.' ),
				array( 'Marie-Claude', 'Great write-up. Water was a touch higher on Sunday but the browns did not seem to mind.' ),
			) as $riffle_c
		) {
			wp_insert_comment(
				array(
					'comment_post_ID'      => $riffle_post_id,
					'comment_author'       => $riffle_c[0],
					'comment_author_email' => sanitize_title( $riffle_c[0] ) . '@example.com',
					'comment_content'      => $riffle_c[1],
					'comment_approved'     => 1,
				)
			);
		}
	}
}

// Remove the default "Hello world!" post if it is still there.
$riffle_hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
if ( $riffle_hello ) {
	wp_delete_post( $riffle_hello->ID, true );
}

// About page and primary menu.
$riffle_about = get_page_by_path( 'about' );
if ( ! $riffle_about ) {
	$riffle_about_id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_title'   => 'About',
			'post_name'    => 'about',
			'post_status'  => 'publish',
			'post_content' => $riffle_blocks(
				array(
					'Riffle is a demo publication built to show off the Riffle WordPress theme: an editorial layout for outdoors and fishing magazines with members-only content, analytics hooks and ad slots built in.',
					'Every story, author and illustration on this site is made up for the demo.',
				)
			),
		)
	);
} else {
	$riffle_about_id = $riffle_about->ID;
}

$riffle_menu_id = wp_create_nav_menu( 'Primary' );
if ( ! is_wp_error( $riffle_menu_id ) ) {
	foreach ( array( 'reports', 'fly-tying', 'gear', 'conservation', 'travel' ) as $riffle_slug ) {
		wp_update_nav_menu_item(
			$riffle_menu_id,
			0,
			array(
				'menu-item-type'      => 'taxonomy',
				'menu-item-object'    => 'category',
				'menu-item-object-id' => $riffle_cats[ $riffle_slug ],
				'menu-item-status'    => 'publish',
			)
		);
	}
	wp_update_nav_menu_item(
		$riffle_menu_id,
		0,
		array(
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $riffle_about_id,
			'menu-item-status'    => 'publish',
		)
	);
	$riffle_locations            = get_theme_mod( 'nav_menu_locations', array() );
	$riffle_locations['primary'] = $riffle_menu_id;
	set_theme_mod( 'nav_menu_locations', $riffle_locations );
}


// Widgets: keep the article sidebar for the ad slot, give the footer some content.
$riffle_widget_blocks = array(
	2 => array( 'content' => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">About Riffle</h3>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Riffle is a demo magazine for the Riffle WordPress theme. Independent writing about rivers, fish and the people who look after them.</p>\n<!-- /wp:paragraph -->" ),
	3 => array( 'content' => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Sections</h3>\n<!-- /wp:heading -->\n\n<!-- wp:categories /-->" ),
	4 => array( 'content' => "<!-- wp:heading {\"level\":3} -->\n<h3 class=\"wp-block-heading\">Latest</h3>\n<!-- /wp:heading -->\n\n<!-- wp:latest-posts {\"postsToShow\":4} /-->" ),
	'_multiwidget' => 1,
);
update_option( 'widget_block', $riffle_widget_blocks );
update_option(
	'sidebars_widgets',
	array(
		'wp_inactive_widgets' => array(),
		'sidebar-article'     => array(),
		'footer-1'            => array( 'block-2' ),
		'footer-2'            => array( 'block-3' ),
		'footer-3'            => array( 'block-4' ),
		'array_version'       => 3,
	)
);

// Pretty permalinks. set_permalink_structure() also refreshes the live $wp_rewrite object.
global $wp_rewrite;
$wp_rewrite->set_permalink_structure( '/%postname%/' );
flush_rewrite_rules();
update_option( 'riffle_demo_seeded', 1 );
