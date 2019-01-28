<?php

namespace Intracto\DrupalHelpers;

use Intracto\DrupalHelpers\Traits\EntityHelperTrait;
use Intracto\DrupalHelpers\Traits\ParagraphHelperTrait;

/**
 * Class EntityTraitWrapper.
 *
 * This class includes the helper traits so we can use it inside modules.
 *
 * @package Intracto\DrupalHelpers
 */
class EntityTraitWrapper {

  use EntityHelperTrait;
  use ParagraphHelperTrait;

}