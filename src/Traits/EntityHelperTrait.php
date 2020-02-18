<?php

namespace Intracto\DrupalHelpers\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Url;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

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
    return $this->getEntityFieldProperty($entity, $field, 'value');
  }

  /**
   * Returns the url for an entity link field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to return a
   * @param string $field
   *   The link field item.
   *
   * @return \Drupal\Core\Url|null
   */
  public function getEntityLinkFieldUrl(EntityInterface $entity, string $field) : ?Url {
    $item = $this->getFirstEntityFieldItem($entity, $field);

    if (!$item instanceof LinkItem) {
      return NULL;
    }

    return $item->getUrl();
  }

  /**
   * Returns the first property of an entity field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $field
   *   The field for which to return a property.
   * @param string $property
   *   The property.
   *
   * @return mixed
   *   The value matching that property.
   */
  public function getEntityFieldProperty(EntityInterface $entity, string $field, string $property) {
    if (!$firstItem = $this->getFirstEntityFieldItem($entity, $field)) {
      return NULL;
    }

    if (!$value = $firstItem->{$property}) {
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
   * Returns the referenced entity label from a entity_reference field.
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
   * @return string|null
   *   The referenced entity label.
   */
  public function getReferencedEntityLabel(EntityInterface $entity, string $field, bool $translated = TRUE, bool $removeUntranslated = FALSE) : ?string {
    $entity = $this->getReferencedEntityByField($entity, $field, $translated, $removeUntranslated);

    if (!$entity) {
      return NULL;
    }

    return $entity->label();
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
   *   An array containing all entity labels.
   */
  public function getReferencedEntityLabels(EntityInterface $entity, string $field, bool $translated = TRUE, bool $removeUntranslated = FALSE) : array {
    $entities = $this->getReferencedEntitiesByField($entity, $field, $translated, $removeUntranslated);

    if (empty($entities)) {
      return [];
    }

    $labels = [];

    foreach ($entities as $entity) {
      $labels[$entity->id()] = $entity->label();
    }

    return $labels;
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
