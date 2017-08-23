<?php

if ( ! class_exists( 'P4EN_Pages_Controller' ) ) {

	/**
	 * Class P4EN_Pages_Controller
	 *
	 * This class will be the base controller for handling various ways listing EN Pages tables
	 */
	abstract class P4EN_Pages_Controller {

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
