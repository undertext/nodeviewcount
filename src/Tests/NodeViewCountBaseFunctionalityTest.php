<?php

namespace Drupal\nodeviewcount\Tests;

/**
 * Tests the base functionality of nodeviewcount module.
 *
 * @group nodeviewcount
 */
class NodeViewCountBaseFunctionalityTest extends NodeViewCountTestBase {

  /**
   * Tests that cron clears old nodeviewcount records.
   */
  public function testExpiredLogs() {
    $this->sendAjaxStatistics($this->firstTestTrackedNode->id(), 0);
    $this->sendAjaxStatistics($this->secondTestTrackedNode->id(), 0);
    sleep(2);
    $this->cronRun();
    $query = $this->connection->select('nodeviewcount', 'nvc')
      ->fields('nvc', ['id']);
    $result = $query->execute()->fetchAll();
    $this->assertEqual(count($result), 2, ' Nodeviewcount statistics is not deleted after cron run.');

    $node_view_count_settings = $this->config('nodeviewcount.settings');
    $node_view_count_settings->set('logs_life_time', 1)->save();
    sleep(2);
    $this->cronRun();
    $result = $query->execute()->fetchAll();
    $this->assertEqual(count($result), 0, ' Nodeviewcount statistics is deleted after cron run.');
  }

  /**
   * Test nodeviewcount js for nodes in full view mode.
   */
  public function testNodesWithFullViewMode() {
    $this->checkFullViewMode('anonymous', $this->firstTestTrackedNode, TRUE);
    $this->checkFullViewMode('anonymous', $this->testNotTrackedNode, FALSE);
    $this->checkFullViewMode('logged', $this->firstTestTrackedNode, TRUE);
    $this->checkFullViewMode('logged', $this->testNotTrackedNode, FALSE);
    $this->checkFullViewMode('administrator', $this->firstTestTrackedNode, FALSE);
    $this->checkFullViewMode('administrator', $this->testNotTrackedNode, FALSE);
  }

  /**
   * Check nodeviewcount settings on node view for full view mode.
   *
   * @param string $user_role
   *   User role to access node view page.
   * @param \Drupal\node\NodeInterface $node
   *   Node to access.
   * @param $expected_result
   *   Result that we expect. TRUE if nodeviewcount settings should be included
   *   on the page, FALSE otherwise.
   */
  protected function checkFullViewMode($user_role, \Drupal\node\NodeInterface $node, $expected_result) {
    $user_id = 0;
    // Create user and login if needed.
    if ($user_role !== 'anonymous') {
      $user = $this->createUserWithRole($user_role);
      $user_id = $user->id();
      $this->drupalLogin($user);
    }
    // Go to the node page.
    $this->drupalGet('node/' . $node->id());
    // Check nodeviewcount statistics script.
    if ($expected_result) {
      $this->assertRaw('modules/nodeviewcount/nodeviewcount.js', 'Nodeviewcount statistics library is included.');
      $settings = $this->getDrupalSettings();
      $expectedSettings = [
        $node->id() => [
          'nid' => $node->id(),
          'uid' => $user_id,
          'view_mode' => 'full',
        ],
      ];
      $this->assertEqual($expectedSettings, $settings['nodeviewcount']['data'], 'drupalSettings has right node information.');
    }
    else {
      $this->assertNoRaw('modules/nodeviewcount/nodeviewcount.js', 'Nodeviewcount statistics library is not included.');
    }
    // Logout if needed.
    if ($user_role !== 'anonymous') {
      $this->drupalLogout();
    }
  }

}
