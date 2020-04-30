<?php
$akamai = new Akamai();
$options = get_option( $this->plugin_name );
?>

<div class="akamai-content">
	<div class="akamai-frame">
		<header>
			<h1>Akamai for WordPress</h1>
		</header>

		<?php $this->is_post_update() ? null : settings_errors(); ?>

		<div class="wrap">
			<form method="post" name="cleanup_options" action="options.php" autocomplete="off">
				<h1><span><?php esc_attr_e( 'API Credentials', 'wp_admin_style' ); ?></span></h1>
				<p>The below credentials can be retrieved either directly from the Akamai Identity and Access
				Management (IAM) settings console, or from the <code>.edgerc</code> files it generates. When entering
				fields from an <code>.edgerc</code> file, be sure to use the information in the section of the file
				(usually <code>[default]</code>) that gives the necessary permissions to the
				<a href="https://developer.akamai.com/api/core_features/fast_purge/v3.html">Fast Purge API</a>
				(formerly known as Content Control Utility or <strong>CCU API</strong>).</p>

				<?php settings_fields( $this->plugin_name ); ?>
				<?php do_settings_sections( $this->plugin_name ); ?>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-credentials-host">
								<?php _e( 'Host', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo $this->plugin_name; ?>-credentials-host"
							       name="<?php echo $this->plugin_name; ?>[credentials][host]"
							       value="<?= $akamai->get_cred( 'host' ) ?>"
							       class="regular-text code"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-credentials-access-token">
								<?php _e( 'Access Token', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo $this->plugin_name; ?>-credentials-access-token"
							       name="<?php echo $this->plugin_name; ?>[credentials][access-token]"
							       value="<?= $akamai->get_cred( 'access-token' ) ?>"
							       class="regular-text code"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-credentials-client-token">
								<?php _e( 'Client Token', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo $this->plugin_name; ?>-credentials-client-token"
							       name="<?php echo $this->plugin_name; ?>[credentials][client-token]"
							       value="<?= $akamai->get_cred( 'client-token' ) ?>"
							       class="regular-text code"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-credentials-client-secret">
								<?php _e( 'Client Secret', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo $this->plugin_name; ?>-credentials-client-secret"
							       name="<?php echo $this->plugin_name; ?>[credentials][client-secret]"
							       value="<?= $akamai->get_cred( 'client-secret' ) ?>"
							       class="regular-text code"
								   style="text-security: disc; -webkit-text-security: disc; -moz-text-security: disc;"/>
						</td>
					</tr>
					</tbody>
				</table>

				<p><button id="verify" type="button" class="button" name="verify" disabled>Verify Credentials</button></p>

				<h1><span><?php esc_attr_e( 'General Settings', 'wp_admin_style' ); ?></span></h1>
				<p>Installation-wide settings that control how the plugin works. The site code is necessary, to
				differentiate this "property" in Akamai since purge requests are made against all properties in the
				account.</p>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?= $this->plugin_name; ?>-unique-sitecode">
								<?php _e( 'Unique Site Code', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?= $this->plugin_name; ?>-unique-sitecode"
							       name="<?= $this->plugin_name; ?>[unique-sitecode]"
							       value="<?= $akamai->get_opt( 'unique-sitecode' ) ?>"
							       class="short-text code"/>
							<p class="description">This code is prepended to all cache tags / surrogate keys.
							Multisite will <em>also</em> include a specific site / blog ID.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?= $this->plugin_name ?>-hostname">
								<?php _e( 'Public Hostname', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?= $this->plugin_name ?>-hostname"
							       name="<?= $this->plugin_name ?>[hostname]"
							       value="<?= $akamai->get_hostname( $options ) ?>"/>
							<br>
							<p class="description">Public hostname for this site.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Debug mode', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?= $this->plugin_name ?>-debug-mode-yes"
								   name="<?= $this->plugin_name ?>[debug-mode]"
								   <?php checked( $akamai->get_opt( 'debug-mode' ), '1' ); ?>
								   value="1">
							<label for="<?= $this->plugin_name; ?>-debug-mode-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?= $this->plugin_name ?>-debug-mode-no"
							       name="<?= $this->plugin_name ?>[debug-mode]"
								   <?php checked( $akamai->get_opt( 'debug-mode' ), '0' ); ?>
								   value="0">
							<label for="<?= $this->plugin_name ?>-debug-mode-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description">Log all admin/settings errors, updates and events in <code>error_log()</code> as well as showing notices.</p>
						</td>
					</tr>
					</tbody>
				</table>

				<h1><span><?php esc_attr_e( 'Cache Options', 'wp_admin_style' ); ?></span> <em style="color: #A0A5AA">(Not Implemented)</em></h1>
				<p style="color: #A0A5AA">Many of these can be set in the Akamai CDN property manager, but the behaviors can also pass
				thru origin headers or even set their rules based on origin headers.</p>

				<h3><span style="color: #A0A5AA"><?php esc_attr_e( 'General Cache Options', 'wp_admin_style' ); ?></span></h3>
				<p style="color: #A0A5AA">These settings apply to how the plugin handles caching, revalidation, errors
				and serving stale content; TTLs; and what cache information is emitted (if any) from the front end of
				the site.</p>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<?php _e( 'Emit Caching Header', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>emit-cache-control-yes"
								   name="<?php echo $this->plugin_name; ?>[emit-cache-control]"
								   <?php checked( null, '1' ); ?>
								   value="1"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>emit-cache-control-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>emit-cache-control-no"
							       name="<?php echo $this->plugin_name; ?>[emit-cache-control]"
								   <?php checked( '0', '0' ); ?>
								   value="0"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>emit-cache-control-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description" style="color: #A0A5AA">Uses the <code>Cache-Control: …</code> header.</p>
						</td>
					</tr>
					</tbody>
				</table>

				<h3><span style="color: #A0A5AA"><?php esc_attr_e( 'Cache Tags', 'wp_admin_style' ); ?></span></h3>
				<p class="description" style="color: #A0A5AA">Here you can set the specific types and the breadth of tags that are generated
				for a specific post, term or user. Currently we default to a maximal setting.</p>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<?php _e( 'Emit Cache Tags (Surrogate Keys)', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>emit-cache-tags-yes"
								   name="<?php echo $this->plugin_name; ?>[emit-cache-tags]"
								   <?php checked( '1', '1' ); ?>
								   value="1"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>emit-cache-tags-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>emit-cache-tags-no"
							       name="<?php echo $this->plugin_name; ?>[emit-cache-tags]"
								   <?php checked( null, '0' ); ?>
								   value="0"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>emit-cache-tags-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description" style="color: #A0A5AA">Uses the <code>Edge-Cache-Tag: …</code> header.</p>
						</td>
					</tr>
					</tbody>
				</table>

				<h1><span><?php esc_attr_e( 'Purge Options', 'wp_admin_style' ); ?></span></h1>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-comments">
								<?php _e( 'Purge On Comment', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo $this->plugin_name; ?>-purge-comments"
							       name="<?php echo $this->plugin_name; ?>[purge_comments]"
							       value="1" <?php checked( $options['purge_comments'] ); ?>"/>

							<label for="<?php echo $this->plugin_name; ?>-purge-comments">
								<span class="description">Purge relevant content when a successful comment is submitted</span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-tags">
								<?php _e( 'Purge Related Tag Archives', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo $this->plugin_name; ?>-purge-tags"
							       name="<?php echo $this->plugin_name; ?>[purge_tags]"
							       value="1" <?php checked( $options['purge_tags'] ); ?>"/>

							<label for="<?php echo $this->plugin_name; ?>-purge-tags">
								<span class="description">Purge archive pages for tags associated with the post</span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-categories">
								<?php _e( 'Purge Related Category Archives', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo $this->plugin_name; ?>-purge-categories"
							       name="<?php echo $this->plugin_name; ?>[purge_categories]"
							       value="1" <?php checked( $options['purge_categories'] ); ?>"/>

							<label for="<?php echo $this->plugin_name; ?>-purge-categories">
								<span
									class="description">Purge archive pages for categories associated with the post</span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-archives">
								<?php _e( 'Purge Related Archives', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo $this->plugin_name; ?>-purge-archives"
							       name="<?php echo $this->plugin_name; ?>[purge_archives]"
							       value="1" <?php checked( $options['purge_archives'] ); ?>"/>

							<label for="<?php echo $this->plugin_name; ?>-purge-archives">
								<span class="description">Purge archive pages associated with the post</span>
							</label>
						</td>
					</tr>
					</tbody>
				</table>
				<?php submit_button( 'Save settings', 'primary', 'submit', false ); ?>
			</form>
		</div>
	</div>
</div>
