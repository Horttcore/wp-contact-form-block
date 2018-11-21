<?php
namespace Horttcore\ContactFormBlock;

class ContactForm
{


	/**
	 * Form has error
	 *
	 * @var bool
	 */
	protected $error = FALSE;


	/**
	 * Form has error
	 *
	 * @var bool
	 */
	protected $errors = [];


	/**
	 * E-Mail
	 *
	 * @var string
	 */
	protected $fieldEMail = '';


	/**
	 * E-Mail
	 *
	 * @var string
	 */
	protected $fieldMessage = '';


	/**
	 * Name
	 *
	 * @var string
	 */
	protected $fieldName = '';


	/**
	 * E-Mail
	 *
	 * @var string
	 */
	protected $fieldPhone = '';


	/**
	 * From
	 *
	 * @var string
	 */
	protected $from;


	/**
	 * E-Mail from name
	 *
	 * @var string
	 */
	protected $fromName;


	/**
	 * E-Mail sendto
	 *
	 * @var string
	 */
	protected $mailTo;


	/**
	 * E-Mail subject
	 *
	 * @var string
	 */
	protected $subject;


	/**
	 * Form has error
	 *
	 * @var bool
	 */
	protected $submit = FALSE;


	/**
	 * Success message
	 *
	 * @var string
	 */
	protected $success = FALSE;


	/**
	 * Setup contact form
	 *
	 * @return void
	 */
	public function __construct()
	{

		$this->from = get_option( 'admin_email' );
		$this->fromName = get_bloginfo( 'name' );
		$this->mailTo = get_option( 'admin_email' );
		$this->subject = sprintf( __( '[%s] Kontaktformular', 'schweissthal-blocks' ), get_bloginfo( 'name' ) );
		$this->success = __( 'Vielen Dank für Ihre Nachricht', 'schweissthal-blocks' );

	}


	/**
	 * Get $_POST value
	 *
	 * @param string $key $_POST Key
	 * @return mixed Get $_POST[$key]
	 */
	public function getPost( $key, $filter = NULL )
	{
		return filter_input( INPUT_POST, $key, $filter );

	}


	/**
	 * Check if field has error
	 *
	 * @param string $field Field name
	 * @return bool
	 */
	public function hasError( $field )
	{
		return ( in_array( $field, $this->errors ) );

	}


	/**
	 * Check for required fields
	 *
	 * @return bool
	 */
	public function isValid()
	{

		if ( $this->getPost( 'honeypot' ) ) :
			$this->errors[] = __( 'Honeypot not empty', 'schweissthal-blocks' );
			return FALSE;
		endif;

		if ( md5( $this->getPost( 'timestamp' ) . '-' . NONCE_SALT ) != $this->getPost( 'hash' ) ) :
			$this->errors[] = __( 'Hash not valid', 'schweissthal-blocks' );
			return FALSE;
		endif;

		if ( $this->getPost( 'timestamp' ) + 5 > time() ) :
			$this->errors[] = __( 'Too fast', 'schweissthal-blocks' );
			return FALSE;
		endif;


		$required = [
			'contact-name',
			'contact-email',
			'contact-message',
		];

		foreach ( $required as $r ) :

			if ( '' != $this->getPost($r) )
				continue;

			$this->error = TRUE;
			$this->errors[] = $r;
			$this->submit = FALSE;

		endforeach;

		$this->fieldEMail = sanitize_email( $this->getPost( 'contact-email') );
		$this->fieldName = sanitize_text_field( $this->getPost( 'contact-name' ) );
		$this->fieldMessage = wp_kses_post( $this->getPost( 'contact-message' ) );
		$this->fieldPhone = sanitize_text_field( $this->getPost( 'contact-phone' ) );

		return !$this->error;

	} // END isValid


