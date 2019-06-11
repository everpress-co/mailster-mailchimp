<?php


require_once $this->plugin_path . 'classes/merlin/class-merlin.php';

class MailsterMerlin extends Merlin {


	function __construct( $config = array(), $strings = array() ) {

		parent::__construct( $config, $strings );

		$this->slug = 'mailster_mailchimp';
		$this->plugin_path = $config['plugin_path'];
		$this->plugin_url = $config['plugin_url'];

	}


	public function steps() {

		unset( $this->steps['child'] );
		unset( $this->steps['child'] );

		$this->steps = array(
			'welcome' => array(
				'name'    => esc_html__( 'Welcome', 'mailster-mailchimp' ),
				'view'    => array( $this, 'welcome' ),
				'handler' => array( $this, 'welcome_handler' ),
			),
		);

		$this->steps['apikey'] = array(
			'name' => esc_html__( 'APIKey', 'mailster-mailchimp' ),
			'view' => array( $this, 'apikey' ),
		);

		$this->steps['lists'] = array(
			'name' => esc_html__( 'SelectLists', 'mailster-mailchimp' ),
			'view' => array( $this, 'lists' ),
		);

		$this->steps['ready'] = array(
			'name' => esc_html__( 'Ready', 'mailster-mailchimp' ),
			'view' => array( $this, 'ready' ),
		);

	}

	protected function footer() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$suffix = '';
		wp_enqueue_style( 'mailster-mailchimp', $this->plugin_url . 'assets/css/style' . $suffix . '.css', array(), MAILSTER_MAILCHIMP_VERSION );
		wp_enqueue_script( 'mailster-mailchimp', $this->plugin_url . 'assets/js/script' . $suffix . '.js', array( 'jquery' ), MAILSTER_MAILCHIMP_VERSION );

		parent::footer();
	}

	protected function apikey() {

		$strings = $this->strings;

		$start     = $strings['btn-start'];
		$no        = $strings['btn-no'];
		$next      = $strings['btn-next'];
		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'welcome' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php esc_html_e( 'Please enter your API Key', 'mailster-mailchimp' ) ?></h1>
			<p id="apikey-info-text"><?php esc_html_e( 'You need your API key from Mailchimp to import your data.', 'mailster-mailchimp' ) ?> <a href="https://admin.mailchimp.com/account/api-key-popup/" onclick="window.open(this.href, 'mailster-mailchimp', 'width=600,height=600');return false;"><strong><?php esc_html_e( 'Click here to get it.', 'mailster-mailchimp' ) ?></strong></a></p>

			<label><input type="text" id="api-key" class="widefat code" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" autofocus tabindex="1" placeholder="12345678901234567890123456789012-xx1"></label>

		</div>

		<footer class="merlin__content__footer">
			<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="verify_api_key">
				<span class="merlin__button--loading__text"><?php echo esc_html( $next ); ?></span>
				<?php echo wp_kses( $this->loading_spinner(), $this->loading_spinner_allowed_html() ); ?>
			</a>
			<?php wp_nonce_field( 'merlin' ); ?>
		</footer>

	<?php
		$this->logger->debug( __( 'The welcome step has been displayed', 'mailster-mailchimp' ) );
	}

	protected function lists() {

		$strings = $this->strings;

		// Text strings.
		$header    = $strings['license-header%s'];
		$action    = $strings['license-tooltip'];
		$label     = $strings['license-label'];
		$skip      = $strings['btn-license-skip'];
		$next      = $strings['btn-next'];

		$count = 3;

		?>

		<div class="merlin__content--transition">

			<?php echo wp_kses( $this->svg( array( 'icon' => 'plugins' ) ), $this->svg_allowed_html() ); ?>

			<svg class="icon icon--checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
				<circle class="icon--checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="icon--checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
			</svg>

			<h1><?php esc_html_e( 'Please select Lists to import', 'mailster-mailchimp' ) ?></h1>

			<p><?php esc_html_e( 'This will create Lists, import their members and set the status.', 'mailster-mailchimp' ) ?></p>

		</div>

		<form action="" method="post">

			<ul class="merlin__drawer--install-plugins mailchimp-lists">
				<li class="hidden">
					<input type="checkbox" name="lists[]" class="checkbox" value="" id="">

					<label for="">
						<i></i>

						<span></span>

					</label>
				</li>
			</ul>

			<a id="merlin__drawer-trigger" class="merlin__button merlin__button--knockout"><span><?php esc_html_e( 'Options', 'mailster-mailchimp' ) ?></span><span class="chevron"></span></a>

			<ul class="merlin__drawer merlin__drawer--install-plugins">

				<li>
					<input type="checkbox" name="options" class="checkbox" value="subscribed" checked id="import_subscribed">

					<label for="import_subscribed">
						<i></i>

						<span><?php esc_html_e( 'Import with status "subscribed"', 'mailster-mailchimp' ) ?></span>

					</label>
				</li>

				<li>
					<input type="checkbox" name="options" class="checkbox" value="pending" id="import_pending">

					<label for="import_pending">
						<i></i>

						<span><?php esc_html_e( 'Import with status "pending"', 'mailster-mailchimp' ) ?></span>

					</label>
				</li>
				<li>
					<input type="checkbox" name="options" class="checkbox" value="unsubscribed" id="import_unsubscribed">

					<label for="import_unsubscribed">
						<i></i>

						<span><?php esc_html_e( 'Import with status "unsubscribed"', 'mailster-mailchimp' ) ?></span>

					</label>
				</li>
				<li>
					<input type="checkbox" name="options" class="checkbox" value="cleaned" id="import_cleaned">

					<label for="import_cleaned">
						<i></i>

						<span><?php esc_html_e( 'Import with status "cleaned"', 'mailster-mailchimp' ) ?></span>

					</label>
				</li>

			</ul>


			<footer class="merlin__content__footer">
					<a href="<?php echo esc_url( $this->step_next_link() ); ?>" class="merlin__button merlin__button--next button-next" data-callback="import_lists">
						<span class="merlin__button--loading__text"><?php esc_html_e( 'Import', 'mailster-mailchimp' ) ?></span>
						<?php echo wp_kses( $this->loading_spinner(), $this->loading_spinner_allowed_html() ); ?>
					</a>
				<?php wp_nonce_field( 'merlin' ); ?>
			</footer>
		</form>
		<?php
		$this->logger->debug( __( 'The license activation step has been displayed', 'mailster-mailchimp' ) );
	}

}

