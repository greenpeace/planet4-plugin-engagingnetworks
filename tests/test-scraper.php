<?php


/**
 * Class ScraperTest.
 * Test p4_page_type custom taxonomy.
 *
 * @package Planet4_Master_Theme
 */
class ScraperTest extends P4_TestCase {


	/**
	 * Test the dom document creation from test files.
	 *
	 * @covers \P4EN\Scraper\Scraper::create_dom_document
	 */
	public function test_dom_document_creation() {

		$scraper  = new \P4EN\Scraper\Scraper();
		$content  = file_get_contents( 'tests/data/get_involved.html' );
		$document = $scraper->create_dom_document( $content );

		// Assert that html file was parsed correctly and the dom document object contains basic html tags.
		$this->assertInstanceOf( 'DOMDocument', $document );
		$this->assertEquals( 1, count( $document->getElementsByTagName( 'html' ) ) );
		$this->assertEquals( 1, count( $document->getElementsByTagName( 'body' ) ) );
		$this->assertEquals( 1, count( $document->getElementsByTagName( 'form' ) ) );
	}

	/**
	 * Test fields extraction from test data files.
	 *
	 * @covers       \P4EN\Scraper\Scraper::extract_fields
	 * @dataProvider pagesProvider
	 */
	public function test_document_extraction( $file, $expected_fields ) {

		$scraper  = new \P4EN\Scraper\Scraper();
		$content  = file_get_contents( 'tests/data/' . $file );
		$document = $scraper->create_dom_document( $content );

		$fields = $scraper->extract_fields( $document, \P4EN\Scraper\ENSelectors::FORM_FIELDS_XPATH );

		// Assert that the extracted fields from the page are the same with the provider data.
		$this->assertEquals( $fields, $expected_fields );
	}

	public function pagesProvider() {
		return
			[
				[
					'get_involved.html',
					json_decode( json_encode(
						[
							[
								'name'      => 'supporter.emailAddress',
								'type'      => 'string',
								'mandatory' => true,
								'label'     => 'Email',
								'id'        => '113507',
							],
							[
								'name'      => 'supporter.country',
								'type'      => 'string',
								'mandatory' => true,
								'label'     => 'country',
								'id'        => '113509',
							],
							[
								'name'      => 'supporter.questions.212677',
								'type'      => 'boolean',
								'mandatory' => false,
								'label'     => 'I am happy for my data to be shared with my local Greenpeace office.',
								'id'        => '212677',
							],
						]
					) )
				],
				[
					'stop_pipelines.html',
					json_decode( json_encode(
						[
							[
								'name'      => 'supporter.firstName',
								'type'      => 'string',
								'mandatory' => false,
								'label'     => 'First name',
								'id'        => '204425',
							],
							[
								'name'      => 'supporter.lastName',
								'type'      => 'string',
								'mandatory' => false,
								'label'     => 'Last name',
								'id'        => '204428',
							],
							[
								'name'      => 'supporter.emailAddress',
								'type'      => 'string',
								'mandatory' => true,
								'label'     => 'Email',
								'id'        => '204427',
							],
							[
								'name'      => 'supporter.country',
								'type'      => 'string',
								'mandatory' => true,
								'label'     => 'Country',
								'id'        => '204429',
							],
							[
								'name'      => 'supporter.questions.204446',
								'type'      => 'boolean',
								'mandatory' => false,
								'label'     => 'Check here if you are a customer of one of these banks. (optional)',
								'id'        => '204446',
							],
							[
								'name'      => 'supporter.questions.2738',
								'type'      => 'boolean',
								'mandatory' => false,
								'label'     => 'I am happy to receive email updates from Greenpeace about important campaigns.',
								'id'        => '2738',
							],
						]


					) )
				],
				[
					'end_plastic_pollution.html',
					json_decode( json_encode(

						[
							[
								'name'      => 'supporter.firstName',
								'type'      => 'string',
								'mandatory' => false,
								'label'     => 'First name',
								'id'        => '175675',
							],
							[
								'name'      => 'supporter.lastName',
								'type'      => 'string',
								'mandatory' => false,
								'label'     => 'Last name',
								'id'        => '175676',
							],
							[
								'name'      => 'supporter.emailAddress',
								'type'      => 'string',
								'mandatory' => true,
								'label'     => 'Email',
								'id'        => '175677',
							],
							[
								'name'      => 'supporter.country',
								'type'      => 'string',
								'mandatory' => true,
								'label'     => 'Country',
								'id'        => '175678',
							],
							[
								'name'      => 'supporter.questions.2738',
								'type'      => 'boolean',
								'mandatory' => false,
								'label'     => 'I am happy to receive email updates from Greenpeace about important campaigns.',
								'id'        => '2738',
							],
						]
					) )
				],
			];
	}
}
