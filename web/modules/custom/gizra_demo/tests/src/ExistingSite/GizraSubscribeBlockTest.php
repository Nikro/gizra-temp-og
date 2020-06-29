<?php

namespace Drupal\Tests\gizra_demo\ExistingSite;

use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * A model test case using traits from Drupal Test Traits.
 */
class GizraSubscribeBlockTest extends ExistingSiteBase {

  /**
   * An example test method; note that Drupal API's and Mink are available.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testSubscribeBlock() {
    // Creates a user (to test against).
    $target_user = $this->createUser([], 'demo_user');

    // Create a demo school (2x)
    $node_a = $this->createNode([
      'title' => 'Spiru Haret',
      'type' => 'school',
      'uid' => '1',
    ]);
    $node_a->setPublished()->save();
    $node_b = $this->createNode([
      'title' => 'G. Asachi',
      'type' => 'school',
      'uid' => '1',
    ]);
    $node_b->setPublished()->save();

    // Navigate to node A, check the block - should be empty.
    $this->drupalGet($node_a->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Hi demo_user, click here if you would like to subscribe to this group called Spiru Haret');

    // Now we should see it - and submit it.
    $this->drupalLogin($target_user);
    $this->drupalGet($node_a->toUrl());
    $this->assertSession()->pageTextContains('Hi demo_user, click here if you would like to subscribe to this group called Spiru Haret');
    $this->clickLink('click here');
    $this->submitForm([], 'Request membership');

    // Now we should see the unsubscribe block.
    $this->drupalGet($node_a->toUrl());
    $this->assertSession()->pageTextContains('Hi demo_user, you are already a member (or pending). If you want to unsubscribe - click here.');
  }

}
