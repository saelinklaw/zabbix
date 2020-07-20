<?php
/*
** Zabbix
** Copyright (C) 2001-2020 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../include/CWebTest.php';
require_once dirname(__FILE__).'/behaviors/CFormParametersBehavior.php';
require_once dirname(__FILE__).'/behaviors/CMessageBehavior.php';

/**
 * @backup items, ids
 */
class testFormLowLevelDiscoveryOverrides extends CWebTest {

	const HOST_ID = 40001;
	const UPDATED_ID = 33800;

	public static $created_id;
	public static $old_hash;

	/**
	 * Attach Behaviors to the test.
	 *
	 * @return array
	 */
	public function getBehaviors() {
		return [
			[
				'class' => CFormParametersBehavior::class,
				'table_selector' => 'id:overrides_filters',
				'table_mapping' => [
					'Macro' => [
						'name' => 'macro',
						'selector' => 'xpath:./input|./textarea',
						'class' => 'CElement'
					],
					'' => [
						'name' => 'operator',
						'selector' => 'xpath:.//select[contains(@id, "_operator")]',
						'class' => 'CDropdownElement'
					],
					'Regular expression' => [
						'name' => 'expression',
						'selector' => 'xpath:./input|./textarea',
						'class' => 'CElement'
					]
				]
			],
			'class' => CMessageBehavior::class
		];
	}