	/**
	 * Render shortcode
	 *
	 * @return string HTML output
	 */
	public function render()
	{

		ob_start();

		if ( wp_verify_nonce( $this->getPost('contact-form-nonce'), 'submit-contact-form' ) && $this->isValid() && $this->sendMail() ) :
			?>
			<div class="response success">
				<p>
					<?php echo $this->success ?>
				</p>
			</div>
			<?php
		endif;

		if ( $this->error || !$this->submit )
			$this->renderForm();

		return ob_get_clean();

	}


	/**
	 * Shortcode render
	 *
	 * @return void
	 **/
	public function renderForm()
	{
		?>

		<form class="contact-form" method="post">

			<p>
				<label class="form-label form-label--input form-label--required" for="contact-name"><?php _e( 'Name', 'schweissthal-blocks' ) ?></label>
				<input required type="text" name="contact-name" id="contact-name" value="<?php echo esc_attr( $this->fieldName ) ?>">
				<?php if ( $this->hasError( 'contact-name' ) ) : ?><span class="input-error"><?php _e( 'Dies ist ein Pflichtfeld', 'schweissthal-blocks' ) ?></span><?php endif; ?>
			</p>

			<p>
				<label class="form-label form-label--input form-label--required" for="contact-email"><?php _e( 'E-Mail', 'schweissthal-blocks' ) ?></label>
				<input required type="email" name="contact-email" id="contact-email" value="<?php echo esc_attr( $this->fieldEMail ) ?>">
				<?php if ( $this->hasError( 'contact-email' ) ) : ?><span class="input-error"><?php _e( 'Dies ist ein Pflichtfeld', 'schweissthal-blocks' ) ?></span><?php endif; ?>
			</p>

			<p>
				<label class="form-label form-label--input" for="contact-phone"><?php _e( 'Telefon', 'schweissthal-blocks' ) ?></label>
				<input type="text" name="contact-phone" id="contact-phone" value="<?php echo esc_attr( $this->fieldPhone ) ?>">
			</p>

			<p>
				<label class="form-label form-label--input form-label--required" for="contact-message"><?php _e( 'Nachricht', 'schweissthal-blocks' ) ?></label>
				<textarea required rows="8" name="contact-message" id="contact-message"><?php echo wp_kses_post( $this->fieldMessage ) ?></textarea>
				<?php if ( $this->hasError( 'contact-message' ) ) : ?><span class="input-error"><?php _e( 'Dies ist ein Pflichtfeld', 'schweissthal-blocks' ) ?></span><?php endif; ?>
			</p>

			<p class="form-submit">
				<small><?php _e( '* Pflichtfeld', 'schweissthal-blocks' ) ?></small>
				<button class="button button--primary" type="submit"><?php _e( 'Absenden', 'schweissthal-blocks' ) ?></button>
			</p>

			<input type="hidden" name="timestamp" value="<?php echo time() ?>">
			<input type="hidden" name="hash" value="<?php echo md5( time() . '-' . NONCE_SALT ) ?>">
			<input style="display: none" type="text" name="honeypot" />

			<?php wp_nonce_field( 'submit-contact-form', 'contact-form-nonce' ) ?>

		</form>

		<?php

	} // END renderForm


	/**
	 * Send form
	 *
	 * @return bool
	 */
	public function sendMail()
	{

		if ( TRUE === $this->submit )
			return;

		$headers = "FROM: " . $this->fromName . " <" . $this->from . ">\n";
		$headers.= "REPLY-TO: " . $this->fieldName . " <" . $this->fieldEMail . ">\n";

		$text = "Name: " .  $this->fieldName . "\n";
		$text .= "E-Mail: " .  $this->fieldEMail . "\n";
		$text .= "Telefon: " .  $this->fieldPhone . "\n";
		$text .= "\n";
		$text .= $this->fieldMessage;

		$this->submit = TRUE;

		return wp_mail( $this->mailTo, $this->subject, $text, $headers );

	} // END sendMail


} // END class ContactForm
