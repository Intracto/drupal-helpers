<?php

namespace Intracto\DrupalHelpers\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Trait ParagraphHelperTrait
 *
 * @package Intracto\DrupalHelpers\Traits
 */
trait ParagraphHelperTrait {

  /**
   * Gets the node parent.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity interface.
   */
  public function getNodeParent(ParagraphInterface $paragraph) : ?EntityInterface {
    return self::getParentOfType($paragraph, NodeInterface::class);
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to find a parent.
   * @param string $type
   *   The parent type to return.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity interface.
   */
  public function getParentOfType(EntityInterface $entity, string $type) : ?EntityInterface {
    if (!$entity instanceof ParagraphInterface) {
      return NULL;
    }

    $parent = $entity->getParentEntity();

    if ($parent instanceof $type) {
      return $parent;
    }

    if ($parent instanceof EntityInterface) {
      return self::getParentOfType($parent, $type);
    }

    return NULL;
  }

}