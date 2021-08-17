<?php
/**
 * Packeta carrier repository
 *
 * @package Packetery
 */

declare( strict_types=1 );

namespace Packetery\Carrier;

/**
 * Class CarrierRepository
 *
 * @package Packetery
 */
class Repository {
	/**
	 * WordPress wpdb object from global
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Repository constructor.
	 *
	 * @param \wpdb $wpdb wpdb.
	 */
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	/**
	 * Gets wpdb object from global variable and sets custom tablename.
	 *
	 * @return \wpdb
	 */
	private function get_wpdb(): \wpdb {
		return $this->wpdb;
	}

	/**
	 * Create table to store carriers.
	 */
	public function create_table(): void {
		$wpdb = $this->get_wpdb();
		$wpdb->query(
			'CREATE TABLE IF NOT EXISTS `' . $wpdb->packetery_carrier . '` (
				`id` int NOT NULL,
				`name` varchar(255) NOT NULL,
				`is_pickup_points` boolean NOT NULL,
				`has_carrier_direct_label` boolean NOT NULL,
				`separate_house_number` boolean NOT NULL,
				`customs_declarations` boolean NOT NULL,
				`requires_email` boolean NOT NULL,
				`requires_phone` boolean NOT NULL,
				`requires_size` boolean NOT NULL,
				`disallows_cod` boolean NOT NULL,
				`country` varchar(255) NOT NULL,
				`currency` varchar(255) NOT NULL,
				`max_weight` float NOT NULL,
				`deleted` boolean NOT NULL,
				UNIQUE (`id`)
			) ' . $wpdb->get_charset_collate()
		);
	}

	/**
	 * Drop table used to store carriers.
	 */
	public function drop(): void {
		$wpdb = $this->get_wpdb();
		$wpdb->query( 'DROP TABLE IF EXISTS `' . $wpdb->packetery_carrier . '`' );
	}

	/**
	 * Gets known carrier ids.
	 *
	 * @return array|null
	 */
	public function get_carrier_ids(): ?array {
		$wpdb = $this->get_wpdb();

		return $wpdb->get_results( 'SELECT `id` FROM `' . $wpdb->packetery_carrier . '`', ARRAY_A );
	}

	/**
	 * Gets all active carriers.
	 *
	 * @param string $country ISO code.
	 *
	 * @return array|null
	 */
	public function get_by_country( string $country ): ?array {
		$wpdb = $this->get_wpdb();

		return $wpdb->get_results( $wpdb->prepare( 'SELECT `id`, `name` FROM `' . $wpdb->packetery_carrier . '` WHERE `country` = %s AND `deleted` = false', $country ), ARRAY_A );
	}

	/**
	 * Set those not in feed as deleted.
	 *
	 * @param array $carriers_in_feed Carriers in feed.
	 */
	public function set_others_as_deleted( array $carriers_in_feed ): void {
		$wpdb = $this->get_wpdb();
		$wpdb->query( 'UPDATE `' . $wpdb->packetery_carrier . '` SET `deleted` = 1 WHERE `id` NOT IN (' . implode( ',', $carriers_in_feed ) . ')' );
		// TODO: find out how to do it properly, can't use IN ('1,2,3')
		// $wpdb->query( $wpdb->prepare( 'UPDATE `' . $wpdb->packetery_carrier . '` SET `deleted` = 1 WHERE `id` NOT IN (%s)', implode( ',', $carriers_in_feed ) ) ); .
	}

	/**
	 * Inserts carrier data to db.
	 *
	 * @param array $data Carrier data.
	 */
	public function insert( array $data ): void {
		$wpdb = $this->get_wpdb();
		$wpdb->insert( $wpdb->packetery_carrier, $data );
	}

	/**
	 * Updates carrier data in db.
	 *
	 * @param array $data Carrier data.
	 * @param int   $carrier_id Carrier id.
	 */
	public function update( array $data, int $carrier_id ): void {
		$wpdb = $this->get_wpdb();
		$wpdb->update( $wpdb->packetery_carrier, $data, array( 'id' => $carrier_id ) );
	}

}