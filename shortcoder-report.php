<?php
/**
 * Shortcoder Report — lists every Shortcoder plugin entry (name + raw content)
 * for every site in a WordPress multisite (subfolder) network.
 *
 * INSTALL: Upload this file next to wp-load.php (the WordPress root of the
 * network's main site) and open it in a browser while logged in as a
 * Super Admin, e.g. https://pilkingtonimmigration.com/shortcoder-report.php
 *
 * Access modes:
 *   ?              -> styled HTML report (this page)
 *   ?download=csv  -> downloads a CSV of every shortcode across all sites
 *   ?json=1        -> returns the same data as JSON
 *
 * Only Super Admins can view its output; anyone else gets a 403.
 */

define( 'WP_USE_THEMES', false );
require_once __DIR__ . '/wp-load.php';

if ( ! is_multisite() ) {
	wp_die( 'This site is not a WordPress multisite network.' );
}

if ( ! is_user_logged_in() || ! is_super_admin() ) {
	status_header( 403 );
	wp_die( 'You must be logged in as a Super Admin to view this report.' );
}

/**
 * Fetch every Shortcoder entry ([sc name="..."]) on the currently active blog.
 *
 * @return array<int, array{id:int, name:string, status:string, raw:string}>
 */
function sc_report_get_shortcodes_for_current_blog() {
	$entries = array();

	if ( ! post_type_exists( 'shortcoder' ) ) {
		return $entries;
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'shortcoder',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	foreach ( $query->posts as $post ) {
		$entries[] = array(
			'id'     => $post->ID,
			'name'   => $post->post_title,
			'status' => $post->post_status,
			'raw'    => $post->post_content,
		);
	}

	return $entries;
}

// Gather data for every site up front so it can feed the HTML report, CSV export and JSON output.
$sites      = get_sites( array( 'number' => 0 ) );
$report     = array();
$totalCount = 0;

foreach ( $sites as $site ) {
	switch_to_blog( $site->blog_id );

	$entries = sc_report_get_shortcodes_for_current_blog();

	$report[] = array(
		'blog_id' => (int) $site->blog_id,
		'name'    => get_bloginfo( 'name' ),
		'url'     => home_url( '/' ),
		'entries' => $entries,
	);

	$totalCount += count( $entries );

	restore_current_blog();
}

if ( isset( $_GET['download'] ) && 'csv' === $_GET['download'] ) {
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="shortcoder-report.csv"' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'Site Name', 'Site URL', 'Shortcode Name', 'Status', 'Raw Content' ) );

	foreach ( $report as $site ) {
		foreach ( $site['entries'] as $entry ) {
			fputcsv( $out, array( $site['name'], $site['url'], $entry['name'], $entry['status'], $entry['raw'] ) );
		}
	}

	fclose( $out );
	exit;
}

if ( isset( $_GET['json'] ) ) {
	wp_send_json( $report );
}

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Shortcoder Report — All Sites</title>
<style>
	* { box-sizing: border-box; }
	body {
		margin: 0;
		color: #1d2327;
		background: #f1f2f6;
        font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande", sans-serif;
	}
	.sc-header {
		padding: 26px 26px 20px;
    background: linear-gradient(100deg, #0f6cbd 0%, #1a7f52 100%);
    color: #ffffff;
        margin-bottom: 54px !important;
            border-radius: 12px;
    margin-top: 35px !important;
	}
	.sc-header h1 { margin: 0 0 6px; font-size: 26px; }
	.sc-header p { margin: 0; opacity: .9; }
	.sc-container { max-width: 1020px; margin: 0 auto;  }

	.sc-toolbar {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		background: #fff;
		border-radius: 10px;
		box-shadow: 0 1px 3px rgba(0,0,0,.08);
		padding: 16px 20px;
		margin: -40px 0 24px;
		position: relative;
	}
	.sc-stats { display: flex; gap: 24px; }
	.sc-stat strong { display: block; font-size: 20px; color: #4f46e5; }
	.sc-stat span { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: .04em; }
	.sc-actions a {
		display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border: 1px solid #9db1c8;
    border-radius: 10px;
    background: #f9fbfe;
    color: #113253;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.18s ease;
	}
	

	.sc-nav {
		background: #fff;
		border-radius: 10px;
		box-shadow: 0 1px 3px rgba(0,0,0,.08);
		padding: 16px 20px;
		margin-bottom: 24px;
	}
	.sc-nav h3 { margin: 0 0 10px; font-size: 13px; text-transform: uppercase; color: #666; }
	.sc-nav a {
		display: inline-block;
		padding: 6px 12px;
		margin: 0 8px 8px 0;
		border-radius: 999px;
		background: #f1f2f6;
		color: #333;
		text-decoration: none;
		font-size: 13px;
	}
	.sc-nav a .badge { color: #4f46e5; font-weight: 700; margin-left: 4px; }

	.sc-site {
		background: #fff;
		border-radius: 10px;
		box-shadow: 0 1px 3px rgba(0,0,0,.08);
		margin-bottom: 20px;
		overflow: hidden;
		scroll-margin-top: 20px;
	}
	.sc-site-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 14px 20px;
		background: #f8f8fc;
		border-bottom: 1px solid #ececf5;
	}
	.sc-site-head a { color: #1d2327; text-decoration: none; font-weight: 600; }
	.sc-site-head a:hover { color: #4f46e5; }
	.sc-count { font-size: 12px; background: #eef0ff; color: #4f46e5; padding: 3px 10px; border-radius: 999px; font-weight: 600; }

	table { width: 100%; border-collapse: collapse; }
	th, td { text-align: left; padding: 10px 20px; border-bottom: 1px solid #f0f0f4; vertical-align: top; }
	th { background: #fafafd; font-size: 12px; text-transform: uppercase; color: #666; }
	td.sc-name { font-weight: 600; font-family: Consolas, monospace; width: 25%; color: #4f46e5; }
	.sc-status { font-size: 11px; text-transform: uppercase; color: #a00; display: block; margin-top: 4px; font-weight: 400; }
	.sc-raw {
		background: #f8f8fc;
		padding: 10px 12px;
		border: 1px solid #ececf5;
		border-radius: 6px;
		white-space: pre-wrap;
		word-break: break-word;
		font-family: Consolas, monospace;
		font-size: 12px;
	}
	.sc-empty { padding: 16px 20px; color: #777; font-style: italic; }
</style>
</head>
<body>

<div class="sc-header sc-container">
	<div class="sc-container" style="padding: 0;">
		<h1>Shortcoder Report</h1>
		<p>Every Shortcode name and raw content, across every site in this network.</p>
	</div>
</div>

<div class="sc-container">

	<div class="sc-toolbar">
		<div class="sc-stats">
			<div class="sc-stat">
				<strong><?php echo count( $report ); ?></strong>
				<span>Sites</span>
			</div>
			<div class="sc-stat">
				<strong><?php echo (int) $totalCount; ?></strong>
				<span>Shortcodes</span>
			</div>
		</div>
		<div class="sc-actions">
			<a class="sc-secondary" href="?download=csv">Download CSV</a>
			<a class="sc-secondary" href="?json=1">View JSON</a>
		</div>
	</div>

	<div class="sc-nav">
		<h3>Jump to site</h3>
		<?php foreach ( $report as $site ) : ?>
			<a href="#site-<?php echo (int) $site['blog_id']; ?>">
				<?php echo esc_html( $site['name'] ); ?><span class="badge">(<?php echo count( $site['entries'] ); ?>)</span>
			</a>
		<?php endforeach; ?>
	</div>

	<?php foreach ( $report as $site ) : ?>
		<div class="sc-site" id="site-<?php echo (int) $site['blog_id']; ?>">
			<div class="sc-site-head">
				<a href="<?php echo esc_url( $site['url'] ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( $site['name'] ); ?> &mdash; <?php echo esc_html( $site['url'] ); ?>
				</a>
				<span class="sc-count"><?php echo count( $site['entries'] ); ?> shortcode(s)</span>
			</div>

			<?php if ( empty( $site['entries'] ) ) : ?>
				<p class="sc-empty">No Shortcoder entries found on this site.</p>
			<?php else : ?>
				<table>
					<thead>
						<tr>
							<th>Shortcode Name</th>
							<th>Raw Content</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $site['entries'] as $entry ) : ?>
							<tr>
								<td class="sc-name">
									[sc name=&quot;<?php echo esc_html( $entry['name'] ); ?>&quot;]
									<?php if ( 'publish' !== $entry['status'] ) : ?>
										<span class="sc-status"><?php echo esc_html( $entry['status'] ); ?></span>
									<?php endif; ?>
								</td>
								<td>
									<div class="sc-raw"><?php echo esc_html( $entry['raw'] ); ?></div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

</div>

</body>
</html>
