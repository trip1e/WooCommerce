<?php
/**
 * Class Country
 *
 * @package Packetery\Options
 */

declare( strict_types=1 );

namespace Packetery\Options;

use Packetery\Carrier\Repository;
use Packetery\Form_Factory;

/**
 * Class Country
 *
 * @package Packetery\Options
 */
class Country {

	/**
	 * Latte_engine.
	 *
	 * @var \Latte\Engine Latte engine.
	 */
	private $latte_engine;

	/**
	 * Carrier repository.
	 *
	 * @var Repository Carrier repository.
	 */
	private $carrier_repository;

	/**
	 * Form factory.
	 *
	 * @var Form_Factory Form factory.
	 */
	private $form_factory;

	/**
	 * Plugin constructor.
	 *
	 * @param \Latte\Engine $latte_engine Latte_engine.
	 * @param Repository    $carrier_repository Carrier repository.
	 * @param Form_Factory  $form_factory Form factory.
	 */
	public function __construct( \Latte\Engine $latte_engine, Repository $carrier_repository, Form_Factory $form_factory ) {
		$this->latte_engine       = $latte_engine;
		$this->carrier_repository = $carrier_repository;
		$this->form_factory       = $form_factory;
	}

	/**
	 * Registers WP callbacks.
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_submenu_page(
			'packeta-options',
			__( 'Carrier settings', 'packetery' ),
			__( 'Carrier settings', 'packetery' ),
			'manage_options',
			'packeta-country',
			array(
				$this,
				'render',
			),
			10
		);
	}

	/**
	 * Creates settings form.
	 *
	 * @param array $carrier_data Country data.
	 *
	 * @return \Nette\Forms\Form
	 */
	private function create_form( array $carrier_data ): \Nette\Forms\Form {
		$optionId = 'packetery_carrier_' . $carrier_data['id'];
		$form = $this->form_factory->create( $optionId );
		$form->setAction( 'options.php' );

		$container = $form->addContainer( $optionId );

		$container->addCheckbox(
			'active',
			__( 'Active carrier', 'packetery' )
		);

		$container->addText( 'name', __( 'Display name', 'packetery' ) )
					->setRequired()
					->addRule( $form::MIN_LENGTH, __( 'Carrier display name must have at least 2 characters!', 'packetery' ), 2 );

		$weight_limits = $container->addContainer( 'weight_limits' );
		$wl0           = $weight_limits->addContainer( '0' );
		$wl0->addInteger( 'weight', __( 'Weight up to (kg)', 'packetery' ) )
			->setRequired();
		$wl0->addInteger( 'price', __( 'Price', 'packetery' ) )
			->setRequired();

		$surcharge_limits = $container->addContainer( 'surcharge_limits' );
		$sl0              = $surcharge_limits->addContainer( '0' );
		$sl0->addInteger( 'order_price', __( 'Order price up to', 'packetery' ) );
		$sl0->addInteger( 'surcharge', __( 'Surcharge', 'packetery' ) );

		$container->addInteger( 'free_shipping_limit', __( 'Free shipping limit', 'packetery' ) );
		$container->addHidden('id');

		$carrierOptions = get_option( $optionId );
		$carrierOptions['id'] = $carrier_data['id'];
		if ( empty( $carrierOptions['name'] ) ) {
			$carrierOptions['name'] = $carrier_data['name'];
		}
		$container->setDefaults( $carrierOptions );


		return $form;
	}

	/**
	 *  Admin_init callback.
	 */
	public function admin_init(): void {
		// TODO: add PP for 'cz', 'sk', 'hu', 'ro' ?
		$all_carriers = $this->carrier_repository->get_carrier_ids();
		foreach ( $all_carriers as $carrier_data ) {
			register_setting( 'packetery_carrier_' . $carrier_data['id'], 'packetery_carrier_' . $carrier_data['id'], array(
				$this,
				'options_validate'
			) );
		}
	}

	/**
	 * Validates options.
	 *
	 * @param array $options Packetery_options.
	 *
	 * @return array
	 */
	public function options_validate( array $options ): array {
		if ( ! empty( $options['id'] ) ) {
			$form     = $this->create_form( $options );
			$optionId = 'packetery_carrier_' . $options['id'];
			$form[ $optionId ]->setValues( $options );
			if ( $form->isValid() === false ) {
				foreach ( $form[ $optionId ]->getControls() as $control ) {
					if ( $control->hasErrors() === false ) {
						continue;
					}
					add_settings_error( $control->getCaption(), esc_attr( $control->getName() ), $control->getError() );
					$options[ $control->getName() ] = '';
				}
			}
		}

		return $options;
	}

	/**
	 *  Renders page.
	 */
	public function render(): void {
		// TODO: fix Processing form data without nonce verification.
		if ( isset( $_GET['code'] ) ) {
			$country_iso = sanitize_text_field( wp_unslash( $_GET['code'] ) );
			// TODO: add PP for 'cz', 'sk', 'hu', 'ro' ?
			$country_carriers = $this->carrier_repository->get_by_country( $country_iso );
			$carriers_data    = array();
			foreach ( $country_carriers as $carrier_data ) {
				$carriers_data[] = array(
					'form' => $this->create_form( $carrier_data ),
					'data' => $carrier_data,
				);
			}
			$this->latte_engine->render(
				PACKETERY_PLUGIN_DIR . '/template/options/country.latte',
				array(
					'forms'       => $carriers_data,
					'country_iso' => $country_iso,
				)
			);
		} else {
			// TODO: countries overview.
		}
	}

}
