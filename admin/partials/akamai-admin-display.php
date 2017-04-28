<?php
//Grab all options
$options = get_option( $this->plugin_name );
?>

<div class="akamai-content">
	<div class="akamai-frame">
		<header>
			<h1>Akamai for WordPress</h1>
		</header>

		<?php settings_errors(); ?>

		<div class="wrap">
			<form method="post" name="cleanup_options" action="options.php">
				<h1><span><?php esc_attr_e( 'API Credentials', 'wp_admin_style' ); ?></span></h1>

				<?php settings_fields( $this->plugin_name ); ?>
				<?php do_settings_sections( $this->plugin_name ); ?>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-edgerc">
								<?php _e( '<code>.edgerc</code> file location', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo $this->plugin_name; ?>-edgerc"
							       name="<?php echo $this->plugin_name; ?>[edgerc]" class="regular-text"
							       value="<?= ( isset( $options['edgerc'] ) ) ? esc_attr( $options['edgerc'] ) : ''; ?>"/>
							<br>
							<?php
							$paths = array();
							if ( isset( $_SERVER['HOME'] ) ) {
								$paths[] = $_SERVER['HOME'];
							}
							$paths[] = 'the current working directory';
							?>
							<span class="description">By default, we'll look in <?= implode( ' and ', $paths ); ?>.</span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-section">
								<?php _e( '<code>.edgerc</code> section', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo $this->plugin_name; ?>-section"
							       name="<?php echo $this->plugin_name; ?>[section]"
							       value="<?= ( isset( $options['edgerc'] ) ) ? esc_attr( $options['section'] ) : 'default'; ?>"
							       class="regular-text"/>
							<br>
							<span class="description">The credentials must have access to the CCU API.</span>
						</td>
					</tr>
					</tbody>
				</table>
				<h1><span><?php esc_attr_e( 'Purge Options', 'wp_admin_style' ); ?></span></h1>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo $this->plugin_name; ?>-hostname">
								<?php _e( 'Public Hostname', $this->plugin_name ); ?>
							</label>
						</th>
						<td>
							<?php $akamai = new Akamai(); ?>
							<input type="text" id="<?php echo $this->plugin_name; ?>-hostname"
							       name="<?php echo $this->plugin_name; ?>[hostname]"
							       value="<?php echo $akamai->get_hostname($options); ?>"/>
							<br>
							<span class="description">Public hostname for this site</span>
						</td>
					</tr>
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
				<button id="verify" type="button" class="button" name="verify">Verify Credentials</button>
				<?php submit_button( 'Save settings', 'primary', 'submit', false ); ?>
			</form>
		</div>
	</div>
</div>
