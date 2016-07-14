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

}
