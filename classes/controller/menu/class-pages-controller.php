<?php

namespace P4EN\Controllers\Menu;

if ( ! class_exists( 'Pages_Controller' ) ) {

	/**
	 * Class Pages_Controller
	 *
	 * This class will be the base controller for handling various ways listing EN Pages tables
	 */
	abstract class Pages_Controller extends Controller {

		const SUBTYPES = [
			'DCF'   => [
				'type' => 'Data capture',
				'subType' => 'Data capture form',
			],
			'MEM'   => [
				'type' => 'Fundraising',
				'subType' => 'Membership',
			],
			'EMS'   => [
				'type' => 'List management',
				'subType' => 'Email subscribe',
			],
			'UNSUB' => [
				'type' => 'List management',
				'subType' => 'Email unsubscribe',
			],
			'PET'   => [
				'type' => 'Advocacy',
				'subType' => 'Petition',
			],
			'ET'    => [
				'type' => 'Advocacy',
				'subType' => 'Email to target',
			],
			'ND'    => [
				'type' => 'Fundraising',
				'subType' => 'Donation',
			],
		];

		const STATUSES = [
			'all'       => 'All',
			'new'       => 'New',
			'live'      => 'Live',
			'tested'    => 'Tested',
		];
	}
}
