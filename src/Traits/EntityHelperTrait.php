<?php

namespace Intracto\DrupalHelpers\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Trait EntityTranslationTrait.
 *
 * Provides helper functions to deal with entity value retrieval.
 *
 * @package Intraco\DrupalHelpers
 */
trait EntityHelperTrait {

  use EntityTranslationTrait;

  /**
   * Gets the entity field value.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to retrieve the field value.
   * @param string $field
   *   The name of the field to return.
   *
   * @return null|string
   *   The entity field value.
   */
  public function getEntityFieldValue(EntityInterface $entity, string $field) : ?string {
    if (!$firstItem = $this->getFirstEntityFieldItem($entity, $field)) {
      return NULL;
    }

    if (!$value = $firstItem->value) {
      return NULL;
    }

    return $value;
  }

  /**
   * Gets the entity field value.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to retrieve the field value.
   * @param string $field
   *   The name of the field to return.
   *
   * @return array
   *   The entity field values.
   */
  public function getEntityFieldValues(EntityInterface $entity, string $field) : array {
    if (!$list = $this->getEntityFieldList($entity, $field)) {
      return [];
    }

    $values = [];

    foreach ($list as $fieldItem) {
      if (!$value = $fieldItem->value) {
        continue;
      }

      $values[] = $value;
    }

    return $values;
  }

  /**
   * Returns the referenced entities from a entity_reference field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to retrieve the field value.
   * @param string $field
   *   The name of the field to return.
   * @param bool $translated
   *   Use the current entity langcode to return translated entities?
   * @param bool $removeUntranslated
   *   Skip a referenced entity that is not translated in the correct langcode.
   *
   * @return array
   */
  public function getReferencedEntitiesByField(EntityInterface $entity, string $field, bool $translated = TRUE, bool $removeUntranslated = FALSE) : array {
    if (!$list = $this->getEntityFieldList($entity, $field)) {
      return [];
    }

    $entities = [];

    foreach ($list as $fieldItem) {
      $referencedEntity = $fieldItem->entity;

      if (!$referencedEntity) {
        continue;
      }

      $entities[] = $referencedEntity;
    }

    if (!$translated) {
      return $entities;
    }

    $activeLangcode = $entity->language()->getId();
    return $this->translateEntities($entities, $activeLangcode, $removeUntranslated);
  }

  /**
   * Returns the referenced entities from a entity_reference field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to retrieve the field value.
   * @param string $field
   *   The name of the field to return.
   * @param bool $translated
   *   Use the current entity langcode to return translated entities?
   * @param bool $removeUntranslated
   *   Skip a referenced entity that is not translated in the correct langcode.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The referenced entity.
   */
  public function getReferencedEntityByField(EntityInterface $entity, string $field, bool $translated = TRUE, bool $removeUntranslated = FALSE) : ?EntityInterface {
    if (!$fieldItem = $this->getFirstEntityFieldItem($entity, $field)) {
      return NULL;
    }

    if (!$referencedEntity = $fieldItem->entity) {
      return NULL;
    }

    if (!$translated) {
      return $referencedEntity;
    }

    $activeLangcode = $entity->language()->getId();
    return $this->translateEntity($referencedEntity, $activeLangcode, $removeUntranslated);
  }

  /**
   * Checks if an entity has a field and it has data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to retrieve the field value.
   * @param string $field
   *   The name of the field to return.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   Whether or not entity has the field and has data for it.
   */
  public function getEntityFieldList(EntityInterface $entity, string $field) : ?FieldItemListInterface {
    if (!$entity instanceof FieldableEntityInterface) {
      return NULL;
    }

    if (!$entity->hasField($field)) {
      return NULL;
    }

    $field = $entity->get($field);

    if ($field->isEmpty()) {
      return NULL;
    }

    return $field;
  }

  /**
   * Returns the first item of a field list.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to retrieve the field item.
   * @param string $field
   *   The name of the field to return.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   *   The field.
   */
  public function getFirstEntityFieldItem(EntityInterface $entity, string $field) : ?TypedDataInterface {
    if (!$list = $this->getEntityFieldList($entity, $field)) {
      return NULL;
    }

    return $list->first();
  }
}