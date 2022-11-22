<?php
//Grab all options
$options = get_option( $this->plugin_name );
?>

<div class="akamai-content">
	<div class="akamai-frame">
		<header>
			<h1><?php esc_html_e( 'Akamai for WordPress', 'akamai' ); ?></h1>
		</header>

		<?php settings_errors(); ?>

		<div class="wrap">
			<form method="post" name="cleanup_options" action="options.php">
				<h1><span><?php esc_html_e( 'API Credentials', 'akamai' ); ?></span></h1>

				<?php settings_fields( $this->plugin_name ); ?>
				<?php do_settings_sections( $this->plugin_name ); ?>

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-edgerc">
								<?php _e( '<code>.edgerc</code> file location', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo esc_attr( $this->plugin_name ); ?>-edgerc"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[edgerc]" class="regular-text"
									value="<?= ( isset( $options['edgerc'] ) ) ? esc_attr( $options['edgerc'] ) : ''; ?>"/>
							<br>
							<?php
							$paths = array();
							if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
								$paths[] = $_SERVER['DOCUMENT_ROOT'];
							}
							$paths[] = __( 'the current working directory', 'akamai' );
							?>
							<span class="description"><?php esc_html_e( 'By default, we\'ll look in ', 'akamai' );
							echo implode( __( ' and ', 'akamai' ), $paths );
							?>.</span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-section">
								<?php _e( '<code>.edgerc</code> section', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<input type="text" id="<?php echo esc_attr( $this->plugin_name ); ?>-section"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[section]"
									value="<?php echo esc_attr( ( isset( $options['section'] ) ) ? esc_attr( $options['section'] ) : 'default' ); ?>"
									class="regular-text"/>
							<br>
							<span class="description"><?php printf(
								'%s <b>%s</b>.',
								esc_html( 'The credentials must have access to the', 'akamai' ),
								esc_html( 'CCU APIs', 'akamai' )
							); ?></span>
						</td>
					</tr>
					</tbody>
				</table>
				<h2 class="title" id="how-to-obtain-credentials"><?php esc_attr_e( 'How to Obtain Credentials', 'akamai' ); ?></h2>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
						</th>
						<td>
							<ol class="how-to">
								<li><?php printf( 
									'%s <b>%s</b>.',
									esc_html__( 'Login to', 'akamai' ),
									esc_html__( 'Akamai Control Center', 'akamai' )
								); ?></li>
								<li><?php printf(
									'%s <b>%s</b> → <b>%s</b>.',
									esc_html__( 'In the main menu, find', 'akamai' ),
									esc_html__( 'Account Admin', 'akamai' ),
									esc_html__( 'Identity and access', 'akamai' )
								); ?></li>
								<li><?php printf(
									'%s <b>%s</b> %s <b>%s</b>.',
									esc_html__( 'Choose', 'akamai' ),
									esc_html__( 'Create API Client', 'akamai' ),
									esc_html__( 'under', 'akamai' ),
									esc_html__( 'Users and API Clients', 'akamai' )
								); ?></li>
								<li><?php printf(
									'%s <b>%s</b> %s <b>%s</b> %s <b>%s</b>.',
									esc_html__( 'Find the' ),
									esc_html__( 'Select APIs' ),
									esc_html__( 'button, and allow' ),
									esc_html__( 'READ-WRITE', 'akamai' ),
									esc_html__( 'for', 'akamai' ),
									esc_html__( 'CCU APIs', 'akamai' )
								); ?></li>
								<li><?php printf(
									'%s <b>%s</b> %s.',
									esc_html__( 'No selection needed under the', 'akamai' ),
									esc_html__( 'Manage purge options', 'akamai' ),
									esc_html__( 'button', 'akamai' )
								); ?></li>
							</ol>
							<p class="description"><?php esc_html_e( 'If you need help providing values for other fields like role/group, contact your Akamai account representative or consult', 'akamai' ); ?> <a href="https://techdocs.akamai.com/developer/docs/set-up-authentication-credentials">https://techdocs.akamai.com/developer/docs/set-up-authentication-credentials</a>.</p>
						</td>
					</tr>
					</tbody>
				</table>
				<h1><span><?php esc_attr_e( 'Purge Options', 'akamai' ); ?></span></h1>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-hostname">
								<?php esc_html_e( 'Public Hostname', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<?php $akamai = new Akamai(); ?>
							<input type="text" id="<?php echo esc_attr( $this->plugin_name ); ?>-hostname"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[hostname]"
									value="<?php echo esc_attr( $akamai->get_hostname( $options ) ); ?>"/>
							<br>
							<span class="description"><?php esc_html_e( 'Public hostname for this site', 'akamai' ); ?></span>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-comments">
								<?php esc_html_e( 'Purge On Comment', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo esc_attr( $this->plugin_name ); ?>-purge-comments"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[purge_comments]"
									value="1" <?php checked( $options['purge_comments'] ?? false ); ?>"/>

							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-comments">
								<span class="description"><?php esc_html_e( 'Purge relevant content when a successful comment is submitted', 'akamai' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-tags">
								<?php esc_html_e( 'Purge Related Tag Archives', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo esc_attr( $this->plugin_name ); ?>-purge-tags"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[purge_tags]"
									value="1" <?php checked( $options['purge_tags'] ?? false ); ?>"/>

							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-tags">
								<span class="description"><?php esc_html_e( 'Purge archive pages for tags associated with the post', 'akamai' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-categories">
								<?php esc_html_e( 'Purge Related Category Archives', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo esc_attr( $this->plugin_name ); ?>-purge-categories"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[purge_categories]"
									value="1" <?php checked( $options['purge_categories'] ?? false ); ?>"/>

							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-categories">
								<span
									class="description"><?php esc_html_e( 'Purge archive pages for categories associated with the post', 'akamai' ); ?></span>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-archives">
								<?php esc_html_e( 'Purge Related Archives', 'akamai' ); ?>
							</label>
						</th>
						<td>
							<input type="checkbox" id="<?php echo esc_attr( $this->plugin_name ); ?>-purge-archives"
									name="<?php echo esc_attr( $this->plugin_name ); ?>[purge_archives]"
									value="1" <?php checked( $options['purge_archives'] ?? false ); ?>"/>

							<label for="<?php echo esc_attr( $this->plugin_name ); ?>-purge-archives">
								<span class="description"><?php esc_html_e( 'Purge archive pages associated with the post', 'akamai' ); ?></span>
							</label>
						</td>
					</tr>
					</tbody>
				</table>
				<p class="submit">
					<button id="verify" type="button" class="button" name="verify"><?php esc_html_e( 'Verify Credentials', 'akamai' ); ?></button>
					<?php submit_button( 'Save settings', 'primary', 'submit', false ); ?>
				</p>
			</form>
		</div>
	</div>
</div>
