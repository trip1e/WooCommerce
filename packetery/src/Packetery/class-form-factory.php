<?php
/**
 * Class Form_Factory
 *
 * @package Packetery
 */

declare( strict_types=1 );

namespace Packetery;

use Nette\Forms\Form;
use Nette\Forms\Validator;

/**
 * Class Form_Factory
 *
 * @package Packetery
 */
class Form_Factory {

	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		Validator::$messages[ Form::FILLED ] = __( 'This field is required!', 'packetery' );
	}

	/**
	 * Creates Form
	 *
	 * @param string|null $name Form name.
	 *
	 * @return Form
	 */
	public function create( ?string $name = null ): Form {
		return new Form( $name );
	}
}
