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

		<div id="akamai-wrapper" class="wrap">
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

				<table id="akamai-creds-form-table" class="form-table">
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
							       class="regular-text code"
								   spellcheck="false"/>
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
							       class="regular-text code"
								   spellcheck="false"/>
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
							       class="regular-text code"
								   spellcheck="false"/>
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
								   spellcheck="false"
								   style="text-security: disc; -webkit-text-security: disc; -moz-text-security: disc;"/>
						</td>
					</tr>
					<tr>
						<th scope="row"></th>
						<td>
							<button id="verify-creds" type="button" class="button button-secondary" name="verify" disabled style="display: inline-block; vertical-align: middle;">Verify Credentials</button>
							<span id="verify-creds-spinner" class="spinner" style="float: none; margin-top: 0;"></span>
						</td>
					</tr>
					</tbody>
				</table>


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
							       class="short-text code"
								   spellcheck="false"/>
							<p class="description">This code is prepended to all cache tags (surrogate keys).
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
							<input type="url" id="<?= $this->plugin_name ?>-hostname"
							       name="<?= $this->plugin_name ?>[hostname]"
							       value="<?= $akamai->get_hostname( $options ) ?>"
								   class="regular-text"/>
							<p class="description">Public hostname for this site. <strong>Used only for purging by URL.</strong></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Debug mode: log errors', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?= $this->plugin_name ?>-log-errors-yes"
								   name="<?= $this->plugin_name ?>[log-errors]"
								   <?php checked( $akamai->get_opt( 'log-errors' ), '1' ); ?>
								   value="1">
							<label for="<?= $this->plugin_name; ?>-log-errors-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?= $this->plugin_name ?>-log-errors-no"
							       name="<?= $this->plugin_name ?>[log-errors]"
								   <?php checked( $akamai->get_opt( 'log-errors' ), '0' ); ?>
								   value="0">
							<label for="<?= $this->plugin_name ?>-log-errors-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description">Log all admin/settings/cache/purge errors, updates and events in <code>error_log()</code> as well as showing notices.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php _e( 'Debug mode: log purges', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?= $this->plugin_name ?>-log-purges-yes"
								   name="<?= $this->plugin_name ?>[log-purges]"
								   <?php checked( $akamai->get_opt( 'log-purges' ), '1' ); ?>
								   value="1">
							<label for="<?= $this->plugin_name; ?>-log-purges-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?= $this->plugin_name ?>-log-purges-no"
							       name="<?= $this->plugin_name ?>[log-purges]"
								   <?php checked( $akamai->get_opt( 'log-purges' ), '0' ); ?>
								   value="0">
							<label for="<?= $this->plugin_name ?>-log-purges-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description">Log successful purge events and responses in <code>error_log()</code> as well as showing notices.</p>
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
							<?php _e( 'Emit Cache Headers', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>-emit-cache-control-yes"
								   name="<?php echo $this->plugin_name; ?>[emit-cache-control]"
								   <?php checked( null, '1' ); ?>
								   value="1"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-emit-cache-control-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>-emit-cache-control-no"
							       name="<?php echo $this->plugin_name; ?>[emit-cache-control]"
								   <?php checked( '0', '0' ); ?>
								   value="0"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-emit-cache-control-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description" style="color: #A0A5AA">Uses the <code>Cache-Control: …</code> header.</p>
						</td>
					</tr>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<label for="<?= $this->plugin_name ?>-cache-default-headers">
								<?php _e( 'Default Cache Header', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?= $this->plugin_name ?>-cache-default-headers"
							       name="<?= $this->plugin_name ?>[cache-default-headers]"
							       value="<?= $akamai->get_opt( 'cache-default-headers' ) ?>"
								   class="regular-text code"
								   spellcheck="false"
								   disabled/>
							<p class="description" style="color: #A0A5AA">Default <code>Cache-Control: …</code> header to emit. This can be
							filtered according to the template being served.</p>
						</td>
					</tr>
					</tbody>
				</table>

				<h3><span style="color: #A0A5AA"><?php esc_attr_e( 'Cache Tags', 'wp_admin_style' ); ?></span></h3>
				<p style="color: #A0A5AA">Here you can set the specific types and the breadth of tags that are generated
				for a specific post, term or user. Currently we default to a maximal setting.</p>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<?php _e( 'Emit Cache Tags (Surrogate Keys)', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>-emit-cache-tags-yes"
								   name="<?php echo $this->plugin_name; ?>[emit-cache-tags]"
								   <?php checked( $this->akamai->get_opt( 'emit-cache-tags' ), '1' ); ?>
								   value="1"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-emit-cache-tags-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>-emit-cache-tags-no"
							       name="<?php echo $this->plugin_name; ?>[emit-cache-tags]"
								   <?php checked( $this->akamai->get_opt( 'emit-cache-tags' ), '0' ); ?>
								   value="0"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-emit-cache-tags-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description" style="color: #A0A5AA">Uses the <code>Edge-Cache-Tag: …</code>
							header. Will always include a site tag (for purging an entire site), and may include other
							default tags (for the template, or specific page, or other metadata as defined by the
							user.</p>
						</td>
					</tr>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<?php _e( 'Emit Tags for Related Objects', $this->plugin_name ); ?>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>-cache-related-tags-yes"
								   name="<?php echo $this->plugin_name; ?>[cache-related-tags]"
								   <?php checked( $this->akamai->get_opt( 'cache-related-tags' ), '1' ); ?>
								   value="1"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-cache-related-tags-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>-cache-related-tags-no"
							       name="<?php echo $this->plugin_name; ?>[cache-related-tags]"
								   <?php checked( $this->akamai->get_opt( 'cache-related-tags' ), '0' ); ?>
								   value="0"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-cache-related-tags-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description" style="color: #A0A5AA">Include tags for related objects (posts,
							terms, users/authors and archives or template pages) along with the displayed item. For a
							list of what these are, and how to add/remove related objects with filter hooks, see
							<a href="https://github.com/theplayerstribune/wp-akamai/wiki/Purging">the Wiki documentation</a></p>
						</td>
					</tr>
					</tbody>
				</table>

				<h1><span><?php esc_attr_e( 'Purge Options', 'wp_admin_style' ); ?></span></h1>
				<p>Here are the settings that control how objects are purged from the Akamai edge servers, and what
				objects are purged when the sites' contents is updated. You can also run emergency purges at the bottom.
				For information on these settings, see the
				<a href="https://developer.akamai.com/api/core_features/fast_purge/v3.html">Fast Purge API v3 documentation</a>.
				<span><em><strong>Note:</strong> some of these controls are disabled as they are no longer implemented or have yet to be
				implemented, and are left here as stubs.</em></span></p>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?= $this->plugin_name ?>-purge-network">
								<?php _e( 'Purge Network', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<select id="<?= $this->plugin_name ?>-purge-network" name="<?= $this->plugin_name ?>[purge-network]">
								<option <?php selected( $this->akamai->get_opt( 'purge-network' ), 'all' ); ?> value="all">Both (Production/Staging)</option>
								<option <?php selected( $this->akamai->get_opt( 'purge-network' ), 'staging' ); ?> value="staging">Staging only</option>
								<option <?php selected( $this->akamai->get_opt( 'purge-network' ), 'production' ); ?> value="production">Production only</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?= $this->plugin_name ?>-purge-type">
								<?php _e( 'Purge Type', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<select id="<?= $this->plugin_name ?>-purge-type" name="<?= $this->plugin_name ?>[purge-type]">
								<option <?php selected( $this->akamai->get_opt( 'purge-type' ), 'invalidate' ); ?> value="invalidate">Invalidate</option>
								<option <?php selected( $this->akamai->get_opt( 'purge-type' ), 'delete' ); ?> value="delete">Delete</option>
							</select>
							<p class="description">The type of purge to send. You should use <strong>invalidate</strong>.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?= $this->plugin_name ?>-purge-method">
								<?php _e( 'Purge Method', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<select id="<?= $this->plugin_name ?>-purge-method" name="<?= $this->plugin_name ?>[purge-method]">
								<option <?php selected( $this->akamai->get_opt( 'purge-method' ), 'tag' ); ?> value="tag">Use cache tags</option>
								<option <?php selected( $this->akamai->get_opt( 'purge-method' ), 'url' ); ?> value="url" disabled>Use URLs</option>
								<option <?php selected( $this->akamai->get_opt( 'purge-method' ), 'arl' ); ?> value="arl" disabled>Use ARLs (cache keys)</option>
								<option <?php selected( $this->akamai->get_opt( 'purge-method' ), 'cpcode' ); ?> value="cpcode" disabled>Use content provider (CP) code</option>
							</select>
							<p class="description">The approach used to purge objects.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-related">
								<?php _e( 'Purge Related Objects', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>-purge-related-yes"
								   name="<?php echo $this->plugin_name; ?>[purge-related]"
								   <?php checked( $this->akamai->get_opt( 'purge-related' ), '1' ); ?>
								   value="1">
							<label for="<?php echo $this->plugin_name; ?>-purge-related-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>-purge-related-no"
							       name="<?php echo $this->plugin_name; ?>[purge-related]"
								   <?php checked( $this->akamai->get_opt( 'purge-related' ), '0' ); ?>
								   value="0">
							<label for="<?php echo $this->plugin_name; ?>-purge-related-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description">Purge related objects (posts, terms, users/authors and archives or
							template pages) along with the updated item. For a list of what these are, and how to
							add/remove related objects with filter hooks, see
							<a href="https://github.com/theplayerstribune/wp-akamai/wiki/Purging">the Wiki documentation</a>.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-default">
								<?php _e( 'Purge Default Tags', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>-purge-default-yes"
								   name="<?php echo $this->plugin_name; ?>[purge-default]"
								   <?php checked( $this->akamai->get_opt( 'purge-default' ), '1' ); ?>
								   value="1">
							<label for="<?php echo $this->plugin_name; ?>-purge-default-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>-purge-default-no"
							       name="<?php echo $this->plugin_name; ?>[purge-default]"
								   <?php checked( $this->akamai->get_opt( 'purge-default' ), '0' ); ?>
								   value="0">
							<label for="<?php echo $this->plugin_name; ?>-purge-default-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description">Purge default cache tags representing often-used page templates or
							responses: these are of the "always should be purged" type, like the <code>404</code> page,
							home page, etc. For information on how to edit these with filter hooks, see
							<a href="https://github.com/theplayerstribune/wp-akamai/wiki/Purging">the Wiki documentation</a>.</p>
						</td>
					</tr>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<label for="<?php echo $this->plugin_name; ?>-purge-comments">
								<?php _e( 'Purge On Comment', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="radio" id="<?php echo $this->plugin_name; ?>-purge-comments-yes"
								   name="<?php echo $this->plugin_name; ?>[purge-on-comment]"
								   <?php checked( $this->akamai->get_opt( 'purge-on-comment' ), '1' ); ?>
								   value="1"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-purge-comments-yes"><?php _e( 'Yes', 'wp_admin_styles' ); ?></label>
							&nbsp;
        					<input type="radio" id="<?php echo $this->plugin_name; ?>-purge-comments-no"
							       name="<?php echo $this->plugin_name; ?>[purge-on-comment]"
								   <?php checked( $this->akamai->get_opt( 'purge-on-comment' ), '0' ); ?>
								   value="0"
								   disabled>
							<label for="<?php echo $this->plugin_name; ?>-purge-comments-no"><?php _e( 'No', 'wp_admin_styles' ); ?></label>
							<p class="description" style="color: #A0A5AA">Purge relevant objects when a successful comment is submitted.</p>
						</td>
					</tr>
					<!--
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-purge-tags">
								<?php _e( 'Purge Related Tag Archives', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo $this->plugin_name; ?>-purge-tags"
							       name="<?php echo $this->plugin_name; ?>[purge_tags]"
							       value="1" <?php checked( $this->akamai->get_opt( 'purge-tags' ) ); ?>"/>

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
							       value="1" <?php checked( $this->akamai->get_opt( 'purge-categories' ) ); ?>"/>

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
							       value="1" <?php checked( $this->akamai->get_opt( 'purge-archives' ) ); ?>"/>

							<label for="<?php echo $this->plugin_name; ?>-purge-archives">
								<span class="description">Purge archive pages associated with the post</span>
							</label>
						</td>
					</tr>
					-->
					</tbody>
				</table>

				<h1><span style="color: #A0A5AA"><?php esc_attr_e( 'Purge Requests', 'wp_admin_style' ); ?></span></h1>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<label for="<?php echo $this->plugin_name; ?>-purge-url">
								<?php _e( 'Purge By URL', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
        					<input type="url" id="<?php echo $this->plugin_name; ?>-purge-url"
								   placeholder="https://example.com/test"
								   value=""
								   class="regular-text"
								   disabled>
        					<button id="<?php echo $this->plugin_name; ?>-purge-url-btn" class="button button-secondary" disabled>Purge</button>
							<p class="description" style="color: #A0A5AA">Paste the URL you want to purge and click the Send Purge Request URL button</p>
        				</td>
					</tr>
					<tr>
						<th scope="row" style="color: #A0A5AA">
							<label for="<?php echo $this->plugin_name; ?>-purge-url">
								<?php _e( 'Purge All', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
        					<button id="<?php echo $this->plugin_name; ?>-purge-all-btn" class="button button-warning" disabled style="color: #dc3232; border-color: #dc3232">Send Purge All Request</button>
							<p class="description" style="color: #A0A5AA"><em><strong>Warning!</strong> This could cause major disruptions to your site.</em></p>
        				</td>
					</tr>
					</tbody>
				</table>

				<?php submit_button( 'Save settings', 'primary', 'submit', false ); ?>
			</form>
		</div>
	</div>
</div>