	/*
	 * Overrides data for LLD creation.
	 */
	public static function getCreateData() {
		return [
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => ''
							],
							'error' => 'Incorrect value for field "Name": cannot be empty.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override without actions'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									]
								]
							],
							'error' => 'At least one action is mandatory.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with empty tags'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Trigger prototype',
										'Tags' => []
									]
								]
							],
							'error' => 'Incorrect value for field "Tags": cannot be empty.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with empty tag name'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Trigger prototype',
										'Tags' => [
											['tag' => '', 'value' => 'value1']
										]
									]
								]
							],
							'error' => 'Incorrect value for field "Tag": cannot be empty.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with empty template'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Host prototype',
										'Link templates' => []
									]
								]
							],
							'error' => 'Incorrect value for field "Link templates": cannot be empty.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with empty delay'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => ''
									]
								]
							],
							'error' => 'Incorrect value for field "Update interval": invalid delay.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with zero delay'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => '0'
									]
								]
							],
							'error' => 'Item will not be refreshed. '.
									'Specified update interval requires having at least one either flexible or scheduling interval.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with 2 days delay'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => '2d'
									]
								]
							],
							'error' => 'Item will not be refreshed. '.
									'Update interval should be between 1s and 1d. Also Scheduled/Flexible intervals can be used.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with empty interval'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => '50m',
										'Custom intervals' => [
											['action' => USER_ACTION_ADD, 'Type' => 'Flexible', 'delay' => '', 'period' => '1-5,01:01-13:05']
										]
									]
								]
							],
							'error' => 'Invalid interval "".'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with empty period'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => '50m',
										'Custom intervals' => [
											['action' => USER_ACTION_ADD, 'Type' => 'Flexible', 'delay' => '20s', 'period' => '']
										]
									]
								]
							],
							'error' => 'Invalid interval "".'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with wrong period'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => '50m',
										'Custom intervals' => [
											['action' => USER_ACTION_ADD, 'Type' => 'Flexible', 'delay' => '20s', 'period' => '1-2']
										]
									]
								]
							],
							'error' => 'Invalid interval "1-2".'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override with wrong scheduling interval'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype'
									],
									'Update interval' => [
										'Delay' => '50m',
										'Custom intervals' => [
											['action' => USER_ACTION_ADD, 'Type' => 'Scheduling', 'delay' => 'wd1-9']
										]
									]
								]
							],
							'error' => 'Invalid interval "wd1-9".'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Minimal override'
							]
						]
					]
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'overrides' => [
						[
							'fields' => [
								'Name' => 'Override_1',
								'If filter matches' => 'Stop processing'
							],
							'Filters' => [
								'Type of calculation' => 'Custom expression',
								'formula' => 'A and B',
								'filter_conditions' => [
									[
										'action' => USER_ACTION_UPDATE,
										'index' => 0,
										'macro' => '{#MACRO1}',
										'operator' => 'does not match',
										'expression' => 'expression_1'
									],
									[
										'macro' => '{#MACRO2}',
										'operator' => 'matches',
										'expression' => 'expression_2'
									]
								]
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Item prototype',
										'Condition' => ['operator' => 'does not match', 'value' => 'item_pattern'],
										'Create enabled' => 'No',
										'Discover' => 'No',
										'History storage period' => [
											'ophistory_history_mode' => 'Storage period',
											'ophistory_history' => '500d'
										],
										'Trend storage period' => [
											'optrends_trends_mode' => 'Storage period',
											'optrends_trends' => '200d'
										]
									],
									'Update interval' => [
										'Delay' => '50m',
										'Custom intervals' => [
											['Type' => 'Flexible', 'delay' => '60s', 'period' => '1-5,01:01-13:05'],
											['Type' => 'Scheduling', 'delay' => 'wd1-3h10-17']
										]
									]
								],
								[
									'fields' => [
										'Object' => 'Trigger prototype',
										'Condition' => ['operator' => 'contains', 'value' => 'trigger_Pattern'],
										'Create enabled' => 'No',
										'Discover' => 'No',
										'Severity' => 'Warning',
										'Tags' => [
											['tag' => 'tag1', 'value' => 'value1'],
											['tag' => 'tag2', 'value' => 'value2']
										]
									]
								],
								[
									'fields' => [
										'Object' => 'Graph prototype',
										'Condition' => ['operator' => 'matches', 'value' => 'Graph_Pattern'],
										'Discover' => 'Yes'
									]
								],
								[
									'fields' => [
										'Object' => 'Host prototype',
										'Condition' => ['operator' => 'does not match', 'value' => 'Host_Pattern'],
										'Create enabled' => 'Yes',
										'Discover' => 'Yes',
										'Link templates' => 'Test Item Template',
										'Host inventory' => 'Disabled'
									]
								]
							]
						],
						[
							'fields' => [
								'Name' => 'Override_2',
								'If filter matches' => 'Continue overrides'
							],
							'Operations' => [
								[
									'fields' => [
										'Object' => 'Graph prototype',
										'Condition' => ['operator' => 'matches', 'value' => '2Graph_Pattern'],
										'Discover' => 'No'
									]
								],
								[
									'fields' => [
										'Object' => 'Host prototype',
										'Condition' => ['operator' => 'does not match', 'value' => '2Host_Pattern'],
										'Create enabled' => 'Yes',
										'Discover' => 'No',
										'Link templates' => 'Test Item Template',
										'Host inventory' => 'Automatic'
									]
								]
							]
						]
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getCreateData
	 */
	public function testFormLowLevelDiscoveryOverrides_Create($data) {
		$this->overridesCreate($data);
	}

	private function overridesCreate($data) {
		$this->page->login()->open('host_discovery.php?form=create&hostid='.self::HOST_ID);
		$form = $this->query('name:itemForm')->waitUntilPresent()->asForm()->one();
		$key = 'lld_override'.time();
		$form->fill(['Name' => 'LLD with overrides',
					'Key' => $key]);
		$form->selectTab('Overrides');
		$form->invalidate();
		$override_container = $form->getField('Overrides')->asTable();

		// Add overrides from data to lld rule.
		foreach($data['overrides'] as $i => $override){
			$override_container->query('button:Add')->one()->click();
			$override_overlay = $this->query('id:lldoverride_form')->waitUntilPresent()->asForm()->one();

			// Fill Override name and what to do if Filter matches.
			if (array_key_exists('fields', $override)) {
				$override_overlay->fill($override['fields']);
			}
			$this->fillOverrideFilter($override);
			$this->fillOverrideOperations($data, $override);

			$this->checkSubmittedOverlay($data['expected'], $override_overlay, CTestArrayHelper::get($override, 'error'));

			if (CTestArrayHelper::get($data, 'expected') === TEST_GOOD) {
				// Check that Override with correct name was added to Overrides table.
				$this->assertEquals($override['fields']['Name'], $override_container->getRow($i)->getColumn('Name')->getText());
				// Check that Override in table has correct processing status.
				$stop_processing = (CTestArrayHelper::get($override['fields'],
						'If filter matches') === 'Stop processing') ? 'Yes' : 'No';
				$this->assertEquals($stop_processing, $override_container->getRow($i)->getColumn('Stop processing')->getText());
			}
		}

		if (CTestArrayHelper::get($data, 'expected') === TEST_GOOD) {
			// Submit LLD create.
			$form->submit();
			$this->assertMessage(TEST_GOOD, 'Discovery rule created');
			$this->assertEquals(1, CDBHelper::getCount('SELECT NULL FROM items WHERE key_='.zbx_dbstr($key)));
			self::$created_id = CDBHelper::getValue('SELECT itemid FROM items WHERE key_='.zbx_dbstr($key));
		}

		$this->checkSavedState($data);
	}

	/*
	 * Overrides data for LLD creation.
	 */
	public static function getUpdateData() {
		return [
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'action' => USER_ACTION_UPDATE,
							'name' => 'Override for update 1',
							'fields' => [
								'Name' => ''
							],
							'error' => 'Incorrect value for field "Name": cannot be empty.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'overrides' => [
						[
							'action' => USER_ACTION_ADD,
							'fields' => [
								'Name' => ''
							],
							'error' => 'Incorrect value for field "Name": cannot be empty.'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'overrides' => [
						[
							'action' => USER_ACTION_REMOVE,
							'name' => 'Override for update 1'
						],
						[
							'action' => USER_ACTION_REMOVE,
							'name' => 'Override for update 2'
						]
					]
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'overrides' => [
						[
							'action' => USER_ACTION_UPDATE,
							'name' => 'Override for update 1',
							'Filters' => [
								'formula' => 'A and B and C',
								'filter_conditions' => [
									[
										'action' => USER_ACTION_ADD,
										'macro' => '{#UPDATED_MACRO3}',
										'operator' => 'does not match',
										'expression' => 'ADDED expression_3'
									]
								]
							],
							'Operations' => [
								[
									'action' => USER_ACTION_ADD,
									'fields' => [
										'Object' => 'Host prototype',
										'Condition' => ['operator' => 'contains', 'value' => 'new host pattern'],
										'Create enabled' => 'No',
										'Discover' => 'Yes',
										'Link templates' => 'Test Item Template',
										'Host inventory' => 'Disabled'
									]
								]
							]
						]
					]
				]
			],
			[
				[
					'expected' => TEST_GOOD,
					'overrides' => [
						[
							'action' => USER_ACTION_UPDATE,
							'name' => 'Override for update 1',
							'fields' => [
								'Name' => 'Updated Override for update 1',
								'If filter matches' => 'Stop processing'
							],
							'Filters' => [
								'Type of calculation' => 'Custom expression',
								'formula' => 'A and B',
								'filter_conditions' => [
									[
										'action' => USER_ACTION_UPDATE,
										'index' => 0,
										'macro' => '{#UPDATED_MACRO1}',
										'operator' => 'does not match',
										'expression' => 'UPDATED expression_1'
									],
									[
										'action' => USER_ACTION_UPDATE,
										'index' => 1,
										'macro' => '{#UPDATED_MACRO2}',
										'operator' => 'matches',
										'expression' => 'UPDATED expression_2'
									]
								]
							],
							'Operations' => [
								[
									'action' => USER_ACTION_UPDATE,
									'index' => 0,
									'fields' => [
										'Create enabled' => 'No',
										'Discover' => 'No',
										'History storage period' => [
											'ophistory_history_mode' => 'Storage period',
											'ophistory_history' => '500d'
										],
										'Trend storage period' => [
											'optrends_trends_mode' => 'Storage period',
											'optrends_trends' => '200d'
										]
									],
									'Update interval' => [
										'Delay' => '50m',
										'Custom intervals' => [
											['Type' => 'Flexible', 'delay' => '60s', 'period' => '1-5,01:01-13:05'],
											['Type' => 'Scheduling', 'delay' => 'wd1-3h10-17']
										]
									]
								],
								[
									'action' => USER_ACTION_UPDATE,
									'index' => 1,
									'fields' => [
										'Create enabled' => 'No',
										'Discover' => 'Yes',
										'Severity' => null,
										'Tags' => null
									]
								]
							]
						]
					]
				]
			]
		];
	}

	/**
	 * @dataProvider getUpdateData
	 *
	 * @backup items
	 */
	public function testFormLowLevelDiscoveryOverrides_Update($data) {
		$this->overridesUpdate($data);
	}

	private function overridesUpdate($data) {
		self::$old_hash = CDBHelper::getHash('SELECT * FROM items WHERE flags=1 ORDER BY itemid');
		$this->page->login()->open('host_discovery.php?form=update&itemid='.self::UPDATED_ID);
		$form = $this->query('name:itemForm')->waitUntilPresent()->asForm()->one();
		$form->selectTab('Overrides');
		$form->invalidate();
		$override_container = $form->getField('Overrides')->asTable();

		$sources = [
			[
				'fields' => [
					'Name' => 'Override for update 1',
					'If filter matches' => 'Continue overrides'
				],
				'Filters' => [
					'Type of calculation' => 'And',
					'formula' => 'A and B',
					'filter_conditions' => [
						[
							'macro' => '{#MACRO1}',
							'operator' => 'matches',
							'expression' => 'test expression_1'
						],
						[
							'macro' => '{#MACRO2}',
							'operator' => 'does not match',
							'expression' => 'test expression_2'
						]
					]
				],
				'Operations' => [
					[
						'Object' => 'Item prototype',
						'Condition' => ['operator' => 'equals', 'value' => 'test item pattern'],
						'Create enabled' => 'Yes',
						'Discover' => 'Yes',
						'Update interval' => [
							'Delay' => '1m',
							'Custom intervals' => [
								['Type' => 'Flexible', 'Interval' => '50s', 'Period' => '1-7,00:00-24:00'],
								['Type' => 'Scheduling', 'Interval' => 'wd1-5h9-18']
							]
						],
						'History storage period' => ['ophistory_history_mode' => 'Do not keep history'],
						'Trend storage period' => ['optrends_trends_mode' => 'Do not keep history']
					],
					[
						'Object' => 'Trigger prototype',
						'Condition' => ['operator' => 'does not equal', 'value' => 'test trigger pattern'],
						'Create enabled' => null,
						'Discover' => null,
						'Severity' => 'Warning',
						'Tags' => [
							['tag' => 'tag1', 'value' => 'value1'],
						]
					]
				]
			],
			[
				'fields' => [
					'Name' => 'Override for update 2',
					'If filter matches' => 'Continue overrides'
				],
				'Operations' => [
					[
						'Object' => 'Graph prototype',
						'Condition' => ['operator' => 'matches', 'value' => 'test graph pattern'],
						'Discover' => 'Yes'
					],
					[
						'Object' => 'Host prototype',
						'Condition' => ['operator' => 'does not match', 'value' => 'test host pattern'],
						'Create enabled' => null,
						'Discover' => null,
						'Link templates' => 'Test Item Template',
						'Host inventory' => 'Automatic'
					]
				]
			]
		];

		foreach ($data['overrides'] as $i => $override) {
			$override_action = CTestArrayHelper::get($override, 'action', USER_ACTION_ADD);

			// Preparing reference data for overrides.
			switch ($override_action) {
				case USER_ACTION_ADD:
					$sources[] = $override;
					break;

				case USER_ACTION_REMOVE:
					foreach ($sources as $id => $source) {
						if ($source['fields']['Name'] === $override['name']) {
							unset($sources[$id]);
						}
					}
					break;

				case USER_ACTION_UPDATE:
					// Find overrides by name.
					foreach ($sources as $id => $source) {
						if ($source['fields']['Name'] === $override['name']) {
							break;
						}
					}

					$this->assertNotNull($id, 'Cannot find reference data by override name '.$override['name']);

					// Check if source has fields from data to update them.
					foreach (CTestArrayHelper::get($override, 'fields', []) as $key => $value) {
						$this->assertArrayHasKey($key, $sources[$id]['fields'], 'Cannot find field '.$key.' in source');
						$sources[$id]['fields'][$key] = $value;
					}

					// Preparing reference data for Filter conditions.
					$conditions = [];
					foreach (CTestArrayHelper::get($override, 'Filters', []) as $key => $value) {
						if ($key === 'filter_conditions') {
							$conditions = $value;
							continue;
						}

						$sources[$id]['Filters'][$key] = $value;
					}

					foreach ($conditions as $condition) {
						switch($condition['action']) {
							case USER_ACTION_ADD:
								$sources[$id]['Filters']['filter_conditions'][] = $condition;
								break;

							case USER_ACTION_UPDATE:
								foreach ($condition as $key => $value) {
									// Skipping 'action' and 'index' fields from reference data.
									if (in_array($key, ['action', 'index'])) {
										continue;
									}

									$sources[$id]['Filters']['filter_conditions'][$condition['index']][$key] = $value;
								}
								break;

							case USER_ACTION_REMOVE:
								unset($sources[$id]['Filters']['filter_conditions'][$condition['index']]);
								break;
						}
					}
					// Open Override overlay.
					$override_container->query('link', $override['name'])->one()->click();
					$override_overlay = $this->query('id:lldoverride_form')->waitUntilPresent()->asForm()->one();

					// Get Operations Table.
					$operations_container = $override_overlay->getField('Operations')->asTable();

					$operations = CTestArrayHelper::get($override, 'Operations', []);
					foreach ($operations  as $j => $operation) {
						$operation_action = CTestArrayHelper::get($operation, 'action', USER_ACTION_ADD);
						// Preparing reference data for Operations.
						switch ($operation_action) {
							case USER_ACTION_ADD:
								$temp = $operation['fields'];
								if (array_key_exists('Update interval', $operation)) {
									$temp['Update interval'] = $operation['Update interval'];
								}
								$sources[$id]['Operations'][] = $temp;
								break;
							case USER_ACTION_UPDATE:
								// Check if source has Operations from data to update them.
								foreach ($operation['fields'] as $key => $value) {
									// Skipping 'action' and 'index' fields from reference data.
									if (in_array($key, ['action', 'index'])) {
										continue;
									}

									$this->assertArrayHasKey($key, $sources[$id]['Operations'][$operation['index']],
											'Cannot find field '.$key.' in source');
									$sources[$id]['Operations'][$operation['index']][$key] = $value;
								}

								break;
							case USER_ACTION_REMOVE:
								unset($sources[$id]['Filters']['filter_conditions'][$condition['index']]);
								break;
						}
					}

					break;
			}

			switch ($override_action) {
				// Perform adding or updating Override.
				case USER_ACTION_ADD:
					$override_container->query('button:Add')->one()->click();
					$id = null;
				case USER_ACTION_UPDATE:
					// Fill Override name and what to do if Filter matches.
					if (array_key_exists('fields', $override)) {
						$override_overlay = $this->query('id:lldoverride_form')->waitUntilPresent()->asForm()->one();
						$override_overlay->fill($override['fields']);
					}
					$this->fillOverrideFilter($override);
					$this->fillOverrideOperations($data, $override, $sources, $id);
					$this->checkSubmittedOverlay($data['expected'], $override_overlay, CTestArrayHelper::get($override, 'error'));

					if (CTestArrayHelper::get($data, 'expected') === TEST_GOOD) {
						// Check that Override with correct name was added to Overrides table.
						$fields = (CTestArrayHelper::get($override, 'fields'))
							? $override['fields']['Name']
							: $sources[$i]['fields']['Name'];
						$this->assertEquals($fields, $override_container->getRow($i)->getColumn('Name')->getText());
						// Check that Override in table has correct processing status.
						$stop_processing = (CTestArrayHelper::get($override,
								'fields.If filter matches') === 'Stop processing') ? 'Yes' : 'No';
						$this->assertEquals($stop_processing, $override_container->getRow($i)->getColumn('Stop processing')->getText());
					}
					break;

				case USER_ACTION_REMOVE:
					$override_container->findRow('Name', $override['name'])
							->query('button:Remove')->one()->click();
					break;

				default:
					throw new Exception('Cannot perform action "'.$override_action.'".');
			}
		}

		if (CTestArrayHelper::get($data, 'expected') === TEST_GOOD) {
			// Submit LLD update.
			$form->submit();
			$this->assertMessage(TEST_GOOD, 'Discovery rule updated');
			$this->assertEquals(1, CDBHelper::getCount('SELECT NULL FROM items WHERE itemid ='.self::UPDATED_ID));

			self::$created_id = self::UPDATED_ID;
			$this->checkSavedState(['overrides' => $sources]);
		}
	}

	/**
	 * @param array         $override          override fields from data
	 *
	 * @return CFormElement $override_overlay  override or condition form in overlay
	 */
	private function fillOverrideFilter($override) {
		$override_overlay = $this->query('id:lldoverride_form')->waitUntilPresent()->asForm()->one();

		// Add Filters to override.
		if (array_key_exists('Filters', $override)) {
			$this->fillParameters($override['Filters']['filter_conditions']);

			// Add Type of calculation if there are more then 2 filters.
			if (array_key_exists('Type of calculation', $override['Filters'])) {
				$override_overlay->query('id:overrides_evaltype')->waitUntilPresent()->one()
						->asDropdown()->fill($override['Filters']['Type of calculation']);

				// Add formula if Type of calculation is Custom.
				if (array_key_exists('formula', $override['Filters'])) {
					$override_overlay->query('id:overrides_formula')->waitUntilPresent()->one()
							->fill($override['Filters']['formula']);
				}
			}
		}

		return $override_overlay;
	}

		/**
	 *
	 * @param array         $data              data provider
	 * @param array         $override          override fields from data
	 *
	 * @return CFormElement $override_overlay  override or condition form in overlay
	 */
	private function fillOverrideOperations($data, $override, $sources = null, $id = null) {
		$override_overlay = $this->query('id:lldoverride_form')->waitUntilPresent()->asForm()->one();
		$operation_container = $override_overlay->getField('Operations')->asTable();

		if (array_key_exists('Operations', $override)) {

			// Add Operations to override.
			foreach($override['Operations'] as $i =>$operation){

				$operation_action = CTestArrayHelper::get($operation, 'action', USER_ACTION_ADD);
				unset($operation['action']);

				$row = null;
				switch ($operation_action) {
					case USER_ACTION_ADD:
						$row = $operation_container->getRows()->count() - 1;
						$operation_container->query('button:Add')->one()->click();
						break;

					case USER_ACTION_UPDATE:
						$row = $operation['index'];
						$operation_container->getRow($row)->query('button:Edit')->one()->click();
						unset($operation['index']);
						break;
				}

				switch ($operation_action) {
					case USER_ACTION_ADD:
					case USER_ACTION_UPDATE:
						$operation_overlay = $this->query('id:lldoperation_form')->waitUntilPresent()->asForm()->one();
						$operation_overlay->fill($operation['fields']);

						// Fill Delay and Intervals.
						if (CTestArrayHelper::get($operation, 'Update interval')) {
							if ($operation['Update interval'] !== null) {
								$intervals = $operation_overlay->getField('Update interval');
								$operation_overlay->query('id:visible_opperiod')->one()->fill(true);
								if (array_key_exists('Delay', $operation['Update interval'])) {
									$intervals->query('id:opperiod_delay')->waitUntilVisible()->one()
											->fill($operation['Update interval']['Delay']);
								}
								if (array_key_exists('Custom intervals', $operation['Update interval'])) {
									$this->query('xpath:.//table[@id="lld_overrides_custom_intervals"]')
											->asMultifieldTable()->one()
											->fill($operation['Update interval']['Custom intervals']);
								}
							}
							else {
								$operation_overlay->query('id:visible_opperiod')->one()->fill(false);
							}
						}

						$operation_overlay->submit();
						$this->checkSubmittedOverlay($data['expected'], $operation_overlay,
								CTestArrayHelper::get($override, 'error'));

						if (CTestArrayHelper::get($data, 'expected') === TEST_GOOD) {
							// Check that Operation was added to Operations table.
							$object = CTestArrayHelper::get($operation, 'fields.Object');
							if ($object === null) {
								$object = $sources[$id]['Operations'][$i]['Object'];
							}

							$operator = CTestArrayHelper::get($operation, 'fields.Condition.operator');
							if ($operator === null) {
								$operator = $sources[$id]['Operations'][$i]['Condition']['operator'];
							}

							$value = CTestArrayHelper::get($operation, 'fields.Condition.value');
							if ($value === null) {
								$value = $sources[$id]['Operations'][$i]['Condition']['value'];
							}

							$condition_text = $object.' '.$operator.' '.$value;
							$this->assertEquals($condition_text, $operation_container->getRow($row)->getColumn('Condition')
									->getText());
						}
						break;

					case USER_ACTION_REMOVE:
						$condition_text = $operation['fields']['Object'].' '.
								$operation['fields']['Condition']['operator'].' '.
								$operation['fields']['Condition']['value'];
						$row = $operation_container->findRow('Condition', $condition_text)
							->query('button:Remove')->one()->click();
						$row->waitUntilNotPresent();
						break;
				}
			}
		}
		// Submit Override.
		$override_overlay->submit();

		return $override_overlay;
	}

	/**
	 * Function for checking successful/failed overlay submitting.
	 *
	 * @param string		$expected	case GOOD or BAD
	 * @param CFormElement	$overlay 	override or condition form in overlay
	 * @param string	    $error		error message text
	 */
	private function checkSubmittedOverlay($expected, $overlay, $error) {
		switch ($expected) {
			case TEST_GOOD:
				$overlay->waitUntilNotPresent();
				break;
			case TEST_BAD:
				$this->assertMessage(TEST_BAD, null, $error);
				break;
		}
	}

	private function checkSavedState($data) {
		// Skip Bad cases.
		if (CTestArrayHelper::get($data, 'expected') === TEST_BAD) {
			return;
		}

		// Open saved LLD.
		$this->page->login()->open('host_discovery.php?form=update&itemid='.self::$created_id);
		$form = $this->query('name:itemForm')->waitUntilPresent()->asForm()->one();
		$form->selectTab('Overrides');
		$override_container = $form->getField('Overrides')->asTable();
		// Get Overrides count.
		$overrides_count = $override_container->getRows()->count();

		// Write Override names from data to array.
		foreach ($data['overrides'] as $override) {
			$override_names[] = $override['fields']['Name'];
			$stop_processing[] = (CTestArrayHelper::get($override['fields'],
					'If filter matches') === 'Stop processing') ? 'Yes' : 'No';
		}

		// Compare Override names from table with data.
		for ($k = 0; $k < $overrides_count - 1; $k++) {
			$this->assertEquals($override_names[$k],
					$override_container->getRow($k)->getColumn('Name')->getText()
			);
			// Check that Override in table has correct processing status.
			$this->assertEquals($stop_processing[$k], $override_container->getRow($k)->getColumn('Stop processing')->getText());
		}

		foreach ($data['overrides'] as $override) {
			// Open each override dialog.
			$row = $override_container->findRow('Name', $override['fields']['Name']);
			$row->query('link', $override['fields']['Name'])->one()->click();
			$override_overlay = $this->query('id:lldoverride_form')->waitUntilPresent()->asForm()->one();

			// Check that Override fields filled with correct data.
			foreach ($override['fields'] as $field => $value) {
				$override_overlay->getField($field)->checkValue($value);
			}

			if (array_key_exists('Filters', $override)) {
				// Check that Fiters are filled correctly.
				$this->assertValues($override['Filters']['filter_conditions']);

				// Check that Evaluation type is filled correctly.
				if (array_key_exists('Type of calculation', $override['Filters'])) {
					$evaluation_type = $override_overlay->query('id:overrides_evaltype')->one()->asDropdown()->getValue();
					$this->assertEquals($override['Filters']['Type of calculation'], $evaluation_type);

					// Check that Formula is filled correctly.
					if (array_key_exists('formula', $override['Filters'])) {
						$formula = CTestArrayHelper::get($override['Filters'], 'Type of calculation') !== 'Custom expression'
							? $override_overlay->query('id:overrides_expression')->one()->getText()
							: $override_overlay->query('id:overrides_formula')->one()->getValue();
						$this->assertEquals($override['Filters']['formula'], $formula);
					}
				}
			}

			$operation_container = $override_overlay->getField('Operations')->asTable();
			// Get Operations count.
			$operation_count = $operation_container->getRows()->count();

			if (array_key_exists('Operations', $override)) {
				// Write Condititons from data to array.
				$condition_text = [];
				foreach($override['Operations'] as $operation) {
					$fields = array_key_exists('fields', $operation) ? $operation['fields'] : $operation;

					$condition_text[] = $fields['Object'].' '.
							$fields['Condition']['operator'].' '.
							$fields['Condition']['value'];
				}

				// Compare Conditions from table with data.
				for ($n = 0; $n < $operation_count - 1; $n++) {
					$row = $operation_container->getRow($n);
					$this->assertEquals($condition_text[$n],
							$row->getColumn('Condition')->getText()
					);
				}

				foreach($override['Operations'] as $i => $operation) {
					$row = $operation_container->getRow($i);
					$row->query('button:Edit')->one()->click();
					$operation_overlay = $this->query('id:lldoperation_form')->waitUntilPresent()->asForm()->one();

//					foreach ($operation['fields'] as $field => $value) {
//						$operation_overlay->getField($field)->checkValue($value);
//					}

					// Close Operation dialog.
					$operation_overlay->submit();
				}
			}

			// Close Override dialog.
			COverlayDialogElement::find()->one()->close();
		}
	}
}
