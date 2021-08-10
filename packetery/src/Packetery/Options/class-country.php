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
		add_submenu_page(
			'packeta-options',
			__( 'Countries', 'packetery' ),
			__( 'Countries', 'packetery' ),
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
		$form = $this->form_factory->create( 'carrier-' . $carrier_data['id'] );
		/**
		 * TODO: Save. Later:
		 * pokud je dopravce aktivní, nabízí se v košíku
		 * název dopravce - zobrazí se v košíku
		 * dopravné zdarma od částky: - po překročení této částky je dopravné zdarma
		 */
		$form->setAction( '' );

		$container = $form->addContainer( 'packetery' );

		$container->addCheckbox(
			'active',
			__( 'Active carrier', 'packetery' )
		);

		$container->addText( 'name', __( 'Display name', 'packetery' ) )
				->setRequired()
				->addRule( $form::MIN_LENGTH, __( 'Carrier display name must have at least 2 characters!', 'packetery' ), 2 );

		$weight_limits = $form->addContainer( 'packetery_weight_limits' );
		$wl0           = $weight_limits->addContainer( '0' );
		$wl0->addInteger( 'weight', __( 'Weight up to', 'packetery' ) )
				->setRequired();
		$wl0->addInteger( 'price', __( 'Price', 'packetery' ) )
				->setRequired();

		$surcharge_limits = $form->addContainer( 'packetery_surcharge_limits' );
		$sl0              = $surcharge_limits->addContainer( '0' );
		$sl0->addInteger( 'order_price', __( 'Order price up to', 'packetery' ) );
		$sl0->addInteger( 'surcharge', __( 'Surcharge', 'packetery' ) );

		$container->addInteger( 'free_shipping_limit', __( 'Free shipping limit', 'packetery' ) );

		// todo load saved data.
		$container->setDefaults(
			array(
				'name' => $carrier_data['name'],
			)
		);

		/*
		TODO kvuli FormsReplicator
		if ( $form->isSubmitted() ) {
			$form->fireEvents();
		}
		*/

		return $form;
	}

	/**
	 *  Renders page.
	 */
	public function render(): void {
		// todo Processing form data without nonce verification.
		if ( isset( $_GET['code'] ) ) {
			$country_iso = sanitize_text_field( wp_unslash( $_GET['code'] ) );
			// TODO: add PP for CZ?
			$country_carriers = $this->carrier_repository->get_by_country( $country_iso );
			if ( $country_carriers ) {
				$carriers_data = array();
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
			}
		} else {
			// TODO: countries overview.
		}
	}

}
