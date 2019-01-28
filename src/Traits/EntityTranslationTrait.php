<?php

namespace Intracto\DrupalHelpers\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;

/**
 * Trait EntityTranslationTrait.
 *
 * Provides helper functions to deal with entity translations.
 *
 * @package Intraco\DrupalHelpers
 */
trait EntityTranslationTrait {

  /**
   * Checks if an entity has a translations and returns the translated version.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity that might contain a translation.
   * @param string $langcode
   *   The langcode in which to translate, leave empty to use current langcode.
   * @param bool $required
   *   If an entity is not translated, returns NULL if set to TRUE.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The translated entity or null.
   */
  public function translateEntity(EntityInterface $entity, string $langcode = NULL, bool $required = FALSE) : ?EntityInterface {
    if ($required) {
      $returnEntity = NULL;
    }
    else {
      $returnEntity = $entity;
    }

    if (!$langcode) {
      $langcode = \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    }

    if (!$entity instanceof TranslatableInterface) {
      return $returnEntity;
    }

    if (!$entity->hasTranslation($langcode)) {
      return $returnEntity;
    }

    return $entity->getTranslation($langcode);
  }

  /**
   * Loops over an array of entities and returns translated entities.
   *
   * @param array $entities
   *   An array of entities.
   * @param string $langcode
   *   The langcode in which to translate, leave empty to use current langcode.
   * @param bool $removeUntranslated
   *   Removes entities that are not translated.
   *
   * @return array
   *   An array of translated entities
   */
  public function translateEntities(array $entities, string $langcode = NULL, bool $removeUntranslated = FALSE) : array {
    // Get the langcode first so we don't have to fetch it in the loop.
    if (!$langcode) {
      $langcode = \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    }

    foreach ($entities as $key => &$entity) {
      if (!$entity instanceof EntityInterface) {
        continue;
      }

      $entity = $this->translateEntity($entity, $langcode, $removeUntranslated);

      // Unset an entity that is now a NULL.
      // It will only be a NULL if translation is required.
      // This is passed by the removeUntranslated variable.
      if (!$entity) {
        unset($entities[$key]);
      }
    }

    return $entities;
  }

}